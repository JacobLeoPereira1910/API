<?php

namespace app\models;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class UserModel
{
    public function getUserFromToken($token)
    {
        $dotenv = Dotenv::createImmutable('C:/xampp/htdocs/api');
        $dotenv->load();
        $key = $_ENV['KEY'];

        $tokenParts = explode('.', $token);
        if (count($tokenParts) !== 3) {
            throw new Exception('Formato inválido do token', 401);
        }

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
        } catch (Exception $e) {
            throw new Exception('Assinatura inválida do token', 401);
        }

        return $decoded;
    }
}
