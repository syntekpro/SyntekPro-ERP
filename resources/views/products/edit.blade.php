@extends('layouts.hub')

@section('title', 'Edit Product')

@section('content')
    <livewire:products.form-page :product="$product" />
@endsection