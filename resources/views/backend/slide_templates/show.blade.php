<html>
<head>
    @include('partymeister-slides::layouts.partials.slide_fonts')
    @if (strpos($_SERVER['HTTP_USER_AGENT'], 'AltonaHTTPClient') > 0)
        <link href="{{config('partymeister-slides.screens_url')}}{{ mix('/css/motor-backend.css') }}" rel="stylesheet" type="text/css"/>
    @else
        <link href="{{ mix('/css/motor-backend.css') }}" rel="stylesheet" type="text/css"/>
    @endif

    <style type="text/css">
        body {
            background: transparent !important;
        }
        .medium-editor-element {
            z-index: 10000;
            width: 98%;
            margin: 0 auto;
            text-align: left;
            font-family: Arial, sans-serif;
        }

        .medium-editor-element p {
            margin-bottom: 0;
        }

        .moveable {
            display: flex;
            font-family: "Roboto", sans-serif;
            z-index: 1000;
            position: absolute;
            width: 300px;
            height: 200px;
            text-align: center;
            font-size: 40px;
            margin: 0 auto;
            font-weight: 100;
            letter-spacing: 1px;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
        }

        .movable span {
            font-size: 10px;
        }

        @if ($preview !== 'true')
        div[data-partymeister-slides-visibility='preview'] {
            display: none;
        }
        @endif

        .snappable-shadow {
            display: none;
        }
    </style>
</head>
<body id="slidemeister-render" scroll="no" style="overflow: hidden">
<div id="slidemeister">
    @if ($record->cached_html_preview != '')
        @if ($preview == 'true')
            {!! $record->cached_html_preview !!}
        @else
            {!! str_replace('/media/',config('partymeister-slides.screens_url').'/media/', $record->cached_html_final) !!}
        @endif
    @endif
</div>
<script>
    window.addEventListener('load', (event) => {
        console.log('page is fully loaded');
    });
</script>
</body>
</html>
