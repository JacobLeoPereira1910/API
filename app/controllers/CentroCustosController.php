<?php

namespace app\controllers;

use Exception;
use app\models\CentroCustosModel;
use app\models\UserModel;

class CentroCustosController
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
        
            $cd_ccusto_filter = $this->getParameter('cd_ccusto');

            $model = new CentroCustosModel();
            $result = $model->getCentrosCustos($cd_ccusto_filter);

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
