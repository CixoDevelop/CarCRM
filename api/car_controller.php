<?php
    require_once("server_rest.php");
    require_once("car.php");

    class file_adapter {
        public function __construct(string $path, string $server_path) {
            $this->path = $path;
            $this->server_path = $server_path;
        }

        public function remove_by_server_path(string $file_path) {
            unlink(str_replace($this->server_path, $this->path, $file_path));
        }

        private function check_extension(string $extension, array $avarible) {
            if (in_array($extension, $avarible)) return;

            throw new server_error(803, "Extension is disabled by server!");
        }

        public function check_document(string $extension) {
            $this->check_extension($extension, [
                "pdf", "xls", "xlsx", "doc", "docx", "html", "xml", "xhtml", 
                "txt", "zip"
            ]);
        }

        public function check_photo(string $extension) {
            $this->check_extension($extension, [
                "jpg", "jpeg", "webm", "png", "bmp", "svg", "apng", "avif", 
                "gif", "webp", "ico", "tiff", "tif"
            ]);
        }

        public function store($name, $extension, $content) {
            $this->check_path();
            $path = $this->path."/".$name.".".$extension;
            $server_path = $this->server_path."/".$name.".".$extension;

            file_put_contents($path, $content);
            
            return $server_path;
        }

        public function check_path() {
            if (is_dir($this->path)) return;
            if (is_writeable($this->path)) return;

            throw new server_error(800, "Cannot store file, check perms!");
        }

        private string $path;
        private string $server_path;
    }

    class car_controller extends simple_tree {

        public function __construct(
            user_adapter $user_adapter, 
            car_adapter $car_adapter,
            file_adapter $file
        ) {
            $this->user_adapter = $user_adapter;
            $this->car_adapter = $car_adapter;
            $this->file = $file;
        
            parent::__construct();
        }
   
        public function API_send_document($method) {
            $received = $this->get_post_as_json();
            $this->check_request(
                $method, "POST",
                $received, [
                    "apikey" => "editor",
                    "id" => "car",
                    "extension" => "string",
                    "document" => "string"
                ]
            );

            $this->file->check_document($received["extension"]);

            $car = $this->car_adapter->get_by_id($received["id"]);
            $decoded = base64_decode($received["document"]); 
           
            $path = $this->file->store(
                $received["id"], 
                $received["extension"], 
                $decoded
            );

            $car->set_documents($path);
            $this->car_adapter->store($car);

            return $this->success();
        }

        public function API_send_photo($method) {
            $received = $this->get_post_as_json();
            $this->check_request(
                $method, "POST",
                $received, [
                    "apikey" => "editor",
                    "id" => "car",
                    "extension" => "string",
                    "photo" => "string"
                ]
            );

            $this->file->check_photo($received["extension"]);

            $car = $this->car_adapter->get_by_id($received["id"]);
            $decoded = base64_decode($received["photo"]); 
           
            $path = $this->file->store(
                $received["id"], 
                $received["extension"], 
                $decoded
            );

            $car->set_photo($path);
            $this->car_adapter->store($car);

            return $this->success();
        }

        public function API_get($method) {
            $this->check_request(
                $method, "GET",
                $_GET, [
                    "apikey" => "reader",
                    "id" => "car"
                ]
            );

            $car = $this->car_adapter->get_by_id($_GET["id"]);

            return $this->success($car->summary());
        }

        public function API_get_all($method) {
            $this->check_request(
                $method, "GET",
                $_GET, [
                    "apikey" => "reader"
                ]
            );

            $cars_from_db = $this->car_adapter->get_all();
            $cars_json = [];
            
            foreach ($cars_from_db as $car) {
                array_push($cars_json, $car->summary());
            }

            return $this->success($cars_json);
        }

        public function API_create($method) {
            $received = $this->get_post_as_json();
            $this->check_request(
                $method, "POST",
                $received, [
                    "apikey" => "editor",
                    "params" => "array",
                ]
            );

            $car = $this->car_adapter->create_car();

            try {
                $car->set_params($received["params"]);
            } catch (server_error $exception) {
                $this->car_adapter->drop($car);
                throw $exception;
            }

            $this->car_adapter->store($car);

            return $this->success($car->get_id());
        }

        public function API_save($method){
            $received = $this->get_post_as_json();
            $this->check_request(
                $method, "POST",
                $received, [
                    "apikey" => "editor",
                    "id" => "car",
                    "params" => "array",
                ]
            );

            $car = $this->car_adapter->get_by_id($received["id"]);
            $car->set_params($received["params"]);
            $this->car_adapter->store($car);

            return $this->success();
        }

        public function API_delete($method) {
            $this->check_request(
                $method, "GET",
                $_GET, [
                    "apikey" => "editor",
                    "id" => "car"
                ]
            );

            $car = $this->car_adapter->get_by_id($_GET["id"]);
            
            if (!empty($car->get_photo())) {
                $this->file->remove_by_server_path($car->get_photo());
            }

            if (!empty($car->get_documents())) {
                $this->file->remove_by_server_path($car->get_documents());
            }

            $this->car_adapter->drop($car);

            return $this->success();
        }

        public function check_request(
            string $method, 
            string $required_method, 
            array $params, 
            array $pattern
        ) {
            if ($method !== $required_method) {
                throw new server_error(
                    700, 
                    "Method must be ".$required_method."!"
                );
            }
            
            foreach ($pattern as $name => $type) {
                if (!array_key_exists($name, $params)) {
                    throw new server_error(701, $name." must be in request!");
                }

                if ($type === "reader") {
                    if (!$this->check_user_privileges(
                        $params[$name], 
                        $type
                    ) && !$this->check_user_privileges(
                        $params[$name],
                        "editor"
                    )) {
                        throw new server_error(
                            704, 
                            "User has no ".$type." required privilege!"
                        );
                    }

                    continue;
                }
                
                if ($type === "editor") {
                    if (!$this->check_user_privileges(
                        $params[$name], 
                        $type
                    )) {
                        throw new server_error(
                            704, 
                            "User has no ".$type." required privilege!"
                        );
                    }

                    continue;
                }
            
                if ($type === "car") {
                    if ((string)(int)$params[$name] != $params[$name]) {
                        throw new server_error(
                            705, 
                            "Car ID is not int(".$params[$name].")!"
                        );
                    }

                    if (!$this->car_adapter->exists((int)$params[$name])) {
                        throw new server_error(706, "Car not exists!");
                    }

                    continue;
                }

                if ($type !== gettype($params[$name])) {
                    throw new server_error(
                        702, 
                        $name." must be in ".$type."!"
                    );
                }
            }
        }

        private function check_user_privileges($user_apikey, $privilege) {
            if (!$this->user_adapter->user_with_apikey_exists($user_apikey)) {
                throw new server_error(703, "User not exists!");
            }

            $user = $this->user_adapter->get_user_by_apikey($user_apikey);

            if ($user->check_privileges($privilege)) return true;
            if ($user->check_privileges("system_admin")) return true;

            return false;
        }

        private user_adapter $user_adapter;
        private car_adapter $car_adapter;
        private file_adapter $file;
    }
?>
