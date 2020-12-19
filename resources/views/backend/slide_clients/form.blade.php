{!! form_start($form) !!}
<div class="@boxWrapper box-primary">
    <div class="@boxHeader with-border">
        <h3 class="box-title">{{ trans('motor-backend::backend/global.base_info') }}</h3>
    </div>
    <div class="@boxBody">
        {!! form_row($form->name) !!}
        {!! form_row($form->type) !!}
        {!! form_row($form->ip_address) !!}
        {!! form_row($form->port) !!}
        {!! form_row($form->sort_position) !!}

        <div class="slidemeister-web d-none">
            <div class="row">
                <div class="col-md-3">
                    {!! form_row($form->jingle_1) !!}
                </div>
                <div class="col-md-3">
                    {!! form_row($form->jingle_2) !!}
                </div>
                <div class="col-md-3">
                    {!! form_row($form->jingle_3) !!}
                </div>
                <div class="col-md-3">
                    {!! form_row($form->jingle_4) !!}
                </div>
            </div>
            {!! form_row($form->configuration) !!}
        </div>
    </div>
    <!-- /.box-body -->

    <div class="@boxFooter">
        {!! form_row($form->submit) !!}
    </div>
</div>
{!! form_end($form) !!}
@section ('right-sidebar')
    <motor-media-mediapool></motor-media-mediapool>
@endsection
@section('right-sidebar-header')<h5 class="pb-0 mb-0">Media</h5>@endsection
@section('view_scripts')
    <link type="text/css" rel="stylesheet" href="https://rawgit.com/patriciogonzalezvivo/glslEditor/gh-pages/build/glslEditor.css">
    <script type="application/javascript" src="https://rawgit.com/patriciogonzalezvivo/glslEditor/gh-pages/build/glslEditor.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#type').change(function(){
                let value = $(this).find(":selected").val();
                if (value === 'slidemeister-web') {
                    $('.slidemeister-web').removeClass('d-none');
                } else {
                    $('.slidemeister-web').addClass('d-none');
                }
            });

            $('#type').change();
        });
    </script>
@append
