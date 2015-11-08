<?php
if (!isset($_GET["lang"]))
{
    die("lang is not set!");
}
else
{
    include(dirname(__FILE__) . "/config/translation_report_motd.php");
    $translation = getReportTranslation($_GET["lang"]);
}

include_once("/data/web/virtuals/93680/virtual/www/domains/ezpz.cz/ext/phpbb/pages/styles/pbtech/template/config/config.php");
include_once("/data/web/virtuals/93680/virtual/www/domains/ezpz.cz/ext/phpbb/pages/styles/pbtech/template/scripts-generic/getPDO.php");
include_once("/data/web/virtuals/93680/virtual/www/domains/ezpz.cz/ext/phpbb/pages/styles/pbtech/template/scripts-generic/PDOQuery.php");
include_once(dirname(__FILE__) . "/config/config_report.php");

$pdo = getPDOConnection();
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html" charset="utf-8" />
    <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
    <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/base-min.css">
    <script src="jquery-1.11.3.min.js"></script>
    <script src="getJsonFromUrl.js"></script>

    <script>
        var success;

        $(document).ready(function()  {
            var params = getJsonFromUrl();

            function getCheckedReasons()
            {
                var reason_ids = [];
                $('.check-reason').each(function() {
                    if ($(this).is(":checked"))
                    {
                        reason_ids.push($(this).attr("reason_id"));
                    }
                });

                return reason_ids;
            }

            function loadFields()
            {
                $("#input-server").val(params["server_name"]);

                $("#input-rep_nick").val(params["rep_nick"]);
                $("#input-rep_sid").val(params["rep_sid"]);
                $("#input-rep_ip").val(params["rep_ip"]);

                $("#input-trg_nick").val(params["trg_nick"]);
                $("#input-trg_sid").val(params["trg_sid"]);
                $("#input-trg_ip").val(params["trg_ip"]);
            }

            $("#button-send").click(function() {
                var reason_ids = getCheckedReasons();
                var reason_custom = $("#input-reason_custom").val();

                if (reason_ids.length == 0 && reason_custom == "")
                {
                    $("#div-message").css("color", "red");
                    $("#div-message").html("<?php echo $translation["texts"]["warning_send"] ?>");
                    return;
                }

                var url = "http://motd-report.ezpz.cz/add_report.php?" +
                    "asdf=v0E7mux9aFRYWNAN" +
                    "&lang=" + encodeURIComponent(params["lang"]) +
                    "&server_id=" + encodeURIComponent(params["server_id"]) +
                    "&rep_nick=" + encodeURIComponent(params["rep_nick"]) +
                    "&rep_sid=" + encodeURIComponent(params["rep_sid"]) +
                    "&rep_ip=" + encodeURIComponent(params["rep_ip"]) +
                    "&trg_nick=" + encodeURIComponent(params["trg_nick"]) +
                    "&trg_sid=" + encodeURIComponent(params["trg_sid"]) +
                    "&trg_ip=" + encodeURIComponent(params["trg_ip"]) +
                    "&demo_file=" + encodeURIComponent(params["demo_file"]) +
                    "&map=" + encodeURIComponent(params["map"]) +
                    ($("#select-round option:selected").val() == "current" ? "&round=" + encodeURIComponent(params["round"]) : "") +
                    (reason_custom != "" ? "&reason_custom=" + encodeURIComponent(reason_custom) : "") +
                    (reason_ids.length != 0 ? "&reason_ids[]=" + reason_ids.join("&reason_ids[]=") : "");

                $.ajax({
                    url: url,
                    success: function(result) {
                        $("#div-message").empty();

                        if (result.success)
                        {
                            $("#div-message").css("color", "green");
                            $("#div-message").html(result.data);

                            /*setTimeout(function(){
                                window.close();
                            }, 3000);*/
                        }
                        else
                        {
                            $("#div-message").css("color", "red");
                            $("#div-message").html(result.data);
                        }
                    }
                });
            });

            loadFields();
        });
    </script>

    <title>EzPz.cz | Report System</title>
    
    <style>
        body {
            font-family: 'Verdana', 'Arial';
            font-size: 14px;
            font-weight: bold;
            text-align: left;
            background-color: #F5F5F5;
        }
 
        table {            
            margin: 0 auto;
            border: 0;
            font-size: 14px;
        }
 
        .wrapper {
            width: 600px;
            margin: 0 auto;
        }
       
        .check-label {
            font-size: 15px;
        }
 
        input[type="checkbox"] {
            margin-right: 20px;
        }
       
        button {
            font-size: 20px;
            font-weight: bold;
            float: none;
            margin: 10px auto 0 auto;
        }
       
        .input-select {
             margin-top: 10px;
        }
 
        .div-check {
          margin-top: 0.1em;
        }
       
        .pure-button {
          display: block;
        }
        
        .button-success {
          color: white;
          border-radius: 4px;
          text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
          background: rgb(28, 184, 65); /* this is a green */
        }

        #button-close {
            color: red;
        }

    </style>

