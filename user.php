<?php
const ALLOW_ORIGIN = "Access-Control-Allow-Origin";
const ALLOW_HEADERS = "Access-Control-Allow-Headers";
const METHOD_NOT_ALLOWED = 405;
const JWT_ALGORITHM = 'HS256';

header(ALLOW_ORIGIN . ": *");
header(ALLOW_HEADERS . ": Authorization, Content-Type, x-xsrf-token, x_csrftoken, Cache-Control, X-Requested-With");

require '../vendor/autoload.php';

use Firebase\JWT\JWT;
use app\database\Connection;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(["error" => "Os campos email e senha são obrigatórios"]);
        exit;
    }

    $header = getallheaders();
    $JWT = authenticateUser($email, $password);
    echo json_encode($JWT);
} else {
    http_response_code(METHOD_NOT_ALLOWED);
    echo json_encode(["error" => "Método não permitido"]);
    exit;
}

function authenticateUser($email, $password)
{
    $dotenv = Dotenv\Dotenv::createImmutable('C:/xampp/htdocs/jwt');
    $dotenv->load();
    $key = $_ENV['KEY'];

    $pdo = Connection::connect();
    $statement = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $statement->bindValue(":email", $email);
    $statement->execute();
    $userFound = $statement->fetchAll();


    if (count($userFound) > 0) {
        $payload = [
            "exp" => time() + 6000,
            "iat" => time(),
            "email" => $email
        ];

        $token = JWT::encode($payload, $key, JWT_ALGORITHM);

        // Criar o array de resposta
        $response = [
            "token" => $token
        ];

        return $response;
    } else {
        http_response_code(401);
        return ["error" => "Credenciais inválidas"];
    }
}
