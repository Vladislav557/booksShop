<?php

return [
    "dsn" => "mysql:host=mysql;dbname=books_shop_db;port=3306",
    "user" => "guest",
    "password" => "secret",
    "options" => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
];