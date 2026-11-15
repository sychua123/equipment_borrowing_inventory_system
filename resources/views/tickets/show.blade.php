{{-- Author: Lai Chee Kit · Module 4: ticket detail, status buttons and replies --}}
@extends('layouts.app')
@section('title', 'Ticket #' . $ticket->id)

@php
  // Button text for each State Pattern action
  $actionLabels = [
      'start' => 'Start working on it',
      'resolve' => 'Mark as resolved',
      'close' => 'Confirm solved and close',
      'reopen' => 'Not solved, reopen',
      'withdraw' => 'Withdraw ticket',
  ];
@endphp

@section('content')
  <p><a href="{{ route('tickets.index') }}">← Back to tickets</a></p>
  <h1>Ticket #{{ $ticket->id }}: {{ $ticket->title }}</h1>

  <dl>
    <dt>Status</dt>       <dd><span class="badge">{{ $ticket->status }}</span></dd>
    <dt>Category</dt>     <dd>{{ $ticket->category }}</dd>
    <dt>Priority</dt>     <dd>{{ $ticket->priority }}</dd>
    <dt>Equipment</dt>    <dd>{{ $equipmentLabel }}</dd>
    <dt>Submitted by</dt> <dd>{{ $ticket->user->name }}</dd>
    <dt>Submitted</dt>    <dd>{{ $ticket->created_at->format('d M Y, H:i') }}</dd>
    @if ($ticket->resolved_at)
      <dt>Resolved</dt>   <dd>{{ $ticket->resolved_at->format('d M Y, H:i') }}</dd>
    @endif
    @if ($ticket->closed_at)
      <dt>Closed</dt>     <dd>{{ $ticket->closed_at->format('d M Y, H:i') }}</dd>
    @endif
    <dt>Description</dt>  <dd class="desc">{{ $ticket->description }}</dd>
  </dl>

  @if ($ticket->photos->isNotEmpty())
    <div class="photos">
      @foreach ($ticket->photos as $photo)
        <a href="{{ route('attachments.show', $photo) }}" target="_blank" rel="noopener">
          <img src="{{ route('attachments.show', $photo) }}" alt="{{ $photo->original_name }}">
        </a>
      @endforeach
    </div>
  @endif

  @if ($ticket->status === 'Resolved' && $ticket->user_id === auth()->id())
    <p class="hint">Is the problem solved? This ticket closes by itself {{ config('module4.auto_close_days') }} days after it was resolved.</p>
  @endif

  {{-- Buttons: only the actions the current state allows AND this user may do --}}
  <p>
    @can('update', $ticket)
      <a class="button" href="{{ route('tickets.edit', $ticket) }}">Edit ticket</a>
    @endcan
    @foreach ($actions as $action)
      <form method="POST" action="{{ route('tickets.status', $ticket) }}" class="inline">
        @csrf
        <input type="hidden" name="action" value="{{ $action }}">
        <button type="submit">{{ $actionLabels[$action] }}</button>
      </form>
    @endforeach
  </p>

  <h2>Replies</h2>

  @forelse ($ticket->replies as $reply)
    <div class="reply">
      <p class="hint">{{ $reply->user->name }} ({{ ucfirst($reply->user->role) }}) · {{ $reply->created_at->format('d M Y, H:i') }}</p>
      <p class="desc">{{ $reply->body }}</p>
      @if ($reply->photos->isNotEmpty())
        <div class="photos">
          @foreach ($reply->photos as $photo)
            <a href="{{ route('attachments.show', $photo) }}" target="_blank" rel="noopener">
              <img src="{{ route('attachments.show', $photo) }}" alt="{{ $photo->original_name }}">
            </a>
          @endforeach
        </div>
      @endif
    </div>
  @empty
    <p class="hint">No replies yet.</p>
  @endforelse

  @can('reply', $ticket)
    <form method="POST" action="{{ route('tickets.replies.store', $ticket) }}" enctype="multipart/form-data">
      @csrf
      <label>Add a reply
        <textarea name="body" rows="4" maxlength="2000" required>{{ old('body') }}</textarea>
        @error('body') <small class="error">{{ $message }}</small> @enderror
      </label>
      <label>Photos (optional)
        <input type="file" name="photos[]" accept=".jpg,.jpeg,.png" multiple>
        <small class="hint">JPG or PNG, up to 2 MB each, at most {{ config('module4.max_photos') }} photos.</small>
        @error('photos') <small class="error">{{ $message }}</small> @enderror
        @error('photos.*') <small class="error">{{ $message }}</small> @enderror
      </label>
      <button type="submit">Send reply</button>
      <small class="hint">A reply cannot be changed after it is sent.</small>
    </form>
  @else
    <p class="hint">This ticket is closed, so no more replies can be added.</p>
  @endcan
@endsection
