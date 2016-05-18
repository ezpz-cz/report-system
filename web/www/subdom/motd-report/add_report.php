<?php

if ($_GET["asdf"] != "v0E7mux9aFRYWNAN")
    die("Unauthorized request!");

if (!isset($_GET["server_id"]))
    die("server_id is not set!");

if (!isset($_GET["trg_nick"]))
    die("trg_nick is not set!");

if (!isset($_GET["trg_sid"]))
    die("trg_sid is not set!");

if (!isset($_GET["trg_ip"]))
    die("trg_ip is not set!");

if (!isset($_GET["rep_nick"]))
    die("rep_nick is not set!");

if (!isset($_GET["rep_sid"]))
    die("rep_sid is not set!");

if (!isset($_GET["rep_ip"]))
    die("rep_ip is not set!");

/*if (!isset($_GET["reason_ids"]))
    die("reason_ids are not set! Expecting array: reason_ids[]=1&reason_ids[]=2&reason_ids[]=3");*/

if (!isset($_GET["lang"]))
    die("lang is not set!");

if (!isset($_GET["map"]))
    die("map is not set!");

if (!isset($_GET["demo_file"]))
    die("demo_file is not set!");

header('Content-Type: application/json');

try
{
    include_once(dirname(__FILE__) . "/config/config_report.php");
    include_once(dirname(__FILE__) . "/config/translation_report_motd.php");

    include_once("/data/web/virtuals/93680/virtual/www/domains/ezpz.cz/ext/phpbb/pages/styles/pbtech/template/scripts-generic/servers.php");
    include_once("/data/web/virtuals/93680/virtual/www/domains/ezpz.cz/ext/phpbb/pages/styles/pbtech/template/scripts-generic/getPDO.php");
    include_once("/data/web/virtuals/93680/virtual/www/domains/ezpz.cz/ext/phpbb/pages/styles/pbtech/template/scripts-generic/PDOQuery.php");
    include_once(dirname(__FILE__) . "/admins.php");

    $server = GetServers()[$_GET["server_id"]];
    $translation = getReportTranslation($_GET["lang"]);

    $pdo = getPDOConnection();

    // first of all we check if player doesn't exceed the report count per day including bonus reports
    // we get the report count from current day + all successful reports multiplied by coefficient
    $query = "  SELECT
                    COUNT(*) AS report_count_day
                FROM
                    `ezpz-report-g`.report_players AS p
                JOIN
                    `ezpz-report-g`.report_report AS r ON r.reporter_id = p.id
                WHERE
                    DATE(r.time_create) = DATE(NOW()) AND p.sid = :rep_sid
                GROUP BY
                    p.id;";

    $report_count_day = getPDOParametrizedQueryScalarValue($pdo, $query, array(":rep_sid" => $_GET["rep_sid"]), __FILE__, __LINE__);

    $query = "  SELECT
                    COUNT(*) AS report_count_successful
                FROM
                    `ezpz-report-g`.report_players AS p
                JOIN
                    `ezpz-report-g`.report_report AS r ON r.reporter_id = p.id
                WHERE
                    p.sid = :rep_sid AND (r.status_id = 3 OR r.status_id = 4)
                GROUP BY
                    p.id;";

    $report_count_successful = getPDOParametrizedQueryScalarValue($pdo, $query, array(":rep_sid" => $_GET["rep_sid"]), __FILE__, __LINE__);

    if (!$report_count_successful)
    {
        $report_count_successful = 0;
    }

    $max_allowed_report_count = $p_max_reports + ceil($report_count_successful * $p_bonus_coefficient);

    if ($report_count_day >= $max_allowed_report_count)
    {
        echo json_encode(array(
            "success" => false,
            "data" => sprintf($translation["texts"]["report_count_exceeded"], $max_allowed_report_count)
        ));

        exit;
    }

    // INSERT reporter to report_players if player doesn't exist there
    // UPDATE ip and nick if reporter exists
    if (PDOcheckEmptyQuery($pdo, "SELECT id FROM `ezpz-report-g`.report_players WHERE sid = :rep_sid", __FILE__, __LINE__, array(":rep_sid" => $_GET["rep_sid"])))
    {
        // find the player's HLStats ID
        $query = "SELECT
                    h.playerId
                  FROM
                    `soe-hlstats`.`hlstats_PlayerUniqueIds` AS h
                  WHERE h.uniqueId = :rep_sid";
        $hlstats_id = getPDOParametrizedQueryScalarValue($pdo, $query, array(":rep_sid" => preg_replace("/STEAM_\d:/", "", $_GET["rep_sid"])), __FILE__, __LINE__);

        if ($hlstats_id != False)
        {
            $query = "INSERT INTO `ezpz-report-g`.report_players(sid, ip, nick, hlstats_id) VALUES (:rep_sid, :rep_ip, :rep_nick, :hlstats_id)";
            $parameters = array(":rep_sid" => $_GET["rep_sid"], ":rep_ip" => $_GET["rep_ip"], ":rep_nick" => $_GET["rep_nick"], ":hlstats_id" => intval($hlstats_id));
            PDOExecParametrizedQuery($pdo, $query, $parameters, __FILE__, __LINE__);
        }
        else
        {
            $query = "INSERT INTO `ezpz-report-g`.report_players(sid, ip, nick) VALUES (:rep_sid, :rep_ip, :rep_nick)";
            $parameters = array(":rep_sid" => $_GET["rep_sid"], ":rep_ip" => $_GET["rep_ip"], ":rep_nick" => $_GET["rep_nick"]);
            PDOExecParametrizedQuery($pdo, $query, $parameters, __FILE__, __LINE__);
        }

        $reporter_id = $pdo->lastInsertId();
    }
    else
    {
        $reporter_id = getPDOParametrizedQueryScalarValue($pdo, "SELECT id FROM `ezpz-report-g`.report_players WHERE sid = :rep_sid", array(":rep_sid" => $_GET["rep_sid"]), __FILE__, __LINE__);
        $parameters = array(":rep_ip" => $_GET["rep_ip"], ":rep_nick" => $_GET["rep_nick"], ":reporter_id" => intval($reporter_id));
        PDOExecParametrizedQuery($pdo, "UPDATE `ezpz-report-g`.report_players SET ip = :rep_ip, nick = :rep_nick WHERE id = :reporter_id", $parameters, __FILE__, __LINE__);
    }

    // INSERT target to report_players if player doesn't exist there
    // UPDATE ip and nick if target exists
    if (PDOcheckEmptyQuery($pdo, "SELECT id FROM `ezpz-report-g`.report_players WHERE sid = :trg_sid", __FILE__, __LINE__, array(":trg_sid" => $_GET["trg_sid"])))
    {
        // find the player's HLStats ID
        $query = "SELECT
                    h.playerId
                  FROM
                    `soe-hlstats`.`hlstats_PlayerUniqueIds` AS h
                  WHERE h.uniqueId = :trg_sid";
        $hlstats_id = getPDOParametrizedQueryScalarValue($pdo, $query, array(":trg_sid" => preg_replace("/STEAM_\d:/", "", $_GET["trg_sid"])), __FILE__, __LINE__);

        if ($hlstats_id != False)
        {
            $query = "INSERT INTO `ezpz-report-g`.report_players(sid, ip, nick, hlstats_id) VALUES (:trg_sid, :trg_ip, :trg_nick, :hlstats_id)";
            $parameters = array(":trg_sid" => $_GET["trg_sid"], ":trg_ip" => $_GET["trg_ip"], ":trg_nick" => $_GET["trg_nick"], ":hlstats_id" => intval($hlstats_id));
            PDOExecParametrizedQuery($pdo, $query, $parameters, __FILE__, __LINE__);
        }
        else
        {
            $query = "INSERT INTO `ezpz-report-g`.report_players(sid, ip, nick) VALUES (:trg_sid, :trg_ip, :trg_nick)";
            $parameters = array(":trg_sid" => $_GET["trg_sid"], ":trg_ip" => $_GET["trg_ip"], ":trg_nick" => $_GET["trg_nick"]);
            PDOExecParametrizedQuery($pdo, $query, $parameters, __FILE__, __LINE__);
        }

        $target_id = $pdo->lastInsertId();
    }
    else
    {
        $target_id = getPDOParametrizedQueryScalarValue($pdo, "SELECT id FROM `ezpz-report-g`.report_players WHERE sid = :trg_sid", array(":trg_sid" => $_GET["trg_sid"]), __FILE__, __LINE__);
        $parameters = array(":trg_ip" => $_GET["trg_ip"], ":trg_nick" => $_GET["trg_nick"], ":target_id" => intval($target_id));
        PDOExecParametrizedQuery($pdo, "UPDATE `ezpz-report-g`.report_players SET ip = :trg_ip, nick = :trg_nick WHERE id = :target_id", $parameters, __FILE__, __LINE__);
    }

    // INSERT map to report_map if map doesn't exist there
    if (PDOcheckEmptyQuery($pdo, "SELECT id FROM `ezpz-report-g`.report_map WHERE map = :map", __FILE__, __LINE__, array(":map" => $_GET["map"])))
    {
        PDOExecParametrizedQuery($pdo, "INSERT INTO `ezpz-report-g`.report_map(map) VALUES (:map)", array(":map" => $_GET["map"]), __FILE__, __LINE__);
        $map_id = $pdo->lastInsertId();
    }
    else
    {
        $map_id = getPDOParametrizedQueryScalarValue($pdo, "SELECT id FROM `ezpz-report-g`.report_map WHERE map = :map", array(":map" => $_GET["map"]), __FILE__, __LINE__);
    }

    // check if target was reported today
    // if true, use the same admin id for this report
    // if false, assign new admin id
    $query = "SELECT admin_id FROM `ezpz-report-g`.report_report AS r JOIN `ezpz-report-g`.report_players AS p ON p.id = r.target_id
              WHERE DATE(r.time_create) = :time_create AND p.sid LIKE :sid";
    $parameters = array(":time_create" => date("Y-m-d"), ":sid" => "%" . $_GET["trg_sid"]);

    $result = getPDOParametrizedQueryResult($pdo, $query, $parameters, __FILE__, __LINE__);

    // find suitable admin for this report (his id)
    if (count($result) == 0)
    {
        // first find admins with lowest number of finished reports
        $admins = getAdminsReports();
        
        $min = $admins[0]["count_report_finished"];
        $admins_finished = array($admins[0]);
        
        for ($i = 1; $i < count($admins); $i++)
        {
            if ($admins[$i]["count_report_finished"] < $min)
            {
                $min = $admins[$i]["count_report_finished"];
                $admins_finished = array($admins[$i]);
            }
            elseif ($admins[$i]["count_report_finished"] == $min)
            {
                $admins_finished[] = $admins[$i];
            }
        }
        
        if (count($admins_finished) == 1)
        {
            $admin_id = $admins_finished[0]["admin_id"];
        }
        else
        {
            $min = $admins_finished[0]["count_report_new"];
            $admins_new = array($admins_finished[0]);

            for ($i = 1; $i < count($admins_finished); $i++)
            {
                if ($admins_finished[$i]["count_report_new"] < $min)
                {
                    $min = $admins_finished[$i]["count_report_new"];
                    $admins_new[$i-1] = $admins_finished[$i];
                }
                elseif ($admins_finished[$i]["count_report_new"] == $min)
                {
                    $admins_new[] = $admins_finished[$i];
                }
            }

            // choose one admin_id randomly
            $admin_id = array_rand($admins_new);
            $admin_id = $admins_new[$admin_id]["admin_id"];
        }
    }
    else
    {
        $admin_id = $result[0]["admin_id"];
    }

    if (!isset($_GET["test"]))
    {
        $query = "INSERT INTO
                `ezpz-report-g`.report_report(`server_id`, `reporter_id`, `target_id`, `admin_id`, `status_id`, `map_id`, `demo_file`"
            . (isset($_GET["round"]) ? ", `round`" : "")
            . (isset($_GET["reason_custom"]) ? ", `reason_custom`) " : ") ") . "
              VALUES
                (:server_id, :reporter_id, :target_id, :admin_id, 1, :map_id, :demo_file"
            . (isset($_GET["round"]) ? ", :round" : " ")
            . (isset($_GET["reason_custom"]) ? ", :reason_custom)" : ")");

        $parameters = array(":server_id" => intval($_GET["server_id"]), ":reporter_id" => intval($reporter_id), ":target_id" => intval($target_id),
            ":admin_id" => intval($admin_id), ":map_id" => intval($map_id), ":demo_file" => $_GET["demo_file"]);

        if (isset($_GET["round"])) {
            $parameters[":round"] = $_GET["round"];
        }

        if (isset($_GET["reason_custom"])) {
            $parameters[":reason_custom"] = $_GET["reason_custom"];
        }

        PDOExecParametrizedQuery($pdo, $query, $parameters, __FILE__, __LINE__);

        $report_id = $pdo->lastInsertId();

        if (isset($_GET["reason_ids"])) {
            foreach ($_GET["reason_ids"] as $reason_id) {
                $query = "INSERT INTO `ezpz-report-g`.report_report_reason(report_id, reason_id) VALUES (:report_id, :reason_id)";
                $parameters = array(":report_id" => $report_id, ":reason_id" => $reason_id);
                PDOExecParametrizedQuery($pdo, $query, $parameters, __FILE__, __LINE__);
            }
        }

        header('Content-Type: application/json');

        echo json_encode(array(
            "success" => True,
            "data" => sprintf($translation["texts"]["report_add_success"], $max_allowed_report_count - $report_count_day - 1, $report_id, $report_id)
        ));


        //send email to admin
        $query = "SELECT a.language, a.email FROM `soe-csgo`.`sb_admins` AS a WHERE id = :admin_id";
        $result = getPDOParametrizedQueryResult($pdo, $query, array(":admin_id" => $admin_id), __FILE__, __LINE__);

        foreach ($result as $row) {
            $translation = getEmailTranslation($row["language"]);
            $email = $row["email"];
        }

        $query = "SELECT r.id, r.time_create, s.status_" . $translation["db"]["suffix"] . " AS status FROM `ezpz-report-g`.`report_report` AS r
              JOIN `ezpz-report-g`.`report_status` AS s ON s.id = r.status_id
              WHERE r.admin_id = :admin_id AND s.id IN (1, 2)";
        $result = getPDOParametrizedQueryResult($pdo, $query, array(":admin_id" => $admin_id), __FILE__, __LINE__);

        $rs_url = "http://ezpz.cz/page/report-system?report_ids=%d";
        $message_other = "";

        if ($result) {
            $message_other = "<div style='font-size: large;'><b>" . $translation["message_other"] . ":</b></div><br /><br />";

            foreach ($result as $row) {
                $message_other .= sprintf("<a href='$rs_url'><b>" . $row["status"] . "</b> | <i>" . $row["time_create"] . "</i></a><br />", $row["id"]);
            }
        }

        $message = "<div style='font-size: large;'><b>" . $translation["message_new"] . ": </b>" . sprintf("<a href='$rs_url'>$rs_url</a>", $report_id, $report_id) . "</div><br /> <br />" .
            $message_other;

        $headers = "From: admin@ezpz.cz\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=cp1250\r\n";

        mail($email, $translation["subject"], $message, $headers);
    }
    else
    {
        print_r($admins);
        print_r($admins_finished);
        print_r($admins_new);
        echo "selected admin_id: $admin_id";
    }
}
catch(Exception $ex)
{
    echo json_encode(array(
        "success" => False,
        "data" => $ex->getMessage()));
}