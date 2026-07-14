@extends('layout.app')

@section('content')
    @include('notes.editor', ['note' => null])
@endsection
