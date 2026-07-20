@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <livewire:accounts.form-page :account="$account" />
@endsection
