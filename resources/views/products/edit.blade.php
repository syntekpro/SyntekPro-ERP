@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <livewire:products.form-page :product="$product" />
@endsection