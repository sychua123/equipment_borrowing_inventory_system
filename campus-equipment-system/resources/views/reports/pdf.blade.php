{{-- Author: Lai Chee Kit · Module 4: the HTML that Dompdf turns into the PDF report --}}
{{-- No charts here: Dompdf cannot run JavaScript, so the PDF shows the figures and tables. --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ $report->title }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1c2430; }
    h1 { font-size: 20px; margin-bottom: 2px; }
    h2 { font-size: 14px; margin-top: 22px; }
    .hint { color: #555; }
    .warn { border: 1px solid #c9a227; padding: 6px 8px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; vertical-align: top; }
    th { background: #e9edf2; }
  </style>
</head>
<body>
  <h1>{{ $report->title }}</h1>
  <p class="hint">Campus Equipment Borrowing &amp; Inventory System<br>
    Period: {{ $filter->rangeLabel() }} · Generated: {{ now()->format('j M Y, H:i') }}</p>

  @foreach ($report->notices as $notice)
    <p class="warn">{{ $notice }}</p>
  @endforeach

  @if ($report->note)
    <p class="hint">{{ $report->note }}</p>
  @endif

  @if ($report->summary)
    <table>
      @foreach ($report->summary as $label => $value)
        <tr><th style="width: 45%">{{ $label }}</th><td>{{ $value }}</td></tr>
      @endforeach
    </table>
  @endif

  @foreach ($report->sections as $section)
    <h2>{{ $section['title'] }}</h2>
    <table>
      <thead>
        <tr>@foreach ($section['columns'] as $column)<th>{{ $column }}</th>@endforeach</tr>
      </thead>
      <tbody>
        @forelse ($section['rows'] as $row)
          <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
        @empty
          <tr><td colspan="{{ count($section['columns']) }}">Nothing to show for this period.</td></tr>
        @endforelse
      </tbody>
    </table>
  @endforeach
</body>
</html>
