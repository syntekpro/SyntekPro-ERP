@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <livewire:supplier-bills.payment-form-page :supplierBill="$supplierBill" />
@endsection
