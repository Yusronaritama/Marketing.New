@extends('errors::layout')

@section('title', 'Too Many Requests')
@section('code', 429)
@section('message', 'Too Many Requests')
@section('description', 'You have made too many requests. Please try again later.')
