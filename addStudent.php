<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}


if ($_REQUEST["type"] == $url[0]){
    $leranName = $db->assoc("SELECT name FROM learn_types WHERE id = ?", [ $_POST["learn_type_id"] ]);
    $directionName = $db->assoc("SELECT name FROM directions WHERE id = ?", [ $_POST["dirction_id"] ]);

    validate(["contract_id", "contract_date", "year_of_admission", "course_id", "direction_id", "exam_lang"]);

    include "modules/uploadFile.php";
    include "modules/menuPages.php";

    $uploadedStudentImage = uploadFile("image", "files/upload/3x4", ["jpg","jpeg","png"]);
    $uploadedPassportImage = uploadFile("passport_scan_image", "files/upload/passport", ["jpg","jpeg","png","pdf"]);
    $uploadedDiplomImage = uploadFile("diplom_scan_image", "files/upload/diplom", ["jpg","jpeg","png","pdf"]);

    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $request_id = $db5->insert("requests", [
            "step" => 3,
            "first_name" => $_POST["first_name"],
            "last_name" => $_POST["last_name"],
            "father_first_name" => $_POST["father_first_name"],
            "sex" => $_POST["sex"],
            "birth_date" => $_POST["birth_date"],
            "phone_1" => $_POST["phone_1"],
            "phone_2" => $_POST["phone_2"],
            "region" => null,
            "direction" => $directionName["name"],
            "direction_id" => $_POST["direction_id"],
            "learn_type" => $leranName["name"],
            "passport_serial_number" => $_POST["passport_serial_number"],
            "exam_lang" => $_POST["exam_lang"],
        ]);
        $code = idCode2($_POST["direction_id"], $request_id,  $_POST["exam_lang"], $leranName["name"]);
        
        $db5->update("requests", [   
            "code" => (int)$code,
        ], [
            "id" => $request_id
        ]);

        // Crm.yangiasr.uz start

        $db->insert("students", [
            "reg_type" => $_POST["reg_type"],
            "code" => $code,
            "creator_user_id" => $user_id,
            "first_name" => $_POST["first_name"],
            "last_name" => $_POST["last_name"],
            "image_id" => $uploadedStudentImage["file_id"],
            "father_first_name" => $_POST["father_first_name"],
            "year_of_admission" => $_POST["year_of_admission"],
            "course_id" => $_POST["course_id"],
            "birth_date" => $_POST["birth_date"],
            "sex" => $_POST["sex"],
            "direction_id" => $_POST["direction_id"],
            "learn_type_id" => $_POST["learn_type_id"],
            "season" => $_POST["season"],
            "phone_1" => $_POST["phone_1"],
            "phone_2" => $_POST["phone_2"],
            "region_id" => $_POST["region_id"],
            "address" => $_POST["address"],
            "nation" => $_POST["nation"],
            // "status" => 1,
            "contract_id" => $_POST["contract_id"],
            "contract_date" => $_POST["contract_date"],
            "annual_contract_amount" => str_replace(",", "", $_POST["annual_contract_amount"]),
            "annual_contract_amount_2" => str_replace(",", "", $_POST["annual_contract_amount_2"]),
            "annual_contract_amount_3" => str_replace(",", "", $_POST["annual_contract_amount_3"]),
            "annual_contract_amount_4" => str_replace(",", "", $_POST["annual_contract_amount_4"]),
            "privilege_amount" => str_replace(",", "", $_POST["privilege_amount"]),
            "privilege_amount2" => str_replace(",", "", $_POST["privilege_amount2"]),
            "privilege_amount3" => str_replace(",", "", $_POST["privilege_amount3"]),
            "privilege_amount4" => str_replace(",", "", $_POST["privilege_amount4"]),
            "privilege_note" => ($_POST["privilege_note"] ? $_POST["privilege_note"] : NULL),
            "payment_method" => $_POST["payment_method"],
            "passport_serial_number" => $_POST["passport_serial_number"],
            "pinfl" => $_POST["pinfl"],
            "teacher_id" => $_POST["teacher_id"],
            "passport_image_id" => $uploadedPassportImage["file_id"],
            "diplom_image_id" => $uploadedDiplomImage["file_id"],
        ]);

        $employee_id = $db->insert("users", [
            "student_code" => $code,
            "role" => "student",
            "login" => $code,
            "password" => md5(md5(encode($_POST["passport_serial_number"]))), // password uchun
            "password_encrypted" => encode($_POST["passport_serial_number"]), // password encrypted uchun
            "password_sended_time" => date("Y-m-d H:i:s"),
            // "permissions" => ($student_permissions ? json_encode($student_permissions) : NULL)
        ]);

        header("Location: studentsList/?page=1");
    } else {
        // header("Content-type: text/plain");
        // print_r($errors);
        // exit;
    }
}

include "system/head.php";

