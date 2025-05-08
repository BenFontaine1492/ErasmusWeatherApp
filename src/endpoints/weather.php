<?php
declare(strict_types=1);

require_once('./ValueObjects/DateTimeValue.php');

/**
 * @param array{
 *     params: array,
 *     user: User,
 *     request: Request,
 * } $args
 */
function weather($args) {

    function getAll($table) {
        $db = new DB;
        $result = $db -> getDataFromTable($table);
        new Response($result);
    }
    
    function getBeetwen(string $from, string $to, $table) {
        $db = new DB;
        $result = $db -> getDataFromTable($table, new DateValue($from), new DateValue($to));
        new Response($result);
    }



    switch($args['request'] -> getMethod()) {
        case 'GET':
            if (!isset($args['params']['city'])) {
                Response::notFound();
            }
            $table = match ($args['params']['city']) {
                'wuerzburg' => 'weather_data_ger',
                'mariehamn' => 'weather_data_fin',
                default => null,
            };
            //Check if table city exists
            if (!$table) {
                Response::notFound('City could not be found');
            }
            
            $getParams = $args['request'] -> getGetData();
            
            try {
                if (isset($getParams['from']) && isset($getParams['to'])) {
                    getBeetwen($getParams['from'], $getParams['to'], $table);
                } else {
                    getAll($table);
                }
            } catch (InvalidArgumentException) {
                Response::badRequest('invalid date time parameters provided');
            }

            break;

        default:
            Response::methodNotAllowed();
    }
}

/**
 * @param array{
 *     params: array,
 *     user: User,
 *     request: Request,
 * } $args
 */
function getLatest($args) {
    //verify Get method is used
    if ($args['request'] -> getMethod() !== 'GET') {
        Response::methodNotAllowed();
    }

    $table = match ($args['params']['city']) {
        'wuerzburg' => 'weather_data_ger',
        'mariehamn' => 'weather_data_fin',
        default => null,
    };
    //Check if table city exists
    if (!$table) {
        Response::notFound('City could not be found');
    }
    
    $db = new DB;
    try {
        $result = $db -> getLastFromTable($table);
    } catch (InvalidArgumentException) {
        Response::badRequest('invalid date time parameters provided');
    }

    new Response($result);
}
?>