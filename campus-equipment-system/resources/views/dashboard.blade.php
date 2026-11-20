{{-- Author: Lai Chee Kit · Module 4: Admin dashboard --}}
@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
  <h1>Dashboard</h1>
  <p class="hint">This month: {{ $filter->rangeLabel() }}</p>

  <div class="panel">
    <h2>Tickets waiting for action ({{ $waitingTotal }})</h2>
    <table>
      <tbody>
        @forelse ($waiting as $ticket)
          <tr>
            <td><a href="{{ route('tickets.show', $ticket) }}">#{{ $ticket->id }} {{ $ticket->subject }}</a></td>
            <td>{{ \App\Models\Ticket::label($ticket->priority) }}</td>
            <td><span class="badge">{{ \App\Models\Ticket::label($ticket->status) }}</span></td>
            <td>{{ $ticket->user->name }}</td>
            <td class="hint">{{ $ticket->created_at->diffForHumans() }}</td>
          </tr>
        @empty
          <tr><td>No tickets are waiting. Well done.</td></tr>
        @endforelse
      </tbody>
    </table>
    <p><a href="{{ route('tickets.index') }}">See all tickets</a></p>
  </div>

  {{-- One panel per report. A panel whose module is not responding shows only its own warning. --}}
  @foreach ($panels as $type => $report)
    <div class="panel">
      <h2>{{ $report->title }}</h2>

      @foreach ($report->notices as $notice)
        <p class="warn">{{ $notice }}</p>
      @endforeach

      @if ($report->summary)
        <div class="cards">
          @foreach ($report->summary as $label => $value)
            <div class="card"><b>{{ $value }}</b>{{ $label }}</div>
          @endforeach
        </div>
      @endif

      @if ($report->charts)
        {{-- The dashboard shows only the first chart of each report --}}
        @include('reports._charts', ['charts' => array_slice($report->charts, 0, 1), 'prefix' => $type])
      @endif

      <p><a href="{{ route('reports.show', $type) }}">Open the full report</a></p>
    </div>
  @endforeach
@endsection
