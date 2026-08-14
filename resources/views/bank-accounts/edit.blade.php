@extends('layouts.hub')

@section('title', __('Edit Bank Account'))

@section('content')
    <livewire:bank-accounts.form-page :bank-account="$bankAccount" />
@endsection