$breadcump_title_1 = "Talabalar";
$breadcump_title_2 = "yangi talaba qo'shish";
$form_title = "Yangi talaba qo'shish";
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
                            <form action="/<?=$url[0]?>"  method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="type" value="<?=$url[0]?>">
                                <div class="form-row">
                                    <!-- <div class="form-group col-12">
                                        <label>Code</label>
                                        <input type="text" name="code" class="form-control" placeholder="code" value="<?=$_POST["code"]?>">
                                    </div> -->

                                    <?=getError("reg_type")?>
                                    <div class="form-group col-12">
                                        <label>O'qish turi</label>

                                        <select name="reg_type" class="form-control default-select form-control-lg">
                                            <option value="oddiy" <?=$_POST["reg_type"] == "oddiy" ? 'selected=""' : ''?>>Oddiy</option>
                                            <option value="oqishni-kochirish" <?=$_POST["reg_type"] == "oqishni-kochirish" ? 'selected=""' : ''?>>O'qishni kochirish</option>
                                            <option value="ikkinchi-mutaxassislik" <?=$_POST["reg_type"] == "ikkinchi-mutaxassislik" ? 'selected=""' : ''?>>Ikkinchi mutaxassislik</option>
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
                                        <label>Otasini ismi:</label>
                                        <input type="text" name="father_first_name" class="form-control" placeholder="Otasini ismi" value="<?=$_POST["father_first_name"]?>">
                                    </div>
                                    
                                    <?=getError("birth_date")?>
                                    <div class="form-group col-12">
                                        <label>Tug'ilgan sanasi ismi:</label>
                                        <input type="date" name="birth_date" class="form-control" placeholder="Tu'gilgan sana" value="<?=$_POST["birth_date"]?>">
                                    </div>

                                    <?=getError("sex")?>
                                    <div class="form-group col-12">
                                        <label>Jinsi:</label>
                                        <div class="col-sm-9">
                                            <div class="form-check">
                                                <input id="label_erkak" class="form-check-input" type="radio" name="sex" value="erkak" checked="">
                                                <label for="label_erkak" class="form-check-label">
                                                    Erkak
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input id="label_ayol" class="form-check-input" type="radio" name="sex" value="ayol">
                                                <label for="label_ayol" class="form-check-label">
                                                    Ayol
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <?=getError("direction_id")?>
                                    <div class="form-group col-12">
                                        <label>Ta'lim yo'nalish:</label>
                                        <select name="direction_id" class="form-control default-select form-control-lg">
                                            <? foreach ($db->in_array("SELECT * FROM directions") as $direction) { ?>
                                                <option value="<?=$direction["id"]?>"><?=$direction["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("learn_type_id")?>
                                    <div class="form-group col-12">
                                        <label>Ta'lim shakli:</label>
                                        <select name="learn_type_id" class="form-control default-select form-control-lg">
                                            <? foreach ($db->in_array("SELECT * FROM learn_types") as $learn_type) { ?>
                                                <option value="<?=$learn_type["id"]?>"><?=$learn_type["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                    
                                    <?=getError("season")?>
                                    <div class="form-group col-12">
                                        <label>Mavsumi:</label>
                                        <select name="season" class="form-control default-select form-control-lg">
                                            <option value="yozgi">Yozgi</option>
                                            <option value="qishki">Qishki</option>
                                        </select>
                                    </div>
                                    
                                    <?=getError("exam_lang")?>
                                    <div class="form-group col-12">
                                        <label>Ta'lim tili:</label>
                                        <select name="exam_lang" class="form-control default-select form-control-lg">
                                            <option value="uz">Uz</option>
                                            <option value="ru">Ru</option>
                                        </select>
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
                                        <label>Viloyati:</label>
                                        <select name="region_id" class="form-control default-select form-control-lg">
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

                                    <?=getError("year_of_admission")?>
                                    <div class="form-group col-12">
                                        <label>Qabul yilli (2022):</label>
                                        <input type="text" maxlength="4" name="year_of_admission" required class="form-control" placeholder="Qabul yilli (2022)" value="<?=$_POST["year_of_admission"]?>">
                                    </div>
                                    
                                    <?=getError("course_id")?>
                                    <div class="form-group col-12">
                                        <label>Nechanchi kursligi (1):</label>
                                        <select name="course_id" class="form-control default-select form-control-lg">
                                            <? foreach ($coursesArr as $course_id => $value) { ?>
                                                <option value="<?=$course_id?>" ><?=$value?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("contract_id")?>
                                    <div class="form-group col-12">
                                        <label>Shartnoma raqami</label>
                                        <input type="text" name="contract_id" required class="form-control" placeholder="Shartnoma raqami" value="<?=$_POST["contract_id"]?>">
                                    </div>

                                    <?=getError("contract_date")?>
                                    <div class="form-group col-12">
                                        <label>Shartnoma sanasi:</label>
                                        <input type="date" name="contract_date" required class="form-control" placeholder="Shartnoma sanasi" value="<?=$_POST["contract_date"]?>">
                                    </div>
                                    
                                    <?=getError("annual_contract_amount")?>
                                    <div class="form-group col-12">
                                        <label>Shartnoma summasi (1-yil uchun)</label>
                                        <input type="text" name="annual_contract_amount" class="form-control" placeholder="Shartnoma summasi (1-yil uchun)" value="<?=$_POST["annual_contract_amount"]?>" id="price-input">
                                    </div>
                                    
                                    <?=getError("annual_contract_amount_2")?>
                                    <div class="form-group col-12">
                                        <label>Shartnoma summasi (2-chi kurs uchun)</label>
                                        <input type="text" name="annual_contract_amount_2" class="form-control" placeholder="Shartnoma summasi (2-chi kurs uchun)" value="<?=$_POST["annual_contract_amount_2"]?>" id="price-input">
                                    </div>
                                    
                                    <?=getError("annual_contract_amount_3")?>
                                    <div class="form-group col-12">
                                        <label>Shartnoma summasi (3-chi kurs uchun)</label>
                                        <input type="text" name="annual_contract_amount_3" class="form-control" placeholder="Shartnoma summasi (3-chi kurs uchun)" value="<?=$_POST["annual_contract_amount_3"]?>" id="price-input">
                                    </div>
                                    
                                    <?=getError("annual_contract_amount_4")?>
                                    <div class="form-group col-12">
                                        <label>Shartnoma summasi (4-chi kurs uchun)</label>
                                        <input type="text" name="annual_contract_amount_4" class="form-control" placeholder="Shartnoma summasi (4-chi kurs uchun)" value="<?=$_POST["annual_contract_amount_4"]?>" id="price-input">
                                    </div>

                                    <?=getError("privilege_amount")?>
                                    <div class="form-group col-12">
                                        <label>1-kurs Imtiyoz summasi</label>
                                        <input type="text" name="privilege_amount" class="form-control" placeholder="1-kurs imtiyoz summasi" id="price-input">
                                    </div>
                                    
                                    <?=getError("privilege_amount2")?>
                                    <div class="form-group col-12">
                                        <label>2-kurs Imtiyoz summasi</label>
                                        <input type="text" name="privilege_amount2" class="form-control" placeholder="2-kurs imtiyoz summasi" id="price-input">
                                    </div>
                                    
                                    <?=getError("privilege_amount3")?>
                                    <div class="form-group col-12">
                                        <label>3-kurs Imtiyoz summasi</label>
                                        <input type="text" name="privilege_amount3" class="form-control" placeholder="3-kurs imtiyoz summasi" id="price-input">
                                    </div>
                                    
                                    <?=getError("privilege_amount4")?>
                                    <div class="form-group col-12">
                                        <label>4-kurs Imtiyoz summasi</label>
                                        <input type="text" name="privilege_amount4" class="form-control" placeholder="4-kurs imtiyoz summasi" id="price-input">
                                    </div>

                                    <?=getError("privilege_note")?>
                                    <div class="form-group col-12">
                                        <label>Imtiyoz sababi</label>
                                        <input type="text" name="privilege_note" class="form-control" placeholder="imtiyoz sababi">
                                    </div>

                                    <?=getError("payment_method")?>
                                    <div class="form-group col-12">
                                        <label>To'lov uslubi:</label>
                                        <select name="payment_method" class="form-control default-select form-control-lg">
                                            <option value="monthly">oyma oy</option>
                                            <option value="half">yarim</option>
                                            <option value="full">to'liq</option>
                                        </select>
                                    </div>

                                    <?=getError("passport_serial_number")?>
                                    <div class="form-group col-12">
                                        <label>Passport raqamlari</label>
                                        <input type="text" name="passport_serial_number" class="form-control" placeholder="Passport raqamlari" value="<?=$_POST["passport_serial_number"]?>">
                                    </div>

                                    <?=getError("pinfl")?>
                                    <div class="form-group col-12">
                                        <label>PINFL</label>
                                        <input type="text" name="pinfl" class="form-control" placeholder="PINFL" value="<?=$_POST["PINFL"]?>">
                                    </div>

                                    <?=getError("teacher_id")?>
                                    <div class="form-group col-12">
                                        <label>Ustozi:</label>
                                        <select name="teacher_id" class="form-control default-select form-control-lg">
                                            <? foreach ($db->in_array("SELECT * FROM teachers") as $teacher) { ?>
                                                <option value="<?=$teacher["id"]?>"><?=$teacher["first_name"] . " " . $teacher["last_name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("passport_scan_image")?>
                                    <div class="form-group col-12">
                                        <label for="passport_scan_image" class="form-label">Passport nusxasi (jpg, jpeg, png, pdf)</label>
                                        <input class="form-control" type="file" name="passport_scan_image" id="passport_scan_image">
                                    </div>

                                    <?=getError("diplom_scan_image")?>
                                    <div class="form-group col-12">
                                        <label for="diplom_scan_image" class="form-label">Diplom nusxasi (jpg, jpeg, png, pdf)</label>
                                        <input class="form-control" type="file" name="diplom_scan_image" id="diplom_scan_image">
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

    $("*#price-input").on("input", function(){
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