<?php

namespace app\database;

require __DIR__ . '/../../vendor/autoload.php';

use PDO;

class Connection
{
  public static function connect()
  {
    return new PDO("mysql:host=localhost;dbname=dados", "root", "", [
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
  }
}


function conecta()
{

  $ip = $_SERVER['SERVER_ADDR'];

  $acesso = "host=172.32.101.24 dbname=bomprato user=bomprato password=bp050713"; // Produção - SEDS - 192.168.0.2 x 192.168.0.3
  // else if ($ip == "172.32.100.22") $acesso = "host=172.32.100.24 dbname=bomprato user=bomprato password=bp050713"; // Produção - KSB - 192.168.1.2 x 192.168.1.3
  //else if ($ip == "172.32.101.22") $acesso = "host=172.32.101.24 dbname=bomprato user=bomprato password=bp050713"; // Produção BP - SEDS - 192.168.0.6 x 192.168.0.3

  //$acesso = "host=192.168.1.4 dbname=bomprato user=postgres password=pgres09";

  $bd = pg_connect($acesso);

  return $bd;
}
