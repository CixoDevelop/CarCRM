<?php

    /** 
     * This class is error that can be catched by server, and return to
     * end user as JSON, with error_code ans cause fields.
     */
    class server_error extends Exception {
        
        /**
         * This is constructor, that return new error object, with 
         * error_code and cause, to return to user as JSON.
         * @param $error_code Code of error
         * @param $cause Cause of error
         * @return New error object
         */
        public function __construct($error_code, $cause) {
            $this->error_code = strval($error_code);
            $this->cause = $cause;
        }

        /** 
         * This is error code.
         */
        public string $error_code = "";
        
        /** 
         * This is error cause.
         */
        public string $cause = "";
    }

    /** 
     * This class is a simple tree. It allows you to register a controller 
     * that has many endpoints at once. Any method of a function that 
     * inherits from simple_tree will be an endpoint whose relative address 
     * is the name of that function.
     */
    abstract class simple_tree {
        
        /** 
         * This class is a simple tree. It allows you to register a controller 
         * that has many endpoints at once. Any method of a function that 
         * inherits from simple_tree will be an endpoint whose relative 
         * address is the name of that function.
         * @return New instance of simple tree
         */
        public function __construct() {
            $this->paths = [];
            $methods = get_class_methods(__CLASS__);

            foreach (get_class_methods($this) as $method) {
                if (substr($method, 0, 4) !== "API_") continue;

                $this->paths["/".substr($method, 4)] = [$this, $method];
            }
        }

        /**
         * This function return paths of simple tree.
         * @return Paths of simple tree
         */
        public function get_paths() {
            return $this->paths;
        }

        /** 
         * This function returns the sent data as a response from the JSON 
         * server containing information that the query was successful.
         * @param $content Content of response
         * @return Success response as JSON
         */
        protected function success($content = null) {
            header('Content-Type: application/json; charset=utf-8');

            $response = "";

            if ($content === null) $response = "Null";
            if (is_string($content)) $response = $content;
            if (is_array($content)) $response = $content;
            if (is_numeric($content)) $response = strval($content);

            return json_encode([
                "status" => "success",
                "response" => $response
            ]);
        }

        /** 
         * This function returns the data sent in the body of a POST or 
         * similar request as an array of JSON data.
         * @return Request body as JSON
         */
        protected function get_post_as_json() {
            $data = json_decode(file_get_contents("php://input"), true);

            if ($data === null) $data = [];

            return $data;
        }
  
        /** 
         * This array store paths in simple tree.
         */
        private array $paths;
    }

    /**
     * This class is a simple interface for REST controllers. It's used by 
     * the server class to recognize that it's dealing with a class-form 
     * controller, and to make HTTP method sorting cleaner, with the 
     * controller making each method just a new function.
     */
    abstract class simple_controller {

        /** 
         * This function is a method that must respond to a DELETE request. 
         * The returned string of characters will be sent to the user in its 
         * entirety, and to modify the header parameters, the standard method 
         * header();
         * @param $path Path from server
         * @return Content to send to user
         */
        public function delete() {
            header("HTTP/1.1 501 Not Implemented");
            return "Not Implemented";
        }
        
        /** 
         * This function is a method that must respond to a GET request. The
         * returned string of characters will be sent to the user in its 
         * entirety, and to modify the header parameters, the standard method 
         * header();
         * @param $path Path from server
         * @return Content to send to user
         */
        public function get() {
            header("HTTP/1.1 501 Not Implemented");
            return "Not Implemented";
        }
        
        /** 
         * This function is a method that must respond to a OPTIONS request. 
         * The returned string of characters will be sent to the user in its 
         * entirety, and to modify the header parameters, the standard method 
         * header();
         * @param $path Path from server
         * @return Content to send to user
         */
        public function options() {
            header("HTTP/1.1 501 Not Implemented");
            return "Not Implemented";
        }   

        /** 
         * This function is a method that must respond to a POST request. The
         * returned string of characters will be sent to the user in its 
         * entirety, and to modify the header parameters, the standard method 
         * header();
         * @param $path Path from server
         * @return Content to send to user
         */
        public function post() {
            header("HTTP/1.1 501 Not Implemented");
            return "Not Implemented";
        }

        /** 
         * This function is a method that must respond to a PUT request. The
         * returned string of characters will be sent to the user in its 
         * entirety, and to modify the header parameters, the standard method 
         * header();
         * @param $path Path from server
         * @return Content to send to user
         */

        public function put() {
            header("HTTP/1.1 501 Not Implemented");
            return "Not Implemented";
        }

    }

    /**
     * This class is a REST server. It allows you to map requests sent to a 
     * standard server, like Apache, in a nice way, using controllers or 
     * functions. As endpoints, you can provide either a static string or a 
     * function that will receive path from the user's URL as the first 
     * parameter, and the user's HTTP method as the second parameter, or a 
     * class as a controller. The class of such a controller must inherit 
     * from simple_controller and override its functions. These functions 
     * are used by this server as specific endpoint methods. The function 
     * only gets the path from the URL from the user, because a single 
     * function is a single method, so the function does not need to receive 
     * a method parameter.
     */
    class server_rest {
        
        /**
         * This function is a constructor that builds a new empty server. 
         * The only argument is base_path which allows you to set the base 
         * path of the server. So if the base path is /abc, then by 
         * registering the endpoint /end you are actually registering 
         * /abc/end. This allows you to have more than one REST application 
         * on one server and store them in separate folders. Then the base 
         * path is /foldername.
         * @param $base_path Base path for server (optional, default "")
         * @return New server object
         */
        public function __construct($base_path = "") {
            $this->base_path = $base_path;
        }

        /**
         * This function allows you to register controllers. Registration is 
         * done collectively, through an associative array, where the name 
         * of the element is path on the server, and the object is the 
         * controller.
         * @param $controllers Controllers array to register
         */
        public function register_controller($controllers) {
            foreach ($controllers as $key => $value) {
                if ($value instanceof simple_tree) {
                    $this->register_simple_tree($value, $key);
                    continue;
                }

                $this->controllers[$this->base_path.$key] = $value;
            }
        }

        /** 
         * This function register new simple tree on server.
         * @param $simple_tree Simple tree to add
         * @param $tree_path Path to add simple tree on
         */
        private function register_simple_tree($simple_tree, $tree_path) {
            foreach ($simple_tree->get_paths() as $key => $value) {
                $this->controllers[$this->base_path.$tree_path.$key] = $value;
            }
        }

        /** 
         * This function allows you to set a controller that will be started 
         * when the path specified in the request is not found on the server.
         * @param $not_found Controller to run, when given path not found
         */
        public function set_not_found($not_found) {
            $this->not_found = $not_found;
        }

        /** 
         * This function generates a server response for a given URL for a
         * given method, using pre-registered controllers.
         * @param $url Url to generate response for
         * @param $method Method which request made
         */
        public function response($url, $method) {
            $parsed = parse_url($url);
            $path = $parsed["path"];

            if (!array_key_exists($path, $this->controllers)) {
                return $this->run_controller(
                    $path, 
                    $method, 
                    $this->not_found
                );
            }

            try {
                return $this->run_controller(
                    $path, 
                    $method, 
                    $this->controllers[$path]
                );
            } catch (server_error $exception) {
                header("Content-Type: application/json");
                
                return json_encode([
                    "error_code" => $exception->error_code,
                    "cause" => $exception->cause,
                    "status" => "fail"
                ]);
            }
        }
    
        /**
         * This function is responsible for executing the controller, passing 
         * the response returned by it. It is responsible for the proper 
         * execution of controllers of all types, i.e. functions, class 
         * controllers and static texts.
         * @param $path Path to run with
         * @param $method Method to run with
         * @param $controller Controller to execute
         * @return Response from controller
         */
        private function run_controller($path, $method, $controller) {
            if ($controller instanceof simple_controller) {
                switch ($method) {
                    case "DELETE":
                        return $controller->delete();

                    case "GET":
                        return $controller->get();

                    case "OPTIONS":
                        return $controller->options();

                    case "POST":
                        return $controller->post();

                    case "PUT":
                        return $controller->put();
                }

                return "";
            }
            
            if (is_array($controller)) {
                return call_user_func_array($controller, [$method]);
            }

            if ($controller instanceof Closure) {
                return $controller($method);
            }

            return $controller;
        }

        /**
         * This variable holds the controller which is responsible for the 
         * action taken when path is not found among the registered 
         * controllers.
         */
        private string|object $not_found = "Not found on server!";
        
        /**
         * This associative array holds the controllers for subsequent paths 
         * on the server.
         */
        private array $controllers = [];
        
        /**
         * This variable holds the base path of the server.
         */
        private string $base_path = "";
    }
?>
