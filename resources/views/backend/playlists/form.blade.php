{!! form_start($form) !!}
<div class="@boxWrapper box-primary">
    <div class="@boxHeader with-border">
        <h3 class="box-title">{{ trans('motor-backend::backend/global.base_info') }}</h3>
    </div>
    <div class="@boxBody">
        {!! form_row($form->name) !!}
        {!! form_row($form->type) !!}
    </div>
    <!-- /.box-body -->
</div>

<div class="@boxWrapper box-primary">
    <div class="@boxHeader with-border">
        <h3 class="box-title">{{ trans('partymeister-slides::backend/playlists.items_info') }}</h3>
    </div>
    <div class="@boxBody">
        <partymeister-slides-playlist :files="{{ json_encode($playlistItems) }}"></partymeister-slides-playlist>
        {!! form_row($form->playlist_items) !!}
    </div>
    <!-- /.box-body -->
    <div class="@boxFooter">
        {!! form_row($form->submit) !!}
    </div>
</div>
{!! form_end($form) !!}

@section ('right-sidebar')
    <ul class="slidemeister-navbar nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#partymeister-slides" role="tab">Slides</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#motor-mediapool" role="tab">Mediapool</a>
        </li>
    </ul>
    <div class="tab-content" style="position: relative; height: 88vh; overflow: scroll;">
        <partymeister-slides-mediapool :preview-image="'{{url('/images/generating-preview.png')}}'"></partymeister-slides-mediapool>
        <div class="tab-pane" id="motor-mediapool" role="tabpanel">
            <motor-media-mediapool></motor-media-mediapool>
        </div>
    </div>
@endsection
@section('right-sidebar-header')<h5 class="pb-0 mb-0">Slides and Media</h5>@endsection
@section('view_scripts')
    <script>

        $('.playlist-submit').on('click', function (e) {
            e.preventDefault();
            $('#playlist-items').val($('input#dropped-files').val());
            $(this).closest('form').submit();
        });
    </script>
@append
