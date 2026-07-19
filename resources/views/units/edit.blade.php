@extends('layouts.hub')

@section('title', 'Edit Unit')

@section('content')
    <livewire:units.form-page :unit="$unit" />
@endsection