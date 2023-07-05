<?php

namespace app\controllers;

use Exception;
use app\models\UserModel;
use app\models\GetTotalAmountMilkModel;

class GetTotalAmountMilkController
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

    public function getParameter($name)
    {
        $postData = file_get_contents('php://input');
        if (empty($postData)) {
            return null;
        }
        $requestBody = json_decode($postData, true);

        switch ($name) {
            case 'cd_ccusto':
                return isset($requestBody['ccusto']) ? $requestBody['ccusto'] : null;
            case 'ano_mes_inicio':
                return isset($requestBody['ano_mes_inicio']) ? $requestBody['ano_mes_inicio'] : null;
            case 'ano_mes_fim':
                return isset($requestBody['ano_mes_fim']) ? $requestBody['ano_mes_fim'] : null;
            default:
                return null;
        }
    }

    public function handleRequest()
    {
        try {
            $token = $this->getTokenFromRequest();

            $ano_mes_inicio = $this->getParameter('ano_mes_inicio') ?? null;
            $ano_mes_fim = $this->getParameter('ano_mes_fim') ?? null;

            if (empty($ano_mes_inicio) || empty($ano_mes_fim)) {
                throw new Exception('Os parâmetros ano_mes_inicio e ano_mes_fim são obrigatórios.');
            }

            $cd_ccusto_filter = $this->getParameter('cd_ccusto');

            $model = new GetTotalAmountMilkModel();
            $result = $model->getMonthValue($cd_ccusto_filter, $ano_mes_inicio, $ano_mes_fim);

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
