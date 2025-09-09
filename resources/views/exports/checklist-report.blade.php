<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Checklist #{{ $checklist->id }} Report</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color:#111; margin:20px; }
    h1 { font-size: 16px; margin: 0 0 6px; }
    h2 { font-size: 13px; margin: 12px 0 6px; }
    .meta { color:#666; font-size: 10px; }

    /* Compact overview (two rows, three columns) */
    .overview { border:1px solid #e6e6e6; border-radius:4px; padding:6px; }
    .overview-row { display:grid; grid-template-columns: repeat(3, 1fr); gap:6px; }
    .cell { font-size: 11px; line-height:1.25; padding:2px 4px; }
    .label { font-size:9px; text-transform:uppercase; color:#666; letter-spacing:.02em; margin-right:6px; }
    .value { font-size:11px; }

    /* Dense table */
    .table { width:100%; border-collapse: collapse; }
    .table th, .table td { padding:4px 6px; border-bottom:1px solid #eee; }
    .table th { background:#f7f7f7; text-transform:uppercase; font-size:9.5px; color:#444; }
    .right { text-align:right; }
    .totals th { background:#fafafa; font-weight:bold; }

    /* Page setup for PDF */
    @page { margin: 20px; }
  </style>
</head>
<body>

  <h1>Checklist #{{ $checklist->id }}</h1>
  <div class="meta">
    Status: {{ ucfirst($checklist->status) }} ·
    Created: {{ $checklist->created_at?->format('Y-m-d H:i') }} ·
    Updated: {{ $checklist->updated_at?->format('Y-m-d H:i') }}
  </div>

  <h2>Overview</h2>
  <div class="overview">
    <div class="overview-row">
      <div class="cell">
        <span class="label">Employee Name:</span>
        <span class="value">{{ data_get($checklist,'employee.fullname','—') }}</span>
      </div>
      <div class="cell">
        <span class="label">Code:</span>
        <span class="value">{{ data_get($checklist,'employee.code','—') }}</span>
      </div>
      <div class="cell">
        <span class="label">Location:</span>
        <span class="value">{{ data_get($checklist,'employee.location.name','—') }}</span>
      </div>
    </div>
    <div class="overview-row">
      <div class="cell">
        <span class="label">Department:</span>
        <span class="value">{{ data_get($checklist,'employee.department.name','—') }}</span>
      </div>
      <div class="cell">
        <span class="label">Manager:</span>
        <span class="value">{{ data_get($checklist,'user.fullname','—') }}</span>
      </div>
      <div class="cell">
        <span class="label">Status:</span>
        <span class="value">{{ ucfirst($checklist->status) }}</span>
      </div>
    </div>
  </div>

  @if ($checklist->visitedZones->isNotEmpty())
    <h2>Visited Zones</h2>
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Code</th>
          <th>From</th>
        <th>To</th>
          <th class="right">Zone Count</th>
          <th class="right">Repeat Zone</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($checklist->visitedZones as $i => $vz)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ data_get($vz,'zone.code','—') }}</td>
            <td>{{ data_get($vz,'zone.from_zone','—') }}</td>
            <td>{{ data_get($vz,'zone.to_zone','—') }}</td>
            <td class="right">{{ number_format((int)$vz->zone_count) }}</td>
            <td class="right">{{ number_format((int)$vz->repeat_count) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="totals">
          <th colspan="4" class="right">Totals</th>
          <th class="right">{{ number_format($checklist->visitedZones->sum('zone_count')) }}</th>
          <th class="right">{{ number_format($checklist->visitedZones->sum('repeat_count')) }}</th>
        </tr>
      </tfoot>
    </table>
  @endif

  <h2>Notes</h2>
  <div style="border:1px solid #eee; padding:8px; font-size:11px; line-height:1.35;">
    {{ $checklist->note ?? '—' }}
  </div>

</body>
</html>
