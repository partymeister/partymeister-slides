<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Slidemeister-Viewer</title>

    <link href="{{ mix('/css/partymeister-slidemeister-web.css') }}" rel="stylesheet" type="text/css"/>
    @include('partymeister-slides::layouts.partials.slide_fonts')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
<div id="app">
    <slidemeister-connector></slidemeister-connector>
    <slidemeister-viewer :standalone="false"></slidemeister-viewer>
</div>
<script type="text/javascript" src="/cables/js/patch.js" async="true"></script>
<script>
    const TOKEN = '{{$api_token}}';
    const BASE_URL = '{{config('app.url')}}'
</script>
<script src="{{mix('js/partymeister-slidemeister-web.js')}}"></script>
<script>
    document.addEventListener('CABLES.jsLoaded', function (event) {
        CABLES.patch = new CABLES.Patch({
            patch: CABLES.exportedPatch,
            "prefixAssetPath": "/wurst",
            "assetPath": "/cables/assets/",
            "jsPath": "/cables/js/",
            "glCanvasId": "glcanvas",
            "glCanvasResizeToWindow": true,
            "onPatchLoaded": patchInitialized,
            "onFinishedLoading": patchFinishedLoading,
            "canvas":{"alpha":true,"premultipliedAlpha":true} // make canvas transparent
        });
    });
</script>
</body>
</html>
