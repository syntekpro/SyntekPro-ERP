@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <livewire:customers.form-page :customer="$customer" />
@endsection
