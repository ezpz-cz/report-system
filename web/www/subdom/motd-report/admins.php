<?php

/*
 * returns assoc array of admins (id => name, count_new_reports, count_finished_reports)
 */
function getAdminsReports()
{
    try
    {
        include_once("/data/web/virtuals/93680/virtual/www/domains/ezpz.cz/ext/phpbb/pages/styles/pbtech/template/scripts-generic/getPDO.php");
        include_once("/data/web/virtuals/93680/virtual/www/domains/ezpz.cz/ext/phpbb/pages/styles/pbtech/template/scripts-generic/PDOQuery.php");

        // get admin ids and names
        $pdo = getPDOConnection();
        $result = getPDOQueryResult($pdo, "SELECT id, name FROM `soe-csgo`.sb_admins WHERE active = 1", __FILE__, __LINE__);

        $admins = array();

        foreach($result as $row)
        {
            $admins[$row["id"]] = array("name" => $row["name"]);
        }

        // get new and finished report counts
        /*$query = "SELECT crn.active, crn.id, count_report_new, count_report_finished
                    FROM
                        (SELECT a.active, a.id, a.name, COUNT(*) as count_report_new
                        FROM `soe-csgo`.sb_admins AS a
                        JOIN `ezpz-report-g`.report_report AS r ON r.admin_id = a.id
                        WHERE r.status_id = 1 OR r.status_id = 2
                        GROUP BY a.id) AS crn
                    LEFT JOIN
                        (SELECT a.active, a.id, COUNT(*) as count_report_finished
                        FROM `soe-csgo`.sb_admins AS a
                        JOIN `ezpz-report-g`.report_report AS r ON r.admin_id = a.id
                        WHERE r.status_id = 3 OR r.status_id = 4 OR r.status_id = 5
                        GROUP BY a.id) AS crf ON crf.id = crn.id
                WHERE crn.active = 1
        ";*/

        $query = "SELECT a.active, a.id, a.name, COUNT(*) as count_report_new
                    FROM `soe-csgo`.sb_admins AS a
                    JOIN `ezpz-report-g`.report_report AS r ON r.admin_id = a.id
                    WHERE (r.status_id = 1 OR r.status_id = 2) AND a.active = 1
                    GROUP BY a.id";

        $result = getPDOQueryResult($pdo, $query, __FILE__, __LINE__);

        foreach($result as $row)
        {
            $admins[$row["id"]]["count_report_new"] = (!is_null($row["count_report_new"]) ? $row["count_report_new"] : 0);
        }

        $query = "SELECT a.active, a.id, COUNT(*) as count_report_finished
                        FROM `soe-csgo`.sb_admins AS a
                        JOIN `ezpz-report-g`.report_report AS r ON r.admin_id = a.id
                        WHERE (r.status_id = 3 OR r.status_id = 4 OR r.status_id = 5) AND a.active = 1
                        GROUP BY a.id";

        $result = getPDOQueryResult($pdo, $query, __FILE__, __LINE__);

        foreach($result as $row)
        {
            $admins[$row["id"]]["count_report_finished"] = (!is_null($row["count_report_finished"]) ? $row["count_report_finished"] : 0);
        }

        foreach($admins as $key => $value)
        {
            if (!array_key_exists("count_report_new", $value))
            {
                $admins[$key]["count_report_new"] = 0;
            }
            if (!array_key_exists("count_report_finished", $value))
            {
                $admins[$key]["count_report_finished"] = 0;
            }
        }

        return $admins;
    }
    catch(Exception $ex)
    {
        echo $ex->getMessage();
    }
}