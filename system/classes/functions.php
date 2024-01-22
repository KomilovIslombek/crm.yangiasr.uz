<?php
function error($code) {
    switch ($code) {
        case "404":
            // include realpath("404-error.php");
            http_response_code(404);
            exit();
    }
}

function image($image_id) {
    global $db;

    $image_id = (int)$image_id;
    if ($image_id) {
        $image = $db->assoc("SELECT * FROM `images` WHERE id = $image_id LIMIT 1");
        if (file_exists($image["file_folder"]) || file_exists("../".$image["file_folder"])) {
            $image_arr = $image;
        }
    }

    return $image_arr;
}

function delete_image($image_id) {
    global $db;
    if (!$image_id) return;
    $image = image($image_id);
    if ($image["id"] > 0) {
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/". $image["file_folder"])) {
            unlink($_SERVER["DOCUMENT_ROOT"] . "/".$image["file_folder"]);
        }
        $db->delete("images", $image_id);
    }
}

function delete_file($file_id) {
    global $db;
    if (!$file_id) return;
    $file = fileArr($file_id);
    if ($file["id"] > 0) {
        if ($file["thumb_image_id"] > 0) {
            delete_image($file["thumb_image_id"]);
        }
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/". $file["file_folder"])) {
            unlink($_SERVER["DOCUMENT_ROOT"] . "/".$file["file_folder"]);
        }
        $db->delete("files", $file_id);
    }
}

function video($video_id) {
    global $db;
    $video_arr = [
        "id" => 0,
        "creator_user_id" => 0,
        "width" => 0,
        "height" => 0,
        "size" => 0,
        "duration" => 0,
        "file_folder" => "",
        "file_info" => json_encode(array()),
        "created_date" => date("Y-m-d H:i:s"),
        "updated_date" => date("Y-m-d H:i:s"),
    ];

    $video_id = (int)$video_id;
    if ($video_id) {
        $video = $db->assoc("SELECT * FROM `videos` WHERE id = $video_id LIMIT 1");
        if (file_exists($video["file_folder"]) || file_exists("../".$video["file_folder"])) {
            $video_arr = $video;
        }
    }
    return $video_arr;
}

function audio($audio_id) {
    global $db;
    $audio_arr = [];

    $audio_id = (int)$audio_id;
    if ($audio_id) {
        $audio = $db->assoc("SELECT * FROM audios WHERE id = $audio_id");
        if (file_exists($audio["file_folder"]) || file_exists("../".$audio["file_folder"])) {
            $audio_arr = $audio;
        }
    }
    return $audio_arr;
}

function fileArr($file_id) {
    global $db;

    $file_id = (int)$file_id;
    if ($file_id) {
        $file = $db->assoc("SELECT * FROM `files` WHERE id = $file_id LIMIT 1");
        if (file_exists($file["file_folder"]) || file_exists("../".$file["file_folder"])) {
            $file_arr = $file;
        }
    }

    return $file_arr;
}

// idCode start

function idCode2($direction_number, $request_id, $exam_lang, $learn_type) {
    $direction_number = (string)$direction_number;
    $request_id = (string)$request_id;

    // print_r([
    //     $direction_number, $request_id, $exam_lang, $learn_type
    // ]);
    // exit;

    $id = "";
    $id .= date("Y");
    $id .= $direction_number;
    $id .= ["uz" => 1, "ru" => 2][$exam_lang];
    $id .= ["Kunduzgi" => 1, "Kechki" => 2, "Sirtqi" => 3][$learn_type];
    $id .= $request_id;

    return $id;
}

/** 
 * Convert number of seconds into hours, minutes and seconds 
 * and return an array containing those values 
 * 
 * @param integer $inputSeconds Number of seconds to parse 
 * @return array 
 */ 

function secondsToTime($inputSeconds, $no_seconds = false) {
    $secondsInAMinute = 60;
    $secondsInAnHour = 60 * $secondsInAMinute;
    $secondsInADay = 24 * $secondsInAnHour;

    // Extract days
    $days = floor($inputSeconds / $secondsInADay);

    // Extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // Extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // Extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    // Format and return
    $timeParts = [];
    $sections = [
        'kun' => (int)$days,
        'soat' => (int)$hours,
        'daqiqa' => (int)$minutes
    ];

    if (!$no_seconds) {
        $sections['soniya'] = (int)$seconds;
    }

    foreach ($sections as $name => $value){
        if ($value > 0){
            $timeParts[] = $value. ' '.$name.($value == 1 ? '' : '');
        }
    }

    return implode(', ', $timeParts);
}

