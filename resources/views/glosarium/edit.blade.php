@extends('glosarium.layout')
@section('title', 'Edit Istilah')

@section('content')
    <h1>Edit Istilah</h1>
    <form method="POST" action="{{ route('glosarium.update', $glosarium) }}">
        @csrf
        @method('PUT')
        @include('glosarium._form')
    </form>
@endsection