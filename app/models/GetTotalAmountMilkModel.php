<?php

namespace app\models;

use PDO;

class GetTotalAmountMilkModel
{
    public function getMonthValue($cd_ccusto_filter, $ano_mes_inicio, $ano_mes_fim)
    {
        try {

            $data_inicio = date('Y-m-d', strtotime($ano_mes_inicio . '01'));
            $data_fim = date('Y-m-d', strtotime($ano_mes_fim . '01'));

            if (!empty($cd_ccusto_filter) && is_array($cd_ccusto_filter)) {
                $parametros = implode(',', array_map(function ($item) {
                    return "'" . $item . "'";
                }, $cd_ccusto_filter));
            } else {
                $parametros = '';
            }
            // Cria uma instância de conexão com o banco de dados
            $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=172.32.100.24", "bomprato", "bp050713");
            $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Monta a consulta SQL
            $query = "SELECT
                    id_origem,
                    cd_ccusto,
                    date_actual,
                    qtd_litros
              FROM
                    vi_fipe_vivaleite
              WHERE
                    date_actual >= '$data_inicio' AND date_actual <= '$data_fim'";

            if (!empty($cd_ccusto_filter)) {
                $query .= " AND cd_ccusto = $parametros";
            }

            $query .= " ORDER BY date_actual";

            $stmt = $pgconnection->prepare($query);

            $stmt->execute();

            // Obtém os resultados da consulta
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata os resultados em um array de dados
            $data = [];
            foreach ($results as $row) {
                $id_origem = $row['id_origem'];
                $cd_ccusto = $row['cd_ccusto'];
                $date_actual = $row['date_actual'];
                $qtd_litros = $row['qtd_litros'];
                $date = $date_actual; // Assign the string value to a separate variable
                $ano_mes = date("Ym", strtotime($date));

                $data[] = [
                    "ano_mes" => $ano_mes,
                    "id_origem" => $id_origem,
                    "cd_ccusto" => $cd_ccusto,
                    "quantitativo" => $qtd_litros,
                ];
            }

            return $data;
        } catch (\Exception $e) {
            // Trate a exceção de acordo com os requisitos do seu aplicativo
            // Por exemplo, você pode registrar a exceção ou lançar uma exceção personalizada
            throw new \Exception("Erro ao obter valores do banco de dados: " . $e->getMessage());
        }
    }
}
