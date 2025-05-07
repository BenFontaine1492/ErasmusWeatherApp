<?php
declare(strict_types = 1);

/**
 * Creates a path object used to route
 * @param string $path Path to reach the function, ex: /login/:id
 * @param string $file The file name including file type, ex: example.php. File must be pressent in the endpoints map!
 * @param string $function The function to call in the provided file
 * @param int|bool $permissions if permissions are needed to access path
 */
function path(string $path, string $file, string $function): stdClass {
    $route = new stdClass();
    $route -> path = $path;
    $route -> file = $file;
    $route -> function = $function;
    return $route;
}

//Write available paths here
$routes = [
    path('/:city', 'weather.php', 'weather'),
    path('/:city/latest', 'weather.php', 'getLatest'),
]
?>