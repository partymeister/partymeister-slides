<script type="module">
    @foreach(config('partymeister-slides-fonts.fonts') as $index => $font)
    slidemeisterProperties.fontFamily.options["{!! $font['family'] !!}"] = "{!! $font['family'] !!}";
    @endforeach
</script>
