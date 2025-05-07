<?php
declare(strict_types = 1);

class DB {

    private mysqli $conn;

    private array $settings = [
        'host' => 'db',
        'db' => 'weather',
        'user' => 'user',
        'password' => 'pass'
    ];

    private array $tables = [
        'weather_data_ger',
        'weather_data_fin'
    ];

    function __construct()
    {
        $this -> conn = new mysqli($this -> settings['host'], $this -> settings['user'], $this -> settings['password'], $this -> settings['db']);
        //check db connection
        if ($this -> conn -> connect_error) {
            Response::internalError('Database connection failed');
        }
    }

    public function getDataFromTable($table, DateValue|null $startDate = null, DateValue|null $endDate = null)
    {
        //check table
        if (!in_array($table, $this -> tables, )) {
            Response::internalError('Non existant db table');
        }

        // Default Query
        $sql = "SELECT * FROM `$table`";
        $types = '';
        $params = [];

        // Query in case Dates are provided
        if ($startDate instanceof DateValue && $endDate instanceof DateValue) {
            $sql .= " WHERE date BETWEEN ? AND ?";
            $types = 'ss';
            $params = [$startDate -> get(), $endDate -> get()];
        }

        $sql .= " ORDER BY date ASC";

        $stmt = $this -> conn -> prepare($sql);
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

        return $data;
    }

    public function getLastFromTable($table) {
        //check table
        if (!in_array($table, $this -> tables)) {
            Response::internalError('Non existant db table');
        }

        // Default Query
        $sql = "SELECT * FROM `$table`";
        $types = '';
        $params = [];

        $sql .= " ORDER BY date ASC LIMIT 1";

        $stmt = $this -> conn -> prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt -> execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt -> close();

        return $data;
    }
}
?>