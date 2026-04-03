<html>
<head>
    @include('partymeister-slides::layouts.partials.slide_fonts')
    @php
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $cssFile = $manifest['packages/partymeister-slides/resources/assets/sass/partymeister-slide-renderer.scss']['file'] ?? null;
        $internalBase = rtrim(config('app.url_internal', config('app.url')), '/');
    @endphp
    @if($cssFile)
        <link rel="stylesheet" href="{{ $internalBase }}/build/{{ $cssFile }}" />
    @endif
    <style>
        body { background: transparent !important; margin: 0; overflow: hidden; }
        .moveable {
            display: flex;
            position: absolute;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
        }
        .medium-editor-element { width: 100%; }
        .medium-editor-element p { margin-bottom: 0; }
        #slidemeister { position: relative; width: 960px; height: 540px; overflow: hidden; transform-origin: top left; }
        /* Dashed selection borders from editor — hide in render */
        .snappable-shadow { display: none; }
    </style>
</head>
<body>
<div id="slidemeister"></div>
<script>
    const definitions = {!! $record->definitions !!};
    const MIN_FONT = 5;

    function resizeAndRender() {
        const container = document.getElementById('slidemeister');
        const measureDiv = document.createElement('div');
        measureDiv.style.cssText = 'position:absolute;left:-9999px;top:-9999px;width:960px;height:540px;overflow:hidden';
        document.body.appendChild(measureDiv);

        const elements = definitions.elements || definitions;
        const sortedElements = Object.values(elements).sort((a, b) => (b.properties?.zIndex || 0) - (a.properties?.zIndex || 0));

        for (const el of sortedElements) {
            const p = el.properties;
            if (!p) continue;
            const c = p.coordinates;

            // --- Text fitting: measure and shrink ---
            if (p.content && p.content.trim() !== '') {
                const testOuter = document.createElement('div');
                testOuter.style.cssText = `width:${c.width}px;height:${c.height}px;display:flex;align-items:${p.verticalAlign};position:absolute;overflow:hidden`;
                const testInner = document.createElement('div');
                testInner.style.cssText = `font-family:${p.fontFamily};font-size:${p.fontSize}px;font-kerning:${p.fontKerning};font-weight:${p.fontWeight};font-stretch:${p.fontStretch}%;font-style:${p.fontStyle};letter-spacing:${p.letterSpacing};line-height:${p.lineHeight};text-transform:${p.textTransform};width:100%`;
                testInner.innerHTML = p.content;
                testOuter.appendChild(testInner);
                measureDiv.appendChild(testOuter);

                let size = p.fontSize;
                while (size > MIN_FONT && testInner.scrollHeight > c.height) {
                    size--;
                    testInner.style.fontSize = size + 'px';
                }
                if (size > MIN_FONT && (c.height - testInner.scrollHeight) < 2) size--;
                p.calculatedFontSize = size + 'px';
                measureDiv.removeChild(testOuter);
            }

            // --- Build the element HTML ---
            const outer = document.createElement('div');
            outer.className = 'moveable';
            outer.style.cssText = `transform:${c.transform};width:${c.width}px;height:${c.height}px;z-index:${p.zIndex};background-color:${p.backgroundColor};opacity:${p.opacity};display:flex;align-items:${p.verticalAlign};position:absolute`;
            if (p.image) { outer.style.backgroundImage = `url(${p.image})`; outer.style.backgroundSize = 'cover'; outer.style.backgroundPosition = 'center'; }
            if (p.dataUrl) { outer.style.backgroundImage = `url(${p.dataUrl})`; outer.style.backgroundSize = 'cover'; outer.style.backgroundPosition = 'center'; }
            outer.setAttribute('data-partymeister-slides-visibility', p.visibility || '');

            const fontSize = p.calculatedFontSize || (p.fontSize + 'px');
            const inner = document.createElement('div');
            inner.className = 'medium-editor-element';
            inner.style.cssText = `font-family:${p.fontFamily};font-size:${fontSize};font-kerning:${p.fontKerning};font-weight:${p.fontWeight};font-stretch:${p.fontStretch}%;font-style:${p.fontStyle};letter-spacing:${p.letterSpacing};color:${p.color};text-align:${p.textAlign};line-height:${p.lineHeight};text-shadow:${p.textShadow};text-transform:${p.textTransform};width:100%`;
            inner.innerHTML = p.content || '';
            outer.appendChild(inner);
            container.appendChild(outer);
        }

        document.body.removeChild(measureDiv);
    }

    function scaleToFit() {
        const el = document.getElementById('slidemeister');
        const scale = window.innerWidth / 960;
        el.style.transform = `scale(${scale})`;
    }

    // Wait for fonts to load, then render and scale
    document.fonts.ready.then(() => {
        resizeAndRender();
        scaleToFit();
        window.addEventListener('resize', scaleToFit);
    });
</script>
</body>
</html>
