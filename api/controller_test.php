<?php

    /* Debug only!! */
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    echo($_SERVER["REQUEST_URI"]);

    require_once("server_rest.php");

    class test_controller extends simple_controller {
        public function get() {
            return "OwO GET!";
        }

        public function put() {
            return "UwU PUT!";
        }
    }

    class test_simple_tree extends simple_tree {
        public function uwu($method) {
            return "UwU tree!";
        }

        public function owo($method) {
            return "OwO in tree!";
        }
    }

    $server = new server_rest("/car_crm");

    $server->register_controller(
        [
            "/uwu" => function () {
                $x = 2;
                $y = 3;
                echo($x + $y);
            },
            "/owo" => "Coś UwU niezwykłego",
            "/test_controller" => new test_controller(),
            "/simple_tree" => new test_simple_tree
        ]
    );

    echo($server->response($_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"]));
?>
