@extends('layouts.hub')

@section('title', 'Edit Supplier')

@section('content')
    <livewire:suppliers.form-page :supplier="$supplier" />
@endsection
