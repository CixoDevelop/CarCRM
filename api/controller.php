<?php

    /* Debug only!! */
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once("server_rest.php");
    require_once("mysql_config.php");
    require_once("user.php");
    require_once("user_controller.php");
    require_once("car.php");
    require_once("car_controller.php");

    $connection = new mysqli(
        $APP_MYSQL_CONFIG["hostname"],
        $APP_MYSQL_CONFIG["login"],
        $APP_MYSQL_CONFIG["password"],
        $APP_MYSQL_CONFIG["database"]
    );

    $file_adapter = new file_adapter("../car_data", "car_data");

    $user_adapter = new user_adapter($connection);
    $user_controller = new user_controller($user_adapter);

    $car_adapter = new car_adapter($connection);
    $car_controller = new car_controller(
        $user_adapter, 
        $car_adapter, 
        $file_adapter
    );

    $server = new server_rest("/car_crm_finaly");

    $server->register_controller(
        [
            "/user" => $user_controller,
            "/car" => $car_controller
        ]
    );

    echo($server->response(
        $_SERVER["REQUEST_URI"], 
        $_SERVER["REQUEST_METHOD"]
    ));
?>
