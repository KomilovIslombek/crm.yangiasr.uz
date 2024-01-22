<?php
// $is_config = true;
// if (empty($load_defined)) include 'load.php';

date_default_timezone_set("Asia/Tashkent");

include "modules/menuPages.php";
// tekshiruv
// if (!$user_id || $user_id == 0) {
// } else {
//     header("Location: /tests");
// }
if (isAuth() == true) {
    header("Location: /$permissions[0]");
    exit;
}

$error = false;
if ($_POST["submit"]) {
    $login = trim($_POST["login"]);
    $pass = trim($_POST["pass"]);

    // $login = str_replace(" ", "", $login);
    // $pass = str_replace(" ", "", $pass);

    if (!$login) {
        $login_error = "IDni kiritishni unutdingiz!";
    }

    if (!$pass) {
        $pass_error = "Passport seriyasi hamda raqamini kiritishni unutdingiz!";
    }

    if ($login && $pass) {
        $user = $db->assoc("SELECT * FROM users WHERE login = ? AND password = ?", [ $login, md5(md5(encode($pass))) ]);
    }

    if ($login && $pass && empty($user["id"])) {
        $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $login ]);

        if (!$student["code"]) {
            $error = "bunday ID raqamli talaba topilmadi";
        } else if ($pass != $student["passport_serial_number"]) {
            $error = "passport seriya xato";
        } else if (!empty($student["code"])) {
            $insert_user_id = $db->insert("users", [
                "role" => "student",
                "first_name" => $student["first_name"],
                "last_name" => $student["last_name"],
                "login" => $login,
                "password" => md5(md5(encode($pass))),
                "password_encrypted" => encode($pass),
                "phone" => $student["phone_1"],
                "student_code" => $student["code"],
                "password_sended_time" => date("Y-m-d H:i:s"),
                // "permissions" => ($student_permissions ? json_encode($student_permissions) : NULL)
            ]);

            
            if ($insert_user_id > 0) {
                $user = $db->assoc("SELECT * FROM users WHERE id = ?", [ $insert_user_id ]);
            } else {
                $user = [];
            }
            
            $session = md5($env->getIp() . $env->getIpViaProxy() . $env->getUserAgent());
            $sessionArr = $db->assoc("SELECT * FROM cms_sessions WHERE session_id = ?", [ $session ]);

            if (!empty($sessionArr["session_id"])) {
                $sql_arr = [];
                $movings = ++$user["movings"];

                if ($user["sestime"] < (time() - 300)) {
                    $movings = 1;
                    $sql_arr["sestime"] = time();
                }

                if ($user["place"] != $headmod) {
                    $sql_arr["place"] = "/login";
                }

                $sql_arr["movings"] = $movings;
                $sql_arr["lastdate"] = time();

                $db->update("cms_sessions", $sql_arr, [
                    "session_id" => $session
                ]);
            } else {
                $db->insert("cms_sessions", [
                    "session_id" => $session,
                    "ip" => $env->getIp(),
                    "ip_via_proxy" => $env->getIpViaProxy(),
                    "browser" => $env->getUserAgent(),
                    "lastdate" => time(),
                    "sestime" => time(),
                    "place" => "/login"
                ]);
            }

            // exit($session);
        
            // Cookie ni o'rnatish
            $cuid = base64_encode($user['id']);
            $cups = md5(encode($pass));
            addCookie("cuid", $cuid);
            addCookie("cups", $cups);
        
            header("Location: /");
        }
    } else {
        $db->update("users", [
            // "password" => md5(md5(encode($password))),
            // "password_encryped" => encode($password),
            "failed_login" => 0,
            // "blocked_time" => date("Y.m.d H:i:s", time() - 60),
            "sestime" => time()
        ], [
            "id" => $user['id']
        ]);

        $session = md5($env->getIp() . $env->getIpViaProxy() . $env->getUserAgent());
        $sessionArr = $db->assoc("SELECT * FROM cms_sessions WHERE session_id = ?", [ $session ]);

        if (!empty($sessionArr["session_id"])) {
            $sql_arr = [];
            $movings = ++$user["movings"];

            if ($user["sestime"] < (time() - 300)) {
                $movings = 1;
                $sql_arr["sestime"] = time();
            }

            if ($user["place"] != $headmod) {
                $sql_arr["place"] = "/login";
            }

            $sql_arr["movings"] = $movings;
            $sql_arr["lastdate"] = time();

            $db->update("cms_sessions", $sql_arr, [
                "session_id" => $session
            ]);
        } else {
            $db->insert("cms_sessions", [
                "session_id" => $session,
                "ip" => $env->getIp(),
                "ip_via_proxy" => $env->getIpViaProxy(),
                "browser" => $env->getUserAgent(),
                "lastdate" => time(),
                "sestime" => time(),
                "place" => "/login"
            ]);
        }

        // exit($session);
    
        // Cookie ni o'rnatish
        $cuid = base64_encode($user['id']);
        $cups = md5(encode($pass));
        addCookie("cuid", $cuid);
        addCookie("cups", $cups);
    
        header("Location: /");
    }

    // if ($login && $pass && empty($user["id"])) {
    //     if (!$error) $error = "id yoki passport seriya xato!";
    // } else {
        
    // }
}

