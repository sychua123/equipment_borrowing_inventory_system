{{-- Author: Lai Chee Kit · Module 4: in-app notification list --}}
@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
  <h1>Notifications</h1>

  <form method="POST" action="{{ route('notifications.read-all') }}">
    @csrf
    <button type="submit">Mark all as read</button>
  </form>

  <table>
    <tbody>
      @forelse ($notifications as $notification)
        <tr class="{{ $notification->isUnread() ? 'unread' : '' }}">
          <td>
            <a href="{{ route('notifications.open', $notification) }}">{{ $notification->message }}</a>
            @if ($notification->isUnread()) <span class="badge">New</span> @endif
          </td>
          <td class="hint">{{ $notification->created_at->diffForHumans() }}</td>
        </tr>
      @empty
        <tr><td>You have no notifications.</td></tr>
      @endforelse
    </tbody>
  </table>

  <p>
    @if ($notifications->previousPageUrl()) <a href="{{ $notifications->previousPageUrl() }}">← Newer</a> @endif
    @if ($notifications->hasMorePages()) <a href="{{ $notifications->nextPageUrl() }}">Older →</a> @endif
  </p>
@endsection
