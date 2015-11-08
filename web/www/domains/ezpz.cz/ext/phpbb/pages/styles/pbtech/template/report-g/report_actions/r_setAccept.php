<?php

if (!isset($_POST["report_id"]))
    die("report_id is not set!");

include_once(dirname(__FILE__)."/../../scripts-generic/getPDO.php");
include_once(dirname(__FILE__)."/../../scripts-generic/PDOQuery.php");
include_once(dirname(__FILE__)."/../../scripts-generic/checkAdmin.php");

session_start();

$report_id = intval($_POST["report_id"]);

if ((checkAdminForReportByReportId($report_id) || checkMainAdmin()))
{
    $pdo = getPDOConnection();

    if(isset($_POST["ban_id"]))
    {
        $query = "UPDATE `ezpz-report-g`.`report_report` SET status_id = 4, time_finish = TIMESTAMP(NOW()), sourcebans_link = :link WHERE id = :report_id";
        $parameters = array(":link" => "http://sourcebans.ezpz.cz/#" . $_POST["ban_id"], ":report_id" => $report_id);
        $result = PDOExecParametrizedQuery($pdo, $query, $parameters, __FILE__, __LINE__);
    }
    else
    {
        $query = "UPDATE `ezpz-report-g`.`report_report` SET status_id = 3, time_finish = TIMESTAMP(NOW()) WHERE id = :report_id";
        $parameters = array(":report_id" => $report_id);
        $result = PDOExecParametrizedQuery($pdo, $query, $parameters, __FILE__, __LINE__);
    }

    header('Content-Type: application/json');

    if ($result)
    {
        echo json_encode(array(
            'success' => true,
        ));
    }
    else
    {
        echo json_encode(array(
            'success' => false,
        ));
    }
}