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
    
    if (!$login) {
        $login_error = "Telefon raqamni kiritishni unutdingiz!";
    }

    if (!$pass) {
        $pass_error = "Parolni kiritishni unutdingiz!";
    }

    if ($login && $pass) {
        $teacher = $db->assoc("SELECT * FROM teachers WHERE login = ?", [ $login ]);

        if (empty($teacher["id"])) {
            $error = "bunday Telefon raqamli ustoz topilmadi";
        } else if (md5(md5(encode($pass))) != $teacher["password"]) {
            $error = "Login yoki parol xato";
        } else {
            $user = $db->assoc("SELECT * FROM users WHERE teacher_id = ?", [ $teacher["id"] ]);
    
            if (empty($user["id"])) {
                $insert_user_id = $db->insert("users", [
                    "role" => "teacher",
                    "first_name" => $teacher["first_name"],
                    "last_name" => $teacher["last_name"],
                    "login" => trim($login),
                    "password" => md5(md5(encode($pass))),
                    "password_encrypted" => encode($pass),
                    // "phone" => $teacher["phone_1"],
                    "teacher_id" => $teacher["id"],
                    "password_sended_time" => date("Y-m-d H:i:s"),
                    // "permissions" => ($teacher_permissions ? json_encode($teacher_permissions) : NULL)
                ]);
    
                if ($insert_user_id > 0) {
                    $user = $db->assoc("SELECT * FROM users WHERE id = ?", [ $insert_user_id ]);
                } else {
                    $user = [];
                }
            }

            if (!empty($user["id"])) {
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
        }
    }
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
                                                            <label>Telefon raqami</label>
                                                            <input 
                                                                type="text"
                                                                name="login"
                                                                value="<?=($_POST['login'] ? $_POST['login'] : "")?>"
                                                                placeholder=""
                                                                required="required"
                                                                id="phone-mask"
                                                                class="form-control <?=($login_error ? 'is-invalid' : '')?>"
                                                            >
                                                            <div class="invalid-feedback">
                                                                <?=($login_error ? $login_error : "iltimos Telefon raqamingizni kiriting")?>
                                                            </div>
                                                        </div>

                                                        <div class="was-validated mt-2">
                                                            <label>Parol</label>
                                                            <input 
                                                                type="text"
                                                                name="pass"
                                                                value="<?=($_POST["pass"] ? $_POST["pass"] : "")?>"
                                                                placeholder=""
                                                                required="required"
                                                                class="form-control <?=($pass_error ? 'is-invalid' : '')?>"
                                                            >
                                                            <div class="invalid-feedback">
                                                                <?=($pass_error ? $pass_error : "Parolni kiriting")?>
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
<?
include "system/scripts.php";
?>
<script>
    $("#phone-mask").on("input keyup", function(e){
        var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,2})(\d{0,3})(\d{0,2})(\d{0,2})/);
        // console.log(x);
        e.target.value = !x[2] ? '+' + (x[1].length == 3 ? x[1] : '998') : '+' + x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
    });

    $("#phone-mask").keyup();
</script>

<script src="theme/vora/vendor/global/global.min.js"></script>
<script src="theme/vora/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

<script src="theme/vora/js/custom.min.js"></script>
<script src="theme/vora/js/dlabnav-init.js"></script>

</body>
</html>