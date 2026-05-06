<?php
/**
 * Admin deploy endpoint for shared hosting without SSH.
 * Kept PHP 7.4-compatible in case composer hasn't run yet and the host's CLI PHP is older.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
@set_time_limit(300);
@ini_set('memory_limit', '512M');
header('Content-Type: text/plain; charset=utf-8');

echo "=== deploy.php — ".date('Y-m-d H:i:s')." ===\n";
echo "PHP version: ".PHP_VERSION."\n";
echo "Project root: ".dirname(__DIR__)."\n\n";

$projectRoot = dirname(__DIR__);

// Minimal .env parser (no Laravel bootstrap needed for token check)
$envToken = null;
$envFile = $projectRoot.'/.env';
if (! is_readable($envFile)) {
    echo "ERROR: .env not readable at {$envFile}\n";
    exit;
}
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (preg_match('/^\s*DEPLOY_TOKEN\s*=\s*(.*)$/', $line, $m)) {
        $envToken = trim($m[1], " \t\"'");
        break;
    }
}

if (! $envToken) {
    echo "ERROR: DEPLOY_TOKEN not set in .env\n";
    echo "Add this line to your .env file:\n";
    echo "DEPLOY_TOKEN=yourSecretToken\n";
    exit;
}

$submittedToken = isset($_GET['token']) ? $_GET['token'] : '';
if ($submittedToken !== $envToken) {
    http_response_code(403);
    echo "Forbidden: token mismatch\n";
    exit;
}

$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : '';
$arg = isset($_GET['arg']) ? $_GET['arg'] : '';
echo "cmd: $cmd\n";
echo "arg: $arg\n\n";

try {
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
            if (! $arg) {
                echo "Missing ?arg=command\n";
                exit;
            }
            $parts = explode(' ', $arg);
            $command = array_shift($parts);
            runArtisan($projectRoot, $command, parseArtisanArgs($parts));
            break;

        case 'info':
            phpinfo();
            break;

        case 'log':
            $lines = isset($_GET['lines']) ? max(1, min(500, (int) $_GET['lines'])) : 100;
            tailLog($projectRoot.'/storage/logs/cron.log', $lines);
            break;

        case 'test-email':
            $url = isset($_GET['url']) ? $_GET['url'] : '';
            if (! $url) {
                echo "Missing ?url=\n";
                exit;
            }
            testEmailScraper($projectRoot, $url);
            break;

        case '':
        default:
            echo "Available commands:\n";
            echo "  ?cmd=composer       - run composer install --no-dev --optimize-autoloader\n";
            echo "  ?cmd=migrate        - php artisan migrate --force\n";
            echo "  ?cmd=cache          - config + route + view cache\n";
            echo "  ?cmd=clear-cache    - clear all caches\n";
            echo "  ?cmd=geo-import     - import departments + cities\n";
            echo "  ?cmd=storage-link   - create public/storage symlink\n";
            echo "  ?cmd=artisan&arg=<command>\n";
            echo "  ?cmd=log[&lines=N]  - tail storage/logs/cron.log (default 100 lines)\n";
            echo "  ?cmd=test-email&url=https://...  - test email scraping on a single URL\n";
            echo "  ?cmd=info           - phpinfo()\n";
    }
} catch (Throwable $e) {
    echo "\nEXCEPTION: ".get_class($e)."\n";
    echo $e->getMessage()."\n";
    echo "at ".$e->getFile().':'.$e->getLine()."\n";
    echo $e->getTraceAsString()."\n";
}

echo "\n=== done ".date('Y-m-d H:i:s')." ===\n";

// ---

function runComposer($root)
{
    $phar = $root.'/composer.phar';
    if (! is_file($phar)) {
        echo "ERROR: composer.phar not found at {$phar}\n";
        echo "Upload it via FTP first (download from https://getcomposer.org/composer.phar).\n";

        return;
    }

    putenv('COMPOSER_HOME='.$root.'/.composer');
    putenv('COMPOSER_ALLOW_SUPERUSER=1');

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

function runArtisan($root, $command, array $params = [])
{
    static $kernel = null;

    if ($kernel === null) {
        if (! is_file($root.'/vendor/autoload.php')) {
            echo "ERROR: vendor/autoload.php missing — run ?cmd=composer first\n";

            return;
        }

        require_once $root.'/vendor/autoload.php';
        // require (not require_once) : bootstrap/app.php returns $app and we need that return value ;
        // require_once returns true on subsequent calls, but static $kernel guards against re-entry anyway.
        $app = require $root.'/bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    }

    $exit = $kernel->call($command, $params);
    echo $kernel->output();
    echo "\nexit code: $exit\n";
}

function testEmailScraper($root, $url)
{
    if (! is_file($root.'/vendor/autoload.php')) {
        echo "ERROR: vendor/autoload.php missing\n";
        return;
    }

    require_once $root.'/vendor/autoload.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    echo "URL testée : {$url}\n";
    $start = microtime(true);

    $scraper = new \App\Services\EmailScraperService();
    $email = $scraper->findEmail($url);

    $elapsed = round((microtime(true) - $start) * 1000);
    echo "Durée : {$elapsed} ms\n";
    echo 'Email trouvé : '.($email ?: '(aucun)')."\n";
}

function tailLog($path, $lines)
{
    if (! is_file($path)) {
        echo "Log file not found: {$path}\n";

        return;
    }
    echo "=== tail -n {$lines} {$path} ===\n\n";
    $handle = fopen($path, 'r');
    if (! $handle) {
        echo "Cannot open log\n";

        return;
    }
    $buffer = [];
    while (($line = fgets($handle)) !== false) {
        $buffer[] = $line;
        if (count($buffer) > $lines) {
            array_shift($buffer);
        }
    }
    fclose($handle);
    echo implode('', $buffer);
}

function parseArtisanArgs(array $parts)
{
    $out = [];
    foreach ($parts as $p) {
        if (substr($p, 0, 2) === '--') {
            $pair = explode('=', substr($p, 2), 2);
            $k = $pair[0];
            $v = isset($pair[1]) ? $pair[1] : true;
            $out['--'.$k] = $v;
        } else {
            $out[] = $p;
        }
    }

    return $out;
}
