<?php
/**
 * Admin deploy endpoint for shared hosting without SSH.
 *
 * Usage:
 *   https://new.top-institut.fr/deploy.php?token=XXX&cmd=composer
 *   https://new.top-institut.fr/deploy.php?token=XXX&cmd=migrate
 *   https://new.top-institut.fr/deploy.php?token=XXX&cmd=cache
 *   https://new.top-institut.fr/deploy.php?token=XXX&cmd=geo-import
 *   https://new.top-institut.fr/deploy.php?token=XXX&cmd=storage-link
 *   https://new.top-institut.fr/deploy.php?token=XXX&cmd=artisan&arg=route:list
 *
 * Set DEPLOY_TOKEN in .env before use.
 * Requires composer.phar at the project root for the "composer" command.
 */

set_time_limit(300);
ini_set('memory_limit', '512M');
header('Content-Type: text/plain; charset=utf-8');

$projectRoot = dirname(__DIR__);

// Minimal .env parser (no Laravel bootstrap needed for token check)
$envToken = null;
$envFile = $projectRoot.'/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (preg_match('/^\s*DEPLOY_TOKEN\s*=\s*(.*)$/', $line, $m)) {
            $envToken = trim($m[1], " \t\"'");
            break;
        }
    }
}

if (! $envToken) {
    http_response_code(500);
    exit("ERROR: DEPLOY_TOKEN not set in .env\n");
}

if (($_GET['token'] ?? '') !== $envToken) {
    http_response_code(403);
    exit("Forbidden\n");
}

$cmd = $_GET['cmd'] ?? '';
$arg = $_GET['arg'] ?? '';

echo "=== deploy.php — ".date('Y-m-d H:i:s')." ===\n";
echo "cmd: $cmd\n\n";

switch ($cmd) {
    case 'composer':
        runComposer($projectRoot);
        break;

    case 'migrate':
        runArtisan($projectRoot, 'migrate', ['--force' => true]);
        break;

    case 'cache':
        runArtisan($projectRoot, 'config:cache');
        runArtisan($projectRoot, 'route:cache');
        runArtisan($projectRoot, 'view:cache');
        break;

    case 'clear-cache':
        runArtisan($projectRoot, 'config:clear');
        runArtisan($projectRoot, 'route:clear');
        runArtisan($projectRoot, 'view:clear');
        runArtisan($projectRoot, 'cache:clear');
        break;

    case 'geo-import':
        runArtisan($projectRoot, 'geo:import');
        break;

    case 'storage-link':
        runArtisan($projectRoot, 'storage:link');
        break;

    case 'artisan':
        if (! $arg) exit("Missing ?arg=command\n");
        $parts = explode(' ', $arg);
        $command = array_shift($parts);
        runArtisan($projectRoot, $command, parseArtisanArgs($parts));
        break;

    case 'info':
        phpinfo();
        break;

    default:
        echo "Available commands:\n";
        echo "  ?cmd=composer       - run composer install --no-dev --optimize-autoloader\n";
        echo "  ?cmd=migrate        - run php artisan migrate --force\n";
        echo "  ?cmd=cache          - config + route + view cache\n";
        echo "  ?cmd=clear-cache    - clear config + route + view + cache\n";
        echo "  ?cmd=geo-import     - import departments + cities from geo.api.gouv.fr\n";
        echo "  ?cmd=storage-link   - create public/storage symlink\n";
        echo "  ?cmd=artisan&arg=<command> - run any artisan command\n";
        echo "  ?cmd=info           - phpinfo()\n";
}

echo "\n=== done ".date('Y-m-d H:i:s')." ===\n";

// ---

function runComposer(string $root): void
{
    $phar = $root.'/composer.phar';
    if (! is_file($phar)) {
        exit("ERROR: composer.phar not found at {$phar}\nUpload it via FTP first.\n");
    }

    putenv('COMPOSER_HOME='.$root.'/.composer');
    putenv('COMPOSER_ALLOW_SUPERUSER=1');

    // Boot composer from the phar
    require_once 'phar://'.$phar.'/vendor/autoload.php';

    $input = new \Symfony\Component\Console\Input\ArrayInput([
        'command' => 'install',
        '--no-dev' => true,
        '--optimize-autoloader' => true,
        '--no-interaction' => true,
        '--no-progress' => true,
        '--working-dir' => $root,
    ]);
    $output = new \Symfony\Component\Console\Output\BufferedOutput();

    $app = new \Composer\Console\Application();
    $app->setAutoExit(false);
    $exit = $app->run($input, $output);

    echo $output->fetch();
    echo "\nexit code: $exit\n";
}

function runArtisan(string $root, string $command, array $params = []): void
{
    if (! is_file($root.'/vendor/autoload.php')) {
        exit("ERROR: vendor/autoload.php missing — run ?cmd=composer first\n");
    }

    require_once $root.'/vendor/autoload.php';
    $app = require_once $root.'/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

    $exit = $kernel->call($command, $params);
    echo $kernel->output();
    echo "\nexit code: $exit\n";
}

function parseArtisanArgs(array $parts): array
{
    $out = [];
    foreach ($parts as $p) {
        if (str_starts_with($p, '--')) {
            [$k, $v] = array_pad(explode('=', substr($p, 2), 2), 2, true);
            $out['--'.$k] = $v;
        } else {
            $out[] = $p;
        }
    }

    return $out;
}
