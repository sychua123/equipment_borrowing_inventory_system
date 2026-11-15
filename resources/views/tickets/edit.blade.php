{{-- Author: Lai Chee Kit · Module 4: edit ticket form (only while the ticket is Open) --}}
@extends('layouts.app')
@section('title', 'Edit Ticket #' . $ticket->id)

@section('content')
  <p><a href="{{ route('tickets.show', $ticket) }}">← Back to the ticket</a></p>
  <h1>Edit Ticket #{{ $ticket->id }}</h1>

  <form method="POST" action="{{ route('tickets.update', $ticket) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('tickets._form')
    <button type="submit">Save changes</button>
  </form>
@endsection
