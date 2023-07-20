<?php
    require_once("server_rest.php");
    require_once("database_adapter.php");

    class car {
        public function __construct(
            int $id, 
            string $photo = "", 
            string $documents = "", 
            array $params = []
        ) {
            $this->id = $id;
            $this->set_photo($photo);
            $this->set_documents($documents);
            $this->set_params($params);
        }

        public function set_photo(string $photo = "") {
            $this->photo = $photo;
        }

        public function set_documents(string $documents = "") {
            $this->documents = $documents;
        }

        private function check_file(string $filename) : bool {
            if (empty($filename)) return true;
            return file_exists($filename);
        }

        public function set_params(array $params) {
            foreach ($params as $key => $value) {
                if (empty($key)) {
                    throw new server_error(402, "Car params key is empty!");
                }

                if (!is_string($value)) {
                    throw new server_error(
                        404, 
                        "Param value for '".$key."' is not string!"
                    );
                }

                if (empty($value)) {
                    throw new server_error(
                        403, 
                        "Param value for '".$key."' is empty!"
                    );
                }
            }

            $this->params = $params;
        }
    
        public function get_params() : array {
            return $this->params;
        }

        public function get_id() : int {
            return $this->id;
        }

        public function get_photo() : string {
            return $this->photo;
        }

        public function get_documents() : string {
            return $this->documents;
        }

        public function summary() : array {
            return [
                "id" => $this->get_id(),
                "photo" => $this->get_photo(),
                "documents" => $this->get_documents(),
                "params" => $this->get_params()
            ];
        }

        private int $id;
        private string $photo;
        private string $documents;
        private array $params;
    }

    class car_adapter extends database_adapter {
        public function __construct($connection) {
            $this->connection = $connection;
            $this->primary_key = "id";
            $this->table_name = "cars";
            $this->rows = [
                "object" => "text"
            ];
        }

        public function exists(int $id) {
            $car = $this->select_data(["id" => $id]);

            return !empty($car);
        }

        public function get_by_id(int $id) : object {
            $car = $this->select_data(["id" => $id]);

            if (empty($car)) {
                throw new server_error(600, "Car with this ID not exists!");
            }

            return $this->car_from_db($car[0]);
        }

        public function get_all() : array {
            $from_db = $this->select_data();
            $cars = [];

            foreach ($from_db as $car) {
                array_push($cars, $this->car_from_db($car));
            }

            return $cars;
        }

        public function create_car() : object {
            $car = new car($this->next_id());
            $car_to_db = $this->car_to_db($car);

            $car_to_db["id"] = $car->get_id();
            $this->append_data($car_to_db);

            return $car;
        }

        public function store(object $car) {
            $condition = ["id" => $car->get_id()];

            $this->update_data($this->car_to_db($car), $condition);
        }

        public function drop(object $car) {
            $this->remove_data(["id" => $car->get_id()]);
        }

        private function car_to_db(object $car) : array {
            return ["object" => $this->serialize_car($car)];
        }

        private function car_from_db(array $container) : object {
            return $this->unserialize_car($container["object"]);
        }

        private function serialize_car(object $car) : string {
            return base64_encode(serialize($car));
        }

        private function unserialize_car(string $car) : object {
            return unserialize(base64_decode($car));
        }
    }
?>
