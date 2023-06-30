<?php

namespace app\models;

use PDO;
use Exception;

class ConsultaProcessoCentroCustoModel
{
    public function GetProcessoCentrosCustos($cd_ccusto_filter, $ano_inicio, $ano_fim)
    {
        try {
            $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=172.32.100.24", "bomprato", "bp050713");
            $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "SELECT * FROM vi_fipe_convenios fipe_convenios
    INNER JOIN vi_lista_ccusto lista_ccusto ON fipe_convenios.cd_ccusto = lista_ccusto.cd_ccusto
    WHERE fipe_convenios.cd_ccusto = :cd_cccusto
    AND fipe_convenios.data_inicio >= :data_inicio
    AND fipe_convenios.data_inicio <= :data_fim
    ORDER BY fipe_convenios.data_inicio";

            $stmt = $pgconnection->prepare($sql);
            $stmt->bindParam(':data_inicio', $ano_inicio);
            $stmt->bindParam(':data_fim', $ano_fim);

            if (!empty($cd_ccusto_filter)) {
                $cd_ccusto_values = explode(',', $cd_ccusto_filter);
                foreach ($cd_ccusto_values as $index => $cd_ccusto_value) {
                    $param = ':param' . ($index + 1);
                    $stmt->bindParam($param, $cd_ccusto_value);
                }
            }

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];

            foreach ($results as $key => $row) {
                $data[] = array(
                    "ano_mes_ini"       => $row['data_inicio'],
                    "ano_mes_fim"       => $row['data_final'],
                    "cd_uge"            => $row['cd_uge'],
                    "id_origem"         => $row['id_origem'],
                    "cd_ccusto"         => $row['cd_ccusto'],
                    "numero_processo"   => $row['num_processo']
                );
            }

            return $data;
        } catch (Exception $e) {
            // Handle the exception here or throw it to the higher level as needed
            throw $e;
        }
    }
}
