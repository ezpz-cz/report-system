<?php

if (!isset($_GET["lang"]))
    die("lang is not set!");

if (!isset($_GET["report_ids"]))
    die("report_id is not set!");

session_start();

/*if ($_SESSION['ezpz_report_permission'] == "1")
{
    $isMainAdmin = True;
}
else
{
    $isMainAdmin = False;
}

if ($_SESSION['ezpz_report_permission'] == "2")
{
    $isAdmin = True;
}*/

try
{
    include_once(dirname(__FILE__)."/../scripts-generic/servers.php");
    include_once(dirname(__FILE__)."/../scripts-generic/getPDO.php");
    include_once(dirname(__FILE__)."/../scripts-generic/PDOQuery.php");
    include_once(dirname(__FILE__)."/../scripts-generic/checkAdmin.php");

    include_once(dirname(__FILE__)."/config/translation_report.php");

    $isMainAdmin = checkMainAdmin();

    $lang = getReportTranslation($_GET["lang"]);

    $pdo = getPDOConnection();

    $parameters_report_id = array();
    $where_report_id = "";
    $id_count = count($_GET['report_ids']);

    if ($id_count > 1)
    {
        $i = 0;
        foreach ($_GET['report_ids'] as $id)
        {
            if ($i < $id_count - 1) $where_report_id .= " r.id = :report_id$i OR ";
            else $where_report_id .= " r.id = :report_id$i ";

            $parameters_report_id[":report_id$i"] = $id;
            $i++;
        }
    }
    else
    {
        $where_report_id = "r.id = :report_id";
        $parameters_report_id[":report_id"] = $_GET['report_ids'][0];
    }

    //print_r($_GET['report_ids']);
    //echo $where_report_id;

    $query_group = "
    SELECT COUNT(*) AS report_count, r.target_id, p.nick, p.sid, p.ip, p.hlstats_id, a.name AS admin_name, a.id AS admin_id, DATE(time_create) AS time_create_date, r.id AS report_id
    FROM `ezpz-report-g`.report_report AS r
    JOIN `ezpz-report-g`.report_players AS p ON p.id = r.target_id
    JOIN `soe-csgo`.sb_admins AS a ON a.id = r.admin_id
    WHERE $where_report_id
    GROUP BY DAY(time_create_date), target_id";

    //print_r($parameters_group);
    //echo "date_create_from: $by_date_create_from | date_create_to: $by_date_create_to <br />";
    //echo $query_group . "<br /><br />";

    $result_group = getPDOParametrizedQueryResult($pdo, $query_group, $parameters_report_id, __FILE__, __LINE__);
    if (!$result_group and !empty($result_group))
    {
        throw new Exception("Cannot get the query result!");
    }

    $table_header = "
            <th>" . $lang["table_headers"]["date"] . "</th>
            <th>" . $lang["table_headers"]["target"] . "</th>
            <th>" . $lang["table_headers"]["reports_count"] . "</th>
            <th>" . $lang["table_headers"]["admin"] . "</th>";

    $tableHeader_inner_original =
        "<th>" . $lang["table_headers"]["time"] . "</th>
        <th>" . $lang["table_headers"]["status"] . "</th>
        <th>" . $lang["table_headers"]["reporter"] . "</th>
        <th>" . $lang["table_headers"]["server"] . "</th>
        <th>" . $lang["table_headers"]["map"] . "/" . $lang["table_headers"]["round"] . "</th>
        <th>" . $lang["table_headers"]["reason"] . "</th>
        <th>" . $lang["table_headers"]["note"] . "</th>";

    $table = '
            <table id="table-reports-group" class="row-border hover">
                <thead>
                    <tr>'
        . $table_header .
        '</tr>
                </thead>
                <tbody>';

    $i = 0;

    foreach ($result_group as $row_group)
    {
        //echo "session admin id:" . $_SESSION['ezpz_sb_admin_id'] . ", DB admin id: " . $row_group["admin_id"] . "<br />";

        $isAdmin = checkAdminForReportByAdminId($row_group["admin_id"]);

        if (($isAdmin || $isMainAdmin))
        {
            $tableHeader_inner = "<th class='no-sort'><input type='checkbox' class='chb-select-all'/><a class='a-select-all'>" . $lang["table_headers"]["all"] . "</a></th>" . $tableHeader_inner_original;
        }
        else
        {
            $tableHeader_inner = $tableHeader_inner_original . "";
        }

        //echo("<br />" . $row["report_count"] . ", " . $row["target_id"] . ", " . $row["time_create"]);

        $query = sprintf("
            SELECT
              r.id AS report_id,
              r.reason_custom,
              r.demo_file,
              r.note,
              TIME_FORMAT(r.time_create, '%%H:%%i:%%s') AS time_create,
              r.time_finish,
              r.round,
              r.sourcebans_link,
              r.map_id,
              r.target_id,
              rep.nick AS rep_nick,
              rep.sid AS rep_sid,
              rep.ip AS rep_ip,
              rep.hlstats_id AS rep_hlstats_id,
              trg.nick AS trg_nick,
              trg.sid AS trg_sid,
              trg.ip AS trg_ip,
              trg.hlstats_id AS trg_hlstats_id,
              GROUP_CONCAT(rs.reason_" . $lang["db"]["suffix"] . " SEPARATOR ', ') AS reasons,
              s.name AS server_name,
              s.server_id,
              s.path,
              m.map,
              m.id,
              st.status_" . $lang["db"]["suffix"] . " AS status,
              st.id AS status_id
            FROM
              `ezpz-report-g`.report_report AS r
            LEFT JOIN `ezpz-report-g`.report_report_reason AS rs_join ON rs_join.report_id = r.id
            LEFT JOIN `ezpz-report-g`.report_reason AS rs ON rs.id = rs_join.reason_id
            LEFT JOIN `ezpz-report-g`.report_players AS rep ON rep.id = r.reporter_id
            LEFT JOIN `ezpz-report-g`.report_players AS trg ON trg.id = r.target_id
            LEFT JOIN `soe-csgo`.utils_servers AS s ON s.server_id = r.server_id
            LEFT JOIN `ezpz-report-g`.report_status AS st ON st.id = r.status_id
            LEFT JOIN `ezpz-report-g`.report_map AS m ON m.id = r.map_id
            WHERE r.id IN
                	(SELECT id FROM `ezpz-report-g`.report_report WHERE target_id = %d AND time_create BETWEEN '%s 00:00:00' AND '%s 23:59:59' AND ($where_report_id))
            GROUP BY r.id",
            $row_group["target_id"], $row_group["time_create_date"], $row_group["time_create_date"]);

        //$where_report_id
        //echo $query . "<br /><br />";

        $result = getPDOParametrizedQueryResult($pdo, $query, $parameters_report_id, __FILE__, __LINE__);
        if (!$result and !empty($result)) throw new Exception("Cannot get the query result!");

        $table_inner = "
            <table class='row-border hover table-reports'>
                <thead>
                    <tr>"
            . $tableHeader_inner .
            "</tr>
                </thead>
                <tbody id='table-body'>";

        if ($result instanceof PDOStatement) $report_count = $result->rowCount();

        if (is_array($result)) $report_count = count($result);

        if ($report_count > 0)
        {
            foreach ($result as $row)
            {
                $table_inner .= sprintf(
                    "<tr report_id='%d'>\n"
                    . (($isAdmin || $isMainAdmin) ? "<td><input class='chb-report' type='checkbox' /></td>" : "") .
                    "<td>%s</td>\n
                <td status_id='%d' "
                    . (($row["status_id"] == 3 or $row["status_id"] == 4)
                        ? "time_finish='" . $row["time_finish"] . "'" .
                        (!is_null($row["sourcebans_link"]) ?
                            "sourcebans_link='" . $row["sourcebans_link"] . "'"
                            : "") . "><bubble class='bubble-status'>%s</bubble>"
                        : ">%s") . "
                </td>\n
                <td class='cell-reporter'
                    rep_sid='%s'
                    rep_ip='%s'
                    rep_hlstats_link='%s'
                    rep_chatlog_link='%s'
                    rep_connectlog_link='%s'>
                    <bubble class='bubble-reporter'>%s</bubble>
                </td>\n
                <td server_id='%d'>%s</td>\n
                <td map_id='%d'><a href='%s'>%s/%d</a></td>\n
                <td>" . $row['reasons'] . ($row['reason_custom'] != '' ? (' + ' . htmlspecialchars($row['reason_custom'])) : '') . "</td>\n
                <td>%s</td>\n
            </tr>",
                    $row["report_id"],
                    $row["time_create"],
                    $row["status_id"], $row["status"],
                    $row["rep_sid"], $row["rep_ip"],
                    "http://stats.ezpz.cz/hlstats.php?mode=playerinfo&player=" . $row["rep_hlstats_id"],
                    "http://ezpz.cz/page/utilities-chatlog?steamid=" . $row["rep_sid"],
                    "http://ezpz.cz/page/utilities-connectlog?steamid=" . $row["rep_sid"],
                    htmlspecialchars($row["rep_nick"]),
                    $row["server_id"], htmlspecialchars($row["server_name"]),
                    $row["map_id"], sprintf("http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/utils-gotv/download.php?server_id=%d&file=%s/%s", $row["server_id"], $row["path"], $row["demo_file"]), $row["map"], $row["round"],
                    htmlspecialchars($row["note"])
                );
            }
            $table_inner .= "</tbody></table>";

            //echo $table_inner . "<br /><br />";

            if (($isAdmin || $isMainAdmin))
            {
                $table_inner .= "
            <div class='div-admin-actions'>
                <button class='button-progress'>" . $lang["buttons"]["progress"] . "</button>
                <button class='button-reject'>" . $lang["buttons"]["reject"] . "</button>
                <button class='button-accept'>" . $lang["buttons"]["accept"] . "</button>
                <button class='button-ban' group_id='$i'>" . $lang["buttons"]["ban"] . "</button>
            </div>";
            }

            $table .= sprintf('
            <tr reports="%s" group_id="%d">
                <td>%s</td>
                <td class="cell-target"
                    trg_sid="%s"
                    trg_ip="%s"
                    trg_nick="%s"
                    trg_hlstats_link="%s"
                    trg_chatlog_link="%s"
                    trg_connectlog_link="%s">
                        <bubble class="bubble-target">%s</bubble>
                </td>
                <td>%d</td>
                <td>%s</td>
            </tr>',
                $table_inner,
                $i,
                $row_group["time_create_date"],
                $row_group["sid"],
                $row_group["ip"],
                htmlspecialchars($row_group["nick"]),
                "http://stats.ezpz.cz/hlstats.php?mode=playerinfo&player=" . $row_group["hlstats_id"],
                "http://ezpz.cz/page/utilities-chatlog?steamid=" . $row_group["sid"],
                "http://ezpz.cz/page/utilities-connectlog?steamid=" . $row_group["sid"],
                htmlspecialchars($row_group["nick"]),
                //$row_group["report_count"],
                $report_count,
                htmlspecialchars($row_group["admin_name"]));

            $i++;
        }
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
    //echo $ex->getMessage();

    header('Content-Type: application/json');

    echo json_encode(array(
        "success" => false,
        "data" => $ex->getMessage()
    ));
}