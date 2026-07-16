@extends('layouts.hub')

@section('title', 'Edit Warehouse')

@section('content')
    <livewire:warehouses.form-page :warehouse="$warehouse" />
@endsection