{{-- Author: Lai Chee Kit · TEMPORARY stand-in for Module 1's login (local machine only) --}}
@extends('layouts.app')
@section('title', 'Choose a test account')

@section('content')
  <h1>Choose a test account</h1>
  <p class="hint">Temporary page. Module 1's real login replaces it later.</p>

  @forelse ($users as $user)
    <form method="POST" action="{{ route('dev.login', $user) }}" style="margin: 10px 0">
      @csrf
      <button type="submit">{{ $user->name }} ({{ ucfirst($user->role) }})</button>
    </form>
  @empty
    <p>No users yet. Run <code>php artisan migrate --seed</code> first.</p>
  @endforelse
@endsection
