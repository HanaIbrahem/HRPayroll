<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{{ $title }} Export</title>
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
    h1 { font-size: 18px; margin: 0 0 8px; }
    .meta { font-size: 11px; margin-bottom: 12px; color: #555; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 6px 8px; }
    th { background: #f5f5f5; text-align: left; }
    tr:nth-child(even) td { background: #fafafa; }
  </style>
</head>
<body>
  <h1>{{ $title }}</h1>
  <div class="meta">Generated at: {{ now()->format('Y-m-d H:i') }}</div>

  <table>
    <thead>
      <tr>
        @foreach ($columns as $c)
          <th>{{ $c['label'] ?? ucfirst($c['field']) }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @forelse ($rows as $r)
        <tr>
          @foreach ($r as $cell)
            <td>{{ $cell }}</td>
          @endforeach
        </tr>
      @empty
        <tr>
          <td colspan="{{ count($columns) }}">No data.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
