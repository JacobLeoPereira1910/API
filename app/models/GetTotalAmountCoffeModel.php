<?php

namespace app\models;

use PDO;
use app\database\PgConnection;

class GetTotalAmountCoffeModel
{
    public function getMonthValue($cd_ccusto_filter, $ano_mes_inicio, $ano_mes_fim)
    {
        $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=172.32.100.24", "bomprato", "bp050713");
        $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        $query = "SELECT 
            vi_fipe_refeicoes.id_origem,
            vi_fipe_refeicoes.cd_ccusto,
            vi_fipe_refeicoes.date_actual,
            vi_fipe_refeicoes.codigo_unidade,
            vi_fipe_refeicoes.qtd_cafe,
            vi_fipe_refeicoes_movel.id_origem AS id_origem_movel,
            vi_fipe_refeicoes_movel.cd_ccusto AS cd_ccusto_movel,
            vi_fipe_refeicoes_movel.codigo_unidade AS codigo_unidade_movel,
            vi_fipe_refeicoes_movel.date_actual AS date_actual_movel,
            vi_fipe_refeicoes_movel.qtd_cafe AS qtd_cafe_movel,
            SUM(COALESCE(vi_fipe_refeicoes.qtd_cafe, 0)) + SUM(COALESCE(vi_fipe_refeicoes_movel.qtd_cafe, 0)) AS total_cafe
        FROM 
            vi_fipe_refeicoes
        LEFT JOIN 
            vi_fipe_refeicoes_movel ON vi_fipe_refeicoes.codigo_unidade = vi_fipe_refeicoes_movel.codigo_unidade
                AND vi_fipe_refeicoes.date_actual = vi_fipe_refeicoes_movel.date_actual
        WHERE ";

        if (empty($cd_ccusto_filter)) {
            $query .= "(vi_fipe_refeicoes.cd_ccusto IS NULL OR vi_fipe_refeicoes_movel.cd_ccusto IS NULL)";
        } else {
            $query .= "(vi_fipe_refeicoes.cd_ccusto = :cd_ccusto OR vi_fipe_refeicoes_movel.cd_ccusto = :cd_ccusto)";
        }

        $query .= " AND (vi_fipe_refeicoes.date_actual >= :ano_mes_inicio OR vi_fipe_refeicoes_movel.date_actual >= :ano_mes_inicio)
            AND (vi_fipe_refeicoes.date_actual <= :ano_mes_fim OR vi_fipe_refeicoes_movel.date_actual <= :ano_mes_fim)
            GROUP BY 
                vi_fipe_refeicoes.id_origem,
                vi_fipe_refeicoes.cd_ccusto,
                vi_fipe_refeicoes.date_actual,
                vi_fipe_refeicoes.codigo_unidade,
                vi_fipe_refeicoes.qtd_cafe,
                vi_fipe_refeicoes_movel.id_origem,
                vi_fipe_refeicoes_movel.cd_ccusto,
                vi_fipe_refeicoes_movel.codigo_unidade,
                vi_fipe_refeicoes_movel.date_actual,
                vi_fipe_refeicoes_movel.qtd_cafe";

        $stmt = $pgconnection->prepare($query);


        if (!empty($cd_ccusto_filter)) {
            $stmt->bindParam(':cd_ccusto', $cd_ccusto_filter, PDO::PARAM_STR);
        }

        $stmt->bindParam(':ano_mes_inicio', $ano_mes_inicio, PDO::PARAM_STR);
        $stmt->bindParam(':ano_mes_fim', $ano_mes_fim, PDO::PARAM_STR);

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($results as $row) {
            $id_origem = $row['id_origem'];
            $cd_ccusto = $row['cd_ccusto'];
            $date_actual = $row['date_actual'];
            $qtd_cafe = $row['qtd_cafe'];
            $cd_ccusto_movel = $row['cd_ccusto_movel'];
            $id_origem_movel = $row['id_origem_movel'];
            $date_actual_movel = $row['date_actual_movel'];
            $qtd_cafe_movel = $row['qtd_cafe_movel'];
            $totalCafe = $row['total_cafe'];
            $date = $date_actual; // Assign the string value to a separate variable
            $ano_mes = date("Ym", strtotime($date));
            $date_movel = $date_actual_movel; // Assign the string value to a separate variable
            $ano_mes_movel = date("Ym", strtotime($date_movel));

            if ($cd_ccusto_filter === null) {
                $data[] = [
                    "id_origem" => $id_origem,
                    "date_actual" => $ano_mes,
                    "cd_ccusto" => $cd_ccusto,
                    "quantitativo" => $totalCafe,
                ];
            } elseif (strpos($cd_ccusto_filter, "000000002") !== false) {
                $data[] = [
                    "id_origem" => $id_origem_movel,
                    "date_actual" => $ano_mes_movel,
                    "cd_ccusto" => $cd_ccusto_movel,
                    "quantitativo" => $qtd_cafe_movel,
                ];
            } else {
                $data[] = [
                    "id_origem" => $id_origem,
                    "date_actual" => $ano_mes,
                    "cd_ccusto" => $cd_ccusto,
                    "quantitativo" => $qtd_cafe,
                ];
            }
        }

        return $data;
    }
}
