<?php

declare(strict_types=1);

header('Access-Control-Allow-Origin: http://localhost:8080');

require_once("./Router.php"); 

$router = new Router;
$queryData = $router->route();

require_once("./DataFetcher.php");

$fetcher = new DataFetcher;
$result = $fetcher->fetch($queryData);

echo $result;

?>
