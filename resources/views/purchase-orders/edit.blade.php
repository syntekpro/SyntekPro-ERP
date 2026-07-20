@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <livewire:purchase-orders.form-page :purchaseOrder="$purchaseOrder" />
@endsection
