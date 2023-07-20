<?php
    require_once("mysql_config.php");
    require_once("user.php");
    require_once("car.php");

    $connection = new mysqli(
        $APP_MYSQL_CONFIG["hostname"],
        $APP_MYSQL_CONFIG["login"],
        $APP_MYSQL_CONFIG["password"],
        $APP_MYSQL_CONFIG["database"]
    );

    $user_adapter = new user_adapter($connection);
    
    echo("Creating table for users... ");

    try {
        $user_adapter->create_table();
        echo("Success!\n");
    } catch (Exception $exception) {
        echo("FAIL!!!\n");
        echo("Error: ".$exception->getMessage()."\n");
    }

    
    echo("Creating user 'admin' with password 'admin'... ");

    try {
        $admin = new user("admin");
        $admin->change_privileges("system_admin", true);

        $user_adapter->create_user($admin, "admin");    
        echo("Success!\n");
    } catch (Exception $exception) {
        echo("FAIL!!!\n");
        echo("Error: ".$exception->getMessage()."\n");
    }

    echo("Creating database for cars...");

    $car_adapter = new car_adapter($connection);

    try {
        $car_adapter->create_table();
        echo("Success!\n");
    } catch (Exception $exception) {
        echo("FAIL!!!\n");
        echo("Error: ".$exception->getMessage()."\n");
    }
?>
