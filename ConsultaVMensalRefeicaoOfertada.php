<?php

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require './vendor/autoload.php';

use app\models\UserModel;
use app\models\GetTotalAmountMealOfferedModel;
use app\controllers\GetTotalAmountMealOfferedController;

try {
    $userModel = new UserModel();
    $model = new GetTotalAmountMealOfferedModel();
    $controller = new GetTotalAmountMealOfferedController($userModel, $model);
    $token = $controller->getTokenFromRequest();
    $result = $controller->handleRequest();
} catch (Exception $e) {
    http_response_code($e->getCode());
    echo json_encode(['error' => $e->getMessage()]);
}
