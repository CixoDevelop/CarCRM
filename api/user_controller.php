<?php
    require_once("server_rest.php");
    require_once("user.php");

    class user_controller extends simple_tree {
        public function __construct($adapter) {
            $this->adapter = $adapter;

            parent::__construct();
        }

        /** 
         * API ENDPOINT /get_all (GET)
         * This endpoint returns all users, accepts "apikey" as parameters, 
         * i.e. the user's API key, which should be an administrator. Resets
         * the array of all users on the system.
         * @param $method Method of the request
         * @return Body of the response
         */
        public function API_get_all($method) {
            $required = ["apikey" => "admin"];

            $check_result = $this->check_request(
                $method, "GET",
                $_GET, $required
            );

            if ($check_result !== true) return $check_result;
           
            $response = [];
            $users = $this->adapter->get_all_users();

            foreach ($users as $user) {
                array_push($response, [
                    "apikey" => $user["apikey"],
                    "user" => $user["user"]->collect_data()
                ]);
            }

            return $this->success($response);
        }

        /** 
         * API ENDPOINT /update_personal_data (POST, JSON)
         * This API endpoint allows you to update the user's personal data, 
         * as parameters it accepts "apikey", i.e. the user's API key that 
         * performs the query, "destination_user_apikey", i.e. the user's API 
         * key to be modified, and "personal_data", i.e. an array with new 
         * data of the user. As a response, we will get success if 
         * everything was successful, or an error message.
         * @param $method Method of the request
         * @return Body of the response
         */
        public function API_update_personal_data($method) {
            $received = $this->get_post_as_json();
            $required = [
                "apikey" => "user", 
                "destination_user_apikey" => "user", 
                "personal_data" => "array"
            ];
            
            $check_result = $this->check_request(
                $method, "POST",
                $received, $required
            );

            if ($check_result !== true) return $check_result;

            $apikey = $received["apikey"];
            $destination = $received["destination_user_apikey"];
            $personal_data = $received["personal_data"];

            $possibility_change = $this->user_can_make_change( 
                $apikey, $destination 
            );

            if ($possibility_change !== true) return $possibility_change;

            $user = $this->adapter->get_user_by_apikey($destination);
            $old = clone $user;
            
            $user->update_personal_data($personal_data);
                
            if ($this->adapter->save_user($old, $user) !== true) {
                throw new server_error(180, "Cannot save user!");
            }

            return $this->success();
        }

        /** 
         * API ENDPOINT /update_password (POST, JSON)
         * This endpoint allows you to change the user's password. As 
         * parameters, it accepts "apikey", i.e. the API key of the user who 
         * makes the request, "destination_user_apikey", i.e. the API key of 
         * the user whose password we change, and "password", i.e. his new 
         * password. As a response we will get an error code or success if 
         * everything went well.
         * @param $method Method of the request
         * @return Body of the response
         */
        public function API_update_password($method) {
            $received = $this->get_post_as_json();
            $required = [
                "apikey" => "user", 
                "destination_user_apikey" => "user", 
                "password" => "string"
            ];

            $check_result = $this->check_request(
                $method, "POST",
                $received, $required
            );

            if ($check_result !== true) return $check_result;

            $apikey = $received["apikey"];
            $destination = $received["destination_user_apikey"];
            $password = $received["password"];

            $possibility_change = $this->user_can_make_change(
                $apikey, 
                $destination
            );

            if ($possibility_change !== true) return $possibility_change;

            $update = $this->adapter->change_password(
                $this->adapter->get_user_by_apikey($destination), 
                $password
            );

            if ($update !== true) return $update;

            return $this->success();
        }

        /** 
         * API ENDPOINT /change_permission (POST, JSON)
         * This endpoint allows you to change user permissions. As parameters 
         * it accepts: "apikey", i.e. the API key of the user with 
         * administrator privileges, "destination_user_apikey", i.e. the API 
         * key of the user whose permissions are changed, "permission", i.e. 
         * the permission to change, "new_state", i.e. "true" if the 
         * permission is to be granted and "false" if it is to be received. 
         * We will receive an error or a success message as a response.
         * @param $method Method of the request
         * @return Body of the response
         */
        public function API_change_permission($method) {
            $received = $this->get_post_as_json();
            $required = [
                "apikey" => "admin", 
                "destination_user_apikey" => "user", 
                "permission" => "string", 
                "new_state" => "string"
            ]; 
            
            $check_result = $this->check_request(
                $method, "POST",
                $received, $required
            );

            if ($check_result !== true) return $check_result;

            $apikey = $received["apikey"];
            $destination = $received["destination_user_apikey"];
            $permission = $received["permission"];
            $state = $received["new_state"] === "true";
           
            $target = $this->adapter->get_user_by_apikey($destination);
            $old = clone $target;

            if ($permission === "system_admin" and $apikey === $destination) {
                throw new server_error(
                    190, 
                    "You cannot drop Your admin privileges"
                );
            }

            $target->change_privileges($permission, $state);
                
            if ($this->adapter->save_user($old, $target) !== true) {
                throw new server_error(500, "Database error!");
            }

            return $this->success();
        }

        /** 
         * API ENDPOINT /drop (POST, JSON)
         * This endpoint allows you to delete a user, as parameters it 
         * accepts: "apikey", i.e. the user's API key, which must be an 
         * admin, and "deleted", i.e. the api key of the user we want to 
         * delete. If everything goes well, we will get a success answer.
         * @param $method Method of the request
         * @return Body of the response
         */
        public function API_drop($method) {
            $received = $this->get_post_as_json();
            $required = [
                "apikey" => "admin",
                "deleted" => "user"
            ];

            $check_result = $this->check_request(
                $method, "POST",
                $received, $required
            );

            if ($check_result !== true) return $check_result;
            
            $apikey = $received["apikey"];
            $deleted = $received["deleted"];

            if ($apikey === $deleted) {
                throw new server_error(90, "You cannot remove Yourself!");
            }

            $deleter = $this->adapter->get_user_by_apikey($deleted);

            if (!$this->adapter->remove_user($deleter)) {
                throw new server_error(100, "Database error!");
            }

            return $this->success();
        }

        /** 
         * API ENDPOINT /create (POST, JSON)
         * This API endpoint creates a new system user, accepts as parameters:
         * "apikey", i.e. the user's API key, which must be an admin, "nick", 
         * i.e. the nick for the new user, "password", i.e. the password for 
         * the new user. If all goes well, it returns the new user's API key.
         * @param $method Method of the request
         * @return Body of the response
         */
        public function API_create($method) {
            $received = $this->get_post_as_json();
            $required = [
                "apikey" => "admin",
                "nick" => "string",
                "password" => "string"
            ];
        
            $check_result = $this->check_request(
                $method, "POST",
                $received, $required
            );

            if ($check_result !== true) return $check_result;

            $apikey = $received["apikey"];
            $nick = $received["nick"];
            $password = $received["password"];
            $user = new user($nick);
            $new_user = $this->adapter->create_user($user, $password);

            if ($new_user === false) {
                throw new server_error(500, "Cannot create user!");
            }

            return $this->success($new_user["apikey"]);
        }

        /** 
         * API ENDPOINT /get (GET)
         * This endpoint returns the user's data, as parameters it takes: 
         * "apikey", which is the api key of the desired user.
         * @param $method Method of the request
         * @return Body of the response
         */
        public function API_get($method) {
            $required = ["apikey" => "user"];

            $check_result = $this->check_request(
                $method, "GET",
                $_GET, $required
            );

            if ($check_result !== true) return $check_result;

            $user = $this->adapter->get_user_by_apikey($_GET["apikey"]);

            return $this->success($user->collect_data());
        }

        /**
         * API ENDPOINT /login (GET)
         * This endpoint allows you to log into the system. As parameters it 
         * accepts "nick", which is the user's nickname in the system, and 
         * "password", which is the user's password in the system. If all 
         * goes well, it will return that user's API key.
         * @param $method Method of the request
         * @return Body of the response
         */
        public function API_login($method) {
            $required = [
                "nick" => "string",
                "password" => "string"
            ];


            $check_result = $this->check_request(
                $method, "GET",
                $_GET, $required
            );

            if ($check_result !== true) return $check_result;
    
            $nick = $_GET["nick"];
            $password = $_GET["password"];

            if (!$this->adapter->check_user_password($nick, $password)) {
                throw new server_error(240, "Bad nick or password!");
            }
                
            return $this->success(
                $this->adapter->login_user($nick, $password)
            );
        }
        
        /** 
         * This function checks whether the request is valid, whether the 
         * method of the request matches, and whether the query parameters 
         * provided by the user are of the correct type. In addition to PHP's 
         * built-in types, you can also query for the "user" type, which will 
         * check if the parameter is an existing user's API key, and for the 
         * "admin" type, which will check if the user is a valid 
         * administrator. The table that is a model for request parameters is 
         * to be an associative array containing the name of the parameter 
         * from the request as a key, and the type of this parameter as value.
         * @param $method Method of request
         * @param $required_method Required method for request
         * @param $params Params that user send
         * @param $required_params Required params model table
         */
        private function check_request(
            $method, 
            $required_method, 
            $params, 
            $required_params
        ) {
            if ($method !== $required_method) {
                throw new server_error(130, "Use ".$required_method."!");
            }

            foreach ($required_params as $param => $type) {
                if (!array_key_exists($param, $params)) {
                    throw new server_error(110, $param." not in request!");
                }

                if ($type === "user" or $type === "admin") {
                    $check = $this->check_user_is_valid($params[$param]);
                    
                    if ($type === "admin") {     
                        $check = $this->check_user_is_admin($params[$param]);
                    }
                    
                    if ($check === true) continue;

                    return $check;
                }

                if (gettype($params[$param]) !== $type) {
                    throw new server_error(120, $param." in bad type!");
                }
            }

            return true;
        }

        /** 
         * This function checks whether the user can make changes, i.e. 
         * whether the user trying to perform an action on another user is an 
         * administrator, or whether the target user and admin are the same.
         * @param $performer API key of performer user
         * @param $destination API key of destination user
         * @return True when user can make change or string with error
         */
        private function user_can_make_change($performer, $destination) {
            if ($performer === $destination) return true;

            $performer_user = $this->adapter->get_user_by_apikey($performer);

            if (!$performer_user->check_privileges("system_admin")) {
                throw new server_error(100, "Performer not system_admin!");
            }

            return true;
        }
    
        /** 
         * This function checks if the user is valid, if the given parameter 
         * is a string, and if such a user exists in the database.
         * @param $apikey Apikey of user to check
         * @return True when all is good or string with error
         */
        private function check_user_is_valid($apikey) {
            if (!is_string($apikey)) {
                throw new server_error(140, "User API key not string!");
            }

            if (!$this->adapter->user_with_apikey_exists($apikey)) {
                throw new server_error(150, "User ".$apikey." not exists!");
            }

            return true;
        }

        /** 
         * This function checks if the user is valid and is also a system 
         * administrator.
         * @param $apikey API key of user to check
         * @return True when all is good or string with error.
         */
        private function check_user_is_admin($apikey) {
            $valid = $this->check_user_is_valid($apikey);

            if ($valid !== true) return $valid;

            $user = $this->adapter->get_user_by_apikey($apikey);

            if (!$user->check_privileges("system_admin")) {
                throw new server_error(160, $user->get_nick()." not admin!");
            }

            return true;
        }

        /** 
         * This variable holds the database adapter for users.
         */
        private $adapter;
    }
?>
