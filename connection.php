<?php

class Connection {

    public static $_connection = null;

    public static function getConnection() {
        if (!Connection::$_connection) {
            $settings = array('localhost', 'root', '123456', 'development');
            Connection::$_connection = new mysqli($settings[0], $settings[1], $settings[2], $settings[3]);
            if (mysqli_connect_errno()) {
                printf("Connect failed: %s\n", mysqli_connect_error());
                exit();
            }
        }
        return Connection::$_connection;
    }

}

?>
