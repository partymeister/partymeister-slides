<html>
<head>
    @include('partymeister-slides::layouts.partials.slide_fonts')
    {{-- Slim CSS: bootstrap reboot + animate.css + slidemeister rules (~88KB vs 466KB) --}}
    {{-- Load via both APP_URL (browser) and APP_URL_INTERNAL (screenshot containers) --}}
    @php
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $cssFile = $manifest['packages/partymeister-slides/resources/assets/sass/partymeister-slide-renderer.scss']['file'] ?? null;
    @endphp
    @if($cssFile)
        <link rel="stylesheet" href="/build/{{ $cssFile }}" />
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
            @php
                // Only prefix relative /media/ paths — skip those already containing a scheme (http/https)
                $screensUrl = config('partymeister-slides.screens_url');
                $html = preg_replace(
                    '#url\((["\']?)(?!https?://)(/media/)#',
                    'url($1' . $screensUrl . '$2',
                    $record->cached_html_final
                );
            @endphp
            {!! $html !!}
        @endif
    @endif
</div>
<script type="module">
    window.addEventListener('load', (event) => {
        console.log('page is fully loaded');
    });
</script>
</body>
</html>
