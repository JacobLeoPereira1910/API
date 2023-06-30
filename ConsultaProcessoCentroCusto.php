<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require './vendor/autoload.php';

use app\controllers\ConsultaProcessoCentroCustoController;
use app\models\UserModel;
use app\models\ConsultaProcessoCentroCustoModel;

try {
    $userModel = new UserModel();
    $model = new ConsultaProcessoCentroCustoModel();
    $controller = new ConsultaProcessoCentroCustoController($userModel, $model);
    $token = $controller->getTokenFromRequest();
    $result = $controller->handleRequest();
} catch (Exception $e) {
    http_response_code($e->getCode());
    echo json_encode(['error' => $e->getMessage()]);
}
