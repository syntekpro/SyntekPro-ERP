@extends('layouts.hub')

@section('title', 'Edit Price Category')

@section('content')
    <livewire:price-categories.form-page :price-category="$priceCategory" />
@endsection