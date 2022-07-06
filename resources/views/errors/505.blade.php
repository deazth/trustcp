@extends('errors.layout')

@php
  $error_number = 505;
@endphp

@section('title')
  Page under construction.
@endsection

@section('description')
  @php
    $default_error_message = "This page is still being constructed. Please dont come again until next code deployment.";
  @endphp
  {!! isset($exception)? ($exception->getMessage()?$exception->getMessage():$default_error_message): $default_error_message !!}
@endsection
