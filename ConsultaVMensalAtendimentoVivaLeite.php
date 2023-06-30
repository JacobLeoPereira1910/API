<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require './vendor/autoload.php';

use app\models\UserModel;
use app\models\GetAmountServiceVivaLeiteModel;
use app\controllers\GetAmountServiceVivaLeiteController;

try {
 $userModel = new UserModel();
 $model = new GetAmountServiceVivaLeiteModel();
 $controller = new GetAmountServiceVivaLeiteController($userModel, $model);
 $token = $controller->getTokenFromRequest();
 $result = $controller->handleRequest();
} catch (Exception $e) {
 http_response_code($e->getCode());
 echo json_encode(['error' => $e->getMessage()]);
}
