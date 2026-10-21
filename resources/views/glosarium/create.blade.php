@extends('glosarium.layout')
@section('title', 'Tambah Istilah')

@section('content')
    <h1>Tambah Istilah</h1>
    <form method="POST" action="{{ route('glosarium.store') }}">
        @csrf
        @include('glosarium._form')
    </form>
@endsection