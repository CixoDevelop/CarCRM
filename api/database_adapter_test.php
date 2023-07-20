<?php
    require_once("database_adapter.php");

    class database_adapter_tester extends database_adapter{
        /* Add test table */
        public function __construct($connection) {
            $this->connection = $connection;
            $this->table_name = "adapter_test";
            $this->primary_key = "prime";
            $this->rows = [
                "UwU" => "text",
                "Test" => "int"
            ];
        }
        
        /* Make public fork of functions to test */

        public function update_data_fork($data, $where) {
            return $this->update_data($data, $where);
        }

        public function select_data_fork($data = []) {
            return $this->select_data($data);
        }

        public function remove_data_fork($data) {
            return $this->remove_data($data);
        }

        public function append_data_fork($data) {
            return $this->append_data($data);
        }
    }

    require_once("mysql_config.php");

    $connection = new mysqli(
        $APP_MYSQL_CONFIG["hostname"],
        $APP_MYSQL_CONFIG["login"],
        $APP_MYSQL_CONFIG["password"],
        $APP_MYSQL_CONFIG["database"]
    );

    $test_adapter = new database_adapter_tester($connection);

    /* Creating table */
    echo("Creating table...\n");
    $test_adapter->create_table();

    /* Appending data */
    echo("Created, press enter to append data...\n");
    readline();
    $test_adapter->append_data_fork(
        [
            "UwU" => "OwO sample test text",
            "Test" => 10
        ]
    );
    $test_adapter->append_data_fork(
        [
            "UwU" => "test",
            "Test" => 20
        ]
    );

    /* Test selecting data */
    echo("Work, selecting data...\n");
    echo(var_dump($test_adapter->select_data_fork()));
    echo(var_dump($test_adapter->select_data_fork(["UwU" => "test"])));
    echo(var_dump($test_adapter->select_data_fork(["Test" => 10])));

    /* Test updating data */
    echo("Work, press enter to update data...\n");
    readline();
    $test_adapter->update_data_fork(
        ["UwU" => "New UwU", "Test" => 30], 
        ["prime" => 1]
    );
    echo("Updated data...\n");
    echo(var_dump($test_adapter->select_data_fork()));

    /* Removing data */
    echo("Work, press enter to remove...\n");
    readline();
    echo(var_dump($test_adapter->select_data_fork()));
    $test_adapter->remove_data_fork(["UwU" => "test"]);
    echo(var_dump($test_adapter->select_data_fork()));

    /* Remove table */
    echo("Press enter to remove test table...\n");
    readline();
    $test_adapter->remove_table();
?>
