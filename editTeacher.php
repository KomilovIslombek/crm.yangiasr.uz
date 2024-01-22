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

$teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [$id]);
if (!$teacher["id"]) {echo"error (teacher not found)";exit;}

include "modules/menuPages.php";

if ($_REQUEST["type"] == $url[0]){
    // validate(["department","first_name", "last_name", "father_first_name", "birth_date", "sex", "phone_1", "phone_2", "region_id", "address", "nation", "contract_id", "contract_expire_date", "role", "academic_title", "diplom_otm", "diplom_direction", "monthly_salary", "state_id", "state_unit_id"]);
    validate(["first_name", "last_name"]);

    $have_teacher = false;
   
    $user = $db->assoc("SELECT * FROM users WHERE teacher_id = ?", [ $teacher["id"] ]);
    if (!empty($user["id"])) {
        $db->update("users", [
            "first_name" => $_POST["first_name"],
            "last_name" => $_POST["last_name"],
            "login" => $_POST["phone_1"],
            "password" => md5(md5(encode($_POST["password"]))), // password uchun
            "password_encrypted" => encode($_POST["password"]), // password encrypted uchun
        ], [
            "id" => $user["id"]
        ]);
    } else {
        $have_teacher = $db->assoc("SELECT * FROM users WHERE login = ?", [ trim($_POST["login"]) ]);

        if (!empty($have_teacher["id"])) {
            $have_login = 'Bunday loginga ega o\'qituvchi mavjud';
        }
    }


    include "modules/uploadFile.php";

    $uploadedTeacherImage = uploadFileWithUpdate("image", "files/upload/3x4", ["jpg","jpeg","png"], false, false, $teacher["image_id"]);
    
    if(!$have_teacher) {
        if (!$errors["forms"] || count($errors["forms"]) == 0) {
            // print_r($_POST[]);
            // exit;
            $teacher_id = $db->update("teachers", [
                // "id" => $_POST["id"],
                "first_name" => $_POST["first_name"],
                "last_name" => $_POST["last_name"],
                "image_id" => $uploadedTeacherImage["file_id"],
                "father_first_name" => $_POST["father_first_name"],
                "birth_date" => $_POST["birth_date"] ==  '' ? null : $_POST["birth_date"],
                "sex" => $_POST["sex"],
                "phone_1" => $_POST["phone_1"] == "+998" ? null : $_POST["phone_1"],
                "phone_2" => $_POST["phone_2"] == "+998" ? null : $_POST["phone_2"],
                "region_id" => $_POST["region_id"]  == "" ? null : $_POST["region_id"],
                "address" => $_POST["address"],
                "nation" => $_POST["nation"],
                "contract_id" => $_POST["contract_id"] ==  '' ? null : $_POST["contract_id"],
                "contract_expire_date" => $_POST["contract_expire_date"] ==  '' ? null : $_POST["contract_expire_date"],
                "role" => $_POST["role"],
                "email" => $_POST["email"],
                "academic_title" => $_POST["academic_title"],
                "diplom_otm" => $_POST["diplom_otm"],
                "diplom_direction" => $_POST["diplom_direction"],
                "monthly_salary" => $_POST["monthly_salary"] == '' ? null : str_replace(",", "", $_POST["monthly_salary"]),
                "state_id" => $_POST["state_id"] == "" ? null : $_POST["state_id"],
                "state_unit_id" => $_POST["state_unit_id"] == "" ? null : $_POST["state_unit_id"],
                "login" => $_POST["phone_1"],
                "password" => md5(md5(encode($_POST["password"]))), // password uchun
                "password_encrypted" => encode($_POST["password"]), // password encrypted uchun
            ], [
                "id" => $teacher["id"]
            ]);

        
            $employee_id = $db->update("users", [
                // "first_name" => $_POST["first_name"],
                // "last_name" => $_POST["last_name"],
                "login" => $_POST["login"],
                "password" => md5(md5(encode($_POST["password"]))), // password uchun
                "password_encrypted" => encode($_POST["password"]), // password encrypted uchun
                "password_sended_time" => date("Y-m-d H:i:s"),
                // "permissions" => ($teacher_permissions ? json_encode($teacher_permissions) : NULL)
            ], [
                "teacher_id" => $teacher["id"]
            ]);


            // $db->delete("teacher_sciences", $teacher["id"], "teacher_id");
            // foreach($_POST["sciences"] as $sciences_id) {
            //     $db->insert("teacher_sciences", [
            //         "creator_user_id" => $user_id,
            //         "teacher_id" => $teacher["id"],
            //         "science_id" => $sciences_id,
            //     ]);
            // }

            if($_POST["department"] == "") {
                $db->delete("department_teachers", $teacher["id"], "teacher_id");
            } else {
                $db->delete("department_teachers", $teacher["id"], "teacher_id");
            
                $departmen_teacher_id = $db->insert("department_teachers", [
                    "creator_user_id" => $user_id,
                    "department_id" => $_POST["department"],
                    "teacher_id" => $teacher["id"],
                ]);
            }
            header("Location: teachersList/?page=" . $page);
            exit;
        } else {
            // header("Content-type: text/plain");
            // print_r($errors);
            // exit;
        }
    }
}

