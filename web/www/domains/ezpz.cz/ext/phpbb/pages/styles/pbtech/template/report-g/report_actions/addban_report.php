<?php

if (!isset($_POST["steamid"]))
    die("steamid is not set!");

if (!isset($_POST["ip"]))
    die("ip is not set!");

if (!isset($_POST["nickname"]))
    die("nickname is not set!");

if (!isset($_POST["reason"]))
    die("reason is not set!");

if (!isset($_POST["length"]))
    die("length is not set!");

if (!isset($_POST["report_ids"]))
    die("report_ids is not set!");

include_once(dirname(__FILE__)."/../../scripts-generic/getPDO.php");
include_once(dirname(__FILE__)."/../../scripts-generic/PDOQuery.php");
include_once(dirname(__FILE__)."/../../scripts-generic/checkAdmin.php");

session_start();

if (!checkAdminBySession())
{
    die("You are not admin!");
}

$pdo = getPDOConnection();

$query = "INSERT INTO `soe-csgo`.`sb_bans` (
                    `type`,
                    `steam`,
                    `ip`,
                    `name`,
                    `reason`,
                    `length`,
                    `admin_id`,
                    `admin_ip`,
                    `create_time`)
                VALUES (
                    '0',
                    :steamid,
                    :ip,
                    :nickname,
                    :reason,
                    :length,
                    :admin_id,
                    '88.86.107.243',
                    UNIX_TIMESTAMP(NOW()))";

//echo $query;

$reason = $_POST['reason'] . " | http://ezpz.cz/page/report-system?report_ids=" . $_POST["report_ids"];

$parameters = array(":steamid" => $_POST['steamid'], ":ip" => $_POST['ip'],
                    ":nickname" => $_POST['nickname'], ":reason" => $reason,
                    ":length" => intval($_POST['length']), ":admin_id" => intval($_SESSION['ezpz_sb_admin_id']));

// http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/report_actions/addban_report.php?steamid=1:0012646&ip=88.45.21.47&nickname=test&reason=test&length=50

if (PDOExecParametrizedQuery($pdo, $query, $parameters, __FILE__, __LINE__))
{
    echo json_encode(array(
        'success' => true,
        'ban_id' => $pdo->lastInsertId()
    ));
}
else
{
    echo json_encode(array(
        'success' => false,
    ));
}

?>