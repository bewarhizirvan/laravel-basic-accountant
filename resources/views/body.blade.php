@extends('layouts.argo')
@include('layouts.sidenav')
@include('layouts.navbar')

@section('content')
    <div class="row mt-4">
        {!! $form ?? '' !!}
    </div>
    <div class="row mt-4">
        {!! $grid ?? '' !!}
    </div>
@endsection
