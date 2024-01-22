<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_REQUEST["page"];
if (empty($page)) $page = 1;

$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : null;
if (!$id) {echo"error id not found";return;}

$employee = $db->assoc("SELECT * FROM users WHERE id = ?", [$id]);
if (!$employee["id"]) {echo"error (employee not found)";exit;}
 
include "modules/menuPages.php";

if ($_REQUEST["type"] == $url[0]){
    // echo "<pre>";
    // print_r($_POST);
    // echo "</pre>";
    // exit;

    validate(["role", "first_name", "last_name", "login", "password"]);
   
    // include "modules/uploadFile.php";

    // $uploadedEmployeeImage = uploadFileWithUpdate("image", "files/upload/3x4", ["jpg","jpeg","png"], false, false, $employee["image_id"]);

    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        
        $employee_id = $db->update("users", [
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
        ], [
            "id" => $employee["id"]
        ]);


        header("Location: employeesList/?page=" . $page);
        exit;
    } else {
        // header("Content-type: text/plain");
        // print_r($errors);
        // exit;
    }
}

if ($_REQUEST["type"] == "deleteemployee") {
    $db->delete("users", $employee["id"], "id");

    header("Location: /employeesList/?page=" . $page);
    exit;
}

include "system/head.php";

$breadcump_title_1 = "Xodimlar";
$breadcump_title_2 = "Xodimni tahrirlash";
$form_title = "Xodimni tahrirlash";
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
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform:none;"><?=$form_title?></h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form action="/<?=$url[0]?>" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="type" value="<?=$url[0]?>">
                                <input type="hidden" name="page" value="<?=$page?>">
                                <input type="hidden" name="id" value="<?=$employee["id"]?>">
                                <input type="hidden" name="type" value="<?=$url[0]?>">

                                <div class="form-row">
                                    
                                    <?=getError("first_name")?>
                                    <div class="form-group col-12">
                                        <label>Ismi</label>
                                        <input type="text" name="first_name" class="form-control" placeholder="Ismi" value="<?=$employee["first_name"]?>">
                                    </div>

                                    <?=getError("last_name")?>
                                    <div class="form-group col-12">
                                        <label>Familiyasi</label>
                                        <input type="text" name="last_name" class="form-control" placeholder="Familiyasi" value="<?=$employee["last_name"]?>">
                                    </div>

                                    <?=getError("role")?>
                                    <div class="form-group col-12">
                                        <label>Lavozimi</label>
                                        <input type="text" name="role" class="form-control" placeholder="Ismi" value="<?=$employee["role"]?>">
                                    </div>

                                    <?=getError("login")?>
                                    <div class="form-group col-12">
                                        <label>Logini</label>
                                        <input type="text" name="login" class="form-control" placeholder="logini" value="<?=$employee["login"]?>">
                                    </div>
                                    
                                    <?=getError("password")?>
                                    <div class="form-group col-12">
                                        <label>Paroli</label>
                                        <input type="text" name="password" class="form-control" placeholder="paroli" value="<?=decode($employee["password_encrypted"])?>">
                                    </div>
                                   
                                    <div class="table-responsive col-12">
                                        <table class="table table-hover table-bordered table-responsive-md">
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
                                                                <? if ($employee["permissions"] && in_array($val["page"], json_decode($employee["permissions"]))) { ?>
                                                                    <input name="permissions[]" value="<?=$val["page"]?>" type="checkbox" class="checkitem form-check-input" id="page_<?=$val["page"]?>" checked="">
                                                                <? } else { ?>
                                                                    <input name="permissions[]" value="<?=$val["page"]?>" type="checkbox" class="checkitem form-check-input" id="page_<?=$val["page"]?>">
                                                                <? } ?>
                                                                <label class="form-check-label" for="page_<?=$val["page"]?>"></label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <? } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <button type="submit" class="btn btn-primary">Saqlash</button>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--**********************************
    Content body end
***********************************-->

<?
include "system/scripts.php";
?>

<script>

    var numberOfChecked = $('input:checkbox:checked').length
    // var numberOfCheckbox = $('input:checkbox').length
    // console.log(numberOfCheckbox);

    // $('#el').is(':checked')
    if($(".checkitem:checked").length == $(".checkitem").length) {
        console.log(numberOfChecked);
        $('#checkAll').prop('checked', true);
    }

    $("#checkAll").change(function(){
        $(".checkitem").prop('checked', $(this).prop('checked'));
    });
    $(".checkitem").change(function() {
        if($(this).prop('checked') == false) {
            $('#checkAll').prop('checked', false);
        }
        if($(".checkitem:checked").length == $(".checkitem").length) {
            $('#checkAll').prop('checked', true);
            console.log("hello");
        }
    })
</script>

<?
include "system/end.php";
?>