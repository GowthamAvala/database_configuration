<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Database Comparison PDF</title>
    
</head>

<body>
    <h2>Database Comparison Result</h2>
    <p>Comparing <strong>{{ $base }}</strong> → <strong>{{ $target }}</strong></p>

    @if ($type === 'schema' || $type === null)
        <div class="section-title">Schema Differences</div>
        @forelse($schemaDiff as $table => $queries)
            <h4>Table: {{ $table }}</h4>
            @foreach ($queries as $line)
                <div class="sql-box">{{ $line }}</div>
            @endforeach
        @empty
            <p>No schema differences found.</p>
        @endforelse
    @endif

    @if ($type === 'data' || $type === null)
        <div class="section-title">Data Differences</div>
        @forelse($dataDiff as $table => $queries)
            <h4>Table: {{ $table }}</h4>
            @foreach ($queries as $line)
                <div class="sql-box">{{ $line }}</div>
            @endforeach
        @empty
            <p>No data differences found.</p>
        @endforelse
    @endif
</body>

</html>
