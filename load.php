<?php

/*
 * JohnCMS NEXT Mobile Content Management System (http://johncms.com)
 *
 * For copyright and license information, please see the LICENSE.md
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://johncms.com JohnCMS Project
 * @copyright   Copyright (C) JohnCMS Community
 * @license     GPL-3
 */

define('_IN_JOHNCMS', 1);

require('system/bootstrap.php');

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db2 */
$db2 = $container->get(PDO::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

$user_id = $systemUser->id;
$rights = $systemUser->rights;

$REQUEST_URI = $_SERVER['REQUEST_URI'];
if ($_SERVER["QUERY_STRING"]) {
    $REQUEST_URI = explode("?", $REQUEST_URI)[0];
}

$coursesArr = [
    1 => "1-kurs",
    2 => "2-kurs",
    3 => "3-kurs",
    4 => "4-kurs",
];

$years = range(2022, date('Y'));
if(!in_array(date("Y", strtotime("+1 year")), $years)) {
    array_push($years, date("Y", strtotime("+1 year")));
}

$url = [];
$fr2url = explode('/', mb_substr(urldecode($REQUEST_URI), 1, mb_strlen(urldecode($REQUEST_URI))));
if ($fr2url){
    foreach($fr2url as $frurl){
        if ($frurl) $url[] = $frurl;
    }
}


if (!function_exists("name")) {
    function name($str) {
        return mb_strtolower(
            str_replace(" ", "-", $str)
        );
    }
}

$permissions = false;
if ($systemUser["permissions"]) {
    $permissions = json_decode($systemUser["permissions"], true);
}

if ($systemUser["role"] == "teacher") {
    $permissions = [ "journalList", "pdf", "calendar", "sciencesList", "addScience", "editScience", "journalAttendance", "journalEvaluate",];
} else if($systemUser["role"] == "student") {
    $permissions = [ "journalList", "personalArea", "calendar", "journalAttendance", "journalEvaluate",];
}

if ($url[0] != "" && $url[0] != "login" && $url[0] != "login-student" && $url[0] != "login-teacher" && $url[0] != "exit" && $url[0] != "api" && $url[0] != "yangiasr.uz") {
    if (!$permissions || !in_array($url[0], $permissions)) {
        echo "Sizda ushbu sahifaga kirish uchun huquqlar yetarli emas!";
        http_response_code(404);
        exit;
    }
}

$load_defined = true;
if ($url[0]) {
    if (!$is_config && file_exists($url[0].".php")) {
        include $url[0].".php";
    } else {
        // include "system/head.php";
        // include "404-error.php";
    }
}
?>