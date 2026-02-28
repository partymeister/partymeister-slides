<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Slidemeister Editor</title>

    @include('partymeister-slides::layouts.partials.slide_fonts')

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; overflow: hidden; }
    </style>
</head>

<body>
<div id="app"></div>

<script>
    window.TOKEN = '{{ $api_token }}';
    window.BASE_URL = '{{ config('app.url') }}';
    window.TEMPLATE_ID = {{ $template_id ?? 'null' }};
</script>

@if(app()->environment('local') && file_exists(public_path('hot-slidemeister-editor')))
    <script type="module" src="http://localhost:5174/@vite/client"></script>
    <script type="module" src="http://localhost:5174/main.ts"></script>
@else
    @php
        $manifest = json_decode(file_get_contents(public_path('slidemeister-editor/.vite/manifest.json')), true);
        $entry = $manifest['main.ts'] ?? null;
    @endphp
    @if($entry)
        @if(isset($entry['css']))
            @foreach($entry['css'] as $css)
                <link rel="stylesheet" href="/slidemeister-editor/{{ $css }}" />
            @endforeach
        @endif
        <script type="module" src="/slidemeister-editor/{{ $entry['file'] }}"></script>
    @endif
@endif
</body>
</html>
