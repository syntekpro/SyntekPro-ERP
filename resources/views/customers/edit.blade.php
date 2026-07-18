@extends('layouts.hub')

@section('title', 'Edit Customer')

@section('content')
    <livewire:customers.form-page :customer="$customer" />
@endsection