if ($_REQUEST["type"] == "deleteteacher") {
    $db->delete("teachers", $teacher["id"], "id");
    // $db->delete("teacher_sciences", $teacher["id"], "teacher_id");
    $db->delete("users", $teacher["id"], "teacher_id");
    $db->delete("department_teachers", $teacher["id"], "teacher_id");
    $db->delete("group_teachers", $teacher["id"], "teacher_id");

    $image_file = fileArr($teacher["image_id"]);
    if ($teacher["image_id"] > 0) {
        if ($image_file["thumb_image_id"] > 0) {
            delete_image($image_file["thumb_image_id"]);
        }
        delete_file($teacher["image_id"]);
    }

    header("Location: /teachersList/?page=" . $page);
    exit;
}

include "system/head.php";

$breadcump_title_1 = "O'qituvchilar";
$breadcump_title_2 = "O'qituvchini tahrirlash";
$form_title = "O'qituvchini tahrirlash";
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
                                <input type="hidden" name="id" value="<?=$teacher["id"]?>">
                                <input type="hidden" name="type" value="<?=$url[0]?>">

                                <div class="form-row">
                                    <?=getError("department")?>
                                    <div class="form-group col-12">
                                        <label>Kafedra</label>
                                        <select name="department" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                           <option selected="" value=""></option>
                                           <? foreach ($db->in_array("SELECT * FROM departments") as $department) { 
                                                $get_teacher = $db->assoc("SELECT * FROM department_teachers WHERE teacher_id = ? AND department_id = ?", [ $teacher["id"], $department["id"] ]);?>
                                                <option value="<?=$department["id"]?>" <?=($department["id"] == $get_teacher["department_id"] ? 'selected=""' : '')?>><?=$department["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                    
                                    <?=getError("first_name")?>
                                    <div class="form-group col-12">
                                        <label>Ismi</label>
                                        <input type="text" name="first_name" class="form-control" placeholder="Ismi" value="<?=$teacher["first_name"]?>">
                                    </div>

                                    <?=getError("last_name")?>
                                    <div class="form-group col-12">
                                        <label>Familiyasi</label>
                                        <input type="text" name="last_name" class="form-control" placeholder="Familiyasi" value="<?=$teacher["last_name"]?>">
                                    </div>

                                    
                                    <?
                                    if ($teacher["image_id"] > 0) {
                                        $image = fileArr($teacher["image_id"]);

                                        if (in_array($image["type"], ["png","jpg","jpeg"])) {
                                            if ($image["file_folder"]) {
                                                echo '<image src="'.$image["file_folder"].'" width="125px">';
                                            }
                                        }
                                    }
                                    ?>

                                    <?=getError("image")?>
                                    <div class="form-group col-12">
                                        <label for="formFile" class="form-label">Rasm yuklash (jpg, jpeg, png)</label>
                                        <input class="form-control" type="file" name="image" id="formFile" accept="image/*">
                                    </div>

                                    <?=getError("father_first_name")?>
                                    <div class="form-group col-12">
                                        <label>Otasining ismi</label>
                                        <input type="text" name="father_first_name" class="form-control" placeholder="Otasinnig ismi" value="<?=$teacher["father_first_name"]?>">
                                    </div>

                                    <?=getError("birth_date")?>
                                    <div class="form-group col-12">
                                        <label>Tug'ilgan sana</label>
                                        <input type="date" name="birth_date" class="form-control" placeholder="Tug'ilgan sana" value="<?=$teacher["birth_date"]?>">
                                    </div>

                                    <?=getError("sex")?>
                                    <div class="form-group col-12">
                                        <label>Jinsi:</label>
                                        <div class="col-sm-9">
                                            <div class="form-check">
                                                <input id="label_erkak" class="form-check-input" type="radio" name="sex" value="erkak"  <?=('erkak' == $teacher["sex"] ? 'checked=""' : '')?>>
                                                <label for="label_erkak" class="form-check-label">
                                                    Erkak
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input id="label_ayol" class="form-check-input" type="radio" name="sex" value="ayol"  <?=('ayol' == $teacher["sex"] ? 'checked=""' : '')?>>
                                                <label for="label_ayol" class="form-check-label">
                                                    Ayol
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <?=getError("phone_1")?>
                                    <div class="form-group col-12">
                                        <label>Telefon raqami</label>
                                        <input type="text" name="phone_1" class="form-control" placeholder="Telefon raqami" value="<?=$teacher["phone_1"]?>" id="phone-mask">
                                    </div>
                                    
                                    <?=getError("phone_2")?>
                                    <div class="form-group col-12">
                                        <label>Qoshimcha telefon raqami</label>
                                        <input type="text" name="phone_2" class="form-control" placeholder="Qoshimcha telefon raqami" value="<?=$teacher["phone_2"]?>" id="phone-mask2">
                                    </div>

                                    <?=getError("region_id")?>
                                    <div class="form-group col-12">
                                        <label>Qaysi viloyatdan ekanligi:</label>
                                        <select name="region_id" class="form-control default-select form-control-lg">
                                            <option selected="" value="">Qaysi viloyatdan ekanligi</option>
                                            <? foreach ($db->in_array("SELECT * FROM regions") as $region) { ?>
                                                <option value="<?=$region["id"]?>" <?=($region["id"] == $teacher["region_id"] ? 'selected=""' : '')?>><?=$region["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("address")?>
                                    <div class="form-group col-12">
                                        <label>Manzili</label>
                                        <textarea type="text" name="address" class="form-control" placeholder="Manzili"><?=$teacher["address"]?></textarea>
                                    </div>
                                    
                                    <?=getError("nation")?>
                                    <div class="form-group col-12">
                                        <label>Fuqaroligi</label>
                                        <textarea type="text" name="nation" class="form-control" placeholder="Fuqaroligi"><?=$teacher["nation"]?></textarea>
                                    </div>

                                    <?=getError("contract_id")?>
                                    <div class="form-group col-12">
                                        <label>Shartnoma raqami</label>
                                        <input type="text" name="contract_id" class="form-control" placeholder="Shartnoma raqami" value="<?=$teacher["contract_id"]?>">
                                    </div>
                                    
                                    <?=getError("contract_expire_date")?>
                                    <div class="form-group col-12">
                                        <label>Shartnoma muddati:</label>
                                        <input type="date" name="contract_expire_date" class="form-control" placeholder="Shartnoma muddati" value="<?=$teacher["contract_expire_date"]?>">
                                    </div>

                                    <?=getError("role")?>
                                    <div class="form-group col-12">
                                        <label>Lavozimi:</label>
                                        <input type="text" name="role" class="form-control" placeholder="Lavozimi" value="<?=$teacher["role"]?>">
                                    </div>

                                    <?=getError("email")?>
                                    <div class="form-group col-12">
                                        <label>Elektron pochta</label>
                                        <input type="text" name="email" class="form-control" placeholder="Elektron pochta" value="<?=$teacher["email"]?>">
                                    </div>

                                    <?=getError("academic_title")?>
                                    <div class="form-group col-12">
                                        <label>ilmiy unvonii:</label>
                                        <input type="text" name="academic_title" class="form-control" placeholder="ilmiy unvonii" value="<?=$teacher["academic_title"]?>">
                                    </div>

                                    <?=getError("diplom_otm")?>
                                    <div class="form-group col-12">
                                        <label>Diplom qaysi otm dan ekanligi:</label>
                                        <input type="text" name="diplom_otm" class="form-control" placeholder="Diplom qaysi otm dan ekanligi" value="<?=$teacher["diplom_otm"]?>">
                                    </div>

                                    <?=getError("diplom_direction")?>
                                    <div class="form-group col-12">
                                        <label>Diplom yonalishi:</label>
                                        <input type="text" name="diplom_direction" class="form-control" placeholder="Diplom yonalishi" value="<?=$teacher["diplom_direction"]?>">
                                    </div>
                                    
                                    <?=getError("monthly_salary")?>
                                    <div class="form-group col-12">
                                        <label>Oylik maoshi:</label>
                                        <input type="text" id="price-input" name="monthly_salary" class="form-control" placeholder="oylik maoshi" value="<?=$teacher["monthly_salary"]?>">
                                    </div>

                                    <?=getError("state_id")?>
                                    <div class="form-group col-12">
                                        <label>Shtati:</label>
                                        <select name="state_id" class="form-control default-select form-control-lg">
                                            <option selected="" value="">Shtatini tanlang</option>
                                            <? foreach ($db->in_array("SELECT * FROM states") as $state) { ?>
                                                <option value="<?=$state["id"]?>" <?=($state["id"] == $teacher["state_id"] ? 'selected=""' : '')?>><?=$state["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("state_unit_id")?>
                                    <div class="form-group col-12">
                                        <label>Shtat birligi:</label>
                                        <select name="state_unit_id" class="form-control default-select form-control-lg">
                                            <option selected="" value="">Shtati birligi tanlang</option>
                                            <? foreach ($db->in_array("SELECT * FROM state_unit") as $state_unit) { ?>
                                                <option value="<?=$state_unit["id"]?>" <?=($state_unit["id"] == $teacher["state_unit_id"] ? 'selected=""' : '')?>><?=$state_unit["state_unit_name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>


                                    <?=getError("login")?>
                                    <?="<h4 class='text-danger'>$have_login</h4>";?>
                                    <div class="form-group col-12">
                                        <label>Logini (Telefon raqami)</label>
                                        <input type="text" name="login" class="form-control" placeholder="logini" value="<?=$teacher["login"]?>" id="phone-mask3" readonly="">
                                    </div>
                                    
                                    <?=getError("password")?>
                                    <?="<h4 class='text-danger'>$have_password</h4>";?>
                                    <div class="form-group col-12">
                                        <label>Paroli (<b id="generate-password" class="text-primary" style="cursor:pointer">generatsiya qilish</b>)</label>
                                        <input type="text" name="password" class="form-control" placeholder="paroli" value="<?=decode($teacher["password_encrypted"])?>" id="password-input">
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
    $("#phone-mask").on("input keyup", function(e){
        var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,2})(\d{0,3})(\d{0,2})(\d{0,2})/);
        // console.log(x);
        e.target.value = !x[2] ? '+' + (x[1].length == 3 ? x[1] : '998') : '+' + x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
    });

    $("#phone-mask").keyup();

    $("#phone-mask2").on("input keyup", function(e){
        var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,2})(\d{0,3})(\d{0,2})(\d{0,2})/);
        // console.log(x);
        e.target.value = !x[2] ? '+' + (x[1].length == 3 ? x[1] : '998') : '+' + x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
    });

    $("#phone-mask2").keyup();

    // 

    $("#phone-mask3").on("input keyup", function(e){
        var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,2})(\d{0,3})(\d{0,2})(\d{0,2})/);
        // console.log(x);
        e.target.value = !x[2] ? '+' + (x[1].length == 3 ? x[1] : '998') : '+' + x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
    });

    $("#phone-mask3").keyup();
        
    // Input mask

    $("#price-input").on("input", function(){
        var val = $(this).val().replaceAll(",", "").replaceAll(" ", "");
        console.log(val);

        if (val.length > 0) {    
            $(this).val(
                String(val).replace(/(.)(?=(\d{3})+$)/g,'$1,')
            );
        }
    });
</script>

<script>
    function generatePassword() {
        var length = 18,
            charset = "abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ0123456789",
            retVal = "";
        for (var i = 0, n = charset.length; i < length; ++i) {
            retVal += charset.charAt(Math.floor(Math.random() * n));
        }
        return retVal;
    }

    $("#generate-password").on("click", function(){
        var interval = window.setInterval(function(){
            $("#password-input").val(generatePassword());
        }, 20);
        setTimeout(function(){
            clearInterval(interval);
        }, 700);
    });
</script>

<?
include "system/end.php";
?>