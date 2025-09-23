<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <table class="table table-bordered">
    <thead>
        <tr>
            <th></th>
            <th>Local</th>
            <th>Staging</th>
            <th>Production</th>
        </tr>
    </thead>
    <tbody>
        @foreach (['localdb','stagingdb','proddb'] as $row)
            <tr>
                <th>{{ ucfirst(str_replace('db','',$row)) }}</th>
                @foreach (['localdb','stagingdb','proddb'] as $col)
                    <td class='align-center'>
                        @if($row === $col)
                             Same
                        @else
                            <button onclick="fetchDiff('{{ $row }}','{{ $col }}','schema')">Schema</button>
                            <button onclick="fetchDiff('{{ $row }}','{{ $col }}','data')">Data</button>
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

<pre id="result"></pre>

<script>
function fetchDiff(base, target, type) {
    let url = type === 'schema'
        ? `/schema-diff?base=${base}&target=${target}`
        : `/data-diff?base=${base}&target=${target}&table=users`; // example
    fetch(url)
        .then(res => res.json())
        .then(data => {
            document.getElementById('result').innerText = data.join("\n");
        });
}
</script>

</body>
</html>