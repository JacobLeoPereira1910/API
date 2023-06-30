<?php

namespace app\models;

use PDO;
use app\database\PgConnection;
use PDOException;

class GetTotalAmountMealOfferedModel
{
    public function getMonthValue($cd_ccusto_filter, $ano, $mes)
    {
        $ip = $_SERVER['SERVER_ADDR'];
        $acesso = "127.0.0.1"; // Default to local development IP

        if ($ip == "172.32.101.22") {
            $acesso = "172.32.101.24"; // SEDS production IP
        } elseif ($ip == "172.32.100.22") {
            $acesso = "172.32.100.24"; // KSB production IP
        }

        $pgconnection = new PDO("pgsql:dbname=bp_analytics;host=172.32.100.24", "postgres", "");
        $pgconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT r.qtd_ofertadas, r.cd_ccusto,
            CASE
                WHEN r.cd_ccusto LIKE '000000001%' THEN vi_fipe_refeicoes.cd_ccusto
                WHEN r.cd_ccusto LIKE '000000002%' THEN vi_fipe_refeicoes_movel.cd_ccusto
            END AS ccusto,
            CASE 
                WHEN r.cd_ccusto LIKE '000000001%' THEN vi_fipe_refeicoes.id_origem
                WHEN r.cd_ccusto LIKE '000000002%' THEN vi_fipe_refeicoes_movel.id_origem
            END AS id_origem
        FROM refeicoes_ofertadas(:cd_ccusto_filter, :ano, :mes) r
        LEFT JOIN vi_fipe_refeicoes ON r.cd_ccusto LIKE '000000001%' AND vi_fipe_refeicoes.cd_ccusto = r.cd_ccusto
        LEFT JOIN vi_fipe_refeicoes_movel ON r.cd_ccusto LIKE '000000002%' AND vi_fipe_refeicoes_movel.cd_ccusto = r.cd_ccusto
        WHERE (r.cd_ccusto LIKE '000000001%' OR r.cd_ccusto LIKE '000000002%')
            AND (vi_fipe_refeicoes.cd_ccusto IS NOT NULL OR vi_fipe_refeicoes_movel.cd_ccusto IS NOT NULL)";

        try {
            $stmt = $pgconnection->prepare($query);

            // Verifique se os parâmetros são definidos antes de vinculá-los
            if (isset($cd_ccusto_filter)) {
                $stmt->bindParam(':cd_ccusto_filter', $cd_ccusto_filter, PDO::PARAM_STR);
            }

            $stmt->bindParam(':ano', $ano, PDO::PARAM_INT);
            $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);

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
            } else {
                // Defina um valor padrão ou lógica para o caso de nenhum resultado ser encontrado
                $data = null;
            }
            return $data;
        } catch (PDOException $e) {
            // Lide com o erro adequadamente, se necessário
            throw $e;
        }
    }
}
