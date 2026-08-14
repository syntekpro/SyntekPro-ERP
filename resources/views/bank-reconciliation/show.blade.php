@extends('layouts.hub')

@section('title', __('Reconcile :bank', ['bank' => $bankAccount->bank_name]))

@section('content')
    <livewire:bank-reconciliation.workspace-page :bank-account="$bankAccount" />
@endsection
