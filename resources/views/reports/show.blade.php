{{-- Author: Lai Chee Kit · Module 4: one report on screen (Tickets, Inventory or Borrowing usage) --}}
@extends('layouts.app')
@section('title', $report->title)

@section('content')
  <p class="tabs">
    @foreach (\App\Http\Controllers\ReportController::REPORTS as $key => [$label])
      <a href="{{ route('reports.show', $key) }}" class="{{ $key === $type ? 'current' : '' }}">{{ $label }}</a>
    @endforeach
  </p>

  <h1>{{ $report->title }}</h1>
  <p class="hint">Period: {{ $filter->rangeLabel() }}</p>

  @foreach ($errors->all() as $message)
    <p class="error">{{ $message }}</p>
  @endforeach

  <form method="GET" action="{{ route('reports.show', $type) }}" class="filters">
    <label>Period
      <select name="preset">
        @foreach (\App\Reports\ReportFilter::PRESETS as $value => $label)
          <option value="{{ $value }}" @selected($filter->preset === $value)>{{ $label }}</option>
        @endforeach
      </select>
    </label>
    <label>From (custom range)
      <input type="date" name="from" value="{{ $filter->preset === 'custom' ? $filter->from->toDateString() : '' }}">
    </label>
    <label>To (custom range)
      <input type="date" name="to" value="{{ $filter->preset === 'custom' ? $filter->to->toDateString() : '' }}">
    </label>
    <label>Category
      <select name="category">
        <option value="">All</option>
        @foreach ($report->categoryOptions as $category)
          <option value="{{ $category }}" @selected($filter->category === $category)>{{ $category }}</option>
        @endforeach
      </select>
    </label>
    @if ($type === 'tickets')
      <label>Status
        <select name="status">
          <option value="">All</option>
          @foreach (array_keys(\App\Models\Ticket::STATES) as $status)
            <option value="{{ $status }}" @selected($filter->status === $status)>{{ $status }}</option>
          @endforeach
        </select>
      </label>
      <label>Priority
        <select name="priority">
          <option value="">All</option>
          @foreach (\App\Models\Ticket::PRIORITIES as $priority)
            <option value="{{ $priority }}" @selected($filter->priority === $priority)>{{ $priority }}</option>
          @endforeach
        </select>
      </label>
    @endif
    <button type="submit">Apply</button>
  </form>

  <p>
    {{-- The export links carry the same filter as the screen --}}
    <a class="button" href="{{ route('reports.export', [$type, 'csv'] + $filter->toQuery()) }}">Export CSV</a>
    <a class="button" href="{{ route('reports.export', [$type, 'pdf'] + $filter->toQuery()) }}">Export PDF</a>
  </p>

  @foreach ($report->notices as $notice)
    <p class="warn">{{ $notice }}</p>
  @endforeach

  @if ($report->note)
    <p class="hint">{{ $report->note }}</p>
  @endif

  @if ($report->summary)
    <div class="cards">
      @foreach ($report->summary as $label => $value)
        <div class="card"><b>{{ $value }}</b>{{ $label }}</div>
      @endforeach
    </div>
  @endif

  @if ($report->charts)
    @include('reports._charts', ['charts' => $report->charts, 'prefix' => $type])
  @endif

  @foreach ($report->sections as $section)
    <h2>{{ $section['title'] }}</h2>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>@foreach ($section['columns'] as $column) <th>{{ $column }}</th> @endforeach</tr>
        </thead>
        <tbody>
          @forelse ($section['rows'] as $row)
            <tr>@foreach ($row as $cell) <td>{{ $cell }}</td> @endforeach</tr>
          @empty
            <tr><td colspan="{{ count($section['columns']) }}">Nothing to show for this period.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  @endforeach
@endsection
