<?php

namespace app\models;

use PDO;
use Exception;
use app\database\Connection;

class CentroCustosModel
{
    public function getCentrosCustos($cd_ccusto_filter)
    {
        $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=172.32.100.24", "bomprato", "bp050713");
        $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT id_origem, cd_ccusto, convert_from(convert_to(nm_ccusto, 'UTF8'), 'LATIN1') as custo, cd_uge, cd_municipio, tp_ccusto, latitude, longitude, status FROM vi_lista_ccusto";

        if (!empty($cd_ccusto_filter)) {
            if (!is_numeric($cd_ccusto_filter)) {
                throw new Exception('O filtro "cd_ccusto" deve ser um número', 400);
            }

            // Realize outras validações específicas do filtro, se necessário

            $sql .= " WHERE cd_ccusto = :cd_ccusto";
            $cd_ccusto_filter = trim($cd_ccusto_filter);
        }

        $stmt = $pgconnection->prepare($sql);

        if (!empty($cd_ccusto_filter)) {
            $stmt->bindParam(':cd_ccusto', $cd_ccusto_filter);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
