<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}
include "modules/menuPages.php";


if ($_REQUEST["type"] == $url[0]){
    
    validate(["first_name", "last_name", "password", "email"]);
    
    include "modules/uploadFile.php";

    $uploadedTeacherImage = uploadFile("image", "files/upload/3x4", ["jpg","jpeg","png"]);
    // $uploadedPassportImage = uploadFile("passport_scan_image", "files/upload/passport", ["jpg","jpeg","png","pdf"]);
    // $uploadedDiplomImage = uploadFile("diplom_scan_image", "files/upload/diplom", ["jpg","jpeg","png","pdf"]);

    $have_teacher = $db->assoc("SELECT * FROM teachers WHERE login = ?", [ $_POST["phone_1"] ]);

    if(!$have_teacher) {
        if (!$errors["forms"] || count($errors["forms"]) == 0) {
            $teacher_id = $db->insert("teachers", [
                // "id" => $_POST["id"],
                "creator_user_id" => $user_id,
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
            ]);
    
            if($teacher_id > 0) {
                $employee_id = $db->insert("users", [
                    "teacher_id" => $teacher_id,
                    "role" => "teacher",
                    "first_name" => $_POST["first_name"],
                    "last_name" => $_POST["last_name"],
                    "login" => $_POST["phone_1"],
                    "password" => md5(md5(encode($_POST["password"]))), // password uchun
                    "password_encrypted" => encode($_POST["password"]), // password encrypted uchun
                    "password_sended_time" => date("Y-m-d H:i:s"),
                    // "permissions" => ($teacher_permissions ? json_encode($teacher_permissions) : NULL)
                ]);
    
                // foreach($_POST["sciences"] as $science_id) {
                //     $added_teacher_sciences = $db->insert("teacher_sciences", [
                //         "creator_user_id" => $user_id,
                //         "teacher_id" => $teacher_id,
                //         "science_id" => $science_id,
                //     ]);
                // }
            }
    
           if($_POST["department"] != "") {
                $departmen_teacher_id = $db->insert("department_teachers", [
                    "creator_user_id" => $user_id,
                    "department_id" => $_POST["department"],
                    "teacher_id" => $teacher_id,
                ]);
           }
            header("Location: teachersList/?page=1");
        } else {
            // header("Content-type: text/plain");
            // print_r($errors);
            // exit;
        }
    } else {
        $have_login = 'Bunday loginga ega o\'qituvchi mavjud';
        // $have_password = 'Bunday parolga ega o\'qituvchi mavjud';
    }
}

include "system/head.php";

