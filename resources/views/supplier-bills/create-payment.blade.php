@extends('layouts.hub')

@section('title', 'Record Supplier Payment')

@section('content')
    <livewire:supplier-bills.payment-form-page :supplierBill="$supplierBill" />
@endsection
