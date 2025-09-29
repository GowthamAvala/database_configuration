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
    <div class="container py-4">
        <h2 class="text-center mb-4">
            Database Comparison Result
        </h2>

        <div class="text-center mt-4">
            <a href="{{ route('compare.download.pdf', ['base' => $base, 'target' => $target, 'type' => $type]) }}"
                class="btn btn-danger" target="_blank">Download PDF</a>

            <a href="{{ route('compare.download.excel', ['base' => $base, 'target' => $target, 'type' => $type]) }}"
                class="btn btn-success" target="_blank">Download Excel</a>
        </div>




        {{-- Show Schema Differences --}}
        @if ($type === 'schema')
            <div class="section-title text-center">Schema Differences</div>
            @if ($schemaPaginator && $schemaPaginator->count())
                <div class="card shadow-sm mb-3">
                    <div class="card-body d-flex flex-column gap-2 p-3">
                        @foreach ($schemaPaginator as $line)
                            <div class="sql-box update">{{ $line }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $schemaPaginator->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center text-muted mt-3">No schema differences found.</div>
            @endif
        @endif

        {{-- Show Data Differences --}}
        @if ($type === 'data')
            <div class="section-title text-center">Data Differences</div>
            @if ($dataPaginator && $dataPaginator->count())
                <div class="card shadow-sm mb-3">
                    <div class="card-body d-flex flex-column gap-2 p-3">
                        @foreach ($dataPaginator as $line)
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

                <div class="d-flex justify-content-center mt-3">
                    {{ $dataPaginator->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center text-muted mt-3">No data differences found.</div>
            @endif
        @endif

        {{-- If type is null, show both schema + data --}}
        @if ($type === null)
            <div class="section-title text-center">Schema Differences</div>
            @if ($schemaPaginator && $schemaPaginator->count())
                <div class="card shadow-sm mb-3">
                    <div class="card-body d-flex flex-column gap-2 p-3">
                        @foreach ($schemaPaginator as $line)
                            <div class="sql-box update">{{ $line }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $schemaPaginator->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center text-muted mt-3">No schema differences found.</div>
            @endif

            <div class="section-title text-center">Data Differences</div>
            @if ($dataPaginator && $dataPaginator->count())
                <div class="card shadow-sm mb-3">
                    <div class="card-body d-flex flex-column gap-2 p-3">
                        @foreach ($dataPaginator as $line)
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

                <div class="d-flex justify-content-center mt-3">
                    {{ $dataPaginator->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center text-muted mt-3">No data differences found.</div>
            @endif
        @endif

        <div class="text-center mt-4">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">⬅ Back</a>
        </div>
    </div>
</body>

</html>
