<?php

namespace app\controllers;

use Exception;
use app\models\CentroCustosModel;
use app\models\UserModel;

class CentroCustosController
{
    public function getTokenFromRequest()
    {
        $postData = file_get_contents('php://input');
        $requestBody = json_decode($postData, true);

        if (!isset($requestBody['JWT'])) {
            throw new Exception('Token não fornecido no corpo da solicitação', 401);
        }

        return $requestBody['JWT'];
    }

    public function verifyToken($token)
    {
        $userModel = new UserModel();
        return $userModel->getUserFromToken($token);
    }

    public function handleRequest()
    {
        try {
            $token = $this->getTokenFromRequest();

            $postData = file_get_contents('php://input');
            if (empty($postData)) {
                throw new Exception('Corpo da solicitação vazio', 400);
            }
            $requestBody = json_decode($postData, true);

            if (!isset($requestBody['ccusto'])) {
                throw new Exception('Filtro "ccusto" não encontrado', 400);
            }

            $ccustoFilter = $requestBody['ccusto'];

            $model = new CentroCustosModel();
            $result = $model->getCentrosCustos($ccustoFilter);

            $this->sendResponse($result);
        } catch (Exception $e) {
            http_response_code($e->getCode());
            echo $e->getMessage();
        }
    }

    public function sendResponse($data)
    {
        $response = json_encode($data);
        echo $response;
    }
}