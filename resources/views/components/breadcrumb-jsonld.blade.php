@props(['items'])

@php
$list = [];
foreach ($items as $i => $item) {
    $list[] = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'name' => $item['name'],
        'item' => isset($item['url']) ? url($item['url']) : null,
    ];
}
// Remove null item from last element (current page)
if (isset($list[count($list) - 1])) {
    unset($list[count($list) - 1]['item']);
}
@endphp

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $list,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
