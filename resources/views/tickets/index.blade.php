{{-- Author: Lai Chee Kit · Module 4: ticket list --}}
@extends('layouts.app')
@section('title', 'Tickets')

@section('content')
  <h1>{{ auth()->user()->isAdmin() ? 'All Tickets' : 'My Tickets' }}</h1>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Category</th>
          <th>Priority</th>
          <th>Status</th>
          @if (auth()->user()->isAdmin()) <th>Submitted by</th> @endif
          <th>Submitted</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($tickets as $ticket)
          <tr>
            <td>{{ $ticket->id }}</td>
            <td><a href="{{ route('tickets.show', $ticket) }}">{{ $ticket->title }}</a></td>
            <td>{{ $ticket->category }}</td>
            <td>{{ $ticket->priority }}</td>
            <td><span class="badge">{{ $ticket->status }}</span></td>
            @if (auth()->user()->isAdmin()) <td>{{ $ticket->user->name }}</td> @endif
            <td>{{ $ticket->created_at->format('d M Y, H:i') }}</td>
          </tr>
        @empty
          <tr><td colspan="7">No tickets yet. <a href="{{ route('tickets.create') }}">Submit the first one.</a></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Previous / next page links (10 tickets per page) --}}
  <p>
    @if ($tickets->previousPageUrl()) <a href="{{ $tickets->previousPageUrl() }}">← Newer</a> @endif
    @if ($tickets->hasMorePages()) <a href="{{ $tickets->nextPageUrl() }}">Older →</a> @endif
  </p>
@endsection
