<?php
    //Antonio Manilla Maldonado
    $HOST = 'localhost';
    $PORT = 3307;
    $DB_NAME = 'coursera_js';
    $DB_USER = 'root';
    $DB_PASSWORD = 'root';
    $pdo = new PDO(
        "mysql:host=$HOST;port=$PORT;dbname=$DB_NAME", $DB_USER, $DB_PASSWORD
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);