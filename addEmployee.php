<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

include "modules/menuPages.php";

if ($_REQUEST["type"] == $url[0]){
   
    validate(["role", "first_name", "last_name", "login", "password"]);
    
    include "modules/uploadFile.php";

    // users_roles
    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $employee_id = $db->insert("users", [
            // "id" => $_POST["id"],
            "phone" => NULL,
            "code" => NULL,
            "role" => $_POST["role"],
            "first_name" => $_POST["first_name"],
            "last_name" => $_POST["last_name"],
            "login" => $_POST["login"],
            "password" => md5(md5(encode($_POST["password"]))), // password uchun
            "password_encrypted" => encode($_POST["password"]), // password encrypted uchun
            "password_sended_time" => date("Y-m-d H:i:s"),
            // "failed_login" => 0,
            // "blocked_time" => date("Y.m.d H:i:s", time() - 60),
            "ip" => $env->getIp(),
            "ip_via_proxy" => $env->getIpViaProxy(),
            "browser" => $env->getUserAgent(),
            "lastdate" => time(),
            "sestime" => time(),
            "permissions" => ($_POST["permissions"] ? json_encode($_POST["permissions"]) : NULL)
        ]);

        header("Location: employeesList/?page=1");
    } else {
        // header("Content-type: text/plain");
        // print_r($errors);
        // exit;
    }
}

include "system/head.php";

$breadcump_title_1 = "Xodimlar";
$breadcump_title_2 = "yangi xodim qo'shish";
$form_title = "Yangi xodim qo'shish";
?>

<!--**********************************
    Content body start
***********************************-->
<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)"><?=$breadcump_title_1?></a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?=$breadcump_title_2?></a></li>
            </ol>
        </div>

        <!-- row -->
        <div class="row">
            <div class="col-lg-8 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform:none;"><?=$form_title?></h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form action="/<?=$url[0]?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="type" value="<?=$url[0]?>">
                                <div class="form-row">
                                    
                                    <?=getError("first_name")?>
                                    <div class="form-group col-12">
                                        <label>Ismi</label>
                                        <input type="text" name="first_name" class="form-control" placeholder="Ismi" value="<?=$_POST["first_name"]?>">
                                    </div>

                                    <?=getError("last_name")?>
                                    <div class="form-group col-12">
                                        <label>Familiyasi</label>
                                        <input type="text" name="last_name" class="form-control" placeholder="Familiyasi" value="<?=$_POST["last_name"]?>">
                                    </div>

                                    <?=getError("role")?>
                                    <div class="form-group col-12">
                                        <label>Lavozimi:</label>
                                        <input type="text" name="role" class="form-control" placeholder="lavozimi" value="<?=$_POST["role"]?>">
                                    </div>

                                    <?=getError("login")?>
                                    <div class="form-group col-12">
                                        <label>Logini:</label>
                                        <input type="text" name="login" class="form-control" placeholder="logini" value="<?=$_POST["login"]?>">
                                    </div>
                                    
                                    <?=getError("password")?>
                                    <div class="form-group col-12">
                                        <label>Paroli:</label>
                                        <input type="password" name="password" class="form-control" placeholder="Paroli" value="<?=$_POST["password"]?>">
                                    </div>
                                    
                                </div>

                                <table class="table table-hover table-bordered table-responsive-sm">
                                        <tbody>
                                            <tr class="bg-dark text-white">
                                                <th><label class="form-check-label" for="checkAll">Hamasini belgilash</label></th>
                                                <td><input type="checkbox" class="form-check-input" id="checkAll"></td>
                                            </tr>
                                            <? foreach($menu_pages as $key => $val) { ?>
                                            <tr>
                                                <th><?=$val["name"]?></th>
                                                <td>
                                                    <div class="col">
                                                        <div class="form-check custom-checkbox mb-3 check-xs">
                                                            <input name="permissions[]" value="<?=$val["page"]?>" type="checkbox" class="checkitem form-check-input" id="page_<?=$val["page"]?>" >
                                                            <label class="form-check-label" for="page_<?=$val["page"]?>"></label>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <? } ?>
                                        </tbody>
                                    </table>

                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <button type="submit" class="btn btn-primary">Qo'shish</button>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->
    </div>
</div>
<!--**********************************
    Content body end
***********************************-->

<?
include "system/scripts.php";
?>

<script>
    $("#checkAll").change(function(){
        $(".checkitem").prop('checked', $(this).prop('checked'));
    });
</script>

<?
include "system/end.php";
?>