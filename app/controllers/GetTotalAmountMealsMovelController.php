<?php

namespace app\controllers;

use Exception;
use app\models\UserModel;
use app\models\GetTotalAmountMealsMovelModel;

class GetTotalAmountMealsMovelController
{
    public function getTokenFromRequest()
    {
        if (!isset($_POST['JWT'])) {
            throw new Exception('Token não fornecido no cabeçalho de autorização', 401);
        }
        return $_POST['JWT'];
    }

    public function verifyToken($token)
    {
        $userModel = new UserModel();
        return $userModel->getUserFromToken($token);
    }

    public function getParameter($name)
    {
        switch ($name) {
            case 'cd_ccusto':
                return isset($_POST['cd_ccusto']) ? $_POST['cd_ccusto'] : null;
            case 'ano_mes_inicio':
                return isset($_POST['ano_mes_inicio']) ? $_POST['ano_mes_inicio'] : null;
            case 'ano_mes_fim':
                return isset($_POST['ano_mes_fim']) ? $_POST['ano_mes_fim'] : null;
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

            $model = new GetTotalAmountMealsMovelModel();
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
