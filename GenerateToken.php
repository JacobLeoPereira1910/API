<?php
header("Access-Control-Allow-Origin: *");
//header('Content-Type: application/json');
require './vendor/autoload.php';

use Firebase\JWT\JWT;

function generateToken()
{
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $key = $_ENV['KEY'];
    $payload = [
        "exp" => time() + 60000,
        "iat" => time(),
    ];

    $token = JWT::encode($payload, $key, 'HS256');

    // Criar o array de resposta
    $response = [
        "token" => $token
    ];

    return json_encode($response);
}

echo generateToken();
