<?php
class DataFetcher{

    public function fetch($routerArray): string 
    {  
        // Data provided by Router
        $tablename = $routerArray["table"];
        $startDate = $routerArray["start"];
        $endDate = $routerArray["end"];

        // Database config
        $host = 'db';
        $db   = 'weather';
        $user = 'user';
        $pass = 'pass';

        // Checks DB connection
        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
    
        // Default Query
        $sql = "SELECT * FROM `$tablename`";
        $types = '';
        $params = [];
    
        // Query in case Dates are provided
        if ($startDate instanceof DateValue && $endDate instanceof DateValue) {
            $sql .= " WHERE time BETWEEN ? AND ?";
            $types = 'ss';
            $params = [$startDate->get(), $endDate->get()];
        }
    
        $sql .= " ORDER BY time ASC";
    
        $stmt = $conn->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
    
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    
        $stmt->close();
        $conn->close();
    
        return json_encode($data);
    }
}