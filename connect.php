<?php

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = 12345678;
const DB_NAME = 'sport';

const DB_OPTIONS = array(
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
);
