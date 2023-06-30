<?php

namespace app\models;

use PDO;
use app\database\PgConnection;

class TotalRefeicoesModel
{
    public function getVMensalRefeicaoOfertada($cd_ccusto_filter, $ano_mes_inicio, $ano_mes_fim)
    {
        $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=172.32.100.24", "bomprato", "bp050713");
        $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT 
            vi_fipe_refeicoes.id_origem,
            vi_fipe_refeicoes.cd_ccusto,
            vi_fipe_refeicoes.date_actual,
            vi_fipe_refeicoes.codigo_unidade,
            vi_fipe_refeicoes.qtd_cafe,
            vi_fipe_refeicoes.qtd_almoco,
            vi_fipe_refeicoes.qtd_jantar,
            vi_fipe_refeicoes_movel.id_origem AS id_origem_movel,
            vi_fipe_refeicoes_movel.cd_ccusto AS cd_ccusto_movel,
            vi_fipe_refeicoes_movel.codigo_unidade AS codigo_unidade_movel,
            vi_fipe_refeicoes_movel.date_actual AS date_actual_movel,
            vi_fipe_refeicoes_movel.qtd_cafe AS qtd_cafe_movel,
            vi_fipe_refeicoes_movel.qtd_almoco AS qtd_almoco_movel,
            vi_fipe_refeicoes_movel.qtd_jantar AS qtd_jantar_movel,
            SUM(COALESCE(vi_fipe_refeicoes.qtd_cafe, 0) + COALESCE(vi_fipe_refeicoes.qtd_almoco, 0) + COALESCE(vi_fipe_refeicoes.qtd_jantar, 0)) AS refeicoes_bomprato,
            SUM(COALESCE(vi_fipe_refeicoes_movel.qtd_cafe, 0) + COALESCE(vi_fipe_refeicoes_movel.qtd_almoco, 0) + COALESCE(vi_fipe_refeicoes_movel.qtd_jantar, 0)) AS refeicoes_bomprato_movel,
            SUM(COALESCE(vi_fipe_refeicoes.qtd_cafe, 0) + COALESCE(vi_fipe_refeicoes.qtd_almoco, 0) + COALESCE(vi_fipe_refeicoes.qtd_jantar, 0) + COALESCE(vi_fipe_refeicoes_movel.qtd_cafe, 0) + COALESCE(vi_fipe_refeicoes_movel.qtd_almoco, 0) + COALESCE(vi_fipe_refeicoes_movel.qtd_jantar, 0)) AS total_refeicoes
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
            vi_fipe_refeicoes.qtd_almoco,
            vi_fipe_refeicoes.qtd_jantar,
            vi_fipe_refeicoes_movel.id_origem,
            vi_fipe_refeicoes_movel.cd_ccusto,
            vi_fipe_refeicoes_movel.codigo_unidade,
            vi_fipe_refeicoes_movel.date_actual,
            vi_fipe_refeicoes_movel.qtd_cafe,
            vi_fipe_refeicoes_movel.qtd_almoco,
            vi_fipe_refeicoes_movel.qtd_jantar";

        $stmt = $pgconnection->prepare($query);

        if (!empty($cd_ccusto_filter)) {
            $stmt->bindParam(':cd_ccusto', $cd_ccusto_filter, PDO::PARAM_STR);
        }

        $stmt->bindParam(':ano_mes_inicio', $ano_mes_inicio, PDO::PARAM_STR);
        $stmt->bindParam(':ano_mes_fim', $ano_mes_fim, PDO::PARAM_STR);

        $filter = $cd_ccusto_filter;
        $ccusto = null;

        if (!empty($filter)) {
            if (strpos($filter, "000000002") !== false) {
                $ccusto = "bom_prato_movel";
            } else {
                $ccusto = "bom_prato";
            }
        }

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($results as $row) {
            $id_origem = $row['id_origem'];
            $cd_ccusto = $row['cd_ccusto'];
            $codigo_unidade = $row['codigo_unidade'];
            $date_actual = $row['date_actual'];
            $cd_ccusto_movel = $row['cd_ccusto_movel'];
            $id_origem_movel = $row['id_origem_movel'];
            $codigo_unidade_movel = $row['codigo_unidade_movel'];
            $date_actual_movel = $row['date_actual_movel'];
            $refeicoes_bomprato = $row['refeicoes_bomprato'];
            $refeicoes_bomprato_movel = $row['refeicoes_bomprato_movel'];
            $totalRefeicoes = $row['total_refeicoes'];

            if ($ccusto == "bom_prato") {
                $data[] = [
                    "id_origem" => $id_origem,
                    "date_actual" => $date_actual,
                    "cd_ccusto" => $cd_ccusto,
                    "quantitativo" => $totalRefeicoes,
                ];
            } elseif ($ccusto == "bom_prato_movel") {
                $data[] = [
                    "id_origem" => $id_origem_movel,
                    "date_actual" => $date_actual_movel,
                    "cd_ccusto" => $cd_ccusto_movel,
                    "quantitativo" => $totalRefeicoes,
                ];
            } elseif ($ccusto === null) { // Adicionado novo bloco elseif para tratar ccusto igual a null
                $data[] = [
                    "id_origem" => $id_origem,
                    "date_actual" => $date_actual,
                    "cd_ccusto" => $cd_ccusto,
                    "quantitativo" => $totalRefeicoes,
                ];
            }
        }
        $item = json_encode($data);
        return $data;
    }
}
