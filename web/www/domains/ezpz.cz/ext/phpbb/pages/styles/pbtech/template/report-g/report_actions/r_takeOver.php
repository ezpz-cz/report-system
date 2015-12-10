<?php

if (!isset($_POST["report_id"]))
    die("report_id is not set!");

include_once(dirname(__FILE__)."/../../scripts-generic/getPDO.php");
include_once(dirname(__FILE__)."/../../scripts-generic/PDOQuery.php");
include_once(dirname(__FILE__)."/../../scripts-generic/checkAdmin.php");

session_start();
header('Content-Type: application/json');

if (checkAdminBySession())
{
    $pdo = getPDOConnection();
    $query = "UPDATE `ezpz-report-g`.`report_report` SET admin_id = :admin_id WHERE id = :id";
    $parameters = array(":admin_id" => $_SESSION['ezpz_sb_admin_id'], ":id" => $_POST["report_id"]);
    $result = PDOExecParametrizedQuery($pdo, $query, $parameters, __FILE__, __LINE__);

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