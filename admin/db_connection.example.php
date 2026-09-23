<?php
function connectToDb()
{
    $db_conn = null;
    try {
        $db_conn = new PDO(
            "mysql:host=localhost;dbname=market_connect;charset=utf8mb4",
            "your_bd_username",
            "your_db_password"
        );
        $db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $ex) {
        echo "Bad connection. Try later. Error: " . $ex->getMessage();
    }
    return $db_conn;
}
?>
