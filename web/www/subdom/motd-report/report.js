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

        $("#input-rep-name").val(params["rep_name"]);
        $("#input-rep-sid").val(params["rep_sid"]);
        $("#input-rep-ip").val(params["rep_ip"]);

        $("#input-trg-name").val(params["trg_name"]);
        $("#input-trg-sid").val(params["trg_sid"]);
        $("#input-trg-ip").val(params["trg_ip"]);
    }

    $("#button-send").click(function() {
        var reason_ids = getCheckedReasons();
        var reason_custom = $("#input-reason_custom").val();

        if (reason_ids.length == 0 && reason_custom == "")
        {
            alert("<?php echo $translation["texts"]["warning_send"] ?>");
            return;
        }

        var url = "add_report.php?" +
            "lang=" + encodeURIComponent(params["lang"]) +
            "&server_id=" + encodeURIComponent(params["server_id"]) +
            "&rep_name=" + encodeURIComponent(params["rep_name"]) +
            "&rep_sid=" + encodeURIComponent(params["rep_sid"]) +
            "&rep_ip=" + encodeURIComponent(params["rep_ip"]) +
            "&trg_name=" + encodeURIComponent(params["trg_name"]) +
            "&trg_sid=" + encodeURIComponent(params["trg_sid"]) +
            "&trg_ip=" + encodeURIComponent(params["trg_ip"]) +
            "&demofile=" + encodeURIComponent(params["demofile"]) +
            ($("#select-round option:selected").val() == "current" ? "&round=" + encodeURIComponent(params["round"]) : "") +
            (reason_custom != "" ? "&reason_custom=" + encodeURIComponent(reason_custom) : "");

        console.log(url);
    });

    loadFields();
});