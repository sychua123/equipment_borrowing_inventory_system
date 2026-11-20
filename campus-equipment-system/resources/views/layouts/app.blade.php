{{-- Author: Lai Chee Kit · shared page layout for Module 4 --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Campus Equipment')</title>
  <style>
    body { font: 16px system-ui; max-width: 1000px; margin: 0 auto; padding: 0 16px 48px; color: #1c2430; }
    nav { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; padding: 14px 0; border-bottom: 1px solid #ddd; margin-bottom: 24px; }
    nav .who { margin-left: auto; color: #555; font-size: 14px; }
    a { color: #1764c0; }
    h2 { margin-top: 36px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #ddd; vertical-align: top; }
    label { display: block; margin: 18px 0; }
    input, select, textarea { display: block; box-sizing: border-box; padding: 10px; width: 100%; font: inherit; }
    input[type=checkbox] { display: inline; width: auto; }
    button, .button { padding: 10px 16px; cursor: pointer; font: inherit; }
    a.button { display: inline-block; border: 1px solid #888; border-radius: 4px; text-decoration: none; background: #f3f3f3; color: #1c2430; }
    .good { color: #146c43; }
    .error { color: #a21a1a; }
    .warn { background: #fff4d6; border: 1px solid #e0b84a; padding: 10px 14px; border-radius: 6px; }
    .hint { color: #666; font-size: 14px; }
    .badge { display: inline-block; padding: 2px 8px; border: 1px solid #999; border-radius: 10px; font-size: 13px; white-space: nowrap; }
    .count { background: #c0271d; color: #fff; border-radius: 10px; padding: 1px 7px; font-size: 13px; }
    dl { display: grid; grid-template-columns: 140px 1fr; gap: 8px 16px; }
    dt { color: #555; } dd { margin: 0; }
    .desc { white-space: pre-wrap; overflow-wrap: anywhere; }
    .table-wrap { overflow-x: auto; }
    .filters { display: flex; gap: 12px; flex-wrap: wrap; align-items: end; margin: 16px 0; }
    .filters label { margin: 0; flex: 1 1 150px; font-size: 14px; }
    .inline { display: inline-block; margin: 0 8px 8px 0; }
    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin: 16px 0; }
    .card { border: 1px solid #ddd; border-radius: 8px; padding: 12px 14px; }
    .card b { display: block; font-size: 26px; }
    .panel { border: 1px solid #ddd; border-radius: 8px; padding: 4px 18px 18px; margin: 20px 0; }
    .chart { max-width: 560px; margin: 16px 0; }
    .reply { border-left: 3px solid #ccd; padding: 4px 14px; margin: 16px 0; }
    .photos img { max-width: 180px; max-height: 140px; border: 1px solid #ccc; margin: 4px 8px 4px 0; }
    .unread { font-weight: 600; }
    .tabs a { margin-right: 14px; }
    .tabs a.current { font-weight: 700; text-decoration: none; color: #1c2430; }
  </style>
</head>
<body>
  <nav>
    <strong>Campus Equipment</strong>
    @auth
      @if (auth()->user()->isAdmin())
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('reports.index') }}">Reports</a>
      @endif
      <a href="{{ route('tickets.index') }}">Tickets</a>
      <a href="{{ route('tickets.create') }}">Submit a ticket</a>

      {{-- The bell: how many notifications this user has not read yet --}}
      @php
        $unreadCount = auth()->user()->ticketNotifications()->where('is_read', false)->count();
      @endphp
      <a href="{{ route('notifications.index') }}">Notifications
        @if ($unreadCount > 0) <span class="count">{{ $unreadCount }}</span> @endif
      </a>

      <span class="who">{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})
        @if (Route::has('dev.login')) · <a href="{{ route('login') }}">Switch user</a> @endif
      </span>
    @endauth
  </nav>

  @if (session('success'))
    <p class="good">{{ session('success') }}</p>
  @endif
  @if (session('error'))
    <p class="error">{{ session('error') }}</p>
  @endif

  @yield('content')
</body>
</html>
