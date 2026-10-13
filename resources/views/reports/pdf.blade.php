<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { color: #26364a; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { border-bottom: 2px solid #244c72; margin-bottom: 18px; padding-bottom: 12px; }
        h1 { color: #173b5e; font-size: 20px; margin: 0 0 6px; }
        .meta { color: #64748b; line-height: 1.6; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #edf3f8; color: #173b5e; text-align: left; }
        th, td { border: 1px solid #d8e1e9; padding: 7px 6px; }
        tr:nth-child(even) td { background: #f8fafc; }
        .empty { color: #64748b; padding: 18px; text-align: center; }
    </style>
</head>
<body>
    <header class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">
            CRM Business Report<br>
            Generated {{ $generatedAt->format('M j, Y g:i A') }} by {{ $generatedBy }}
            @if($filters['date_from'] ?? false)
                <br>Date range: {{ $filters['date_from'] }} to {{ $filters['date_to'] ?? 'Present' }}
            @endif
        </div>
    </header>

    <table>
        <thead><tr>@foreach($headings as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead>
        <tbody>
            @forelse($rows as $row)
                <tr>@foreach($headings as $heading)<td>{{ $row[$heading] ?? '' }}</td>@endforeach</tr>
            @empty
                <tr><td class="empty" colspan="{{ count($headings) }}">No records match the selected filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
