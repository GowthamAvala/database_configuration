<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Compare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/custom.css', 'resources/js/app.js'])

</head>

<body>
    <div class="container-fluid py-5" style="background-color: #f0f2f5;">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-primary">Database Comparison</h2>
            <p class="text-muted fs-5">Compare schema and data between Local, Staging, and Production databases</p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach (['localdb' => 'Local', 'stagingdb' => 'Staging', 'proddb' => 'Production'] as $rowKey => $rowLabel)
                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 h-100 rounded-4">
                        <div class="card-header text-white text-center fs-4 fw-bold p-3 bg-primary">
                            {{ $rowLabel }}
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center align-items-center gap-3 py-4">
                            @foreach (['localdb' => 'Local', 'stagingdb' => 'Staging', 'proddb' => 'Production'] as $colKey => $colLabel)
                                @if ($rowKey !== $colKey)
                                    <div
                                        class="w-100 d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3 mb-2">
                                        <span
                                            class="fw-bold fs-5 text-dark flex-grow-1 text-center text-sm-start">{{ $colLabel }}</span>
                                        <button class="btn btn-outline-success btn-lg flex-grow-0"
                                            onclick="openCompare('{{ $rowKey }}','{{ $colKey }}','schema')">
                                            Schema
                                        </button>
                                        <button class="btn btn-outline-primary btn-lg flex-grow-0"
                                            onclick="openCompare('{{ $rowKey }}','{{ $colKey }}','data')">
                                            Data
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


    <script>
        function openCompare(base, target, type) {
            window.open(`/compare-result?base=${base}&target=${target}&type=${type}`, '_blank');
        }
    </script>



    <script>
        function openCompare(base, target, type) {
            window.open(`/compare-result?base=${base}&target=${target}&type=${type}`, '_blank');
        }
    </script>
</body>

</html>
