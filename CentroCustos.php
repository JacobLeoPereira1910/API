<?php

// Definir constantes
const ALLOW_ORIGIN = "Access-Control-Allow-Origin";
const ALLOW_HEADERS = "Access-Control-Allow-Headers";
const METHOD_NOT_ALLOWED = 405;
const JWT_ALGORITHM = 'HS256';

// Definir cabeçalhos CORS
header(ALLOW_ORIGIN . ": *");
header(ALLOW_HEADERS . ": Authorization, Content-Type, x-xsrf-token, x_csrftoken, Cache-Control, X-Requested-With");

// Autoload das classes
require './vendor/autoload.php';

// Importar classes necessárias
use app\controllers\CentroCustosController;
use app\models\UserModel;
use app\models\CentroCustosModel;
use Exception;

echo "<pre>";
print_r($_POST);
echo "</pre>";

try {
    $userModel = new UserModel();
    $centroCustosModel = new CentroCustosModel();    
    $controller = new CentroCustosController($userModel, $centroCustosModel);
     $token = $controller->getTokenFromRequest();
     $result = $controller->handleRequest();
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code($e->getCode());
    echo json_encode(['error' => $e->getMessage()]);
}
