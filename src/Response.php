<?php
class Response {
    public int $statusCode = 200;
    public object|array $responseObject;

    function __construct(object|array $responseObject, int $httpStatusCode = 200) {
        $this -> statusCode = $httpStatusCode;
        $this -> responseObject = $responseObject;

        $json = json_encode($responseObject, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        header('Access-Control-Allow-Origin: http://localhost:8080');
        header('Content-Type: application/json');
        http_response_code($httpStatusCode);

        if ($httpStatusCode !== 204) {
            header('Content-Type: application/json;charset=utf-8');
            echo $json;
        }
        exit();
    }


    static function badRequest(string $errorMsg, array $errorsArr = []) {
        $response = new stdClass();
        $response -> error = 'Bad request';
        if ($errorMsg !== '') {
            $response -> msg = $errorMsg;
        }
        if (count($errorsArr) > 0) {
            $response -> errors = $errorsArr;
        }
        return new Response($response, 400);
    }

    static function unauthorized ($errorMsg = 'You need to have a valid auth token to see this') {
        $response = new stdClass();
        $response -> error = 'Unauthorized';
        $response -> msg = $errorMsg;
        return new Response($response, 401);
    }

    static function forbidden ($errorMsg = 'You are not allowed to see this') {
        $response = new stdClass();
        $response -> error = 'Forbidden';
        $response -> msg = $errorMsg;
        return new Response($response, 403);
    }

    static function notFound ($errorMsg = "The endpoint doesn't exist") {
        $response = new stdClass();
        $response -> error = 'Not Found';
        $response -> msg = $errorMsg;
        return new Response($response, 404);
    }

    static function methodNotAllowed ($errorMsg = "The requested resource requires another method", $method = false) {
        $response = new stdClass();
        $response -> error = 'Method Not Allowed';
        $response -> msg = $errorMsg;
        $method && $response -> expected_method = $method;
        return new Response($response, 405);
    }

    static function internalError ($errorMsg = 'An error ocured') {
        $response = new stdClass();
        $response -> error = 'Internal Server Error';
        $response -> msg = $errorMsg;
        return new Response($response, 500);
    }
}
?>