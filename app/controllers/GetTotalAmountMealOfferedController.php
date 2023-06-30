<?php

namespace app\controllers;

use Exception;
use app\models\UserModel;
use app\models\GetTotalAmountMealOfferedModel;

class GetTotalAmountMealOfferedController
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
        }
    }

    public function handleRequest()
    {
        try {
            $ano_mes_inicio = $this->getParameter('ano_mes_inicio') ?? null;
            $cd_ccusto_filter = $this->getParameter('cd_ccusto') ?? null;

            
            if (empty($ano_mes_inicio) || empty($cd_ccusto_filter)) {
                throw new Exception('Os parâmetros de período e centro de custo são obrigatórios.');
            }

            $model = new GetTotalAmountMealOfferedModel();
            $ano = date('Y', strtotime($ano_mes_inicio));
            $mes = date('m', strtotime($ano_mes_inicio));
            $result = $model->getMonthValue($cd_ccusto_filter, $ano, $mes);

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
