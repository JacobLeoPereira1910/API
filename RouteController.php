<?php

require './vendor/autoload.php';

class RouteController
{
  private $routeController;

  function __construct()
  {
    $this->routeController = new AdressControl();
  }

  public function setPage()
  {
    try {
      $route = array();
      $linkPage = array();

      if (isset($_POST['ano_mes_inicio']) && isset($_POST['ano_mes_fim']) || isset($_POST['cd_ccusto'])) {
        $startYear = $_POST['ano_mes_inicio'];
        $endYear = $_POST['ano_mes_fim'];
        $cdCusto = $_POST['cd_ccusto'];

        $link = array_filter(explode("/", $startYear, $endYear));
        $links = array_filter(explode("/", $cdCusto));

        if (count($link) > 0  || count($links) > 0) {
          throw new Exception('Serviço não encontrado!');
        }
      }
    } catch (Exception $e) {
      // Handle the exception
    }
  }
}
