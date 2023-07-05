<?php

namespace app\models;

use PDO;

class GetAmountServiceVivaLeiteModel
{
    public function getMonthValue($cd_ccusto_filter = [], $ano_mes_inicio, $ano_mes_fim)
    {

        $data_inicio = date('Y-m-d', strtotime($ano_mes_inicio . '01'));
        $data_fim = date('Y-m-d', strtotime($ano_mes_fim . '01'));

        if (!empty($cd_ccusto_filter) && is_array($cd_ccusto_filter)) {
            $parametros = implode(',', array_map(function ($item) {
                return "'" . $item . "'";
            }, $cd_ccusto_filter));
        } else {
            $parametros = '';
        }

        try {
            // Cria uma instância de conexão com o banco de dados
            $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=172.32.100.24", "bomprato", "bp050713");
            $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Monta a consulta SQL
            $query = "SELECT
                    id_origem,
                    cd_ccusto,
                    date_actual,
                    qtd_atendimentos
              FROM
                    vi_fipe_vivaleite
              WHERE
                    date_actual >= :ano_mes_inicio AND date_actual <= :ano_mes_fim";

            if (!empty($cd_ccusto_filter)) {
                $query .= " AND cd_ccusto in ($parametros)";
            }

            $query .= " ORDER BY date_actual";

            $stmt = $pgconnection->prepare($query);

            $stmt->bindParam(':ano_mes_inicio', $data_inicio, PDO::PARAM_STR);
            $stmt->bindParam(':ano_mes_fim', $data_fim, PDO::PARAM_STR);

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            foreach ($results as $row) {
                $id_origem = $row['id_origem'];
                $cd_ccusto = $row['cd_ccusto'];
                $date_actual = $row['date_actual'];
                $qtd_atendimentos = $row['qtd_atendimentos'];
                $date = $date_actual; // Assign the string value to a separate variable
                $ano_mes = date("Ym", strtotime($date));

                $data[] = [
                    "ano_mes" => $ano_mes,
                    "id_origem" => $id_origem,
                    "cd_ccusto" => $cd_ccusto,
                    "quantitativo" => $qtd_atendimentos,
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
