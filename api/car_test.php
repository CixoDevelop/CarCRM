<?php
    require_once("car.php");

    function dump($x) { echo(var_dump($x)); }

    $test_car_simple = new car(
        1234, 
        "", 
        "", 
        [
            "UwU" => "OwO", 
            "Cos" => "Inne"
        ]
    );

    dump($test_car_simple);

    $test_car_with_error = null;
    
    try {
        $test_car_with_error = new car(
            1234, 
            "", 
            "", 
            [
                "UwU" => [], 
                "Cos" => "Inne"
            ]
        );
    } catch (server_error $e) {
        echo ($e->cause."\n");
    }

    try {
        $test_car_with_error = new car(
            1234, 
            "", 
            "", 
            [
                "UwU" => "OwO", 
                "" => "OwO"
            ]
        );
    } catch (server_error $e) {
        echo ($e->cause."\n");
    }

    try {
        $test_car_with_error = new car(
            1, 
            "OwO", 
            "Document",
        );
    } catch (server_error $e) {
        echo ($e->cause."\n");
    }

    try {
        $test_car_with_error = new car(
            1, 
            "/etc/hostname" 
        );
    } catch (server_error $e) {
        echo ($e->cause."\n");
    }

    dump($test_car_with_error);

    class car_adapter_test extends car_adapter {
        public function __construct($connection) {
            parent::__construct($connection);
            $this->table_name = "cars_test";
        }
    }

    require_once("mysql_config.php");

    $connection = new mysqli(
        $APP_MYSQL_CONFIG["hostname"],
        $APP_MYSQL_CONFIG["login"],
        $APP_MYSQL_CONFIG["password"],
        $APP_MYSQL_CONFIG["database"]
    );


    $adapter = new car_adapter_test($connection);

    try {
        $adapter->create_table();
    } catch (Exception $e) {
        echo($e->getMessage());
        $adapter->remove_table();
        $adapter->create_table();
    }

    dump($adapter->get_all());
    dump($adapter->create_car());
    dump($adapter->create_car());
    dump($adapter->get_all());
    dump($adapter->get_by_id(1));

    $car_to_update = $adapter->get_by_id(1);
    $car_to_update->set_photo("/etc/hostname");
    $car_to_update->set_params([
        "UwU" => "OwO",
        "QwQ" => "QwQ"
    ]);

    $adapter->store($car_to_update);
    dump($adapter->get_by_id(1));

    try {
        $adapter->remove_table();
    } catch (Exception $e) {
        echo($e->getMessage());
    }
?>
