<?php

namespace app\models;

use PDO;
use Exception;

class GetTotalAmountProcessesCCustoSegModel
{
    public function getMonthValue($cd_ccusto_filter = [], $ano_mes_inicio, $ano_mes_fim)
    {
        $data_inicio = date('Y-m-d', strtotime($ano_mes_inicio . '01'));
        $data_fim = date('Y-m-d', strtotime($ano_mes_fim . '01'));

        if (!empty($cd_ccusto_filter) && is_array($cd_ccusto_filter)) {
            $cd_ccusto_filter = array_map(function ($item) {
                return "'" . $item . "'";
            }, $cd_ccusto_filter);
            $parametros = implode(',', $cd_ccusto_filter);
        } else {
            $parametros = '';
        }

        try {
            $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=172.32.100.24", "bomprato", "bp050713");
            $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "SELECT * FROM vi_fipe_convenios fipe_convenios
                INNER JOIN vi_lista_ccusto lista_ccusto ON fipe_convenios.cd_ccusto = lista_ccusto.cd_ccusto
                WHERE fipe_convenios.cd_ccusto IN ($parametros)
                AND fipe_convenios.data_inicio >= :data_inicio
                AND fipe_convenios.data_inicio <= :data_fim
                ORDER BY fipe_convenios.data_inicio";

            $stmt = $pgconnection->prepare($sql);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];

            foreach($results as $row) {
                $date_ini = date('Ym', strtotime($row['data_inicio']));
                $date_end = date('Ym', strtotime($row['data_final']));
                $data = [
                    "ano_mes_ini"       => $date_ini,
                    "ano_mes_fim"       => $date_end,
                    "cd_uge"            => $row['cd_uge'],
                    "id_origem"         => $row['id_origem'],
                    "cd_ccusto"         => $row['cd_ccusto'],
                    "numero_processo"   => $row['num_processo']
                ];

            }
            return $data;
        } catch (Exception $e) {
            // Handle the exception here or throw it to the higher level as needed
            throw $e;
        }
    }
}
