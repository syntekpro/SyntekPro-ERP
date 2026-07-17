@extends('layouts.hub')

@section('title', 'Edit Account')

@section('content')
    <livewire:accounts.form-page :account="$account" />
@endsection
