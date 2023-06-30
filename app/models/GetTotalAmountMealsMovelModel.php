<?php

namespace app\models;

use PDO;
use app\database\PgConnection;

class GetTotalAmountMealsMovelModel
{
    public function getMonthValue($cd_ccusto_filter, $ano_mes_inicio, $ano_mes_fim)
    {

        $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=172.32.100.24", "bomprato", "bp050713");
        $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = "SELECT
            id_origem,
            cd_ccusto,
            codigo_unidade,
            date_actual,
            qtd_cafe,
            qtd_almoco,
            qtd_jantar,
            SUM(COALESCE(qtd_cafe, 0) + COALESCE(qtd_almoco, 0) + COALESCE(qtd_jantar, 0)) AS total_refeicoes
        FROM
            vi_fipe_refeicoes_movel
        WHERE
            date_actual >= :ano_mes_inicio AND date_actual <= :ano_mes_fim";

        if (!empty($cd_ccusto_filter)) {
            $query .= " AND cd_ccusto = :cd_ccusto";
        }

        $query .= " GROUP BY
            id_origem,
            cd_ccusto,
            codigo_unidade,
            date_actual,
            qtd_cafe,
            qtd_almoco,
            qtd_jantar";

        $stmt = $pgconnection->prepare($query);

        $stmt->bindParam(':ano_mes_inicio', $ano_mes_inicio, PDO::PARAM_STR);
        $stmt->bindParam(':ano_mes_fim', $ano_mes_fim, PDO::PARAM_STR);

        if (!empty($cd_ccusto_filter)) {
            $stmt->bindParam(':cd_ccusto', $cd_ccusto_filter, PDO::PARAM_STR);
        }

        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($results as $row) {
            $id_origem = $row['id_origem'];
            $cd_ccusto = $row['cd_ccusto'];
            $codigo_unidade = $row['codigo_unidade'];
            $date_actual = $row['date_actual'];
            $totalRefeicoes = $row['total_refeicoes'];

            $data[] = [
                "id_origem" => $id_origem,
                "date_actual" => $date_actual,
                "cd_ccusto" => $cd_ccusto,
                "quantitativo" => $totalRefeicoes,
            ];
        }

        return $data;
    }
}
