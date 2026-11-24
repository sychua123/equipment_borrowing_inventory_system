{{-- Author: Lai Chee Kit · Module 4: submit ticket form --}}
@extends('layouts.app')
@section('title', 'Submit a Ticket')

@section('content')
  <h1>Submit a Ticket</h1>

  {{-- enctype is needed so the photos are sent with the form --}}
  <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
    @csrf
    @include('tickets._form')
    <button type="submit">Submit ticket</button>
  </form>
@endsection
