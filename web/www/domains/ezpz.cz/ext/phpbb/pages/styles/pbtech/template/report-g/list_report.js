var table;
var lang;
var banSuccess = {"success": false, "ban_id": -1};
var args = getJsonFromUrl();
var translation;

$(document).ready(function ()
{
    function getTranslation(lang)
    {
        if (lang == "cze")
        {
            return {
                "alerts": {
                    "no_report": "Nemáš zaškrtnuté žádné reporty!",
                    "progress": "Opravdu chceš označit hotový report jako 'řeší se'?"
                },
                "status": {
                    "progress": "řeší se",
                    "rejected": "hotové – zamítnuto",
                    "accept_ban": "hotové – přijato, ban",
                    "accept": "hotové – přijato"
                },
                "bubble": {
                    "finished": "Hotovo"
                }
            }
        }
        else
        {
            return {
                "alerts": {
                    "no_report": "You don't have any checked reports!",
                    "progress": "Do you really want to set finished report as 'in progress'?"
                },
                "status": {
                    "progress": "in progress",
                    "rejected": "finished – rejected",
                    "accept_ban": "finished – accepted, banned",
                    "accept": "finished – accepted"
                },
                "bubble": {
                    "finished": "Finished"
                }
            }
        }
    }

    lang = $("langinfo").attr("value");
    translation = getTranslation(lang);

    function loadTable(url)
    {
        console.log("Loading table...");
        //console.log(url);

        //$("#div-table").empty();
        $("#div-table").html("<div id='div-loader'><img src='http://ezpz.cz/ext/phpbb/pages/styles/pbtech/theme/gears.gif' alt='Loading...' style='display: block; margin-left: auto; margin-right: auto;' /></div>");

        /*$("#div-table").slideUp(function() {
         $("#div-table").empty();
         $("#div-loader").slideDown();
         });*/

        $.ajax({
            url: url,
            success: function(result) {
                $("#div-table").html(result.data);

                if (result.success)
                {
                    table = $('#table-reports-group').DataTable({
                        "order": [[ 0, 'desc' ]],
                        //"scrollX": true
                    });

                    //$('#table-reports-group thead').remove();
                }

                //$("#div-loader").slideUp(function() {
                //    $("#div-table").slideDown();
                //});
            }
        });

        showUrl();
    }

    function loadInnerTable(report_ids)
    {

    }

    function showBubble(element, content)
    {
        $(element).qtip({
            content: content,
            show: {
                ready: true
            },
            style: {
                classes: 'qtip-tipped qtip-shadow',
                tip: false
            },
            hide: {
                fixed: true,
                delay: 150
            },
            position: {
                my: 'left center',
                at: 'right center',
                adjust: {
                    x: 10
                }
            }
        });
    }

    function resetFields()
    {
        $("#select-admin").prop("disabled", false);
        $("#select-admin").val($("#select-admin option:first").val());
        $("#select-status").val($("#select-status option:first").val());
        $("#input-date-from").val("");
        $("#input-date-to").val("");
        $("#select-reason").val($("#select-reason option:first").val());
        $("#input-text-target").val("");
        $("#input-text-reporter").val("");
        $("#select-server").val($("#select-server option:first").val());
        $("#input-text-report_ids").val("");
        $("#input-text-reason_custom").val("");
        $("#input-check-my_reports").attr("checked", false);
        $("#input-text-report_ids").prop("disabled", false);
        showUrl();
    }

    function getTodayDate()
    {
        var date = new Date();

        return date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
    }

    function showUrl()
    {
        var url = "http://ezpz.cz/page/report-system?";

        if ($("#select-admin").val() != "-")
        {
            url += "admin=" + encodeURIComponent($("#select-admin").val()) + "&";
        }

        var reports_status_ids = $("#select-status").find(":selected");
        if ($(reports_status_ids[0]).attr("status_id") != "-1" && reports_status_ids.length)
        {
            var ids = [];
            for (var i = 0; i < reports_status_ids.length; ++i)
            {
                ids.push($(reports_status_ids[i]).attr("status_id"));
            }
            url += "&status_ids=" + ids.join(",") + "&";
        }

        if ($("#input-date-from").val() != "")
        {
            url += "date_from=" + encodeURIComponent($("#input-date-from").val()) + "&";
        }

        if ($("#input-date-to").val() != "")
        {
            url += "date_to=" + encodeURIComponent($("#input-date-to").val()) + "&";
        }

        if ($("#select-reason").val() != "-")
        {
            url += "reason_id=" + encodeURIComponent($("#select-reason").find(":selected").attr("reason_id")) + "&";
        }

        if ($("#input-text-target").val() != "")
        {
            url += "target=" + encodeURIComponent($("#input-text-target").val()) + "&";
        }

        if ($("#input-text-reporter").val() != "")
        {
            url += "reporter=" + encodeURIComponent($("#input-text-reporter").val()) + "&";
        }

        if ($("#select-server").val() != "-")
        {
            url += "server=" + encodeURIComponent($("#select-server").val()) + "&";
        }

        if ($("#input-text-report_ids").val() != "")
        {
            url += "report_ids=" + encodeURIComponent($("#input-text-report_ids").val()) + "&";
        }

        if ($("#input-text-reason_custom").val() != "")
        {
            url += "reason_custom=" + encodeURIComponent($("#input-text-reason_custom").val()) + "&";
        }

        $("#input-url").val(url);
    }

    function parseUrl()
    {
        var admin = args["admin"];
        var status = args["status_ids"];
        var date_from = args["date_from"];
        var date_to = args["date_to"];
        var reason_id = args["reason_id"];
        var target = args["target"];
        var reporter = args["reporter"];
        var server = args["server"];
        var report_ids = args["report_ids"];
        var reason_custom = args["reason_custom"];

        if (typeof admin !== 'undefined')
        {
            $("#select-admin").val(admin);
        }

        if (typeof status !== 'undefined')
        {
            var status_ids = status.split(",");
            for (var i = 0; i < status_ids.length; ++i)
            {
                $("#select-status option[status_id='" + status_ids[i] + "']").attr("selected", "selected");
            }
        }

        if (typeof date_from !== 'undefined')
        {
            $("#input-date-from").val(date_from);
        }

        if (typeof date_to !== 'undefined')
        {
            $("#input-date-to").val(date_to);
        }

        if (typeof reason_id !== 'undefined')
        {
            //$("#select-reason").val(reason);
            $("#select-reason option[reason_id='" + reason_id + "']").attr("selected", "selected");
        }

        if (typeof target !== 'undefined')
        {
            $("#input-text-target").val(target);
        }

        if (typeof reporter !== 'undefined')
        {
            $("#input-text-reporter").val(reporter);
        }

        if (typeof server !== 'undefined')
        {
            $("#select-server").val(server);
        }

        if (typeof report_ids !== 'undefined')
        {
            $("#input-text-report_ids").val(report_ids);
        }

        if (typeof reason_custom !== 'undefined')
        {
            $("#input-text-reason_custom").val(reason_custom);
        }
    }

    $("#button-search").click(function(e)
    {
        var url;

        var reports_status_ids_url = "";
        var reports_status_ids = $("#select-status").find(":selected");
        if ($(reports_status_ids[0]).attr("status_id") != "-1" && reports_status_ids.length)
        {
            for (var i = 0; i < reports_status_ids.length; ++i)
            {
                reports_status_ids_url += "&report_status_ids[]=" + $(reports_status_ids[i]).attr("status_id");
            }
        }

        if ($(reports_status_ids[0]).attr("status_id") == "-1" || !reports_status_ids.length)
        {
            reports_status_ids_url = "&report_status_ids[]=";
        }

        if ($("#input-check-my_reports").prop("checked"))
        {
            url = "http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/list_report.php?" +
                "lang=" + lang +
                "&serverid=" + encodeURIComponent($("#select-server").find(":selected").attr("serverid")) +
                //"&report_status_id=" + encodeURIComponent($("#select-status").find(":selected").attr("status_id")) +
                reports_status_ids_url +
                "&reason_id=" + encodeURIComponent($("#select-reason").find(":selected").attr("reason_id")) +
                "&reason_custom=" + encodeURIComponent($("#input-text-reason_custom").val()) +
                "&date_create_from=" + encodeURIComponent($("#input-date-from").val()) +
                "&date_create_to=" + encodeURIComponent($("#input-date-to").val()) +
                "&target=" + encodeURIComponent($("#input-text-target").val()) +
                "&reporter=" + encodeURIComponent($("#input-text-reporter").val()) +
                //"&map_id=" + encodeURIComponent($("#select-map").find(":selected").attr("map_id")) +
                "&admin_id=" + encodeURIComponent($("#input-check-my_reports").attr("admin_id"));
        }
        else
        {
            var report_ids_string = $("#input-text-report_ids").val();
            var regex = /\d+/g;
            var match;
            var report_ids = [];

            do
            {
                match = regex.exec(report_ids_string);
                if ((match && match[0] !== null))
                {
                    report_ids.push(match[0]);
                }
            } while (match);

            report_ids = $.unique(report_ids);

            url = "http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/getGroupedReports.php?" +
                "lang=" + lang +
                "&serverid=" + encodeURIComponent($("#select-server").find(":selected").attr("serverid")) +
                    //"&report_status_id=" + encodeURIComponent($("#select-status").find(":selected").attr("status_id")) +
                reports_status_ids_url +
                "&reason_id=" + encodeURIComponent($("#select-reason").find(":selected").attr("reason_id")) +
                "&reason_custom=" + encodeURIComponent($("#input-text-reason_custom").val()) +
                "&date_create_from=" + encodeURIComponent($("#input-date-from").val()) +
                "&date_create_to=" + encodeURIComponent($("#input-date-to").val()) +
                "&target=" + encodeURIComponent($("#input-text-target").val()) +
                "&reporter=" + encodeURIComponent($("#input-text-reporter").val()) +
                    //"&map_id=" + encodeURIComponent($("#select-map").find(":selected").attr("map_id")) +
                "&admin_id=" + encodeURIComponent($("#select-admin").find(":selected").attr("admin_id")) +
                (report_ids.length > 0 ? "&report_ids[]=" + report_ids.join("&report_ids[]=") : "");
        }

        //console.log(url);

        loadTable(url);
        //loadTable(url);
        e.preventDefault();
    });

    $("#button-my-today").click(function(e)
    {
        var today_date = getTodayDate();

        var url = "http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/list_report.php?" +
            "lang=" + lang +
            "&serverid=-1" + encodeURIComponent($("#select-server").find(":selected").attr("serverid")) +
            "&admin_id=" + encodeURIComponent($("#button-my-today").attr("admin_id")) +
            "&date_create_from=" + encodeURIComponent(today_date) +
            "&date_create_to=" + encodeURIComponent(today_date);

        //console.log(url);

        $("#button-reset").trigger("click");

        loadTable(url);
        //loadTable(url);
        e.preventDefault();
    });

    $("#button-reset").click(function(e)
    {
        resetFields();
        e.preventDefault();
    });

    $("#button-today").click(function(e)
    {
        $("#input-date-from").val(getTodayDate());
        $("#input-date-to").val(getTodayDate());
        e.preventDefault();
    });

    $("#input-check-my_reports").change(function () {
        var select_admin = $("#select-admin");

        if ($("#input-check-my_reports").prop("checked"))
        {
            select_admin.prop("disabled", true);
            select_admin.val(select_admin.find("option[admin_id='" + $("#input-check-my_reports").attr("admin_id") + "']").val());
            $("#input-text-report_ids").prop("disabled", true);
        }
        else
        {
            select_admin.prop("disabled", false);
            $("#select-admin").val($("#select-admin option:first").val());
            $("#input-text-report_ids").prop("disabled", false);
        }
    });

    $("#input-check-today").change(function () {
        if ($("#input-check-today").prop("checked"))
        {
            $("#input-date-from").prop("disabled", true);
            $("#input-date-from").val(getTodayDate());

            $("#input-date-to").prop("disabled", true);
            $("#input-date-to").val(getTodayDate());
        }
        else
        {
            $("#input-date-from").prop("disabled", false);
            $("#input-date-from").val("");

            $("#input-date-to").prop("disabled", false);
            $("#input-date-to").val("");
        }
    });

    $('#div-table').on('click', 'tr[report_ids]', function ()
    {
        var tr = $(this);
        var row = table.row(tr);

        if (row.child.isShown())
        {
            row.child.hide();
            tr.removeClass('shown');
        }
        else
        {
            var rc = row.child("<div class='div-inner-reports'></div>").show();
            var table_div = tr.next().find(".div-inner-reports");

            if (tr.attr("table-inner"))
            {
                table_div.html(tr.attr("table-inner"));
                table_div.children(".table-reports").DataTable({
                    "order": [[ 1, 'desc' ]],
                    "columnDefs": [ { "targets": "no-sort", "orderable": false } ],
                    //"scrollX": true,
                    "paging":   false,
                    "ordering": false,
                    "info":     false,
                    "sDom": 'lrtip'
                });
                tr.addClass('shown');
            }
            else
            {
                table_div.html("<div id='div-loader'><img src='http://ezpz.cz/ext/phpbb/pages/styles/pbtech/theme/ajax-loader-small.gif' alt='Loading...' style='display: block; margin-left: auto; margin-right: auto;' /></div>");
                var report_ids = tr.attr("report_ids").split(",");
                var url = "http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/getReports.php?lang=en&report_ids[]=" +
                    (report_ids.length > 0 ? report_ids.join("&report_ids[]=") : report_ids[0]);

                //console.log(url);

                $.ajax({
                    url: url,
                    success: function(result) {
                        if (result.success)
                        {
                            table_div.html(result.data);
                            table_div.children(".table-reports").DataTable({
                                "order": [[ 1, 'desc' ]],
                                "columnDefs": [ { "targets": "no-sort", "orderable": false } ],
                                //"scrollX": true,
                                "paging":   false,
                                "ordering": false,
                                "info":     false,
                                "sDom": 'lrtip'
                            });
                            tr.addClass('shown');
                            tr.attr("table-inner", table_div.prop('outerHTML'));
                        }
                    }
                });
            }
        }
    });

    $('#div-table').on('click', '.button-progress', function ()
    {
        var button = $(this);

        var reports = $(button).parent().prev().find(".table-reports").find(".chb-report:checked").parent().parent();

        if (!reports.length)
        {
            alert(translation["alerts"]["no_report"]);
            return;
        }

        reports.each(function()
        {
            var report = $(this);

            var status = report.find("td[status_id]");

            if (status.attr("status_id") == "2")
            {
                return;
            }

            if ((status.attr("status_id") == "3" || status.attr("status_id") == "4"))
            {
                if (!confirm(translation["alerts"]["progress"]))
                {
                    return;
                }
            }

            $.ajax(
                {
                    type: "POST",
                    url: "http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/report_actions/r_setInProgress.php",
                    data: {
                        "report_id": report.attr("report_id")
                    },
                    success: function(response)
                    {
                        if (response.success)
                        {
                            status.attr("status_id", "2");
                            status.fadeOut(function() {
                                status.text(translation["status"]["progress"]).fadeIn();
                            });
                        }
                    }
                });
        });
    });

    $('#div-table').on('click', '.button-reject', function ()
    {
        var button = $(this);

        var reports = $(button).parent().prev().find(".table-reports").find(".chb-report:checked").parent().parent();

        if (!reports.length)
        {
            alert(translation["alerts"]["no_report"]);
            return;
        }

        reports.each(function()
        {
            var report = $(this);

            var status = report.find("td[status_id]");

            if (status.attr("status_id") != "5")
            {
                $.ajax(
                    {
                        type: "POST",
                        url: "http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/report_actions/r_setReject.php",
                        data: {
                            "report_id": report.attr("report_id")
                        },
                        success: function(response)
                        {
                            if (response.success)
                            {
                                status.attr("status_id", "4");
                                status.fadeOut(function() {
                                    status.text(translation["status"]["rejected"]).fadeIn();
                                });
                            }
                        }
                    });
            }
        });
    });

    $('#div-table').on('click', '.button-accept', function ()
    {
        var button = $(this);

        var reports = $(button).parent().prev().find(".table-reports").find(".chb-report:checked").parent().parent();

        if (!reports.length)
        {
            alert(translation["alerts"]["no_report"]);
            return;
        }

        reports.each(function()
        {
            var report = $(this);

            var status = report.find("td[status_id]");

            if (status.attr("status_id") != "3")
            {
                $.ajax(
                    {
                        type: "POST",
                        url: "http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/report_actions/r_setAccept.php",
                        data: {
                            "report_id": report.attr("report_id")
                        },
                        success: function(response)
                        {
                            if (response.success)
                            {
                                status.attr("status_id", "3");
                                status.fadeOut(function() {
                                    status.text(translation["status"]["accept"]).fadeIn();
                                });
                            }
                        }
                    });
            }
        });
    });

    $('#div-table').on('click', '.button-ban', function ()
    {
        var button = $(this);

        var reports = $(button).parent().prev().find(".table-reports").find(".chb-report:checked").parent().parent();

        if (!reports.length)
        {
            alert(translation["alerts"]["no_report"]);
            return;
        }

        var report_ids = [];

        reports.each(function() {
            report_ids.push($(this).attr("report_id"));
        });

        var ban_info = button.closest("#table-reports-group").find("tr[group_id=" + button.attr("group_id") + "]").find("td.cell-target");

        button.attr("href", "http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/report_actions/addban_report.html?" +
            "nickname=" + encodeURIComponent(ban_info.attr("trg_nick")) +
            "&steamid=" + encodeURIComponent(ban_info.attr("trg_sid")) +
            "&ip=" + encodeURIComponent(ban_info.attr("trg_ip")) +
            "&report_ids=" + report_ids.join(","));

        $(".button-ban").fancybox(
            {
                "overlayColor": "#000",  // here you set the background black
                "overlayOpacity": 1,  // here you set the transparency of background: 1 = opaque
                "hideOnOverlayClick": true,  // if true, closes fancybox when clicking OUTSIDE the box
                "hideOnContentClick": true, // if true, closes fancybox when clicking INSIDE the box
                "type": "iframe", // the type of content : iframe for external pages
                //"width": 640, // if type=iframe is always smart to set dimensions
                //"height": 700,
                'scrolling': 'no',
                'titleShow': false,
                'autoscale': false,
                'autoDimensions': false,
                'afterClose': function ()
                {
                    if (banSuccess["success"])
                    {
                        reports.each(function () {
                            var report = $(this);
                            var status = report.find("td[status_id]");
                            $.ajax(
                                {
                                    type: "POST",
                                    url: "http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/report_actions/r_setAccept.php",
                                    data: {
                                        "report_id": report.attr("report_id"),
                                        "ban_id": banSuccess["ban_id"]
                                    },
                                    success: function(response)
                                    {
                                        if (response.success)
                                        {
                                            status.attr("status_id", "4");
                                            status.fadeOut(function () {
                                                status.html("<b><a target='_blank' href='http://sourcebans.ezpz.cz/#" + banSuccess["ban_id"] + "'>" + translation["status"]["accept_ban"] + "</a></b>").fadeIn();
                                            });
                                        }
                                    }
                                });
                        });
                    }
                }
            });
    });

    $('#div-table').on('click', '.button-take', function ()
    {
        var button = $(this);

        var reports = $(button).parent().prev("div").find("tr[report_id]").each(function() {
            $.ajax(
                {
                    type: "POST",
                    url: "http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/report_actions/r_takeOver.php",
                    data: {
                        "report_id": $(this).attr("report_id")
                    },
                    success: function(response)
                    {
                        if (response.success)
                        {
                            $("#input-date-from").val(button.attr("date_create"));
                            $("#input-date-to").val(button.attr("date_create"));
                            $("#input-text-target").val(button.attr("trg_sid"));

                            $("#button-search").trigger("click");
                        }
                    }
                });
        });
    });

    /*$('#div-table').on('click', '.button-note', function ()
     {
     var button = $(this);

     $(button).parent().prev().find(".table-reports").find(".chb-report:checked").parent().parent().each(function() {
     var report = $(this);

     var note = report.find("td[note]");

     $.ajax(
     {
     type: "POST",
     url: "http://ezpz.cz/ext/phpbb/pages/styles/pbtech/template/report-g/report_actions/r_setNote.php",
     data: {
     "report_id": report.attr("report_id"),
     "note": report.find("td[note]")
     },
     success: function(response)
     {
     if (response.success)
     {
     note.attr("note", "4");
     status.fadeOut(function() {
     status.text((lang == "cze" ? "hotové – zamítnuto" : "finished – rejected")).fadeIn();
     });
     }
     }
     });

     });
     });*/

    $("#div-table").on("mouseover", ".bubble-reporter", function() {
        var that = $(this);
        var this_parent = $(that).parent();

        var content = $.parseHTML("<b>SteamID:</b> " + $(this_parent).attr('rep_sid') + "<br />" +
            "<b>IP:</b> " + $(this_parent).attr('rep_ip') + "<br />" +
            "<b><a target='_blank' href='" + $(this_parent).attr('rep_chatlog_link') + "'>ChatLog</a></b> <br />" +
            "<b><a target='_blank' href='" + $(this_parent).attr('rep_connectlog_link') + "'>ConnectLog</a></b> <br />" +
            "<b><a target='_blank' href='" + $(this_parent).attr('rep_hlstats_link') + "'>HLStats</a></b>");

        showBubble(that, content);
    });

    $("#div-table").on("mouseover", ".bubble-target", function() {
        var that = $(this);
        var this_parent = $(that).parent();

        var content = $.parseHTML("<b>SteamID:</b> " + $(this_parent).attr('trg_sid') + "<br />" +
            "<b>IP:</b> " + $(this_parent).attr('trg_ip') + "<br />" +
            "<b><a target='_blank' href='" + $(this_parent).attr('trg_chatlog_link') + "'>ChatLog</a></b> <br />" +
            "<b><a target='_blank' href='" + $(this_parent).attr('trg_connectlog_link') + "'>ConnectLog</a></b> <br />" +
            "<b><a target='_blank' href='" + $(this_parent).attr('trg_hlstats_link') + "'>HLStats</a></b>");

        showBubble(that, content);
    });

    $("#div-table").on("mouseover", ".bubble-status", function() {
        var that = $(this);
        var this_parent = $(that).parent();

        var content = $.parseHTML(translation["bubble"]["finished"] + ": " + $(this_parent).attr('time_finish') + "<br />" +
            ($(this_parent).attr('sourcebans_link') != "" ? "<b><a target='_blank' href='" + $(this_parent).attr('sourcebans_link') + "'>SourceBans</a></b>" : ""));

        //($(this_parent).attr('status_id') == "3" &&

        showBubble(that, content);
    });

    $("#div-table").on("mouseover", ".bubble-note", function() {
        var that = $(this);
        var this_parent = $(that).parent();

        var content = this_parent.attr("note");

        showBubble(that, content);
    });

    $('#div-table').on('change', '.chb-select-all', function() {
        var chbAll = $(this);
        //var chbs = chbAll.closest("table").find(".chb-report");
        var chbs = chbAll.closest(".dataTables_scroll").find(".chb-report");

        if (chbAll.prop("checked"))
        {
            chbs.each(function()
            {
                $(this).prop("checked", true);
            });

            chbAll.next("a").text("NONE")
        }
        else
        {
            chbs.each(function()
            {
                $(this).prop("checked", false);
            });

            chbAll.next("a").text("ALL")
        }
    });

    $('#div-table').on('click', '.a-select-all', function() {
        var a = $(this);
        var chbAll = a.prev("input.chb-select-all");
        var chbs = chbAll.closest(".dataTables_scroll").find(".chb-report");

        if (chbAll.prop("checked"))
        {
            chbs.each(function()
            {
                chbAll.prop("checked", false);
                $(this).prop("checked", false);
                a.text("ALL");
            });
        }
        else
        {
            chbs.each(function()
            {
                chbAll.prop("checked", true);
                $(this).prop("checked", true);
                a.text("NONE");
            });
        }
    });

    /*
     $('input.datetimepicker').datepicker(
     {
     duration: '',
     changeMonth: false,
     changeYear: false,
     yearRange: '2010:2020',
     showTime: false,
     time24h: true
     });

     $.datepicker.regional['cs'] = {
     closeText: 'Zavřít',
     prevText: '&#x3c;Dříve',
     nextText: 'Později&#x3e;',
     currentText: 'Nyní',
     monthNames: ['leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen',
     'září', 'říjen', 'listopad', 'prosinec'],
     monthNamesShort: ['led', 'úno', 'bře', 'dub', 'kvě', 'čer', 'čvc', 'srp', 'zář', 'říj', 'lis', 'pro'],
     dayNames: ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'],
     dayNamesShort: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
     dayNamesMin: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
     weekHeader: 'Týd',
     dateFormat: 'dd/mm/yy',
     firstDay: 1,
     isRTL: false,
     showMonthAfterYear: false,
     yearSuffix: ''
     };
     $.datepicker.setDefaults($.datepicker.regional['cs']);
     */

    var datepicker_from_options =
    {
        dateFormat: 'yy-mm-dd',
        showAnim: "slideDown",
        onClose: function(selectedDate) {
            $("#input-date-to").datepicker("option", "minDate", selectedDate);
        }
    };

    var datepicker_to_options =
    {
        dateFormat: 'yy-mm-dd',
        showAnim: "slideDown",
        onClose: function(selectedDate) {
            $("#input-date-from").datepicker("option", "maxDate", selectedDate);
        }
    };

    if (lang == "cze")
    {
        var czech_region =
        {
            closeText: 'Zavřít',
            prevText: '&#x3c;Dříve',
            nextText: 'Později&#x3e;',
            currentText: 'Nyní',
            monthNames: ['leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen',
                'září', 'říjen', 'listopad', 'prosinec'],
            monthNamesShort: ['led', 'úno', 'bře', 'dub', 'kvě', 'čer', 'čvc', 'srp', 'zář', 'říj', 'lis', 'pro'],
            dayNames: ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'],
            dayNamesShort: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
            dayNamesMin: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
            weekHeader: 'Týd',
            dateFormat: 'yy-mm-dd',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
        };

        $('#input-date-from').datepicker(datepicker_from_options);
        $('#input-date-to').datepicker(datepicker_to_options);

        $.datepicker.regional['cs'] = czech_region;
        $.datepicker.setDefaults($.datepicker.regional['cs']);
    }
    else
    {
        $('#input-date-from').datepicker(datepicker_from_options);
        $('#input-date-to').datepicker(datepicker_to_options);
    }

    if (args["report_ids"] === "undefined")
    {
        var admin_info = $("admininfo");

        if (admin_info.attr("admin") == "true") {
            $("#select-status option[status_id='1']").prop('selected', true);
            $("#select-admin option[admin_id='" + admin_info.attr("admin_id") + "'").prop('selected', true);
        }
    }

    if (args["status_ids"] === "undefined")
    {
        $("#select-status option[status_id='-1']").attr("selected", "selected");
    }

    parseUrl();
    $("#select-status").dropdownchecklist({width: 500, firstItemChecksAll: true});
    $("#button-search").trigger("click");
});