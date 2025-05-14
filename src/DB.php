<?php
declare(strict_types = 1);

class DB {

    private string $url = 'http://db:8086';
    private string $org = 'your-org'; // Set this to your InfluxDB organization
    private string $bucket = 'weather';
    private string $token = 'your-token'; // Set your InfluxDB token here

    private array $tables = [
        'weather_data_ger',
        'weather_data_fin'
    ];

    public function __construct()
    {
        // You could check connectivity here if needed
    }

    public function getDataFromTable($table, DateValue|null $startDate = null, DateValue|null $endDate = null): array
    {
        if (!in_array($table, $this->tables)) {
            Response::internalError('Non existant db table');
        }

        $start = $startDate ? $startDate->get() : '-1h';
        $end = $endDate ? $endDate->get() : 'now()';

        $flux = <<<EOT
        from(bucket: "{$this->bucket}")
        |> range(start: $start, stop: $end)
        |> filter(fn: (r) => r._measurement == "$table")
        |> pivot(rowKey:["_time"], columnKey: ["_field"], valueColumn: "_value")
        |> keep(columns: ["_time", "temp", "hum", "pressure", "warnings", "location"])
        |> sort(columns: ["_time"], desc: true)
        EOT;


        file_put_contents("/debug/flux_query.txt", $flux);

        return $this->queryInflux($flux);
    }

    public function getLastFromTable($table): array
    {
        if (!in_array($table, $this->tables)) {
            Response::internalError('Non existant db table');
        }

        $flux = <<<EOT
        from(bucket: "{$this->bucket}")
        |> range(start: -30d)
        |> filter(fn: (r) => r._measurement == "$table")
        |> pivot(rowKey:["_time"], columnKey: ["_field"], valueColumn: "_value")
        |> keep(columns: ["_time", "temp", "hum", "pressure", "warnings", "location"])
        |> sort(columns: ["_time"], desc: true)
        |> limit(n: 1)
        EOT;
        
        file_put_contents("/debug/flux_query_latest.txt", $flux);

        $results = $this->queryInflux($flux);

        return $results[0] ?? ["no data available"];
    }

    private function queryInflux(string $flux): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "{$this->url}/api/v2/query?org={$this->org}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $flux);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Token {$this->token}",
            "Content-Type: application/vnd.flux",
            "Accept: application/csv"
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
            
        if ($error) {
            Response::internalError("InfluxDB request failed: $error");
        }

        return $this->parseCsv($response);
    }
    private function parseCsv(string $csv): array
    {
        $lines = explode("\n", $csv);
        $data = [];
        $headers = [];
    
        foreach ($lines as $line) {
            // Skip metadata and empty lines
            if (str_starts_with($line, '#') || trim($line) === '') {
                continue;
            }
    
            $fields = str_getcsv($line);
    
            if (empty($headers)) {
                $headers = $fields;
                continue;
            }
    
            $row = array_combine($headers, $fields);
    
            if (!$row) {
                continue;
            }
    
            $entry = [
                'time' => $row['_time'] ?? null,
                'temp' => isset($row['temp']) ? (float)$row['temp'] : null,
                'hum' => isset($row['hum']) ? (float)$row['hum'] : null,
                'pressure' => isset($row['pressure']) ? (float)$row['pressure'] : null,
                'warnings' => isset($row['warnings']) ? json_decode($row['warnings'], true) : [],
                'location' => $row['location'] ?? 'unknown'
            ];
    
            $data[] = $entry;
        }
    
        return $data;
    }
    
}
?>
