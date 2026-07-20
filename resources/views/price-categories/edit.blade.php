@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <livewire:price-categories.form-page :price-category="$priceCategory" />
@endsection