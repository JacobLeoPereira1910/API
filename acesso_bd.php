<?php
function conectaPDOAnalytics()
{

 try {

  $acesso = "172.32.100.24";

  #$pgconnection=new PDO("mysql:dbname=bomprato;host=192.168.10.220", "postgres", "pgres04");
  $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=" . $acesso, "bomprato", "bp050713");
  $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  return array(true, $pgconnection);
 } catch (Exception $error) {
  return array(false, $error->getMessage());
 }
}
