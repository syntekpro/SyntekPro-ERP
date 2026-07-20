@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <livewire:shops.form-page :shop="$shop" />
@endsection