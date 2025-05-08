<?php
declare(strict_types=1);

require_once("./response.php");
require_once("./routes.php");
require_once("./router.php");

$uri = new Uri;
$router = new Router($routes);

$routeObject = $router -> findRoute($uri -> getUrlArr(''));

//Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    new Response([], 204);
}

if ($routeObject -> route) {
    require_once './request.php';
    require_once './db.php';

    //include routed file
    require_once './endpoints/' . $routeObject -> route -> file;
    //Call routed function
    call_user_func_array($routeObject -> route -> function, [['params' => $routeObject -> params, 'request' => new Request()]]);
} else {
    Response::notFound();
}
?>