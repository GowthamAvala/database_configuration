<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                    <td style='text-align:center;'>
                        @if($row === $col)
                             No Change
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
            : `/data-diff?base=${base}&target=${target}&table=users`; 
         $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                document.getElementById('result').innerText = data.join("\n");
            },
            error: function(xhr, status, error) {
                console.error('Error fetching diff:', error);
            }
        });
    }
</script>

</body>
</html>