<?php

namespace app\models;

use PDO;
use app\database\PgConnection;

class GetTotalAmountLunchModel
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
        $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=172.32.100.24", "bomprato", "bp050713");
        $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT 
            vi_fipe_refeicoes.id_origem,
            vi_fipe_refeicoes.cd_ccusto,
            vi_fipe_refeicoes.date_actual,
            vi_fipe_refeicoes.codigo_unidade,
            vi_fipe_refeicoes.qtd_almoco,
            vi_fipe_refeicoes_movel.id_origem AS id_origem_movel,
            vi_fipe_refeicoes_movel.cd_ccusto AS cd_ccusto_movel,
            vi_fipe_refeicoes_movel.codigo_unidade AS codigo_unidade_movel,
            vi_fipe_refeicoes_movel.date_actual AS date_actual_movel,
            vi_fipe_refeicoes_movel.qtd_almoco AS qtd_almoco_movel,
            SUM(COALESCE(vi_fipe_refeicoes.qtd_almoco, 0)) + SUM(COALESCE(vi_fipe_refeicoes_movel.qtd_almoco, 0)) AS total_almoco
        FROM 
            vi_fipe_refeicoes
        LEFT JOIN 
            vi_fipe_refeicoes_movel ON vi_fipe_refeicoes.codigo_unidade = vi_fipe_refeicoes_movel.codigo_unidade
                AND vi_fipe_refeicoes.date_actual = vi_fipe_refeicoes_movel.date_actual
        WHERE ";

        if (empty($parametros)) {
            $query .= "(vi_fipe_refeicoes.cd_ccusto IS NULL OR vi_fipe_refeicoes_movel.cd_ccusto IS NULL)";
        } else {
            $query .= "(vi_fipe_refeicoes.cd_ccusto iN ($parametros) OR vi_fipe_refeicoes_movel.cd_ccusto IN ($parametros))";
        }

        $query .= " AND (vi_fipe_refeicoes.date_actual >= :ano_mes_inicio OR vi_fipe_refeicoes_movel.date_actual >= :ano_mes_inicio)
            AND (vi_fipe_refeicoes.date_actual <= :ano_mes_fim OR vi_fipe_refeicoes_movel.date_actual <= :ano_mes_fim)
            GROUP BY 
    vi_fipe_refeicoes.id_origem,
    vi_fipe_refeicoes.cd_ccusto,
    vi_fipe_refeicoes.date_actual,
    vi_fipe_refeicoes.codigo_unidade,
    vi_fipe_refeicoes.qtd_almoco,
    vi_fipe_refeicoes_movel.id_origem,
    vi_fipe_refeicoes_movel.cd_ccusto,
    vi_fipe_refeicoes_movel.codigo_unidade,
    vi_fipe_refeicoes_movel.date_actual,
    vi_fipe_refeicoes_movel.qtd_almoco
";

        $stmt = $pgconnection->prepare($query);

        $stmt->bindParam(':ano_mes_inicio', $data_inicio, PDO::PARAM_STR);
        $stmt->bindParam(':ano_mes_fim', $data_fim, PDO::PARAM_STR);

        $filter = '';
        if (!empty($cd_ccusto_filter) && is_array($cd_ccusto_filter)) {
            $filter = implode(',', array_map(function ($item) {
                return "'" . $item . "'";
            }, $cd_ccusto_filter));
        }

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
            $date_actual = $row['date_actual'];
            $qtd_almoco = $row['qtd_almoco'];
            $cd_ccusto_movel = $row['cd_ccusto_movel'];
            $id_origem_movel = $row['id_origem_movel'];
            $date_actual_movel = $row['date_actual_movel'];
            $qtd_almoco_movel = $row['qtd_almoco_movel'];
            $totalAlmoco = $row['total_almoco'];

            if ($ccusto == "bom_prato") {
                $data[] = [
                    "id_origem" => $id_origem,
                    "date_actual" => $date_actual,
                    "cd_ccusto" => $cd_ccusto,
                    "quantitativo" => $qtd_almoco,
                ];
            } elseif ($ccusto == "bom_prato_movel") {
                $data[] = [
                    "id_origem" => $id_origem_movel,
                    "date_actual" => $date_actual_movel,
                    "cd_ccusto" => $cd_ccusto_movel,
                    "quantitativo" => $qtd_almoco_movel,
                ];
            } elseif ($ccusto === null) { 
                $data[] = [
                    "id_origem" => $id_origem,
                    "date_actual" => $date_actual,
                    "cd_ccusto" => $cd_ccusto,
                    "quantitativo" => $totalAlmoco,
                ];
            }
        }
        $item = json_encode($data);
        return $data;
    }
}
