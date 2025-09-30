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
                    class="btn btn-success me-2" target="_blank">Download CSV</a>
            @endif
 
            <a href="{{ route('compare.download.excel', ['base' => $base, 'target' => $target, 'type' => $type]) }}"
                class="btn btn-primary" target="_blank">Download Excel</a>
        </div>

        <!-- Session Messages -->
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {!! session('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
 
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {!! session('warning') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
 
        </div>
 
        <!-- ERROR ALERTS -->
        @if (session('error'))
            <div class="alert alert-warning alert-dismissible fade show text-center mx-auto w-75" role="alert">
                <strong>Error:</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <!-- Error Messages -->
        @if (!empty($errors) && is_array($errors))
            @foreach ($errors as $error)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endforeach
        @endif
 
       
        @if ($type === 'schema' || $type === null)
            <div class="section-title text-center mt-4">Schema Differences</div>
            @if ($schemaPaginator && $schemaPaginator->count())
                <div class="card shadow-sm mb-3">
                    <div class="card-body d-flex flex-column gap-2 p-3">
                        @foreach ($schemaPaginator as $line)
                            <div class="sql-box update">
                                <span class="badge bg-info text-dark me-2">
                                    Apply on Target DB: {{ $target }}
                                </span>
                                {{ $line }}
                            </div>
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
                            @php
                                $lineType = 'normal';
                                $badgeClass = 'bg-secondary';
                                $badgeText = "Target DB: $target";
 
                                if (str_starts_with($line, 'INSERT')) {
                                    $lineType = 'insert';
                                    $badgeClass = 'bg-success';
                                    $badgeText = "Insert into: $target";
                                } elseif (str_starts_with($line, 'UPDATE')) {
                                    $lineType = 'update';
                                    $badgeClass = 'bg-warning text-dark';
                                    $badgeText = "Update on: $target";
                                } elseif (str_starts_with($line, 'DELETE')) {
                                    $lineType = 'delete';
                                    $badgeClass = 'bg-danger';
                                    $badgeText = "Delete from : $target";
                                }
                            @endphp
 
                            <div class="sql-box {{ $lineType }}">
                                <span class="badge {{ $badgeClass }} me-2">
                                    {{ $badgeText }}
                                </span>
                                {{ $line }}
                            </div>
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