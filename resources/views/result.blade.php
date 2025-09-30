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
            @if ($totalRecords <= 5000)
                <a href="{{ route('compare.download.pdf', ['base' => $base, 'target' => $target, 'type' => $type]) }}"
                    class="btn btn-danger me-2" target="_blank">Download PDF</a>
            @else
                <a href="{{ route('compare.download.excel', ['base' => $base, 'target' => $target, 'type' => $type]) }}"
                    class="btn btn-primary" target="_blank">Download Excel</a>
            @endif


            <a href="{{ route('compare.download.sql', ['base' => $base, 'target' => $target, 'type' => $type]) }}"
                class="btn btn-dark me-2" target="_blank"> Download SQL </a>
        </div>


        @if ($type === 'schema' || $type === null)
            <div class="section-title text-center mt-4">Schema Differences</div>
            @if ($schemaPaginator && $schemaPaginator->count())
                <div class="card shadow-sm mb-3">
                    <div class="card-body d-flex flex-column gap-2 p-3">


                        @foreach ($schemaPaginator as $line)
                            @if (str_contains(strtolower($line), 'does not exist') || str_contains(strtolower($line), 'failed'))
                                <div class="sql-box update">
                                    <span class="fw-bold text-danger">⚠ Issue:</span>
                                    <br>
                                    {{ $line }}
                                </div>
                            @endif
                        @endforeach


                        <span class="fw-bold">-- Apply on : {{ $target }}</span>
                        @foreach ($schemaPaginator as $line)
                            @if (!str_contains(strtolower($line), 'does not exist') && !str_contains(strtolower($line), 'failed'))
                                <div class="sql-box update">
                                    {{ $line }}
                                </div>
                            @endif
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


        @if ($type === 'data' || $type === null)
            <div class="section-title text-center mt-4">Data Differences</div>
            @if ($dataPaginator && $dataPaginator->count())
                <div class="card shadow-sm mb-3">
                    <div class="card-body d-flex flex-column gap-2 p-3">


                        @foreach ($dataPaginator as $line)
                            @if (str_contains(strtolower($line), 'does not exist') || str_contains(strtolower($line), 'failed'))
                                <div class="sql-box update">
                                    <span class="fw-bold text-danger">⚠ Issue:</span>
                                    <br>
                                    {{ $line }}
                                </div>
                            @endif
                        @endforeach


                        <span class="fw-bold m-3">
                            <h5>Apply on : {{ $target }}</h5>
                        </span>
                        @foreach ($dataPaginator as $line)
                            @if (!str_contains(strtolower($line), 'does not exist') && !str_contains(strtolower($line), 'failed'))
                                <div class="sql-box update">
                                    {{ $line }}
                                </div>
                            @endif
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
