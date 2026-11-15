{{-- Author: Lai Chee Kit · Module 4: ticket list with filters --}}
@extends('layouts.app')
@section('title', 'Tickets')

@section('content')
  <h1>{{ auth()->user()->isAdmin() ? 'All Tickets' : 'My Tickets' }}</h1>

  @if ($errors->any())
    <p class="error">Some filter values were not valid, so all tickets are shown.</p>
  @endif

  {{-- Filters use GET, so the choices stay in the URL --}}
  <form method="GET" action="{{ route('tickets.index') }}" class="filters">
    <label>Search
      <input name="q" maxlength="100" value="{{ $filters['q'] ?? '' }}" placeholder="Title or description">
    </label>
    <label>Status
      <select name="status">
        <option value="">Any</option>
        @foreach (array_keys(\App\Models\Ticket::STATES) as $status)
          <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
        @endforeach
      </select>
    </label>
    <label>Category
      <select name="category">
        <option value="">Any</option>
        @foreach (\App\Models\Ticket::CATEGORIES as $category)
          <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
        @endforeach
      </select>
    </label>
    <label>Priority
      <select name="priority">
        <option value="">Any</option>
        @foreach (\App\Models\Ticket::PRIORITIES as $priority)
          <option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ $priority }}</option>
        @endforeach
      </select>
    </label>
    <label>Sort by
      <select name="sort">
        <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest first</option>
        <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest first</option>
        <option value="updated" @selected(($filters['sort'] ?? '') === 'updated')>Recently updated</option>
      </select>
    </label>
    <button type="submit">Apply</button>
    <a href="{{ route('tickets.index') }}">Clear</a>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Category</th>
          <th>Equipment</th>
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
            <td>{{ $ticket->equipment_id ? $equipment->label($ticket->equipment_id) : '-' }}</td>
            <td>{{ $ticket->priority }}</td>
            <td><span class="badge">{{ $ticket->status }}</span></td>
            @if (auth()->user()->isAdmin()) <td>{{ $ticket->user->name }}</td> @endif
            <td>{{ $ticket->created_at->format('d M Y, H:i') }}</td>
          </tr>
        @empty
          <tr><td colspan="8">No tickets found. <a href="{{ route('tickets.create') }}">Submit a ticket.</a></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Previous / next page links (10 tickets per page) --}}
  <p>
    @if ($tickets->previousPageUrl()) <a href="{{ $tickets->previousPageUrl() }}">← Previous page</a> @endif
    @if ($tickets->hasMorePages()) <a href="{{ $tickets->nextPageUrl() }}">Next page →</a> @endif
  </p>
@endsection
