@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <livewire:suppliers.form-page :supplier="$supplier" />
@endsection
