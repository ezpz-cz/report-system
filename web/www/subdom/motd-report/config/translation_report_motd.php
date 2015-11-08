<?php

function getReportTranslation($lang)
{
    if ($lang == "cze") return array(
        "texts" => array(
            "warning" => "ZNEUŽITÍ TOHOTO SYSTÉMU SE TRESTÁ BANEM!",
            "warning_send" => "Vyplň alespoň jeden důvod! Stačí i jiný důvod.",
            "report_count_exceeded" => "Pro tento den jsi vyčerpal počet reportů (%d reportů)! Úspěšnými reporty se tento počet bude zvyšovat.",
            "report_add_success" => "Report byl úspěšně odeslán! Dnes ještě můžeš poslat %d reportů. Úspěšnými reporty se tento počet bude zvyšovat. Stav reportu lze sledovat na <a href='http://ezpz.cz/page/report?report_id=%d'></a>"),

        "buttons" => array(
            "send" => "Odeslat report!",
            "close" => "Zavřít"),

        "db" => array(
            "suffix" => "cze"),

        "controls" => array(
            "trg_nick" => "Nahlášený nick",
            "trg_sid" => "Nahlášené SteamID",
            "trg_ip" => "Nahlášená IP",
            "rep_nick" => "Tvůj nick",
            "rep_sid" => "Tvoje SteamID",
            "rep_ip" => "Tvoje IP",
            "server" => "Server",
            "round" => "Kolo ve hře",
            "round_before" => "Před tímto kolem",
            "round_current" => "Tohle kolo",
            "reason" => "DŮVOD",
            "reason_custom" => "Jiný důvod"),
    );

    if ($lang == "en") return array(
        "texts" => array(
            "warning" => "ABUSE OF THIS SYSTEM WILL RESULT IN BAN!",
            "warning_send" => "Fill in at least one reason! It can be only other reason.",
            "report_count_exceeded" => "You have reached the report count for today (%d reports)! Having successful reports you will be able to send more reports.",
            "report_add_success" => "Report was successfully sent! Today you can still send %d reports. Having successful reports you will be able to send more report(s).
                                    You can look at report status at <a href='http://ezpz.cz/page/report?report_ids=%d'>http://ezpz.cz/page/report?report_ids=%d</a>"),

        "buttons" => array(
            "send" => "Send report!",
            "close" => "Close"),

        "db" => array(
            "suffix" => "en"),

        "controls" => array(
            "trg_nick" => "Target nick",
            "trg_sid" => "Target SteamID",
            "trg_ip" => "Target IP",
            "rep_nick" => "Your nick",
            "rep_sid" => "Your SteamID",
            "rep_ip" => "Your IP",
            "server" => "Server",
            "round" => "Round in game",
            "round_before" => "Before this round",
            "round_current" => "This round",
            "reason" => "REASON",
            "reason_custom" => "Other reason"),
    );

    return False;
}