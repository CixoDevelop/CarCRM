<?php
    require_once("database_adapter.php");
    require_once("server_rest.php");

    /**
     * This class is a user class in the system. It manages its permissions, 
     * name and personal data. It is stored in the database as a serialized 
     * object. From the database position, apart from the serialized object, 
     * access is only to the username and password for verification during 
     * login.
     */
    class user {

        /**
         * The constructor of the object, requires the user's nickname, this 
         * is the minimum value to place the user in the database.
         * @param $nick Nick of user
         * @return Instance of new user
         */
        public function __construct($nick) {
            $this->set_nick($nick);
        }

        /** 
         * This feature allows you to change the username. It checks whether 
         * it is valid, that is, whether it does not contain special 
         * characters. Polish characters are allowed.
         * @param $nick New nick for user
         */
        public function set_nick($nick) {
            if (!$this->validate_string($nick)) {
                throw new server_error(70, "Bad chars in user nick!");
            }

            if (strlen($nick) < 4) {
                throw new server_error(80, "Too short nick!");
            }

            $this->nick = $nick;
        }

        /** 
         * Returns the current username.
         * @return Username
         */
        public function get_nick() {
            return $this->nick;
        }

        /** 
         * This function allows you to change personal data of user. New data
         * is setting as user personal data.
         * @param $data New user personal data
         */
        public function update_personal_data($data) {
            if (!is_array($data)) {
                throw new server_error(310, "Personal data not array!");   
            }

            foreach ($data as $key => $value) {
                if (empty($data) or empty($value)) {
                    throw new server_error(320, "Few rows is empty!");
                }
            }

            $this->personal_data = $data;
        }

        /** 
         * This feature allows you to add new personal details or change 
         * existing ones by using an existing key. New values are stored 
         * in the form of an associative array, where the key is the name 
         * of the data, such as name or surname, and the value is the 
         * content of this field. This function validates both keys and 
         * values against forbidden characters.
         * @param $data Array of data to add or change
         * @return True if all went good or false when not
         */
        public function add_personal_data($data) {
            foreach ($data as $key => $value) {
                if (!$this->validate_string($key)) return false;
                if (!$this->validate_string($value)) return false;
            }

            foreach ($data as $key => $value) {
                $this->personal_data[$key] = $value;
            }

            return true;
        }

        /**
         * This function returns an associative array containing personal 
         * data.
         * @return Personal data array
         */
        public function get_personal_data() {
            return $this->personal_data;
        }

        /** 
         * This feature allows you to grant or revoke permissions to a user. 
         * Authorizations are codes in the form of numbers or character 
         * strings, while status is information whether the user should have 
         * an authorization after updating or not.
         * @param $code Privileges code to change
         * @param $status True when user will have privileges, false when not
         */
        public function change_privileges($code, $status) {
            if($status == true) {
                $this->add_privileges($code);
            } else {
                $this->remove_privileges($code);
            }

            $privileges = $this->privileges;
            $this->privileges = [];

            foreach ($privileges as $privilege) {
                if (empty($privilege)) continue;

                array_push($this->privileges, $privilege);
            }
        }
    
        /** 
         * This function checks whether the user has the right to perform the 
         * action with the given code.
         * @param $code Code of privileges to check
         * @return True when user has permissions false when not
         */
        public function check_privileges($code) {
            return in_array($code, $this->privileges);
        }

        /**
         * This function grants the user permission.
         * @param $code Code of privileges to grant
         */
        private function add_privileges($code) {
            if (in_array($code, $this->privileges)) {
                return;
            }

            array_push($this->privileges, $code);
        }

        /** 
         * This function removes the user's permission.
         * @param $code Code of privileges to remove
         */
        private function remove_privileges($code) {
            if (!in_array($code, $this->privileges)) {
                return;
            }

            unset($this->privileges[array_search($code, $this->privileges)]);
        }
        
        /** 
         * This function validates a string by checking that it is not empty 
         * and does not contain any forbidden characters.
         * @param $string String to check
         * @return True if string is propertly of false when not
         */
        private function validate_string($string) {
            if (empty($string)) {
                return false;
            }

            $chars_validator = '/[#$%^&*()+=\-\[\]\';,.\/{}|":<>?~\\\\]/';

            if (preg_match($chars_validator, $string)) {
                return false;
            }

            return true;
        }

        /** 
         * This function returns all user data, such as personal data, 
         * nickname or permissions in the form of an associative array.
         * @return Associative array
         */
        public function collect_data() {
            return [
                "nick" => $this->get_nick(),
                "personal_data" => $this->get_personal_data(),
                "privileges" => $this->privileges
            ];
        }

        /** 
         * This configuration variable of the current user's nickname.
         */
        private string $nick = "";
        
        /** 
         * This associative array stores the user's personal information.
         */
        private array $personal_data = [];

        /** 
         * This array stores the user's permissions.
         */
        private array $privileges = [];
    }

    /** 
     * This class is used to load users into the database, update their 
     * data, delete them from the database, login, etc.
     */
    class user_adapter extends database_adapter {
        /** 
         * The constructor of the class is responsible for creating a 
         * consistent table in the database and managing the connection 
         * to the MySQL server.
         * @param $connection Connection to MySQL object
         * @return New adapter object
         */
        public function __construct($connection) {
            $this->connection = $connection;
            $this->primary_key = "id";
            $this->table_name = "users";
            $this->rows = [
                "apikey" => "char(64)", 
                "nick" => "char(64)", 
                "password" => "char(64)", 
                "object" => "text"
            ];
        }

        /** 
         * This function returns an array of all the users in the database 
         * as an associative array, where the user field is the user object 
         * and the apikey field is the user's API key.
         * @return Array of all users and their API key
         */
        public function get_all_users() {
            $result = $this->select_data();
            $users = [];

            if ($result === false) return []; 

            foreach ($result as $user) {
                $new_user_field = [
                    "user" => $this->unserialize_user($user["object"]),
                    "apikey" => $user["apikey"]
                ];

                array_push($users, $new_user_field);
            }

            return $users;
        }

        /** 
         * This function checks if the nickname is in use by another user. 
         * The nickname must be unique to the user, and once used, it cannot 
         * be used again.
         * @param $nick Nick to check
         * @return True when nick in use or false when not
         */
        public function check_nick_in_use($nick) {
            $condition = ["nick" => $nick];
            $user = $this->select_data($condition);
        
            return !empty($user);
        }
    
        /** 
         * This function creates a new user in the database, based on a user 
         * object of type user, and a password in the form of a string. A new 
         * API key is generated automatically.
         * @param $user User object to insert into database
         * @param $password Password for new user
         * @return True when all went good or false when not
         */
        public function create_user($user, $password) {
            if ($this->check_nick_in_use($user->get_nick())) {
                throw new server_error(10, "Nick already in use.");
            }

            if (strlen($password) < 8) {
                throw new server_error(60, "Password too short!");
            }

            $container = [
                "apikey" => $this->create_apikey(),
                "nick" => $user->get_nick(),
                "password" => $this->hash_password($password),
                "object" => $this->serialize_user($user)
            ];

            if ($this->append_data($container) !== true) return false;

            return $container;
        }

        /** 
         * This function generates a new and unique 64 character API key. 
         * The key is random, the random number comes from a PHP generator.
         * @return New random and unique API key
         */
        private function create_apikey() {
            $new_apikey = "";

            do {
                $new_apikey = bin2hex(random_bytes(32));
            } while($this->user_with_apikey_exists($new_apikey));

            return $new_apikey;
        }

        /** 
         * This function removes the user from the database.
         * @param $user User to remove from database
         * @return True when all went good or false when not
         */
        public function remove_user($user) {
            return $this->remove_data(["nick" => $user->get_nick()]) === true;
        }

        /** 
         * This function checks that user with given API key exists in 
         * database, and return boolean.
         * @param $apikey API key to check user with it
         * @return True when user exists or false when not
         */
        public function user_with_apikey_exists($apikey) {
            return !empty($this->select_data(["apikey" => $apikey]));
        }

        /**
         * This function checks that user with given nick and password exists 
         * in database, that is usefull when login new user into system.
         * @param $nick Nick of user
         * @param $password Password of user
         * @return True when user exists or false when not
         */
        public function check_user_password($nick, $password) {
            $login_data = [
                "nick" => $nick, 
                "password" => $this->hash_password($password)
            ];
            
            return !empty($this->select_data($login_data));
        }

        /** 
         * This function retrieves a user object using an API key. If no such 
         * user exists, an error will be thrown.
         * @param $apikey API key of user to select
         * @return User with given API key
         */
        public function get_user_by_apikey($apikey) {
            $user = $this->select_data(["apikey" => $apikey]);

            if (empty($user)) {
                throw new server_error(20, "User with this API key not exists");
            }

            return $this->unserialize_user($user[0]["object"]);
        }

        /** 
         * This function is used to download the user's API key using his 
         * login data, i.e. nickname and password. If no such user exists, 
         * an error will be thrown.
         * @param $nick Nick of user to select
         * @param $password Password of user to select
         * @return API key of user
         */
        public function login_user($nick, $password) {
            $login_data = [
                "nick" => $nick, 
                "password" => $this->hash_password($password)
            ];
            $user = $this->select_data($login_data);

            if (empty($user)) {
                throw new server_error(30, "User with this login data not exists");
            }

            return $user[0]["apikey"];
        }

        /**
         * This function updates the user in the database. As the user to be 
         * updated we specify the old user object, and as the new one we 
         * specify a clone of the old object on which we made changes.
         * @param $old_user Old user to update it
         * @param $new_user New user to save instead of old user object
         * @return True when all went good or false when not
         */
        public function save_user($old_user, $new_user) {
            if (
                $old_user->get_nick() !== $new_user->get_nick() and 
                $this->check_nick_in_use($new_user->get_nick())
            ) {
                throw new server_error(40, "New nick already in use.");
            }

            $condition = ["nick" => $old_user->get_nick()];
            $new_values = [
                "nick" => $new_user->get_nick(),
                "object" => $this->serialize_user($new_user)
            ];

            return $this->update_data($new_values, $condition);
        }

        /** 
         * This function is used to change the user's password, as the user 
         * for whom we are to change the password, we provide the user object, 
         * and the next argument is the new password in the form of a string.
         * @param $user User object to change their password
         * @param $new_password New password of user
         * @return True when all went good or false when not
         */
        public function change_password($user, $new_password) {
            if (strlen($new_password) < 8) {
                throw new server_error(60, "Password too short!");
            }

            $condition = ["nick" => $user->get_nick()];
            $new_values = ["password" => $this->hash_password($new_password)];

            return $this->update_data($new_values, $condition);
        }

        /** 
         * This function ensures that the password in the database is stored 
         * in a secure hash form. It adds a salt to the password and hashes 
         * it with the SHA256 algorithm.
         * @param $password Password to hash it
         * @return Hashed password
         */
        private function hash_password($password) {
            return hash("sha256", $password."UwUsalt");
        }

        /** 
         * This function serialize user and change serialized object to string
         * form with base 64 function. User object serialized with that 
         * function can be safety save in database.
         * @param $user User object to save it
         * @return Serialized user object
         */
        private function serialize_user($user) {
            return base64_encode(serialize($user));
        }

        /** 
         * This function deserializes the user object from the database into 
         * a normal PHP object that can then be used normally.
         * @param $serialized Serialized user object from database
         * @return Unserialized user object from database
         */
        private function unserialize_user($serialized) {
            return unserialize(base64_decode($serialized));
        }
    }
?>
