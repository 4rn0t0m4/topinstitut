@props(['items'])

@php
$list = [];
foreach ($items as $i => $item) {
    $entry = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'name' => $item['name'],
    ];
    if (isset($item['url'])) {
        $entry['item'] = url($item['url']);
    }
    $list[] = $entry;
}

$schema = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $list,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

<script type="application/ld+json">
{!! $schema !!}
</script>
