@extends('layouts.hub')

@section('title', 'Edit Shop')

@section('content')
    <livewire:shops.form-page :shop="$shop" />
@endsection