{{-- Author: Lai Chee Kit · shared page layout for Module 4 --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Campus Equipment')</title>
  <style>
    body { font: 16px system-ui; max-width: 860px; margin: 0 auto; padding: 0 16px 48px; color: #1c2430; }
    nav { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; padding: 14px 0; border-bottom: 1px solid #ddd; margin-bottom: 24px; }
    nav .who { margin-left: auto; color: #555; font-size: 14px; }
    a { color: #1764c0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #ddd; vertical-align: top; }
    label { display: block; margin: 18px 0; }
    input, select, textarea { display: block; box-sizing: border-box; padding: 10px; width: 100%; font: inherit; }
    button { padding: 10px 16px; cursor: pointer; font: inherit; }
    .good { color: #146c43; }
    .error { color: #a21a1a; }
    .hint { color: #666; font-size: 14px; }
    .badge { display: inline-block; padding: 2px 8px; border: 1px solid #999; border-radius: 10px; font-size: 13px; white-space: nowrap; }
    dl { display: grid; grid-template-columns: 140px 1fr; gap: 8px 16px; }
    dt { color: #555; } dd { margin: 0; }
    .desc { white-space: pre-wrap; overflow-wrap: anywhere; }
    .table-wrap { overflow-x: auto; }
  </style>
</head>
<body>
  <nav>
    <strong>Campus Equipment</strong>
    <a href="{{ route('tickets.index') }}">Tickets</a>
    <a href="{{ route('tickets.create') }}">Submit a ticket</a>
    @auth
      <span class="who">{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})
        @if (Route::has('login')) · <a href="{{ route('login') }}">Switch user</a> @endif
      </span>
    @endauth
  </nav>

  @if (session('success'))
    <p class="good">{{ session('success') }}</p>
  @endif

  @yield('content')
</body>
</html>
