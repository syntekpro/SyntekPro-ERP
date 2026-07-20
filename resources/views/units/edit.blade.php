@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <livewire:units.form-page :unit="$unit" />
@endsection