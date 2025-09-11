<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Checklists Detailed</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color:#111; margin:20px; }
    h1 { font-size: 16px; margin: 0 0 6px; }
    h2 { font-size: 13px; margin: 12px 0 6px; }
    .meta { color:#666; font-size: 10px; }
    .overview { border:1px solid #e6e6e6; border-radius:4px; padding:6px; margin-top:6px; }
    .overview-row { display:grid; grid-template-columns: repeat(3, 1fr); gap:6px; }
    .cell { font-size: 11px; line-height:1.25; padding:2px 4px; }
    .label { font-size:9px; text-transform:uppercase; color:#666; letter-spacing:.02em; margin-right:6px; }
    .value { font-size:11px; }
    .table { width:100%; border-collapse: collapse; }
    .table th, .table td { padding:4px 6px; border-bottom:1px solid #eee; }
    .table th { background:#f7f7f7; text-transform:uppercase; font-size:10px; color:#444; }
    .right { text-align:right; }
    .totals th { background:#fafafa; font-weight:bold; }
    .page-break { page-break-after: always; }
    @page { margin: 20px; }
  </style>
</head>
<body>

@foreach ($rows as $idx => $c)
  <h1>
    Checklist #{{ $c->id }} — {{ data_get($c,'employee.fullname','—') }}
    @php $code = data_get($c,'employee.code'); @endphp
    @if ($code)<small>({{ $code }})</small>@endif
  </h1>

  <div class="meta">
    Status: {{ ucfirst($c->status) }} ·
    Uploaded: {{ optional($c->created_at)->format('Y-m-d H:i') }} ·
    Approved: {{ optional($c->approved_at)->format('Y-m-d H:i') }}
  </div>

  <div class="overview">
    <div class="overview-row">
      <div class="cell"><span class="label">Department:</span> <span class="value">{{ data_get($c,'employee.department.name','—') }}</span></div>
      <div class="cell"><span class="label">Location:</span>   <span class="value">{{ data_get($c,'employee.location.name','—') }}</span></div>
      <div class="cell"><span class="label">Manager:</span>    <span class="value">{{ data_get($c,'user.fullname','—') }}</span></div>
    </div>
    <div class="overview-row" style="margin-top:6px;">
      <div class="cell"><span class="label">From:</span>       <span class="value">{{ $c->start_date }}</span></div>
      <div class="cell"><span class="label">To:</span>         <span class="value">{{ $c->end_date }}</span></div>
      <div class="cell"><span class="label">Approved By:</span><span class="value">{{ data_get($c,'approver.fullname','—') }}</span></div>
    </div>
  </div>

  @if ($c->visitedZones->isNotEmpty())
    <h2>Visited Zones</h2>
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Code</th>
          <th>From</th>
          <th>To</th>
          <th class="right">Zone</th>
          <th class="right">Repeat</th>
          <th class="right">Cost</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($c->visitedZones as $i => $vz)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ data_get($vz,'zone.code','—') }}</td>
            <td>{{ data_get($vz,'zone.from_zone','—') }}</td>
            <td>{{ data_get($vz,'zone.to_zone','—') }}</td>
            <td class="right">{{ number_format((int)$vz->zone_count) }}</td>
            <td class="right">{{ number_format((int)$vz->repeat_count) }}</td>
            <td class="right">{{ number_format((int)$vz->calculated_cost) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="totals">
          <th colspan="4" class="right">Totals</th>
          <th class="right">{{ number_format((int) $c->visitedZones->sum('zone_count')) }}</th>
          <th class="right">{{ number_format((int) $c->visitedZones->sum('repeat_count')) }}</th>
          <th class="right">{{ number_format((int) $c->visitedZones->sum('calculated_cost')) }}</th>
        </tr>
      </tfoot>
    </table>
  @endif

  @if ($idx !== count($rows) - 1)
    <div class="page-break"></div>
  @endif
@endforeach

</body>
</html>
