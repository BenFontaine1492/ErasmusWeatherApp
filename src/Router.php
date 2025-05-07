<?php
declare(strict_types = 1);


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


class Router {

    private array $routes = [];
    private array $routesObj;
    public stdClass $route;

    function __construct(array $routes) {
        $this -> routesObj = $routes;
        foreach ($routes as $route) {
            array_push($this -> routes, $route -> path);
        }
    }
    
    public function findRoute(array $uri) {
        $return = new stdClass();
        $return -> route = false;
        $return -> params = [];

        if (count($uri) === 0) {
            $this -> route = $this -> routesObj[0];
            $return -> route = $this -> routesObj[0];
            return $return;
        }
        foreach ($this -> routes as $index => $route) {

            $route = array_slice(explode('/',strtolower($route)), 1);
            /*
            if ($uri[0] != $route[0]) {
                continue;
            }
            */
            if (count($uri) != count($route)){
                continue;
            }
            foreach ($route as $i => $comp) {
                if ($comp[0] === ':'){
                    if (is_string($uri[$i])) {
                        $return -> params[substr($comp, 1)] = $uri[$i];
                        continue;
                    } else {
                        continue 2;
                    }
                } elseif ($uri[$i] === $comp) {
                    continue;
                } else {
                    continue 2;
                }
            }
            $this -> route = $this -> routesObj[$index];
            $return -> route = $this -> routesObj[$index];
            return $return;
        }
        return $return;
    }
}

class Uri {

    public $uri;

    function __construct() {
        $this -> uri = $_SERVER['REQUEST_URI'];
    }
    /**
     * converts raw uri to a usable uri array
     * @param string $root the api's parent directory
     * @return array The usable uri array
     */
    function getUrlArr(string $root): Array {
        $uriArrRoot = explode('/', strtolower($this -> uri));
        //check if root is in root
        if ($root !== '') {
            $rootIndex = array_search(strtolower($root), $uriArrRoot);
        } else {
            $rootIndex = 0;
        }
        //Error if root isn't found
        if (!is_integer($rootIndex)) {
            throw new Error('The given root could not be found');
        }

        //Remove get parameters
        $uriArr = array_slice($uriArrRoot, $rootIndex + 1);
        $lastComp = explode('?', end($uriArr));
        $uriArr[count($uriArr) - 1] = $lastComp[0];

        //fix if trailing slash
        if (end($uriArr) === '') {
            array_pop($uriArr);
        }
        return $uriArr;
    }
}
?>