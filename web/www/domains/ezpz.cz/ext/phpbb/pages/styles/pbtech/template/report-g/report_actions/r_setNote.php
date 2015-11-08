<?php

if (!isset($_POST["report_id"]))
    die("report_id is not set!");

if (!isset($_POST["note"]))
    die("note is not set!");

include_once(dirname(__FILE__)."/../../scripts-generic/getPDO.php");
include_once(dirname(__FILE__)."/../../scripts-generic/PDOQuery.php");
include_once(dirname(__FILE__)."/../../scripts-generic/checkAdmin.php");

session_start();

$report_ids = $_POST["report_ids"];
$note = $_POST["note"];

?>

if ((checkAdminForReportByReportId($report_id) || checkMainAdmin()))
{
$pdo = getPDOConnection();

    $query = "UPDATE `ezpz-report-g`.`report_report` SET note = :note WHERE id = :id";

    header('Content-Type: application/json');

    if (PDOExecParametrizedQuery($pdo, $query, array(":note" => $note, ":id" => $report_id), __FILE__, __LINE__))
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

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html" charset="utf-8" />
    <script src="../../scripts-generic/getJsonFromUrl.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="../../scripts-generic/jquery/jquery-1.11.2.min.js"></script>

    <script type="text/javascript">
    $(document).ready(function ()
    {
        var args = getJsonFromUrl();

        $("#text-note").val(<?php $note ?>);

        $('body').on('click', '#button-submit', function () {
            if ($("#text-note").val() == "")
            {
                if (!confirm("Opravdu chceš smazat poznámku?")) return;
            }

            $.ajax(
                {
                    type: "POST",
                    url: "http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/report_actions/r_setNote.php",
                    data:
                    {
                        "report_ids": $("#input-nickname").val(),
                        "note": $("#input-steamid").val()
                    },
                    success: function(response)
                    {
                        var responsejson = JSON.parse(response);

                        if (responsejson.success)
                        {
                            parent.banSuccess["success"] = true;
                            parent.banSuccess["ban_id"] = responsejson.ban_id;
                            parent.$.fancybox.close();
                        }
                        else
                        {
                            alert("Došlo k chybě při přidávání banu. Zkus to později nebo kontaktuj technika.");
                        }
                    }
                });
            });
        });
    </script>

    <style>
h1 {
    color: #074e79;
    background: none;
    border-bottom: 1px solid #3380C8;
            padding-left: 1em;
            text-align: center;
        }
        table{
    margin: 0 auto !important;
        }
        td {
    padding: 5px;
        }
    </style>
</head>
<body>
<h1>Změnit poznámku</h1>

<label for="text-note">Poznámka: </label>
<textarea id="text-note" class="form-control"></textarea>

<button class="btn btn-primary btn-lg btn-block" id="button-submit">Přidat ban!</button>


</body>
</html>