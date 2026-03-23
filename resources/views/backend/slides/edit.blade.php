@extends('motor-admin::layouts.backend')

@section('htmlheader_title')
    {{ trans('motor-admin::backend/global.home') }}
@endsection

@section('contentheader_title')
    {{ trans('partymeister-slides::backend/slides.edit') }}
    {!! link_to_route('backend.slides.index', trans('motor-admin::backend/global.back'), [], ['class' => 'pull-right float-right btn btn-sm btn-danger']) !!}
@endsection

@section('main-content')
	@include('motor-admin::errors.list')
	@include('partymeister-slides::backend.slides.form')
@endsection