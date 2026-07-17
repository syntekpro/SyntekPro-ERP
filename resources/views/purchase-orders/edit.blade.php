@extends('layouts.hub')

@section('title', 'Edit Purchase Order')

@section('content')
    <livewire:purchase-orders.form-page :purchaseOrder="$purchaseOrder" />
@endsection
