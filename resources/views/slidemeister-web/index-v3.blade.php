<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Slidemeister-Viewer</title>

    @include('partymeister-slides::layouts.partials.slide_fonts')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; overflow: hidden; background: #000; }
    </style>
</head>

<body>
<div id="app"></div>

{{-- CABLES.gl WebGL patch - loaded before Vue app --}}
<script type="text/javascript" src="/cables/js/patch.js" async="true"></script>
<script>
    // Globals consumed by the Vue app
    window.TOKEN = '{{ $api_token }}';
    window.BASE_URL = '{{ config('app.url') }}';

    // CABLES.gl initialization
    document.addEventListener('CABLES.jsLoaded', function (event) {
        CABLES.patch = new CABLES.Patch({
            patch: CABLES.exportedPatch,
            "prefixAssetPath": "{{ config('partymeister-slides.cables_asset_prefix', '/cables/') }}",
            "assetPath": "/cables/assets/",
            "jsPath": "/cables/js/",
            "glCanvasId": "glcanvas",
            "glCanvasResizeToWindow": true,
            "onPatchLoaded": function() {
                if (typeof window.patchInitialized === 'function') {
                    window.patchInitialized();
                }
            },
            "onFinishedLoading": function() {
                if (typeof window.patchFinishedLoading === 'function') {
                    window.patchFinishedLoading();
                }
            },
            "canvas": {"alpha": true, "premultipliedAlpha": true}
        });
    });
</script>

{{-- Vue 3 app - production build or dev server --}}
@if(app()->environment('local') && file_exists(public_path('hot-slidemeister')))
    <script type="module" src="http://localhost:5173/@vite/client"></script>
    <script type="module" src="http://localhost:5173/main.ts"></script>
@else
    @php
        $manifest = json_decode(file_get_contents(public_path('slidemeister-web/.vite/manifest.json')), true);
        $entry = $manifest['main.ts'] ?? null;
    @endphp
    @if($entry)
        @if(isset($entry['css']))
            @foreach($entry['css'] as $css)
                <link rel="stylesheet" href="/slidemeister-web/{{ $css }}" />
            @endforeach
        @endif
        <script type="module" src="/slidemeister-web/{{ $entry['file'] }}"></script>
    @endif
@endif
</body>
</html>
