<?php

const DATABASE_HOST = '127.0.0.1';
const DATABASE_USER = 'root';
const DATABASE_PASSWORD = 'root';
const DATABASE_NAME = 'blog';

function database_connect(): PDO
{
    return new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
}