function getAvatar($user) {
    if ($user["id"]) {
        if ($user['profileimg'] && file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . $user['profileimg'])) {
            return $user['profileimg'];
        } else {
            $nm = mb_strtoupper($user["first_name"][0].$user["last_name"][0]);
            return "profileimg.php?n=$nm&c=blue";
        }
    } else {
        return "theme/main/assets/img/logo/logo-icon.png";
    }
}

function encrypt_decrypt($action, $string) {
    if (!$string) return;
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'Y1htZWRQb28=';
    $secret_iv = 'q4WFkeZ3F6aXl2dg==';
    // hash
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

function encode($str) {
    return encrypt_decrypt("encrypt", $str);
}

function decode($str) {
    return encrypt_decrypt("decrypt", $str);
}

$domain = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]");

function addCookie($key, $val) {
    global $domain;
    $_COOKIE[$key] = $val;
    setcookie($key, $val, time() + (3600 * 24 * 365 * 5), "/");
}

function removeCookie($key, $id = 0) {
    // if ($key == "phone") exit($id);
    unset($_COOKIE[$key]);
    setcookie($key, '', time()-100000);
    setcookie($key, '', time()-100000, '/');
}

function getUser($user_id) {
    global $db;
    $user = $db->assoc("SELECT * FROM users WHERE id = ?", [ $user_id ]);

    if ($user["id"]) {
        $user["full_name"] = $user["first_name"] . " " . $user["last_name"];
        return $user;
    } else {
        return false;
    }
}

function isAuth() {
    global $user_id;

    // tekshiruv
    if (!$user_id || $user_id == 0) {
        return false;
    } else {
        return true;
    }
}

function multiexplode($delimiters,$string) {
    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return array_map(function($v){return trim($v);}, $launch);
}

function addMenu($icon, $name, $menus_arr) {
    global $url, $systemUser;
    
    $menus = '';
    $has_active = false;
    $allow = false;

    $permissions = false;
    if ($systemUser["permissions"]) {
        $permissions = json_decode($systemUser["permissions"]);
    }

    if ($systemUser["role"] == "teacher") {
        $permissions = [ "journalList", "pdf", "calendar", "sciencesList"];
    } else if($systemUser["role"] == "student") {
        $permissions = [ "journalList", "personalArea", "calendar"];
    }

    foreach ($menus_arr as $menu) {
        $is_active = $url[0] == $menu["page"] ? true : false;
        if ($is_active) $has_active = true;

        if (in_array($menu["page"], $permissions)) {
            $allow = true;
            $menus .= '<li class="'.($is_active ? 'mm-active' : "").'">
                <a href="'.$menu['href'].'" class="'.($is_active ? 'mm-active' : "").'">'.$menu['name'].'</a>
            </li>';
        }
    }

    if ($allow) {
        echo ('
        <li class="'.($has_active ? "mm-active" : "").'">
            <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                <i class="'.$icon.'"></i>
                <span class="nav-text">'.$name.'</span>
            </a>
            <ul aria-expanded="false" class="'.($is_active ? "mm-collapse mm-show" : "").'">
                '.$menus.'
            </ul>
        </li>');
    }
}

$errors = [];
function validate($forms) {
    global $errors;
    foreach ($forms as $form) {
        if (empty($_POST[$form])) {
            $errors["forms"][$form][] = "$form ni kiritishni unutdingiz!";
        }
    }
}

function getError($formName) {
    global $errors;

    if ($errors["forms"][$formName] && count($errors["forms"][$formName]) > 0) {
        foreach ($errors["forms"][$formName] as $errorText) {
            echo '<h4 class="text-danger">'.$errorText.'</h4>';
        }
    }
}

// function ipHistory($userData) {
//     global $db2, $env;
//     // Удаляем из истории текущий адрес (если есть)
//     $db2->exec("DELETE FROM `cms_users_iphistory`
//         WHERE `user_id` = '" . $userData['id'] . "'
//         AND `ip` = '" . $env->getIp() . "'
//         AND `ip_via_proxy` = '" . $env->getIpViaProxy() . "'
//         LIMIT 1
//     ");

//     // Вставляем в историю предыдущий адрес IP
//     $db2->exec("INSERT INTO `cms_users_iphistory` SET
//         `user_id` = '" . $userData['id'] . "',
//         `ip` = '" . $userData['ip'] . "',
//         `ip_via_proxy` = '" . $userData['ip_via_proxy'] . "',
//         `time` = '" . $userData['lastdate'] . "'
//     ");

//     // Обновляем текущий адрес в таблице `users`
//     $db2->exec("UPDATE `users` SET
//         `ip` = '" . $env->getIp() . "',
//         `ip_via_proxy` = '" . $env->getIpViaProxy() . "'
//         WHERE `id` = '" . $userData['id'] . "'
//     ");
// }
?>