$breadcump_title_1 = "O'qituchilar";
$breadcump_title_2 = "yangi o'qituvchi qo'shish";
$form_title = "Yangi o'qituvchi qo'shish";
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
                                    <?=getError("department")?>
                                    <div class="form-group col-12">
                                        <label>Kafedra nomi:</label>
                                        <select name="department" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                           <option selected="" value=""></option>
                                            <? foreach ($db->in_array("SELECT * FROM departments") as $department) { ?>
                                                <option value="<?=$department["id"]?>"><?=$department["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

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

                                    <?=getError("image")?>
                                    <div class="form-group col-12">
                                        <label for="formFile" class="form-label">Rasm yuklash (jpg, jpeg, png)</label>
                                        <input class="form-control" type="file" name="image" id="formFile" accept="image/*">
                                    </div>

                                    <?=getError("father_first_name")?>
                                    <div class="form-group col-12">
                                        <label>Otasining ismi:</label>
                                        <input type="text" name="father_first_name" class="form-control" placeholder="Otasining ismi" value="<?=$_POST["father_first_name"]?>">
                                    </div>
                                    
                                    <?=getError("birth_date")?>
                                    <div class="form-group col-12">
                                        <label>Tug'ilgan sanasi:</label>
                                        <input type="date" name="birth_date" class="form-control" placeholder="Tug'ilgan sanasi" value="<?=$_POST["birth_date"]?>">
                                    </div>

                                    <?=getError("sex")?>
                                    <div class="form-group col-12">
                                        <label>Jinsi:</label>
                                        <div class="col-sm-9">
                                            <div class="form-check">
                                                <input id="man" class="form-check-input" type="radio" name="sex" value="erkak" checked="">
                                                <label for="man" class="form-check-label">
                                                    Erkak
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input id="woman" class="form-check-input" type="radio" name="sex" value="ayol">
                                                <label for="woman" class="form-check-label">
                                                    Ayol
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <?=getError("phone_1")?>
                                    <div class="form-group col-12">
                                        <label>Telefon raqami</label>
                                        <input type="text" name="phone_1" class="form-control" placeholder="Telefon raqami" value="<?=$_POST["phone_1"]?>" id="phone-mask">
                                    </div>

                                    <?=getError("phone_2")?>
                                    <div class="form-group col-12">
                                        <label>Qoshimcha telefon raqami</label>
                                        <input type="text" name="phone_2" class="form-control" placeholder="Qoshimcha telefon raqami" value="<?=$_POST["phone_2"]?>" id="phone-mask2">
                                    </div>

                                    <?=getError("region_id")?>
                                    <div class="form-group col-12">
                                        <label>Qaysi viloyatdan ekanligi:</label>
                                        <select name="region_id" class="form-control default-select form-control-lg">
                                            <option selected="" value="">Qaysi viloyatdan ekanligi</option>
                                            <? foreach ($db->in_array("SELECT * FROM regions") as $region) { ?>
                                                <option value="<?=$region["id"]?>"><?=$region["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                    
                                    <?=getError("address")?>
                                    <div class="form-group col-12">
                                        <label>Manzili</label>
                                        <textarea type="text" name="address" class="form-control" placeholder="Manzili"><?=$_POST["address"]?></textarea>
                                    </div>
                                    
                                    <?=getError("nation")?>
                                    <div class="form-group col-12">
                                        <label>Fuqaroligi</label>
                                        <textarea type="text" name="nation" class="form-control" placeholder="Fuqaroligi"><?=$_POST["nation"]?></textarea>
                                    </div>

                                    <?=getError("contract_id")?>
                                    <div class="form-group col-12">
                                        <label>Shartnoma raqami</label>
                                        <input type="text" name="contract_id" class="form-control" placeholder="Shartnoma raqami" value="<?=$_POST["contract_id"]?>">
                                    </div>
                                    
                                    <?=getError("contract_expire_date")?>
                                    <div class="form-group col-12">
                                        <label>Shartnoma muddati:</label>
                                        <input type="date" name="contract_expire_date" class="form-control" placeholder="Shartnoma muddati" value="<?=$_POST["contract_expire_date"]?>">
                                    </div>

                                    <?=getError("role")?>
                                    <div class="form-group col-12">
                                        <label>Lavozimi:</label>
                                        <input type="text" name="role" class="form-control" placeholder="lavozimi" value="<?=$_POST["role"]?>">
                                    </div>

                                    <?=getError("email")?>
                                    <div class="form-group col-12">
                                        <label>Elektron pochta</label>
                                        <input type="text" name="email" class="form-control" placeholder="Elektron pochta" value="<?=$_POST["email"]?>">
                                    </div>

                                    <?=getError("academic_title")?>
                                    <div class="form-group col-12">
                                        <label>ilmiy unvoni:</label>
                                        <input type="text" name="academic_title" class="form-control" placeholder="ilmiy unvoni" value="<?=$_POST["academic_title"]?>">
                                    </div>

                                    <?=getError("diplom_otm")?>
                                    <div class="form-group col-12">
                                        <label>Diplom qaysi otm dan ekanligi:</label>
                                        <input type="text" name="diplom_otm" class="form-control" placeholder="diplom qaysi otm dan ekanligi" value="<?=$_POST["diplom_otm"]?>">
                                    </div>

                                    <?=getError("diplom_direction")?>
                                    <div class="form-group col-12">
                                        <label>Diplom yonalishi:</label>
                                        <input type="text" name="diplom_direction" class="form-control" placeholder="diplom yonalishi" value="<?=$_POST["diplom_direction"]?>">
                                    </div>
                                    
                                    <?=getError("monthly_salary")?>
                                    <div class="form-group col-12">
                                        <label>Oylik maoshi:</label>
                                         <input type="text" id="price-input" name="monthly_salary" class="form-control" placeholder="oylik maoshi" value="<?=$_POST["monthly_salary"]?>">
                                    </div>

                                    <?=getError("state_id")?>
                                    <div class="form-group col-12">
                                        <label>Shtati:</label>
                                        <select name="state_id" class="form-control default-select form-control-lg">
                                            <option selected="" value="">Shtatini tanlang</option>
                                            <? foreach ($db->in_array("SELECT * FROM states") as $state) { ?>
                                                <option value="<?=$state["id"]?>"><?=$state["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                    
                                    <?=getError("state_unit_id")?>
                                    <div class="form-group col-12">
                                        <label>Shtati birligi:</label>
                                        <select name="state_unit_id" class="form-control default-select form-control-lg">
                                            <option selected="" value="">Shtatini birligini tanlang</option>
                                            <? foreach ($db->in_array("SELECT * FROM state_unit") as $state_unit) { ?>
                                                <option value="<?=$state_unit["id"]?>"><?=$state_unit["state_unit_name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    
                                    <?=getError("password")?>
                                    <?="<h4 class='text-danger'>$have_password</h4>";?>
                                    <div class="form-group col-12">
                                        <label>Paroli:</label>
                                        <input type="password" name="password" class="form-control" placeholder="Paroli" value="<?=$_POST["password"]?>">
                                    </div>
                                </div>

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

<?
include "system/end.php";
?>