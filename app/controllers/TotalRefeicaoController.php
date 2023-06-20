<?php

namespace app\controllers;

use Exception;
use app\models\CentroCustosModel;
use app\models\UserModel;
use app\models\TotalRefeicoesModel;


class TotalRefeicaoController
{
    public
    function getTokenFromRequest()
    {
        if (!isset($_POST['JWT'])) {
            throw new Exception('Token não fornecido no cabeçalho de autorização', 401);
        }

        //$token = $_POST['JWT'];
        return $_POST['JWT'];
    }
    public
    function verifyToken($token)
    {
        $userModel = new UserModel();
        return $userModel->getUserFromToken($token);
    }

    public
    function getParameter($name)
    {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }

        return null;
    }

    public function handleRequest()
    {
        try {
            $token = $this->getTokenFromRequest();
            $user = $this->verifyToken($token);

            $cd_ccusto_filter = $this->getParameter('cd_ccusto');
            $ano_mes_inicio = $this->getParameter('ano_mes_inicio');
            $ano_mes_fim = $this->getParameter('ano_mes_fim');

            $model = new TotalRefeicoesModel();
            $result = $model->getVMensalRefeicaoOfertada($cd_ccusto_filter, $ano_mes_inicio, $ano_mes_fim);

            $this->sendResponse($result);
        } catch (Exception $e) {
            http_response_code($e->getCode());
            echo $e->getMessage();
        }
    }

    public
    function sendResponse($data)
    {
        $response = json_encode($data);
        echo $response;
    }
}
