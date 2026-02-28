<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Slidemeister Generator</title>

    @include('partymeister-slides::layouts.partials.slide_fonts')

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
    </style>
</head>

<body>
<div id="app"></div>

<script>
    window.TOKEN = '{{ $api_token }}';
    window.BASE_URL = '{{ config('app.url') }}';
    window.COMPETITION_ID = {{ $competition_id }};
</script>

@if(app()->environment('local') && file_exists(public_path('hot-slidemeister-generator')))
    <script type="module" src="http://localhost:5175/@vite/client"></script>
    <script type="module" src="http://localhost:5175/main.ts"></script>
@else
    @php
        $manifest = json_decode(file_get_contents(public_path('build/slidemeister-generator/.vite/manifest.json')), true);
        $entry = $manifest['main.ts'] ?? $manifest['resources/assets/js/slidemeister-generator/main.ts'] ?? null;
    @endphp
    @if($entry)
        @if(isset($entry['css']))
            @foreach($entry['css'] as $css)
                <link rel="stylesheet" href="/build/slidemeister-generator/{{ $css }}" />
            @endforeach
        @endif
        <script type="module" src="/build/slidemeister-generator/{{ $entry['file'] }}"></script>
    @endif
@endif
</body>
</html>
