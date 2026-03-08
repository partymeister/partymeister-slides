@extends('motor-backend::layouts.backend')

@section('htmlheader_title')
    {{ trans('motor-backend::backend/global.home') }}
@endsection

@section('contentheader_title')
    {{ trans('partymeister-slides::backend/playlists.playlists') }}
    @if (has_permission('playlists.write'))
        {!! link_to_route('backend.playlists.create', trans('partymeister-slides::backend/playlists.new'), [], ['class' => 'float-right btn btn-sm btn-success']) !!}
        @include('partymeister-slides::layouts.partials.slide_clients_controls')
    @endif
@endsection

@section('main-content')
    <div class="@boxWrapper">
        <div class="@boxHeader">
            @include('motor-backend::layouts.partials.search')
        </div>
        <!-- /.box-header -->
        @if (isset($grid))
            @include('motor-backend::grid.table')
        @endif
    </div>
@endsection

@include('partymeister-slides::layouts.partials.slide_clients_scripts')
@section('view_scripts')
    <script type="module">
        $('.delete-record').click(function (e) {
            if (!confirm('{{ trans('motor-backend::backend/global.delete_question') }}')) {
                e.preventDefault();
                return false;
            }
        });

        updatePlaylists();
        setInterval(function(){ updatePlaylists(); }, 30000);
    </script>
@append