$page_name = "Kirish";

// include "system/head.php";
?>

<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
     	
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="keywords" content="" />
	<meta name="author" content="" />
	<meta name="robots" content="" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="format-detection" content="telephone=no">
	
	<!-- PAGE TITLE HERE -->
	<title>Kirish</title>
	<!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="theme/vora/images/favicon.png" />
	<link href="theme/vora/vendor/jquery-nice-select/css/nice-select.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons"rel="stylesheet">
    <link href="theme/vora/css/style.css" rel="stylesheet">

</head>

<body class="h-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div id="idPageContents">
                    <!--id-Section-->
                    <section class="sptb">
                        <div class="container customerpage">
                            <div class="row">
                                <div class="col-lg-5 col-xl-5 col-md-6 d-block mx-auto">
                                    <div class="card">
                                        <div class="card-body p-6">
                                            <div class="mb-6">
                                                <h5 class="fs-25 font-weight-semibold">Kirish</h5>
                                            </div>
                                            <div class="single-page customerpage">
                                                <div class="wrapper wrapper2 box-shadow-0">
                                                    <form action="" method="POST" class="needs-validation was-validated" novalidate id="reg-form">
                                                        <? if (!empty($error)) { ?>
                                                            <h5 class="text-danger text-center"><?=$error?></h5>
                                                        <? } ?>

                                                        <div class="was-validated">
                                                            <label>ID</label>
                                                            <input 
                                                                type="text"
                                                                name="login"
                                                                value="<?=($_POST['login'] ? $_POST['login'] : "")?>"
                                                                placeholder="Talaba ID"
                                                                required="required"
                                                                class="form-control <?=($login_error ? 'is-invalid' : '')?>"
                                                            >
                                                            <div class="invalid-feedback">
                                                                <?=($login_error ? $login_error : "iltimos ID raqamingizni kiriting")?>
                                                            </div>
                                                        </div>

                                                        <div class="was-validated">
                                                            <label>Passport seriyasi hamda raqami</label>
                                                            <input 
                                                                type="text"
                                                                name="pass"
                                                                value="<?=($_POST["pass"] ? $_POST["pass"] : "")?>"
                                                                placeholder="AA1234567"
                                                                required="required"
                                                                class="form-control <?=($pass_error ? 'is-invalid' : '')?>"
                                                            >
                                                            <div class="invalid-feedback">
                                                                <?=($pass_error ? $pass_error : "iltimos Passport seriya hamda raqamingizni kiriting")?>
                                                            </div>
                                                        </div>
                                                        
                                                        <input type="hidden" name="submit" value="submit">

                                                        <div class="submit mt-3">
                                                            <button type="submit" class="btn btn-primary btn-block fs-16">Kirish</button>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!--/id-Section-->

                    </style>
                </div>
            </div>
        </div>
    </div>

<!--**********************************
	Scripts
***********************************-->
<!-- Required vendors -->
<script src="theme/vora/vendor/global/global.min.js"></script>
<script src="theme/vora/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

<script src="theme/vora/js/custom.min.js"></script>
<script src="theme/vora/js/dlabnav-init.js"></script>

</body>
</html>