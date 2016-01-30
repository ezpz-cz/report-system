<style>
    input[type="text"], input[type="number"] {
        height: 20px;
    }
    #div-controls{
        margin-left: 10px;
        margin-bottom: 10px;
    }
    div.form-group{
        margin-bottom: 10px !important;
    }
    #show{
        float: right;
        margin-right: 1em;
        font-size: medium;
    }
    td[colspan="4"]{
        padding: 0;
    }
</style>
<script>

$(document).ready(function(){
    $("#show").click(function(){
        $("#second_filter").toggle('slow', function() {
            if ($(this).is(':visible')) {
                $("#show").text('<?php echo $translation["page_filters"]["close"] ?>');
            } else {
                $("#show").text('<?php echo $translation["page_filters"]["open"] ?>');
            }
        });
    });
});

</script>

<div id="div-filters">
    <form class="form-inline">
        <legend style="margin-top: 0.1em;"><?php echo $translation["page_filters"]["filters"] ?></legend>
        <fieldset id="main_filter">
            <div class="form-group">
                <label for="select-admin"><?php echo $translation["page_filters"]["admin"] ?></label>
                <?php
                // admin
                $pdo = getPDOConnection();
                $query = "SELECT id, name FROM `soe-csgo`.sb_admins";
                $result = getPDOQueryResult($pdo, $query, __FILE__, __LINE__);

                echo "<select class='form-control' id='select-admin'>";
                echo "<option admin_id=''>-</option>";

                foreach ($result as $row)
                {
                    echo "<option admin_id='" . $row["id"] . "'>" . $row["name"] . "</option>";
                }

                echo "</select>";
                ?>

            </div>
            <div class="form-group">
                <label for="select-status"><?php echo $translation["page_filters"]["status"] ?></label>
                <?php
                // report status

                $query = "SELECT id, status_" . $translation["db"]["suffix"] . " FROM `ezpz-report-g`.report_status";
                $result = getPDOQueryResult($pdo, $query, __FILE__, __LINE__);

                echo "<select class='form-control' id='select-status' multiple='multiple'>";
                //echo "<select class='form-control' id='select-status'>";
                echo "<option status_id='-1'>" . $translation["page_filters"]["all"] . "</option>";

                foreach ($result as $row)
                {
                    echo "<option status_id='" . $row["id"] . "'>";
                    echo $row["status_" . $translation["db"]["suffix"]];
                    echo "</option>";
                }
                echo "</select>";
                ?>
            </div>

            <?php
            if ($isAdmin)
            {
                echo "<br />";
                echo "<label for='input-check-my_reports'>" . $translation["page_filters"]["checkbox_my_reports"] . "</label>";
                echo "<input id='input-check-my_reports' type='checkbox' style='margin: 0 0 4px 0;' admin_id='" . $_SESSION['ezpz_sb_admin_id'] . "' />";
                echo "<br />";
            }
            ?>
            <!-- Datum od do-->
            <div class="form-group">
                <label for="input-date-from"><?php echo $translation["page_filters"]["date_from"] ?></label>
                <input class="form-control" id="input-date-from" type="text"/>

                <label for="input-date-to"><?php echo $translation["page_filters"]["date_to"] ?></label>
                <input class="form-control" id="input-date-to" type="text"/>

                <!--<label for="input-check-today"><?php echo $translation["page_filters"]["checkbox_today"] ?></label>
                <input class="form-control" id="input-check-today" type="checkbox"/>-->
                <button class="btn btn-default" id="button-today"><?php echo $translation["page_filters"]["button_today"] ?></button>
            </div>
        </fieldset>
        <fieldset id="second_filter" style="display: none;">
            <!-- Dovod          -->
            <div class="form-group">
                <label for="select-reason"><?php echo $translation["page_filters"]["reason"] ?></label>
                <?php

                $query = "SELECT id, reason_" . $translation["db"]["suffix"] . " FROM `ezpz-report-g`.report_reason";
                $result = getPDOQueryResult($pdo, $query, __FILE__, __LINE__);

                echo "<select class=\"form-control\" id='select-reason'>";
                echo "<option reason_id=''>-</option>";

                foreach ($result as $row)
                {
                    echo "<option reason_id='" . $row["id"] . "'>";
                    echo $row["reason_" . $translation["db"]["suffix"]];
                    echo "</option>";
                }

                echo "</select>";
                ?>
            </div>
            <!--  target + reporter  -->
            <br/>

            <div class="form-group">
                <label for="input-text-target"><?php echo $translation["page_filters"]["target"] ?></label>
                <input class="form-control" id="input-text-target" type="text" value=""
                       placeholder="<?php echo $translation["page_filters"]["nick"] . "/" . $translation["page_filters"]["steamid"] . "/" . $translation["page_filters"]["ip"] ?>"/>
            </div>
            <div class="form-group">
                <label for="input-text-reporter"><?php echo $translation["page_filters"]["reporter"] ?></label>
                <input class="form-control" id="input-text-reporter" type="text" value=""
                       placeholder="<?php echo $translation["page_filters"]["nick"] . "/" . $translation["page_filters"]["steamid"] . "/" . $translation["page_filters"]["ip"] ?>"/>
            </div>

            <!--   server         -->
            <br/>

            <div class="form-group">
                <label for="select-server"><?php echo $translation["page_filters"]["server"] ?></label>
                <select class="form-control" id="select-server" name="select-server">
                    <!--<option class="option-server" serverid=''><?php echo $translation["page_filters"]["server_all"] ?></option>-->
                    <option class="option-server" serverid=''>-</option>
                    <?php
                    foreach (GetServers() as $id => $row)
                    {
                        echo '<option class="option-server" serverid=' . $row["server_id"] . '>' . $row["name"] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="input-report_ids"> <?php echo $translation["page_filters"]["report_ids"] ?></label>
                <input class="form-control" id="input-text-report_ids" type="text" pattern="^([0-9]++).+" />
            </div>
            <div class="form-group">
                <label for="input-text-reason_custom"><?php echo $translation["page_filters"]["reason_custom"] ?></label>
                <input class="form-control" id="input-text-reason_custom" type="text" />
            </div>

        </fieldset>

        <a id="show"><?php echo $translation["page_filters"]["open"] ?></a>

    </form>

    <div id="div-controls">
        <button class="btn btn-default" id="button-search"><?php echo $translation["page_filters"]["button_search"] ?></button>

        <button class="btn btn-default" id="button-reset"><?php echo $translation["page_filters"]["button_reset_filter"] ?></button>

<!--        <div style="padding-left: 5px; display: inline; border-left: thick solid #000000;">

            <button class="btn btn-default" id="button-today"><php //echo $translation["page_filters"]["button_today"] ?></button>-->

            <!--<php
                if ($isAdmin)
                {
                    echo "<button class=\"btn btn-default\" id=\"button-my-today\" admin_id='" . $_SESSION['ezpz_sb_admin_id'] . "'>" . $translation["page_filters"]["button_my_today_reports"] . "</button>";
                }
            ?>-->

        </div>
        <br />

        <label for="input-url"><?php echo $translation["page_filters"]["filter_url"] ?></label>
        <input id="input-url" type="text" readonly="readonly" style='width:90%;' onmouseover="this.select()" />

    </div>
</div>