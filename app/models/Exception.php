<?php

namespace app\controllers;

use Exception;
use app\models\UserModel;
use app\models\CentroCustosModel;

class CustomException extends \Exception
{
    public function handleRequest()
    {
        try {
            $token = $this->getTokenFromRequest();
            $user = $this->verifyToken($token);
            
            $cd_ccusto_filter = $this->getParameter('cd_ccusto');

            $model = new CentroCustosModel();
            $result = $model->getCentrosCustos($cd_ccusto_filter);

            $this->sendResponse($result);
        } catch (Exception $e) {
            http_response_code($e->getCode());
            echo $e->getMessage();
        }
    }

    private function getTokenFromRequest()
    {
        if (!isset($_POST['JWT'])) {
            throw new Exception('Token não fornecido no cabeçalho de autorização', 401);
        }

        return $_POST['JWT'];
    }

    private function verifyToken($token)
    {
        $userModel = new UserModel();
        return $userModel->getUserFromToken($token);
    }

    private function getParameter($name)
    {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }

        return null;
    }

    private function sendResponse($data)
    {
        $response = json_encode($data);
        echo $response;
    }
}
