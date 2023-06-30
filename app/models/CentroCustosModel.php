<?php

namespace app\models;

use PDO;

class CentroCustosModel
{
    public function getCentrosCustos($cd_ccusto_filter = [])
    {
        if (!empty($cd_ccusto_filter) && is_array($cd_ccusto_filter)) {
            $parametros = implode(',', array_map(function ($item) {
                return "'" . $item . "'";
            }, $cd_ccusto_filter));
        } else {
            $parametros = '';
        }

        $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=172.32.100.24", "bomprato", "bp050713");
        $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT id_origem, cd_ccusto, convert_from(convert_to(nm_ccusto, 'UTF8'), 'LATIN1') as custo, cd_uge, cd_municipio, tp_ccusto, latitude, longitude, status FROM vi_lista_ccusto";

        if (!empty($parametros)) {
            $sql .= " WHERE cd_ccusto IN ($parametros)";
        }

        $stmt = $pgconnection->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = [];

        foreach ($result as $row) {
            $data[] = [
                "id_origem" => $row['id_origem'],
                "cd_ccusto" => $row['cd_ccusto'],
                "nm_custo" => $row['custo'],
                "cd_uge" => $row['cd_uge'],
                "cd_municipio" => $row['tp_ccusto'],
                "lat" => $row['latitude'],
                "lng" => $row['longitude'],
                "status" => $row['status']
            ];
        }

        return $data;
    }
}
