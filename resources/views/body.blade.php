@extends('layouts.argo')
@include('layouts.sidenav')

@section('content')
    <div class="row mt-4">
        {!! $form ?? '' !!}
    </div>
    <div class="row mt-4">
        {!! $grid ?? '' !!}
    </div>
@endsection
