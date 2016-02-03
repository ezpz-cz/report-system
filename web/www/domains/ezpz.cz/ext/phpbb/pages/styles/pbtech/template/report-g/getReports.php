<?php

if (!isset($_GET["lang"]))
    die("lang is not set!");

if (!isset($_GET["report_ids"]))
    die("report_ids is not set!");

if (!isset($_GET["group_id"]))
    die("group_id is not set!");

session_start();

try
{
    include_once(dirname(__FILE__)."/../scripts-generic/servers.php");
    include_once(dirname(__FILE__)."/../scripts-generic/getPDO.php");
    include_once(dirname(__FILE__)."/../scripts-generic/PDOQuery.php");
    include_once(dirname(__FILE__)."/../scripts-generic/checkAdmin.php");

    include_once(dirname(__FILE__)."/config/translation_report.php");

    $isMainAdmin = checkMainAdmin();

    $lang = getReportTranslation($_GET["lang"]);
    $group_id = $_GET["group_id"];

    $pdo = getPDOConnection();

    $by_report_ids = $_GET["report_ids"];
    $parameters_report_id = array();
    $where_report_id = "";
    $id_count = count($by_report_ids);

    if ($id_count > 1)
    {
        $i = 0;
        foreach ($by_report_ids as $id)
        {
            if ($i < $id_count - 1) $where_report_id .= " r.id = :report_id$i OR ";
            else $where_report_id .= " r.id = :report_id$i ";

            $parameters_report_id[":report_id$i"] = $id;
            $i++;
        }
    }
    else
    {
        $where_report_id = "r.id = :report_id ";
        $parameters_report_id[":report_id"] = $by_report_ids[0];
    }

    $tableHeader_original =
        "<th>" . $lang["table_headers"]["time"] . "</th>
            <th>" . $lang["table_headers"]["status"] . "</th>
            <th>" . $lang["table_headers"]["reporter"] . "</th>
            <th>" . $lang["table_headers"]["server"] . "</th>
            <th>" . $lang["table_headers"]["map"] . "/" . $lang["table_headers"]["round"] . "</th>
            <th>" . $lang["table_headers"]["reason"] . "</th>
            <th>" . $lang["table_headers"]["note"] . "</th>";

    $query = "
    SELECT
      r.id AS report_id,
      r.reason_custom,
      r.demo_file,
      r.note,
      TIME_FORMAT(r.time_create, '%H:%i:%s') AS time_create,
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
      st.id AS status_id,
      a.id AS admin_id
    FROM
      `ezpz-report-g`.report_report AS r
    LEFT JOIN `ezpz-report-g`.report_report_reason AS rs_join ON rs_join.report_id = r.id
    LEFT JOIN `ezpz-report-g`.report_reason AS rs ON rs.id = rs_join.reason_id
    LEFT JOIN `ezpz-report-g`.report_players AS rep ON rep.id = r.reporter_id
    LEFT JOIN `ezpz-report-g`.report_players AS trg ON trg.id = r.target_id
    LEFT JOIN `soe-csgo`.utils_servers AS s ON s.server_id = r.server_id
    LEFT JOIN `ezpz-report-g`.report_status AS st ON st.id = r.status_id
    LEFT JOIN `ezpz-report-g`.report_map AS m ON m.id = r.map_id
    LEFT JOIN `soe-csgo`.sb_admins AS a ON a.id = r.admin_id
    WHERE "
        . $where_report_id .
    "GROUP BY
        r.id;";

    //echo $query . "<br /><br />";

    $result = getPDOParametrizedQueryResult($pdo, $query, $parameters_report_id, __FILE__, __LINE__);
    if (!$result and !empty($result)) {
        throw new Exception("Cannot get the query result!");
    }

    foreach ($by_report_ids as $id)
    {
        $isAdminForReport = checkAdminForReportByReportId($id);
    }

    if (($isAdminForReport || $isMainAdmin))
    {
        $tableHeader_inner = "<th class='no-sort'><input type='checkbox' class='chb-select-all'/><a class='a-select-all'>" . $lang["table_headers"]["all"] . "</a></th>" . $tableHeader_original;
    }
    else
    {
        $tableHeader_inner = $tableHeader_original;
    }

    $table_inner = "
            <table class='row-border hover table-reports'>
                <thead>
                    <tr>"
        . $tableHeader_inner .
        "</tr>
                </thead>
                <tbody id='table-body'>";


    $onlyNew = True;

    foreach ($result as $row)
    {
        $table_inner .= sprintf(
            "<tr report_id='%d'>\n"
            . (($isAdminForReport || $isMainAdmin) ? "<td><input class='chb-report' type='checkbox' /></td>" : "") .
            "<td><a href='http://ezpz.cz/page/report-system?report_ids=%d'>%s</a></td>\n
            <td status_id='%d' "
            . (($row["status_id"] == 3 or $row["status_id"] == 4 or $row["status_id"] == 5)
                ? "time_finish='" . $row["time_finish"] . "' " .
                (!is_null($row["sourcebans_link"]) ?
                    "sourcebans_link='" . $row["sourcebans_link"] . "'"
                    : "sourcebans_link=''") . "><bubble class='bubble-status'>%s</bubble>"
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
            <td>" . $row['reasons'] . ($row['reason_custom'] != '' ? (' + ' . htmlspecialchars($row['reason_custom'])) : '') . "</td>\n" .
            ($row["note"] != "" ? "<td note='%s'><bubble class='bubble-note'>Show</bubble></td>\n" : "<td></td>") .
            "</tr>",
            $row["report_id"],
            $row["report_id"], $row["time_create"],
            $row["status_id"], $row["status"],
            $row["rep_sid"], $row["rep_ip"],
            "http://stats.ezpz.cz/hlstats.php?mode=playerinfo&player=" . $row["rep_hlstats_id"],
            "http://ezpz.cz/page/utilities-chatlog?steamid=" . $row["rep_sid"],
            "http://ezpz.cz/page/utilities-connectlog?steamid=" . $row["rep_sid"],
            htmlspecialchars($row["rep_nick"]),
            $row["server_id"], htmlspecialchars($row["server_name"]),
            $row["map_id"], sprintf("http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/utils-gotv/download.php?server_id=%d&file=%s%s", $row["server_id"], ($row["path"] != "" ? $row["path"] . "/" : ""), $row["demo_file"]), $row["map"], $row["round"],
            htmlspecialchars($row["note"])
        );

        if ($row["status_id"] != "1")
        {
            $onlyNew = False;
        }
    }
    $table_inner .= "</tbody></table>";

    //echo $table_inner . "<br /><br />";

    if (($isAdminForReport || $isMainAdmin))
    {
        // <button class='button-note' >" . $lang["buttons"]["note"] . "</button>
        $table_inner .= "
        <div class='div-admin-actions'>
            <button class='button-progress'>" . $lang["buttons"]["progress"] . "</button>
            <button class='button-reject'>" . $lang["buttons"]["reject"] . "</button>
            <button class='button-accept'>" . $lang["buttons"]["accept"] . "</button>
            <button class='button-ban' group_id='$group_id'>" . $lang["buttons"]["ban"] . "</button>";
        if ($isMainAdmin && !$isAdminForReport && $onlyNew)
            $table_inner .= sprintf("<button class='button-take' date_create='%s' trg_sid='%s'>" . $lang["buttons"]["take_over"] . "</button>",
                $row_group["time_create_date"], $row_group["sid"]);

        $table_inner .= "</div>";
    }

    if (checkAdminBySession() && $onlyNew && !$isAdminForReport && !$isMainAdmin)
    {
        $table_inner .= sprintf("
        <div class='div-admin-actions'>
            <button class='button-take' date_create='%s' trg_sid='%s'>" . $lang["buttons"]["take_over"] . "</button>
        </div>", $row_group["time_create_date"], $row_group["sid"]);
    }

    //echo $table_inner;

    header('Content-Type: application/json');

    echo json_encode(array(
        "success" => true,
        "data" => $table_inner
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