</head>
<body>
    <div class="wrapper">
        <h2 style="text-align: center; color: red;"><?php echo $translation["texts"]["warning"] ?></h2>

        <hr />

        <div id="div-controls" class="pure-form pure-form-aligned">
            <table>
                <tr>
                    <td><label for="input-server"><?php echo $translation["controls"]["server"] ?>:</label></td>
                    <td colspan="2">
                        <input type="text" id="input-server" name="server" size="50" readonly="readonly" />
                    </td>
                </tr>
                <tr>
                    <td><label for="input-rep_nick"><?php echo $translation["controls"]["rep_nick"] ?>:</label></td>
                    <td colspan="2">
                        <input type="text" id="input-rep_nick" name="name" size="50" readonly="readonly" />
                    </td>
                </tr>
                <tr>
                    <td><label for="input-rep_sid"><?php echo $translation["controls"]["rep_sid"] ?>:</label></td>
                    <td colspan="2">
                        <input type="text" id="input-rep_sid" name="sid" size="50" readonly="readonly" />
                    </td>
                </tr>
                <tr>
                    <td><label for="input-rep_ip"><?php echo $translation["controls"]["rep_ip"] ?>:</label></td>
                    <td colspan="2">
                        <input type="text" id="input-rep_ip" name="ip" size="50" readonly="readonly" />
                    </td>
                </tr>
                <tr>
                    <td><label for="input-trg_nick"><?php echo $translation["controls"]["trg_nick"] ?>:</label></td>
                    <td colspan="2">
                        <input type="text" id="input-trg_nick" name="trg_name" size="50" readonly="readonly" />
                    </td>
                </tr>
                <tr>
                    <td><label for="input-trg_sid"><?php echo $translation["controls"]["trg_sid"] ?>:</label></td>
                    <td colspan="2">
                        <input type="text" id="input-trg_sid" name="trg_sid" size="50" readonly="readonly" />
                    </td>
                </tr>
                <tr>
                    <td><label for="input-trg_ip"><?php echo $translation["controls"]["trg_ip"] ?>:</label></td>
                    <td colspan="2">
                        <input type="text" id="input-trg_ip" name="trg_ip" size="50" readonly="readonly" />
                    </td>
                </tr>
                <tr style="display: none;">
                    <td><label for="input-lang">Language: </label></td>
                    <td colspan="2">
                        <input type="text" id="input-lang" name="lang" size="50" readonly="readonly" />
                    </td>
                </tr>
            </table>

            <fieldset style="margin-top: 1em">
                <legend style="font-size: 1.5em;"><?php echo $translation["controls"]["reason"] ?></legend>
                    <?php
                    $query = "SELECT id, reason_" . $translation["db"]["suffix"] . " FROM `ezpz-report-g`.report_reason";
                    $result = getPDOQueryResult($pdo, $query, __FILE__, __LINE__);

                    foreach ($result as $row)
                    {
                        $row_id = $row["id"];
                        echo "<div class='div-check'>";
                        echo "<label for='check-reason-$row_id' class='check-label'>" . $row["reason_" . $translation["db"]["suffix"]] . "</label>";
                        echo "<input type='checkbox' class='check-reason' size='30' id='check-reason-$row_id' reason_id='$row_id' />";
                        echo "</div>";
                    }
                    ?>

                    <div style="margin-top: 5px;">
                        <label for="input-reason_custom" style="margin-right: 10px; font-size: 15px;"><?php echo $translation["controls"]["reason_custom"] ?>:</label>
                        <textarea id="input-reason_custom" cols="50" name="other" ></textarea>
                    </div>

                <label for="select-round" class="check-label"><?php echo $translation["controls"]["round"] ?>:</label>
                <select id="select-round" class="input-select">
                    <option value="before"><?php echo $translation["controls"]["round_before"] ?></option>
                    <option value="current"><?php echo $translation["controls"]["round_current"] . " (" . $_GET["round"] . ")" ?></option>
                </select>

                <br />

                <button  id="button-send" class="button-success pure-button"><?php echo $translation["buttons"]["send"] ?></button>
            </fieldset>

            <div id="div-message"></div>

            <button  id="button-close" class="button-success pure-button" onclick="window.close();"><?php echo $translation["buttons"]["close"] ?></button>
        </div>

        <hr />

        <h2 style="text-align: center; color: red;"><?php echo $translation["texts"]["warning"] ?></h2>
    </div>
</body>
</html>