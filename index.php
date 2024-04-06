<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/database.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestPath = $_SERVER['PATH_INFO'] ?? null;
const APP_HOST = 'http://localhost:8000/api/v1/shorten';

use Hashids\Hashids;

function error()
{
    echo '404 not found';
}

function shorten()
{
    $body = file_get_contents('php://input');
    $input = json_decode($body, true);
    if ($input === null || $input === []) {
        exit('request body is empty');
    }

    $url = $input['url'] ?? null;
    if ($url === '' || is_string($url) === false) {
        exit ('body not correct');
    }
    $filterUrl = filter_var($url, FILTER_VALIDATE_URL);
    if ($filterUrl === false) {
        exit('not correct url');
    }
    $parseUrl = parse_url($url);
    if ($parseUrl['scheme'] !== 'https' && $parseUrl['scheme'] !== 'http') {
        exit('begin url with https');
    }
    $connection = database_connect();
    $connection->query(
        sprintf(
            'INSERT INTO urls(url) VALUES (%s)',
            $connection->quote($url)
        )
    );
    $hashids = new Hashids('', 6);
    $id = $hashids->encode($connection->lastInsertId());

    echo json_encode(['short_url' => sprintf('%s/%s', APP_HOST, $id)]);
}

function redirect(int $id)
{
    if ($id < 1) {
        exit('not correct id');
    }

    $connection = database_connect();
    $url = $connection
        ->query(sprintf('SELECT url FROM urls WHERE id = %d', $id))
        ->fetchColumn();

    if ($url === false) {
        error();
        exit;
    }

    header(sprintf('Location: %s', $url));
}

if ($requestMethod === 'POST' && $requestPath === '/api/v1/shorten') {
    shorten();
} elseif ($requestPath === null) {
    error();
} else {
    $shortUrl = mb_substr($requestPath, 1);
    $hashids = new Hashids('', 6);
    $idArray = $hashids->decode($shortUrl);
    if ($idArray === []) {
        error();
    } else {
        $id = $idArray[0];
        redirect($id);
    }
}

