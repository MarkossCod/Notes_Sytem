@extends('layout.app')

@section('content')

<div class="form-container">

<h1>Nova Nota</h1>

<form action="{{ route('notes.store') }}" method="POST">

@csrf

<input type="text"
name="title"
placeholder="Título da Nota"
required>

<input type="date"
name="created_day"
required>

<button type="submit">
Criar Nota
</button>

</form>

</div>

@endsection