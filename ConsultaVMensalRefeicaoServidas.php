<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require './vendor/autoload.php';

use app\models\UserModel;
use app\models\TotalRefeicoesModel;
use app\controllers\TotalRefeicaoController;

try {
    $userModel = new UserModel();
    $totalRefeicoesModel = new TotalRefeicoesModel();
    $controller = new TotalRefeicaoController($userModel, $totalRefeicoesModel);
    $token = $controller->getTokenFromRequest();
    $result = $controller->handleRequest();
} catch (Exception $e) {
    http_response_code($e->getCode());
    echo json_encode(['error' => $e->getMessage()]);
}
?>
