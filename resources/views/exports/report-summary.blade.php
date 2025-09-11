<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Checklists Summary</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color:#111; margin:20px; }
    h1 { font-size: 16px; margin: 0 0 8px; }
    .table { width:100%; border-collapse: collapse; margin-top:8px; }
    .table th, .table td { padding:4px 6px; border-bottom:1px solid #eee; }
    .table th { background:#f7f7f7; text-transform:uppercase; font-size:10px; color:#444; }
    .right { text-align:right; }
    @page { margin: 20px; }
  </style>
</head>
<body>
  <h1>Checklists Summary</h1>
  <p>HR Department Payroll</p>
  <table class="table">
    <thead>
      <tr>
        <th>Checklist_ID</th>
        <th>Employee</th>
        <th>Code</th>
        <th>Department</th>
        <th>Location</th>
        <th>Manager</th>
        <th>From</th>
        <th>To</th>
      
        <th class="right">Total Price</th>
   
      </tr>
    </thead>
    <tbody>
      @foreach ($rows as $c)
        <tr>
          <td>{{ $c->id }}</td>
          <td>{{ data_get($c,'employee.fullname','—') }}</td>
          <td>{{ data_get($c,'employee.code','—') }}</td>
          <td>{{ data_get($c,'employee.department.name','—') }}</td>
          <td>{{ data_get($c,'employee.location.name','—') }}</td>
          <td>{{ data_get($c,'user.fullname','—') }}</td>
          <td>{{ $c->start_date }}</td>
          <td>{{ $c->end_date }}</td>
        
          <td class="right">{{ data_get($c->calculated_cost,'-') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
