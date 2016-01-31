<?php

if (!isset($_GET["serverid"]))
    die("serverid is not set!");

if (!isset($_GET["lang"]))
    die("lang is not set!");

session_start();

try
{
    include_once(dirname(__FILE__)."/../scripts-generic/servers.php");
    include_once(dirname(__FILE__)."/../scripts-generic/getPDO.php");
    include_once(dirname(__FILE__)."/../scripts-generic/PDOQuery.php");
    include_once(dirname(__FILE__)."/../scripts-generic/checkAdmin.php");

    include_once(dirname(__FILE__)."/config/translation_report.php");

    $isMainAdmin = checkMainAdmin();

    if ($_GET["serverid"] == "" OR $_GET["serverid"] == "-1")
    {
        $server = NULL;
    }
    else
    {
        $server = GetServers()[$_GET["serverid"]];
    }

    $lang = getReportTranslation($_GET["lang"]);

    $pdo = getPDOConnection();

    $where = " WHERE ";

    if (isset($_GET["report_ids"]) AND count($_GET["report_ids"]) > 0)
    {
        $by_report_ids = $_GET["report_ids"];
        $parameters = array();
        $id_count = count($by_report_ids);

        if ($id_count > 1)
        {
            $i = 0;
            foreach ($by_report_ids as $id)
            {
                if ($i < $id_count - 1) $where .= " r.id = :report_id$i OR ";
                else $where .= " r.id = :report_id$i ";

                $parameters[":report_id$i"] = $id;
                $i++;
            }
        }
        else
        {
            $where .= "r.id = :report_id";
            $parameters[":report_id"] = $by_report_ids[0];
        }
    }
    else
    {
        $by_report_status_ids = $_GET['report_status_ids'];
        $by_reason_id = $_GET['reason_id'];
        $by_reason_custom = $_GET['reason_custom'];
        $by_date_create_from = $_GET['date_create_from'];
        $by_date_create_to = $_GET['date_create_to'];

        $by_target = preg_replace("/STEAM_.:/", "", $_GET['target']);
        $by_reporter = preg_replace("/STEAM_.:/", "", $_GET['reporter']);

        $by_map_id = $_GET['map_id'];
        $by_admin_id = intval($_GET['admin_id']);

        $conditions = array();
        $parameters = array();
        $where = "";

        if ($server != NULL)
        {
            $conditions[] = "r.server_id = :server_id";
            $parameters[":server_id"] = intval($server["server_id"]);
        }

        $where_status_ids = " (";
        $id_count = count($by_report_status_ids);

        if ($id_count > 1)
        {
            $i = 0;
            foreach ($by_report_status_ids as $id)
            {
                if ($i < $id_count - 1) $where_status_ids .= " r.status_id = :status_id$i OR ";
                else $where_status_ids .= " r.status_id = :status_id$i ";

                $parameters[":status_id$i"] = $id;
                $i++;
            }
            $where_status_ids .= ") ";
            $conditions[] = $where_status_ids;
        }
        elseif ($id_count == 1 and $by_report_status_ids[0] != "")
        {
            $conditions[] = "r.status_id = :status_id";
            $parameters[":status_id"] = $by_report_status_ids[0];
        }

        if ($by_target != "")
        {
            $conditions[] = " (trg.nick LIKE :target_nick OR trg.sid LIKE :target_sid OR trg.ip = :target_ip) ";
            $parameters[":target_nick"] = "%$by_target%";
            $parameters[":target_sid"] = "%$by_target%";
            $parameters[":target_ip"] = "$by_target";
        }

        if ($by_reporter != "")
        {
            $conditions[] = " (rep.nick LIKE :reporter_nick OR rep.sid LIKE :reporter_sid OR rep.ip = :reporter_ip) ";
            $parameters[":reporter_nick"] = "%$by_reporter%";
            $parameters[":reporter_sid"] = "%$by_reporter%";
            $parameters[":reporter_ip"] = "$by_reporter";
        }

        if ($by_map_id != "")
        {
            $conditions[] = "m.id = :map_id";
            $parameters[":map_id"] = $by_map_id;
        }

        if ($by_admin_id != "")
        {
            $conditions[] = "r.admin_id = :admin_id";
            $parameters[":admin_id"] = $by_admin_id;
        }

        if ($by_reason_id != "" and $by_reason_custom != "")
        {
            $conditions[] = " (rs.id = :reason_id OR r.reason_custom LIKE :reason_custom) ";
            $parameters[":reason_id"] = intval($by_reason_id);
            $parameters[":reason_custom"] = "%$by_reason_custom%";
        }

        if ($by_reason_id != "" and $by_reason_custom == "")
        {
            $conditions[] = "rs.id = :reason_id";
            $parameters[":reason_id"] = intval($by_reason_id);
        }

        if ($by_reason_id == "" and $by_reason_custom != "")
        {
            $conditions[] = "r.reason_custom LIKE :reason_custom";
            $parameters[":reason_custom"] = "%$by_reason_custom%";
        }

        if (($by_date_create_from != "" and $by_date_create_to == ""))
        {
            $where = " WHERE DATE(time_create) >= :time_create";
            $parameters = array(":time_create" => $by_date_create_from);
        }
        else if (($by_date_create_from == "" and $by_date_create_to != ""))
        {
            $where = " WHERE DATE(time_create) <= :time_create";
            $parameters = array(":time_create" => $by_date_create_to);
        }
        else if (($by_date_create_from != "" and $by_date_create_to != ""))
        {
            $where = " WHERE DATE(time_create) BETWEEN :time_create_from AND :time_create_to";
            $parameters = array(":time_create_from" => $by_date_create_from, ":time_create_to" => $by_date_create_to);
        }

        if (count($conditions) > 1) {
            $where = " AND " . implode(' AND ', $conditions);
        }

        if (count($conditions) == 1) {
            $where = " AND " . $conditions[0];
        }
    }

    $query = "
      SELECT
        DATE(time_create) AS time_create_date,
        GROUP_CONCAT(r.id SEPARATOR ',') AS report_ids,
        a.name AS admin_name, a.id AS admin_id,
        trg.nick AS trg_nick, trg.ip AS trg_ip, trg.sid AS trg_sid, trg.hlstats_id
      FROM
        `ezpz-report-g`.report_report AS r
      JOIN
        `ezpz-report-g`.report_report_reason AS rs_join ON rs_join.report_id = r.id
      JOIN
        `ezpz-report-g`.report_reason AS rs ON rs.id = rs_join.reason_id
      JOIN
        `ezpz-report-g`.report_players AS rep ON rep.id = r.reporter_id
      JOIN
        `ezpz-report-g`.report_players AS trg ON trg.id = r.target_id
      JOIN
        `soe-csgo`.utils_servers AS s ON s.server_id = r.server_id
      JOIN
        `ezpz-report-g`.report_status AS st ON st.id = r.status_id
      JOIN
        `ezpz-report-g`.report_map AS m ON m.id = r.map_id
      JOIN
        `soe-csgo`.sb_admins AS a ON a.id = r.admin_id"
      . $where . "
      GROUP BY
        DAY(time_create_date), target_id
      ORDER BY
        time_create_date DESC";

    //echo $query;

    //print_r($parameters);
    //echo "$query <br /><br />";

    $result = getPDOParametrizedQueryResult($pdo, $query, $parameters, __FILE__, __LINE__);
    if (!$result and !empty($result))
    {
        throw new Exception("Cannot get the query result!");
    }

    $table_header = "
                <th>" . $lang["table_headers"]["date"] . "</th>
                <th>" . $lang["table_headers"]["target"] . "</th>
                <th>" . $lang["table_headers"]["admin"] . "</th>";

    $table = '
                <table id="table-reports-group" class="row-border hover">
                    <thead>
                        <tr>'
        . $table_header .
        '</tr>
                    </thead>
                    <tbody>';

    $i = 0;

    foreach($result as $row)
    {
        $report_ids = join(array_unique(explode(",", $row["report_ids"])), ",");

        $table .= sprintf('
            <tr group_id="%d" report_ids="%s">
                <td><a href="http://ezpz.cz/page/report-system?date_create_to=%s&date_create_from=%s&target=%s">%s</a></td>
                <td class="cell-target"
                    trg_sid="%s"
                    trg_ip="%s"
                    trg_nick="%s"
                    trg_hlstats_link="%s"
                    trg_chatlog_link="%s"
                    trg_connectlog_link="%s">
                        <bubble class="bubble-target">%s</bubble>
                </td>
                <td>%s</td>
            </tr>',
            $i, $report_ids,
            $row["time_create_date"], $row["time_create_date"], $row["trg_sid"], $row["time_create_date"],
            $row["trg_sid"],
            $row["trg_ip"],
            htmlspecialchars($row["nick"]),
            "http://stats.ezpz.cz/hlstats.php?mode=playerinfo&player=" . $row["hlstats_id"],
            "http://ezpz.cz/page/utilities-chatlog?steamid=" . $row["trg_sid"],
            "http://ezpz.cz/page/utilities-connectlog?steamid=" . $row["trg_sid"],
            htmlspecialchars($row["trg_nick"]),
            htmlspecialchars($row["admin_name"]));

        $i++;
    }

    $table .= "    </tbody>
                </table>";

    //echo $table;

    header('Content-Type: application/json');

    echo json_encode(array(
        "success" => true,
        "data" => $table
    ));
}
catch(Exception $ex)
{
    header('Content-Type: application/json');

    echo json_encode(array(
        "success" => false,
        "data" => $ex->getMessage()
    ));
    //echo $ex->getMessage();
}