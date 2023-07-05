<?php

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
require './vendor/autoload.php';

use app\controllers\CentroCustosController;
use app\models\UserModel;
use app\models\CentroCustosModel;

try {
    $userModel = new UserModel();
    $centroCustosModel = new CentroCustosModel();    
    $controller = new CentroCustosController($userModel, $centroCustosModel);
     $token = $controller->getTokenFromRequest();
     $result = $controller->handleRequest();
    
} catch (Exception $e) {
    http_response_code($e->getCode());
    echo json_encode(['error' => $e->getMessage()]);
}
