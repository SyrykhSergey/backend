<?php

    global $link, $UploadDir;
    function getData($method)
    {
        $data = new stdClass();
        if ($method != "GET")
        {
            $data->body = json_decode(file_get_contents('php://input'));

        }
        $data->parameters = [];
        $dataGet = $_GET;
        foreach ($dataGet as $key => $value) {
            if ($key != "q")
            {
                $data->parameters[$key] = $value;
            }
        }
        return $data;
    }


    function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }




    header('Content-type: application/json');
    $link = mysqli_connect("127.0.0.1", "root", "", "kt");
    $UploadDir = "uploads";

    if (!$link) {
        echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
        echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
        echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
        exit;
    }

    $url = isset($_GET['q']) ? $_GET['q']: '';
    $url = rtrim($url, "/");
    $urlList = explode('/',$url);

    //echo json_encode($urlList);

    $router = $urlList[0];
    $requestData = getData(getMethod());


    $method = $_SERVER['REQUEST_METHOD'];


    if(file_exists(realpath(dirname(__FILE__)) . '/routers/' . $router . '.php'))
    {
        include_once 'routers/' . $router . '.php';

        route($method, $urlList, $requestData,$link);
    }
    else{
        header('HTTP/1.0 400 Bad Request');
        echo json_encode(array(
            'error' => 'Bad Path'
        ));

    }



?>

