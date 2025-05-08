<?php
class Request {

    private array $post;
    private array $put;
    private array $get;
    private array $delete;
    private string $method;

    /**
     * Parameters only to be used in testing and wont work by default!
     */
    function __construct(string|null $method = null, string|null $data = null) {
        $requestData = $data ?? file_get_contents('php://input');
        $this -> method = $method ?? $_SERVER['REQUEST_METHOD'];

        if(isset(getallheaders()["Content-Type"])){
            $requestContentType = getallheaders()["Content-Type"];
        } else {
            $requestContentType = null;
        }
        if($this -> json_validate($requestData)){
            switch($this -> method){
                case "POST":
                    $this -> post = json_decode($requestData, true);
                    break;
                case "PUT":
                    $this -> put = json_decode($requestData, true);
                    break;
                case "DELETE":
                    $this -> delete = $_GET;
                    break;
                case "GET":
                    $this -> get = $_GET;
                    break;        
            }
        } elseif(($this -> method === "PUT" || $this -> method === "POST") && ($requestContentType !== "application/json" && $requestContentType !== null)){
            Response::badRequest('Json request body expected');
        } else {
            $this -> post = [];
            $this -> put = [];
            $this -> get = $_GET;
        }
    }

    private function json_validate(string $string): bool {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    function getGetData() {
        return $this -> get;
    }

    function getDeleteData() {
        return $this -> delete;
    }

    function getPostData() {
        return $this -> post;
    }

    function getPutData() {
        return $this -> put;
    }

    function getMethod() {
        return $this -> method;
    }

    static function getAuthorizationHeader() {
        return getallheaders()['Authorization'] ?? false;
    }

    function validateMethod(string $requestedMethod){
        if (strtolower($_SERVER['REQUEST_METHOD']) !== strtolower($requestedMethod)){
            return Response::methodNotAllowed('The requested resource requires annother method', strtoupper($requestedMethod));
        }
    }
}
?>