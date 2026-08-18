<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
<channel>
    <title>{{ $empresa['name'] ?? 'Sommy' }} — Catálogo de productos</title>
    <link>{{ url('/') }}</link>
    <description>Colchones, sommiers, almohadas y blanquería directo de fábrica.</description>
    @foreach($items as $item)
    <item>
        <g:id>{{ $item['id'] }}</g:id>
        <g:title>{{ $item['title'] }}</g:title>
        <g:description>{{ $item['description'] }}</g:description>
        <g:link>{{ $item['link'] }}</g:link>
        <g:image_link>{{ $item['image'] }}</g:image_link>
        <g:price>{{ $item['price'] }}</g:price>
        <g:availability>{{ $item['stock'] > 0 ? 'in stock' : 'out of stock' }}</g:availability>
        <g:condition>new</g:condition>
        <g:brand>{{ $item['brand'] }}</g:brand>
        <g:identifier_exists>false</g:identifier_exists>
    </item>
    @endforeach
</channel>
</rss>
