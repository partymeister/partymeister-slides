@section('view_styles')
    @include('partymeister-slides::layouts.partials.slide_fonts')
    <style type="text/css">
        .sortable-ghost {
            position: absolute;
            opacity: 0.7;
            top: 50px;
            left: 50px;
            width: 200px;
            /*top: 30%;*/
            /*left: 40%;*/
            /*max-width: 20%;*/
        }

        .sortable-ghost .file-description {
            display: none;
        }
    </style>
@append
@section('main-content')
    <div class="@boxWrapper box-primary" style="margin-bottom: 0;">
        <div class="@boxHeader">
            TOOLBAR
        </div>
    </div>
    <div id="slidemeister-wrapper">
        <div id="slidemeister-canvas">
            <div id="slidemeister-canvas-border">
            </div>
            <div id="slidemeister">
                <partymeister-slides-elements id="slide-editor" :name="'slide-editor'"></partymeister-slides-elements>
            </div>
            <partymeister-slides-dropzone></partymeister-slides-dropzone>
        </div>
    </div>
    <div class="loader loader-default"
         data-text="&hearts; {{ trans('partymeister-slides::backend/slides.generating') }} &hearts;"></div>
@endsection
@section ('right-sidebar')
    <ul class="slidemeister-navbar nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#slidemeister-form" role="tab">Properties</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#slidemeister-blocks" role="tab">Mediapool</a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="slidemeister-form" style="position: relative; height: 88vh; overflow: scroll;" role="tabpanel">
            <div class="container mt-3" style="position: absolute; top: 0;">
                {!! form_start($form, ['id' => 'slide-form']) !!}
                {!! form_row($form->name) !!}
                {!! form_row($form->category_id) !!}
                {!! form_row($form->slide_type) !!}
                {!! form_row($form->definitions) !!}
                {!! form_row($form->cached_html_preview) !!}
                {!! form_row($form->cached_html_final) !!}
                {!! form_row($form->image_data) !!}
                {!! form_row($form->submit) !!}
                {!! form_end($form, false) !!}
                <br>
                <partymeister-slides-controls :simple="true"></partymeister-slides-controls>
            </div>
        </div>
        <div class="tab-pane" id="slidemeister-blocks" style="position: relative; height: 88vh;" role="tabpanel">
            <motor-media-mediapool></motor-media-mediapool>
        </div>
    </div>
@endsection
@section('right-sidebar-header')<h5 class="pb-0 mb-0">Properties and Media</h5>@endsection

@section('view_scripts')
    <script>
        $(document).ready(function () {
            $('.slidemeister-save').on('click', function (e) {

                $('.loader').addClass('is-active');

                Vue.prototype.$eventHub.$on('partymeister-slides:receive-definitions', (data) => {
                    if (data.name === 'slide-editor') {
                        $('input[name="definitions"]').val(data.definitions_as_form_data);
                        $('input[name="cached_html_preview"]').val($('#slidemeister').html());
                        $('input[name="cached_html_final"]').val($('#slidemeister').html());
                        $('#slide-form').submit();
                    }
                });

                Vue.prototype.$eventHub.$emit('partymeister-slides:request-definitions', 'slide-editor');
                e.preventDefault();
            });

            if ($('input[name="definitions"]').val() != '') {
                Vue.prototype.$eventHub.$emit('partymeister-slides:load-definitions', {
                    name: 'slide-editor',
                    elements: JSON.parse($('input[name="definitions"]').val()),
                    replacements: {},
                    type: false,
                });
            }
        });

    </script>
@append
