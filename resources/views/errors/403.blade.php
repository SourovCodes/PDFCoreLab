@extends('errors.layout')

@section('title', '403 Forbidden')
@section('code', '403')
@section('message', 'Forbidden')
@section('description', $exception->getMessage() ?: 'You don\'t have permission to access this resource.')
