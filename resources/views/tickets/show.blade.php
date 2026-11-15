{{-- Author: Lai Chee Kit · Module 4: ticket detail --}}
@extends('layouts.app')
@section('title', 'Ticket #' . $ticket->id)

@section('content')
  <p><a href="{{ route('tickets.index') }}">← Back to tickets</a></p>
  <h1>Ticket #{{ $ticket->id }}: {{ $ticket->title }}</h1>

  <dl>
    <dt>Status</dt>       <dd><span class="badge">{{ $ticket->status }}</span></dd>
    <dt>Category</dt>     <dd>{{ $ticket->category }}</dd>
    <dt>Priority</dt>     <dd>{{ $ticket->priority }}</dd>
    <dt>Equipment</dt>
    <dd>
      @if ($equipment)
        {{ $equipment['asset_tag'] }} · {{ $equipment['name'] }}
      @elseif ($ticket->equipment_id)
        Equipment #{{ $ticket->equipment_id }} (details unavailable)
      @else
        None
      @endif
    </dd>
    <dt>Submitted by</dt> <dd>{{ $ticket->user->name }}</dd>
    <dt>Submitted</dt>    <dd>{{ $ticket->created_at->format('d M Y, H:i') }}</dd>
    <dt>Description</dt>  <dd class="desc">{{ $ticket->description }}</dd>
  </dl>
@endsection
