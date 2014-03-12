<?php

return array(
    // The IPs of the servers allowed to report matches
    'allowed_ips' => array(
        '127.0.0.1',
        '127.0.0.2'
    ),

    // The user ID of the person who will be entering the matches automatically
    'autoreport_uid' => 0,

    // When set to true, all match details and unauthorized access attempts will be reported in the specified log
    // file
    'log_details' => true,

    // The path and name of the log file if you have set $LOG_DETAILS to true. If you wish, you can change the name
    // of the file to have the date
    'log_file' => "leagueOverseer.log",


///  ========================================= Advanced Debug Options =================================================

    // Unless you know what you are doing, do NOT change any of these variables as they will disable any security
    // checks that are put in place. I'm looking at you brad, don't change these values.
    //
    // Really. Do. Not. Change. These. Values.
    //

    // This option should remain false on any production server. When this option is set to true, it will ignore
    // official servers and accept match reports from any IP at all therefore allowing abuse. This option only
    // exists for debugging the script.
    'disable_ip_check' => false,

    // This variable should remain as $_POST on any production server. This option only exists as a method to debug
    // the queries via a browser using $_GET, which allows you to write the URL with the desired parameters. This
    // option may allow for heavy abuse and a lot of errors if changed from the default value.
    'report_method' => $_POST
);