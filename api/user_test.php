<?php
    require_once("user.php");

    function dump($var) {
        echo(var_dump($var));
    }

    $user_1 = new user("UwU");

    dump($user_1);
    dump($user_1->get_personal_data());
    dump($user_1->add_personal_data(["Imię" => "Cixo", "Nazwisko" => "Jakiś"]));
    dump($user_1->get_personal_data());
    dump($user_1->add_personal_data(["Imię" => "CixoUwU", "Wiek" => "17"]));
    dump($user_1->get_personal_data());
    dump($user_1->add_personal_data(["Imię" => "CixoUwU!-", "Wiek" => "18"]));
    dump($user_1->get_personal_data());
    
    dump($user_1->get_nick());
    dump($user_1->set_nick("OwO"));
    dump($user_1->get_nick());

    dump($user_1->check_privileges(10));
    $user_1->change_privileges(10, true);
    dump($user_1->check_privileges(10));
    $user_1->change_privileges(10, false);
    dump($user_1->check_privileges(10));
    $user_1->change_privileges(20, true);
    $user_1->change_privileges(40, true);

    $user_1_serialized = serialize($user_1);
    $user_1_copy = unserialize($user_1_serialized);

    dump($user_1);
    dump($user_1_copy);

    class user_test_adapter extends user_adapter {
        public function __construct($connection) {
            parent::__construct($connection);
            $this->table_name = "users_test";
        }
    }
   
    require_once("mysql_config.php");

    $connection = new mysqli(
        $APP_MYSQL_CONFIG["hostname"],
        $APP_MYSQL_CONFIG["login"],
        $APP_MYSQL_CONFIG["password"],
        $APP_MYSQL_CONFIG["database"]
    );


    $adapter = new user_test_adapter($connection);

    echo("Creating table...\n");
    $adapter->create_table();

    echo("Append users...\n");
    $adapter->create_user(new user("Test1"), "Test");
    $adapter->create_user(new user("Test2"), "Test");
    

    echo("All users...\n");

    $test_apikey = $adapter->get_user_apikey_by_login_data("Test1", "Test");
    echo("Apikey Test1: ".$test_apikey."\n");

    $test_apikey = $adapter->get_user_apikey_by_login_data("Test2", "Test");
    echo("Apikey Test2: ".$test_apikey."\n");
    
    echo("Getting user by apikey: \n");
    $test_user = $adapter->get_user_by_apikey($test_apikey);
    dump($test_user);

    echo("Updating user info...\n");
    $new_test_user = clone $test_user;
    $new_personal_data = ["Name" => "Adam", "Surname" => "Mankowski"];
    if (!$new_test_user->add_personal_data($new_personal_data)) {
        echo("Error whem adding new personal data...\n");
    }
    $new_test_user->set_nick("UwU");
    dump($new_test_user);
    echo("\n");
    if (!$adapter->save_user($test_user, $new_test_user)) {
        echo("Error when saving user...\n");
    }
    $get_new_user = $adapter->get_user_by_apikey($test_apikey);
    dump($get_new_user);
    echo("\n");

    echo("Change user password...\n");
    $adapter->change_password($get_new_user, "UwUpassword");
    echo("Check user by old password: ");
    dump($adapter->user_with_login_data_exists("UwU", "Test"));
    echo("Check user by new password: ");
    dump($adapter->user_with_login_data_exists("UwU", "UwUpassword"));

    echo("Remove user...\n");
    dump($adapter->get_all_users());
    echo("Remove result: ");
    dump($adapter->remove_user($get_new_user));
    echo("After removing...\n");
    dump($adapter->get_all_users());

    echo("Press enter to remove table...\n");
    readline();
    $adapter->remove_table();
?>
