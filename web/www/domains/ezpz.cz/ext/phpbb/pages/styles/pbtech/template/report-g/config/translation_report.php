<?php

function getReportTranslation($lang)
{
    if ($lang == "cze") return array(
        "table_headers" => array(
            "date" => "Datum",
            "target" => "Nahlášený",
            "reports_count" => "Počet reportů",
            "admin" => "Admin",
            "time" => "Čas",
            "status" => "Stav",
            "reporter" => "Nahlásil",
            "server" => "Server",
            "map" => "Mapa",
            "round" => "Kolo",
            "reason" => "Důvod",
            "note" => "Poznámka",
            "all" => "VŠE"),

        "buttons" => array(
            "progress" => "Řešit",
            "reject" => "Zamítnout",
            "accept" => "Přijmout",
            "ban" => "Přijmout a banovat",
            "note" => "Změnit poznámku"),

        "db" => array(
            "suffix" => "cze"),

        "page" => array(
            "reports" => "Reporty"),

        "page_filters" => array(
            "filters" => "Filtry",
            "reports" => "Reporty",
            "admin" => "Admin",
            "open" => "Rozšířený filtr",
            "close" => "Skryj rozšířený filtr",
            "status" => "Stav",
            "date_from" => "Datum od",
            "date_to" => "Datum do",
            "reason" => "Důvod",
            "target" => "Nahlášený",
            "nick" => "Nick",
            "steamid" => "SteamID",
            "ip" => "IP",
            "reporter" => "Nahlásil",
            "server" => "Server",
            "server_all" => "Všechny",
            "report_ids" => "ID reportů",
            "reason_custom" => "Vlastní důvod",
            "button_search" => "Hledat",
            "button_my_today_reports" => "Moje dnešní reporty",
            "checkbox_my_reports" => "Hledat v mých reportech",
            "checkbox_today" => "Dnešek",
            "button_reset_filter" => "Resetovat filtr",
            "button_today" => "Dnešek",
            "filter_url" => "Link na tento filtr")
    );

    if ($lang == "en") return array(
        "table_headers" => array(
            "date" => "Date",
            "target" => "Target",
            "reports_count" => "Reports count",
            "admin" => "Admin",
            "time" => "Time",
            "status" => "Status",
            "reporter" => "Reporter",
            "server" => "Server",
            "map" => "Map",
            "round" => "Round",
            "reason" => "Reason",
            "note" => "Note",
            "all" => "ALL"),

        "buttons" => array(
            "progress" => "Deal with",
            "reject" => "Reject",
            "accept" => "Accept",
            "ban" => "Accept and ban",
            "note" => "Change note"),

        "db" => array(
            "suffix" => "en"),

        "page" => array(
            "reports" => "Reports"),

        "page_filters" => array(
            "filters" => "Filters",
            "reports" => "Reports",
            "admin" => "Admin",
            "open" => "Advanced filter",
            "close" => "Hide advanced filter",
            "status" => "Status",
            "date_from" => "Date from",
            "date_to" => "Date to",
            "reason" => "Reason",
            "target" => "Target",
            "nick" => "Nick",
            "steamid" => "SteamID",
            "ip" => "IP",
            "reporter" => "Reporter",
            "server" => "Server",
            "server_all" => "All",
            "report_ids" => "Report IDs",
            "reason_custom" => "Custom reason",
            "button_search" => "Search",
            "button_my_today_reports" => "My today reports",
            "checkbox_my_reports" => "Search in my reports",
            "checkbox_today" => "Today",
            "button_reset_filter" => "Reset filter",
            "button_today" => "Today",
            "filter_url" => "Link for this filter")
    );

    return False;
}