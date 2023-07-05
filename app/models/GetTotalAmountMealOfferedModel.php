<?php

namespace app\models;

use PDO;
use app\database\PgConnection;
use PDOException;

class GetTotalAmountMealOfferedModel
{
    public function processMonthValues($cd_ccusto_filter, $ano, $mes)
    {
        $results = [];

        foreach ($cd_ccusto_filter as $ccusto) {
            $result = $this->getMonthValue($ccusto, $ano, $mes);
            $results[] = $result;
        }

        return $results;
    }

    public function getMonthValue($ccusto, $ano, $mes)
    {
        $ip = $_SERVER['SERVER_ADDR'];
        $acesso = "127.0.0.1"; // Default to local development IP

        if ($ip == "172.32.101.22") {
            $acesso = "172.32.101.24"; // SEDS production IP
        } elseif ($ip == "172.32.100.22") {
            $acesso = "172.32.100.24"; // KSB production IP
        }

        if (!empty($ccusto) && is_array($ccusto)) {
            $parametros = implode(',', array_fill(0, count($ccusto), '?'));
        } else {
            $ccusto = [$ccusto]; // Converte o valor único em um array para uso posterior
            $parametros = '?';
        }

        $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=172.32.100.24", "postgres", "");
        $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        if (is_array($ccusto)) {
            foreach($ccusto as $key => $item) {


                $query = "SELECT r.qtd_ofertadas, r.cd_ccusto,
        CASE
            WHEN r.cd_ccusto LIKE '000000001%' THEN vi_fipe_refeicoes.cd_ccusto
            WHEN r.cd_ccusto LIKE '000000002%' THEN vi_fipe_refeicoes_movel.cd_ccusto
        END AS ccusto,
        CASE 
            WHEN r.cd_ccusto LIKE '000000001%' THEN vi_fipe_refeicoes.id_origem
            WHEN r.cd_ccusto LIKE '000000002%' THEN vi_fipe_refeicoes_movel.id_origem
        END AS id_origem
    FROM refeicoes_ofertadas('$item', $ano, $mes) AS r
    LEFT JOIN vi_fipe_refeicoes ON r.cd_ccusto LIKE '000000001%' AND vi_fipe_refeicoes.cd_ccusto = '$item'
LEFT JOIN vi_fipe_refeicoes_movel ON r.cd_ccusto LIKE '000000002%' AND vi_fipe_refeicoes_movel.cd_ccusto = '$item'
    WHERE (r.cd_ccusto LIKE '000000001%' OR r.cd_ccusto LIKE '000000002%')
        AND (vi_fipe_refeicoes.cd_ccusto IS NOT NULL OR vi_fipe_refeicoes_movel.cd_ccusto IS NOT NULL)";

                $stmt = $pgconnection->prepare($query);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    $totalRefeicoes = $result['qtd_ofertadas'];
                    $cd_ccusto = $result['cd_ccusto'];
                    $id_origem = $result['id_origem'];
                    $date = $ano . $mes;
                    $data = [
                        "ano_mes" => $date,
                        "cd_ccusto" => $cd_ccusto,
                        "id_origem" => $id_origem,
                        "quantitativo" => $totalRefeicoes,
                    ];
                }

                // Substitua a linha print_r($data); por:
                echo json_encode($data, JSON_PRETTY_PRINT);

            }

        }

    }
}
