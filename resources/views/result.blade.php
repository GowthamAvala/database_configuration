<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Comparison Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/custom.css'])
</head>

<body>
    <div class="container py-5">
        <h2 class="text-center fw-bold mb-4 text-primary">Database Comparison Result</h2>
        <h3>Pavan</h3>

        <div class="alert alert-info text-center fw-bold">
            Comparing <span class="text-success">{{ $base }}</span> against <span
                class="text-danger">{{ $target }}</span>
        </div>

        @if ($type === 'schema')
            <div class="section-title text-center"> Schema Differences</div>
            @if (!empty($schemaDiff))
                @foreach ($schemaDiff as $table => $queries)
                    <div class="table-header">Table: {{ $table }}</div>
                    <div class="card shadow-sm mb-3">
                        <div class="card-body d-flex flex-column gap-2 p-3">
                            @foreach ($queries as $line)
                                <div class="sql-box update">{{ $line }}</div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center text-muted mt-3"> No schema differences found.</div>
            @endif
        @elseif($type === 'data')
            <div class="section-title text-center"> Data Differences</div>
            @if (!empty($dataDiff))
                @foreach ($dataDiff as $table => $queries)
                    <div class="table-header">Table: {{ $table }}</div>
                    <div class="card shadow-sm mb-3">
                        <div class="card-body d-flex flex-column gap-2 p-3">
                            @foreach ($queries as $line)
                                @php
                                    $lineType = 'normal';
                                    if (str_starts_with($line, 'INSERT')) {
                                        $lineType = 'insert';
                                    } elseif (str_starts_with($line, 'UPDATE')) {
                                        $lineType = 'update';
                                    } elseif (str_starts_with($line, 'DELETE')) {
                                        $lineType = 'delete';
                                    }
                                @endphp
                                <div class="sql-box {{ $lineType }}">{{ $line }}</div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center text-muted mt-3"> No data differences found.</div>
            @endif
        @endif

        <div class="text-center mt-5">
            <a href="/" class="btn btn-primary"> Back to Dashboard</a>
        </div>
    </div>
</body>

</html>
