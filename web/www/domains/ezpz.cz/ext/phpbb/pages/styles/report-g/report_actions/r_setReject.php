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

    $query = "UPDATE `ezpz-report-g`.`report_report` SET status_id = 5, time_finish = TIMESTAMP(NOW()) WHERE id = :id";

    header('Content-Type: application/json');

    if (PDOExecParametrizedQuery($pdo, $query, array("id" => $report_id), __FILE__, __LINE__))
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