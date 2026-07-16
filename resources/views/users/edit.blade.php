@extends('layouts.hub')

@section('title', 'Edit User')

@section('content')
    <livewire:users.form-page :user="$user" />
@endsection