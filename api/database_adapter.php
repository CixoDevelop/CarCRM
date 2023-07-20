<?php
    /** 
     * This is class for raise errors in SQL. That is used to catch all
     * SQL erors and send it to user as JSON form, to show it on frontend.
     */
    class database_error extends Exception {
        
        /** 
         * This function constructing readonly error object, to catch it.
         * @param $error Text of error
         */
        public __construct(string $error) {
            $this->error = $error;
        }
    
        /** 
         * This is error to show it.
         */
        public readonly string $error;
    }

    /** 
     * This is an abstract class of an object that allows database 
     * interaction. Generally, it is used to manage a table of objects
     * in a database, create such a table, add records to it, delete 
     * records, update data in records, and delete tables. To create an 
     * object that extends this adapter for the purpose of storing a 
     * specific class, you need to override the constructor so that it 
     * saves the columns of the needed table to the object's rows variable 
     * as an associative table that stores the key - the name of the column 
     * and the value - the type of data that this column stores. Then you 
     * should overwrite primary_key, i.e. the name of the primary key, and 
     * table_name - the name of the table. Finally, we also overwrite 
     * connection as the connection received in the parameter from the user. 
     */
    abstract class database_adapter {
        /** 
         * This function is a constructor that needs to be overridden to store
         * the required columns and types as an associative array of the rows 
         * object, where the key of this array is the column name and the value 
         * of the key is the type of the column. The next variable to be 
         * overwritten is primary_key, i.e. the name of the primary key, then 
         * only table_name, i.e. the name of the table in the database, and 
         * connection, i.e. connection to the database.
         * @param $connection Connection to database object
         * @return New adapter object
         */
        public abstract function __construct($connection);

        /** 
         * This function is responsible for creating a table in the database. 
         * Creates tables according to the structure given in the object's 
         * rows variable. Returns either the result of the query or false 
         * if the command caused an error.
         * @return Query result or false when error
         */
        public function create_table() {
            $query = "create table ".$this->table_name."(";
            $query .= $this->primary_key;
            $query .= " int unsigned auto_increment primary key, ";

            foreach ($this->rows as $row => $type) {
                $query .= $row." ".$type.", ";
            }
            
            $query = substr($query, 0, -2).")";
        
            return $this->make_binary_query($query);
        }
        
        /** 
         * This function removes an existing table from the database. It uses
         * the object's table_name variable to delete the table, and returns 
         * either the result of the query, or false if the query caused an 
         * error.
         * @return Query result or false when error
         */
        public function remove_table() {
            return $this->make_binary_query("drop table ".$this->table_name);
        }

        /** 
         * This function is used to delete records from the database. In the 
         * parameter, pass the known values of the columns to be deleted, the 
         * where part of the command will be created from this data. The data 
         * is in the associative array format, where the key - the column 
         * name, and the value under the key as the column value.
         * @param @data An associative array of data to create the condition
         * @return Status of query or false when error
         */
        protected function remove_data($data = []) {
            $query = "delete from ";
            $query .= $this->table_name;
            $query .= " ";
            $query .= $this->where_statement_syntax_generate($data);
       
            return $this->make_binary_query($query);
        }

        /** 
         * This function allow You to get next ID that AUTO_INCREMENT 
         * MySQL function will set on next Your MySQL table row.
         * @return Next ID in table
         */
        protected function next_id() {
            $query = "select AUTO_INCREMENT from information_schema.TABLES ";
            $query .= "where TABLE_NAME = '";
            $query .= $this->table_name;
            $query .= "'";

            $result = $this->connection->query($query);
            return intval($result->fetch_assoc()["AUTO_INCREMENT"]);
        }

        
        /** 
         * This function allows you to update data in a table where the first 
         * argument is the new data for the columns. It should be an 
         * associative array where key is the name of the column and value is 
         * the new value of that column. To update only selected records, 
         * specify condition in the second argument. This is a similar 
         * associative array, except that the values are the values that a 
         * record must have in order for it to be updated.
         * @param $data Associative array with new data to update in database
         * @param $where Associative array with conditions to select records
         * @return Result of query or false when error
         */
        protected function update_data($data, $where) {
            $query = "update ";
            $query .= $this->table_name;
            $query .= " set ";

            foreach ($data as $row => $value) {
                $query .= $row." = ";

                if (is_string($value)) {
                    $query .= '"'.$value.'"';
                } else {
                    $query .= $value;
                }

                $query .= ", ";
            }

            $query = substr($query, 0, -2);
            $query .= $this->where_statement_syntax_generate($where);
        
            return $this->make_binary_query($query);     
        }

        /**
         * This function generates a condition for a mysql query using the 
         * "where" syntax, followed by a condition. It turns a key-column 
         * associative array in a database where the value is the contents 
         * of the column, into a condition that can be appended to the end 
         * of a mysql query.
         * @param $data Data to create statement from
         * @return Where statement string
         */
        private function where_statement_syntax_generate($data) {
            $statement = "";

            if (empty($data)) return $statement;

            $statement .= " where ";
            
            foreach ($data as $row => $value) {
                $statement .= $row." = ";

                if (is_string($value)) {
                    $statement .= '"'.$value.'"';
                } else {
                    $statement .= $value;
                }

                $statement .= " and ";
            }
            
            $statement = substr($statement, 0, -5);
        
            return $statement;
        }

        /** 
         * This function allows you to extract data from the database. The 
         * data variable is a filter, it must contain the keys that are 
         * columns in the database, and the values that these columns must 
         * have for the values to be extracted. If it manages to extract 
         * some values, an array with these values will be returned, while 
         * if an error occurs, false will be returned.
         * @param $data Filter with required data
         * @return Selected data or false
         */
        protected function select_data($data = []) {
            $query = "select * from ";
            $query .= $this->table_name;
            $query .= $this->where_statement_syntax_generate($data);

            $result = $this->connection->query($query);
            $selected_data = [];

            while ($new_row = $result->fetch_assoc()) {
                array_push($selected_data, $new_row);
            }

            return $selected_data;
        }

        /** 
         * This function adds new data to the database. It validates that all 
         * given columns are really in the database, and then adds new data. 
         * It will return the request state or false if there was an error.
         * @param $data Array of data to insert, keys is row names
         * @return Query result or false when error corrupted.
         */
        protected function append_data($data) {
            $keys = [];
            $values = [];

            foreach ($data as $row => $value) {
                if (
                    !array_key_exists($row, $this->rows) and
                    $row !== $this->primary_key
                ) {
                    throw new Exception("Row ".$row." not found in rows.!");
                }
                
                array_push($keys, $row);
                array_push($values, $value);
            }

            $query = "insert into ";
            $query .= $this->table_name;
            $query .= " (";
            
            foreach ($keys as $key) {
                $query .= $key.", ";
            }

            $query = substr($query, 0, -2).") values (";

            foreach ($values as $value) {
                if (is_string($query)) {
                    $query .= '"'.$value.'"';
                } else {
                    $query .= $value;
                }

                $query .= ", ";
            }

            $query = substr($query, 0, -2).")";
            
            return $this->make_binary_query($query);
        }

        /**
         * This function allows you to query the database and get the result 
         * of the query or false if an error occurs.
         * @param $query Query to make
         * @return Result of query or false when error 
         */
        private function make_binary_query($query) {
            $result = $this->connection->query($query);

            if ($result === true) return true;

            if ($this->connection->error != "") {
                throw new Exception("MySQL error: ".$this->connection->error);
            }
            
            return false;
        }

        /**
         * This variable store table structure description.
         */
        protected array $rows;
       
        /** 
         * This variable store connection to database.
         */
        protected object $connection;
        
        /** 
         * This variable store table name in database.
         */
        protected string $table_name;
        
        /** 
         * This variable store primary key name for table.
         */
        protected string $primary_key;
    }
?>
