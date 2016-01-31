<?php

if (!isset($_GET["serverid"]))
    die("serverid is not set!");

if (!isset($_GET["lang"]))
    die("lang is not set!");

session_start();

