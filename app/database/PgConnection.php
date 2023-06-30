<?php

namespace app\database;

use PDO;
use Exception;

class PgConnection
{
    public function Connection()
    {
        try {
            $ip = $_SERVER['SERVER_ADDR'];
            $acesso = "127.0.0.1"; // Default to local development IP

            if ($ip == "172.32.101.22") {
                $acesso = "172.32.101.24"; // SEDS production IP
            } elseif ($ip == "172.32.100.22") {
                $acesso = "172.32.100.24"; // KSB production IP
            }

            $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=" . $acesso, "bomprato", "bp050713");
            $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pgconnection;
        } catch (Exception $error) {
            throw new Exception("Failed to connect to the database: " . $error->getMessage());
        }
    }
}
