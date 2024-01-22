<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

// exit("texnik ishlar ketyabdi");

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_REQUEST["page"];
if (empty($page)) $page = 1;

$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : null;
if (!$id) {echo"error id not found";return;}

$student = $db->assoc("SELECT * FROM students WHERE id = ?", [ $id ]);
if (!$student["code"]) {echo"error (student not found)";exit;}

$setting = $db->assoc("SELECT * FROM settings");

if (!empty($setting["from_year_privilege"]) && trim($student["year_of_admission"]) - 0 <= $setting["from_year_privilege"] && $systemUser["id"] != 1) {
    $disabled_privilege_1 = "disabled";
} else {
    $disabled_privilege_1 = null;
}

if (!empty($setting["from_year_privilege"]) && trim($student["year_of_admission"]) + 1 <= $setting["from_year_privilege"] && $systemUser["id"] != 1) {
    $disabled_privilege_2 = "disabled";
} else {
    $disabled_privilege_2 = null;
}

if (!empty($setting["from_year_privilege"]) && trim($student["year_of_admission"]) + 2 <= $setting["from_year_privilege"] && $systemUser["id"] != 1) {
    $disabled_privilege_3 = "disabled";
} else {
    $disabled_privilege_3 = null;
}

if (!empty($setting["from_year_privilege"]) && trim($student["year_of_admission"]) + 3 <= $setting["from_year_privilege"] && $systemUser["id"] != 1) {
    $disabled_privilege_4 = "disabled";
} else {
    $disabled_privilege_4 = null;
}

// Disabled shartnoma summasi
if (!empty($setting["from_year_amount"]) && trim($student["year_of_admission"]) - 0 <= trim($setting["from_year_amount"]) && $systemUser["id"] != 1) {
    $disabled_amount_1 = "disabled";
} else {
    $disabled_amount_1 = null;
}

if (!empty($setting["from_year_amount"]) && trim($student["year_of_admission"]) + 1 <= trim($setting["from_year_amount"]) && $systemUser["id"] != 1) {
    $disabled_amount_2 = "disabled";
} else {
    $disabled_amount_2 = null;
}

if (!empty($setting["from_year_amount"]) && trim($student["year_of_admission"]) + 2 <= trim($setting["from_year_amount"]) && $systemUser["id"] != 1) {
    $disabled_amount_3 = "disabled";
} else {
    $disabled_amount_3 = null;
}

if (!empty($setting["from_year_amount"]) && trim($student["year_of_admission"]) + 3 <= trim($setting["from_year_amount"]) && $systemUser["id"] != 1) {
    $disabled_amount_4 = "disabled";
} else {
    $disabled_amount_4 = null;
}
// $block_privilege = explode("-", $setting["year_privilege"]);

// foreach ($db->in_array("SELECT * FROM students") as $student_change) {
//     $contract_date = preg_split('/ +/', $student_change["contract_id"], null, PREG_SPLIT_NO_EMPTY);
//     if(!empty($contract_date[1])) {
//         $unix_date = strtotime($contract_date[1]);
//     }

//     $db->update("students", [   
//         "contract_id" => $contract_date[0],
//         "contract_date" => $unix_date ? date("Y-m-d", $unix_date) : null, 
//     ], [
//         "code" => $student_change["code"]
//     ]);
// }


if ($_REQUEST["type"] == $url[0]){
    validate(["code", "first_name", "last_name", "father_first_name", "passport_serial_number", "birth_date", "sex", "direction_id", "learn_type_id", "phone_1", "phone_2", "region_id", "payment_method", "teacher_id", "status", "year_of_admission", "course_id", "exam_lang"]);
    $leranName = $db->assoc("SELECT name FROM learn_types WHERE id = ?", [ $_POST["learn_type_id"] ]);

    include "modules/uploadFile.php";
    include "modules/menuPages.php";

    $uploadedStudentImage = uploadFileWithUpdate("image", "files/upload/3x4", ["jpg","jpeg","png"], false, false, $student["image_id"]);
    $uploadedPassportImage = uploadFileWithUpdate("passport_scan_image", "files/upload/passport", ["jpg","jpeg","png", "pdf"], false, false, $student["passport_image_id"]);
    $uploadedDiplomImage = uploadFileWithUpdate("diplom_scan_image", "files/upload/diplom", ["jpg","jpeg","png", "pdf"], false, false, $student["diplom_image_id"]);
    if(mb_strlen($student["code"]) > 7) {
        $request = $db5->assoc("SELECT * FROM requests WHERE passport_serial_number = ?", [ $student["passport_serial_number"] ]);

        $direction = $db->assoc("SELECT * FROM directions WHERE id = ?", [ $_POST["direction_id"] ]);

        $new_code = idCode2($direction["number"], $request["id"], $_POST["exam_lang"], $leranName["name"]);
    } else {
        $new_code = $student["code"];
    }

    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $db->update("students", [
            "reg_type" => $_POST["reg_type"],
            "code" => $new_code,
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
            "status" => $_POST["status"] == "ha" ? 1 : 0,
            "contract_id" => $_POST["contract_id"],
            "contract_date" => $_POST["contract_date"],

            "annual_contract_amount" => $disabled_amount_1 ? $student["annual_contract_amount"] : str_replace(",", "", $_POST["annual_contract_amount"]),
            "annual_contract_amount2" => $disabled_amount_2 ? $student["annual_contract_amount2"] : str_replace(",", "", $_POST["annual_contract_amount2"]),
            "annual_contract_amount3" => $disabled_amount_3 ? $student["annual_contract_amount3"] : str_replace(",", "", $_POST["annual_contract_amount3"]),
            "annual_contract_amount4" => $disabled_amount_4 ? $student["annual_contract_amount4"] : str_replace(",", "", $_POST["annual_contract_amount4"]),

            "annual_contract_amount_note" => $_POST["annual_contract_amount_note"],
            "annual_contract_amount_note_2" => $_POST["annual_contract_amount_note_2"],
            "annual_contract_amount_note_3" => $_POST["annual_contract_amount_note_3"],
            "annual_contract_amount_note_4" => $_POST["annual_contract_amount_note_4"],

            "privilege_amount" => $disabled_privilege_1 ? $student["privilege_amount"] : str_replace(",", "", $_POST["privilege_amount"]),
            "privilege_amount2" => $disabled_privilege_2 ? $student["privilege_amount2"] : str_replace(",", "", $_POST["privilege_amount2"]),
            "privilege_amount3" => $disabled_privilege_3 ? $student["privilege_amount3"] : str_replace(",", "", $_POST["privilege_amount3"]),
            "privilege_amount4" => $disabled_privilege_4 ? $student["privilege_amount4"] : str_replace(",", "", $_POST["privilege_amount4"]),

            "privilege_note" => ($_POST["privilege_note"] ? $_POST["privilege_note"] : NULL),
            "payment_method" => $_POST["payment_method"],
            "passport_serial_number" => $_POST["passport_serial_number"],
            "pinfl" => $_POST["pinfl"],
            "teacher_id" => $_POST["teacher_id"],
            "passport_image_id" => $uploadedPassportImage["file_id"],
            "diplom_image_id" => $uploadedDiplomImage["file_id"],
            "exam_lang" => $_POST["exam_lang"],
            "card_number" => $_POST["card_number"],
            "group_id" => $_POST["group_id"]
        ], [
            "id" => $student["id"]
        ]);

        $updated_student = $db->assoc("SELECT * FROM students WHERE id = ?", [ $student["id"] ]);

        if ($updated_student["code"] != $student["code"]) {
            $db->update("payments", [
                "code" => $updated_student["code"]
            ], [
                "student_id" => $student["id"]
            ]);
        }
        
        $employee_id = $db->update("users", [
            "first_name" => $_POST["first_name"],
            "last_name" => $_POST["last_name"],
            "login" => $new_code,
            "student_code" => (int)$new_code,
            "password" => md5(md5(encode($_POST["passport_serial_number"]))), // password uchun
            "password_encrypted" => encode($_POST["passport_serial_number"]), // password encrypted uchun
            "password_sended_time" => date("Y-m-d H:i:s"),
            "permissions" => ($student_permissions ? json_encode($student_permissions) : NULL)
        ], [
            "student_code" => $student["code"]
        ]);

        $db5->update("requests", [   
            "code" => (int)$new_code,
            "first_name" => $_POST["first_name"],
            "last_name" => $_POST["last_name"],
            "father_first_name" => $_POST["father_first_name"],
            "sex" => $_POST["sex"],
            "birth_date" => $_POST["birth_date"],
            "phone_1" => $_POST["phone_1"],
            "phone_2" => $_POST["phone_2"],
            "region" => null,
            "direction_id" => $_POST["direction_id"],
            "learn_type" => $leranName["name"],
            "passport_serial_number" => $_POST["passport_serial_number"],
            "exam_lang" => $_POST["exam_lang"],
        ], [
            "id" => $request["id"]
        ]);

        header("Location: /studentsList/?page=" . $page);
        exit;
    } else {
        // header("Content-type: text/plain");
        // print_r($errors);
        // exit;
    }
}

if ($_REQUEST["type"] == "deleteStudent") {
    $db->delete("students", $student["code"], "code");
    $db->delete("group_users", $student["code"], "student_code");

    $image_file = fileArr($student["image_id"]);
    if ($student["image_id"] > 0) {
        if ($image_file["thumb_image_id"] > 0) {
            delete_image($image_file["thumb_image_id"]);
        }
        delete_file($student["image_id"]);
    }

    $passport_image = fileArr($student["passport_image_id"]);
    if ($student["passport_image_id"] > 0) {
        if ($passport_image["thumb_image_id"] > 0) {
            delete_image($passport_image["thumb_image_id"]);
        }
        delete_file($student["passport_image_id"]);
    }

    $diplom_image = fileArr($student["diplom_image_id"]);
    if ($student["diplom_image_id"] > 0) {
        if ($diplom_image["thumb_image_id"] > 0) {
            delete_image($diplom_image["thumb_image_id"]);
        }
        delete_file($student["diplom_image_id"]);
    }

    header("Location: /studentsList/?page=" . $page);
    exit;
}

include "system/head.php";

$breadcump_title_1 = "Talabalar";
$breadcump_title_2 = "Talabani tahrirlash";
$form_title = "Talabani tahrirlash";
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
                            <form action="/<?=$url[0]?>/?code=<?=$student["code"]?>" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="type" value="<?=$url[0]?>">
                                <input type="hidden" name="page" value="<?=$page?>">
                                <input type="hidden" name="id" value="<?=$student["id"]?>">
                                <input type="hidden" name="type" value="<?=$url[0]?>">

                                <div class="form-row">
                                    <?=getError("reg_type")?>
                                    <div class="form-group col-12">
                                        <label>O'qish turi</label>

                                        <select name="reg_type" class="form-control default-select form-control-lg">
                                            <option value="oddiy" <?=$student["reg_type"] == "oddiy" ? 'selected=""' : ''?>>Oddiy</option>
                                            <option value="oqishni-kochirish" <?=$student["reg_type"] == "oqishni-kochirish" ? 'selected=""' : ''?>>O'qishni kochirish</option>
                                            <option value="ikkinchi-mutaxassislik" <?=$student["reg_type"] == "ikkinchi-mutaxassislik" ? 'selected=""' : ''?>>Ikkinchi mutaxassislik</option>
                                        </select>
                                    </div>

                                    <?=getError("code")?>
                                    <div class="form-group col-12">
                                        <label>code</label>
                                        <input type="text" name="code" class="form-control" placeholder="code" value="<?=$student["code"]?>" readonly>
                                    </div>
                                    
                                    <?=getError("first_name")?>
                                    <div class="form-group col-12">
                                        <label>Ismi</label>
                                        <input type="text" name="first_name" class="form-control" placeholder="Ismi" value="<?=$student["first_name"]?>">
                                    </div>

                                    <?=getError("last_name")?>
                                    <div class="form-group col-12">
                                        <label>Familiyasi</label>
                                        <input type="text" name="last_name" class="form-control" placeholder="Familiyasi" value="<?=$student["last_name"]?>">
                                    </div>

                                    
                                    <?
                                    if ($student["image_id"] > 0) {
                                        $image = fileArr($student["image_id"]);

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
                                        <input type="text" name="father_first_name" class="form-control" placeholder="Otasinnig ismi" value="<?=$student["father_first_name"]?>">
                                    </div>

                                    <?=getError("birth_date")?>
                                    <div class="form-group col-12">
                                        <label>Tug'ilgan sana</label>
                                        <input type="date" name="birth_date" class="form-control" placeholder="Tug'ilgan sana" value="<?=$student["birth_date"]?>">
                                    </div>

                                    <?=getError("sex")?>
                                    <div class="form-group col-12">
                                        <label>Jinsi:</label>
                                        <div class="col-sm-9">
                                            <div class="form-check">
                                                <input id="label_erkak" class="form-check-input" type="radio" name="sex" value="erkak"  <?=('erkak' == $student["sex"] ? 'checked=""' : '')?>>
                                                <label for="label_erkak" class="form-check-label">
                                                    Erkak
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input id="label_ayol" class="form-check-input" type="radio" name="sex" value="ayol"  <?=('ayol' == $student["sex"] ? 'checked=""' : '')?>>
                                                <label for="label_ayol" class="form-check-label">
                                                    Ayol
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <?=getError("direction_id")?>
                                    <div class="form-group col-12">
                                        <label>Ta'lim yo'nalishi:</label>
                                        <select name="direction_id" class="form-control default-select form-control-lg">
                                            <? foreach ($db->in_array("SELECT * FROM directions") as $direction) { ?>
                                                <option value="<?=$direction["id"]?>" <?=($direction["id"] == $student["direction_id"] ? 'selected=""' : '')?>><?=$direction["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                    
                                    <?=getError("learn_type_id")?>
                                    <div class="form-group col-12">
                                        <label>Ta'lim shakli:</label>
                                        <select name="learn_type_id" class="form-control default-select form-control-lg">
                                            <? foreach ($db->in_array("SELECT * FROM learn_types") as $learn_type) { ?>
                                                <option value="<?=$learn_type["id"]?>" <?=($learn_type["id"] == $student["learn_type_id"] ? 'selected=""' : '')?>><?=$learn_type["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                    
                                    <?=getError("season")?>
                                    <div class="form-group col-12">
                                        <label>Mavsumi:</label>
                                        <select name="season" class="form-control default-select form-control-lg">
                                            <option value="yozgi" <?=('yozgi' == $student["season"] ? 'selected=""' : '')?>>Yozgi</option>
                                            <option value="qishki" <?=('qishki' == $student["season"] ? 'selected=""' : '')?>>Qishki</option>
                                        </select>
                                    </div>

                                    <?=getError("exam_lang")?>
                                    <div class="form-group col-12">
                                        <label>Ta'lim tili:</label>
                                        <select name="exam_lang" class="form-control default-select form-control-lg">
                                            <option value="uz" <?=('uz' == $student["exam_lang"] ? 'selected=""' : '')?>>Uz</option>
                                            <option value="ru" <?=('ru' == $student["exam_lang"] ? 'selected=""' : '')?>>Ru</option>
                                        </select>
                                    </div>

                                    <?=getError("phone_1")?>
                                    <div class="form-group col-12">
                                        <label>Telefon raqami</label>
                                        <input type="text" name="phone_1" class="form-control" placeholder="Telefon raqami" value="<?=$student["phone_1"]?>" id="phone-mask">
                                    </div>
                                    
                                    <?=getError("phone_2")?>
                                    <div class="form-group col-12">
                                        <label>Qoshimcha telefon raqami</label>
                                        <input type="text" name="phone_2" class="form-control" placeholder="Qoshimcha telefon raqami" value="<?=$student["phone_2"]?>" id="phone-mask2">
                                    </div>

                                    <?=getError("region_id")?>
                                    <div class="form-group col-12">
                                        <label>Viloyati:</label>
                                        <select name="region_id" class="form-control default-select form-control-lg">
                                            <? foreach ($db->in_array("SELECT * FROM regions") as $region) { ?>
                                                <option value="<?=$region["id"]?>" <?=($region["id"] == $student["region_id"] ? 'selected=""' : '')?>><?=$region["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("address")?>
                                    <div class="form-group col-12">
                                        <label>Manzili</label>
                                        <textarea type="text" name="address" class="form-control" placeholder="Manzili"><?=$student["address"]?></textarea>
                                    </div>
                                    
                                    <?=getError("nation")?>
                                    <div class="form-group col-12">
                                        <label>Fuqaroligi</label>
                                        <textarea type="text" name="nation" class="form-control" placeholder="Fuqaroligi"><?=$student["nation"]?></textarea>
                                    </div>

                                    <?=getError("status")?>
                                    <div class="form-group col-12">
                                        <label>Statusi:</label>
                                        <select name="status" class="form-control default-select form-control-lg">
                                            <option value="yoq" <?=("0" == $student["status"] ? 'selected=""' : '')?>>O'qimayapti</option>
                                            <option value="ha" <?=("1" == $student["status"] ? 'selected=""' : '')?>>O'qiyapti</option>
                                        </select>
                                    </div>

                                    <?=getError("year_of_admission")?>
                                    <div class="form-group col-12">
                                        <label>Qabul yilli (2022)</label>
                                        <input type="text" maxlength="4" name="year_of_admission" class="form-control" placeholder="Qabul yili" value="<?=$student["year_of_admission"]?>">
                                    </div>
                                    
                                    <?=getError("course_id")?>
                                    <div class="form-group col-12">
                                        <label>Nechanchi kursligi (1)</label>
                                        <select name="course_id" class="form-control default-select form-control-lg">
                                            <? foreach ($coursesArr as $course_id => $value) { ?>
                                                <option value="<?=$course_id?>" <?=($course_id == $student["course_id"] ? 'selected=""' : '')?> ><?=$value?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("contract_id")?>
                                    <div class="form-group col-12">
                                        <label>Shartnoma raqami</label>
                                        <input type="text" name="contract_id" class="form-control" placeholder="Shartnoma raqami" value="<?=$student["contract_id"]?>">
                                    </div>

                                    <?=getError("contract_date")?>
                                    <div class="form-group col-12">
                                        <label>Shartnoma sanasi</label>
                                        <input type="date" name="contract_date" class="form-control" placeholder="Shartnoma sanasi" value="<?=$student["contract_date"]?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <?=getError("annual_contract_amount")?>
                                    <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                        <label>Shartnoma summasi (1-kurs uchun) (<?=($student["year_of_admission"])?> yil):</label>
                                        <input type="text" <?=$disabled_amount_1 ? $disabled_amount_1 : ''?> name="<?=$disabled_amount_1 ? '' : 'annual_contract_amount'?>" class="form-control" placeholder="Shartnoma summasi (1-kurs uchun)" value="<?=number_format($student["annual_contract_amount"])?>" id="price-input">
                                    </div>

                                    <?=getError("annual_contract_amount_note")?>
                                    <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                        <label>Shartnoma izohi (1-kurs uchun) (<?=($student["year_of_admission"])?> yil):</label>
                                        <input type="text" name="<?=$disabled_amount_1 ? '' : 'annual_contract_amount_note'?>" class="form-control" placeholder="Shartnoma izohi (1-kurs uchun)" value="<?=$student["annual_contract_amount_note"]?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <?=getError("annual_contract_amount2")?>
                                    <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                        <label>Shartnoma summasi (2-kurs uchun) (<?=($student["year_of_admission"]+1)?> yil):</label>
                                        <input type="text" <?=$disabled_amount_2 ? $disabled_amount_2 : ''?> name="<?=$disabled_amount_2 ? '' : 'annual_contract_amount2'?>" class="form-control" placeholder="Shartnoma summasi (2-kurs uchun)" value="<?=number_format($student["annual_contract_amount2"])?>" id="price-input">
                                    </div>

                                    <?=getError("annual_contract_amount_note_2")?>
                                    <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                        <label>Shartnoma izohi (2-kurs uchun) (<?=($student["year_of_admission"])?> yil):</label>
                                        <input type="text" name="<?=$disabled_amount_1 ? '' : 'annual_contract_amount_note_2'?>" class="form-control" placeholder="Shartnoma izohi (2-kurs uchun)" value="<?=$student["annual_contract_amount_note_2"]?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <?=getError("annual_contract_amount3")?>
                                    <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                        <label>Shartnoma summasi (3-kurs uchun) (<?=($student["year_of_admission"]+2)?> yil):</label>
                                        <input type="text" <?=$disabled_amount_3 ? $disabled_amount_3 : ''?> name="<?=$disabled_amount_3 ? '' : 'annual_contract_amount3'?>" class="form-control" placeholder="Shartnoma summasi (3-kurs uchun)" value="<?=number_format($student["annual_contract_amount3"])?>" id="price-input">
                                    </div>

                                    <?=getError("annual_contract_amount_note_3")?>
                                    <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                        <label>Shartnoma izohi (3-kurs uchun) (<?=($student["year_of_admission"])?> yil):</label>
                                        <input type="text" name="<?=$disabled_amount_1 ? '' : 'annual_contract_amount_note_3'?>" class="form-control" placeholder="Shartnoma izohi (3-kurs uchun)" value="<?=$student["annual_contract_amount_note_3"]?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <?=getError("annual_contract_amount4")?>
                                    <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                        <label>Shartnoma summasi (4-kurs uchun) (<?=($student["year_of_admission"]+3)?> yil):</label>
                                        <input type="text" <?=$disabled_amount_4 ? $disabled_amount_4 : ''?> name="<?=$disabled_amount_4 ? '' : 'annual_contract_amount4'?>" class="form-control" placeholder="Shartnoma summasi (4-kurs uchun)" value="<?=number_format($student["annual_contract_amount4"])?>" id="price-input">
                                    </div>

                                    <?=getError("annual_contract_amount_note_4")?>
                                    <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                        <label>Shartnoma izohi (4-kurs uchun) (<?=($student["year_of_admission"])?> yil):</label>
                                        <input type="text" name="<?=$disabled_amount_1 ? '' : 'annual_contract_amount_note_4'?>" class="form-control" placeholder="Shartnoma izohi (4-kurs uchun)" value="<?=$student["annual_contract_amount_note_4"]?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <?=getError("privilege_amount")?>
                                    <div class="form-group col-12">
                                        <label>Imtiyoz summasi (1-kurs uchun) (<?=($student["year_of_admission"])?> yil):</label>
                                        <input type="text" <?=$disabled_privilege_1 ? $disabled_privilege_1 : ''?> name="<?=$disabled_privilege_1 ? '' : 'privilege_amount'?>" class="form-control" placeholder="1-kurs imtiyoz summasi" value="<?=number_format($student["privilege_amount"])?>">
                                    </div>
                                    
                                    <?=getError("privilege_amount2")?>
                                    <div class="form-group col-12">
                                        <label>Imtiyoz summasi (2-kurs uchun) (<?=($student["year_of_admission"]+1)?> yil):</label>
                                        <input type="text" <?=$disabled_privilege_2 ? $disabled_privilege_2 : ''?> name="<?=$disabled_privilege_2 ? '' : 'privilege_amount2'?>" class="form-control" placeholder="2-kurs imtiyoz summasi" value="<?=number_format($student["privilege_amount2"])?>">
                                    </div>
                                    
                                    <?=getError("privilege_amount3")?>
                                    <div class="form-group col-12">
                                        <label>Imtiyoz summasi (3-kurs uchun) (<?=($student["year_of_admission"]+2)?> yil):</label>
                                        <input type="text" <?=$disabled_privilege_3 ? $disabled_privilege_3 : ''?> name="<?=$disabled_privilege_3 ? '' : 'privilege_amount3'?>" class="form-control" placeholder="3-kurs imtiyoz summasi" value="<?=number_format($student["privilege_amount3"])?>">
                                    </div>
                                    
                                    <?=getError("privilege_amount4")?>
                                    <div class="form-group col-12">
                                        <label>Imtiyoz summasi (4-kurs uchun) (<?=($student["year_of_admission"]+3)?> yil):</label>
                                        <input type="text" <?=$disabled_privilege_4 ? $disabled_privilege_4 : ''?> name="<?=$disabled_privilege_4 ? '' : 'privilege_amount4'?>" class="form-control" placeholder="4-kurs imtiyoz summasi" value="<?=number_format($student["privilege_amount4"])?>">
                                    </div>
                                    
                                    <?=getError("privilege_note")?>
                                    <div class="form-group col-12">
                                        <label>Imtiyoz sababi</label>
                                        <input type="text" name="privilege_note" class="form-control" placeholder="imtiyoz sababi" value="<?=$student["privilege_note"]?>">
                                    </div>

                                    <?=getError("payment_method")?>
                                    <div class="form-group col-12">
                                        <label>To'lov uslubi:</label>
                                        <select name="payment_method" class="form-control default-select form-control-lg">
                                            <option value="monthly" <?=($student["payment_method"] == "monthly" ? 'selected=""' : "")?>>oyma oy</option>
                                            <option value="half" <?=($student["payment_method"] == "half" ? 'selected=""' : "")?>>yarim</option>
                                            <option value="full" <?=($student["payment_method"] == "full" ? 'selected=""' : "")?>>to'liq</option>
                                        </select>
                                    </div>

                                    <?=getError("passport_serial_number")?>
                                    <div class="form-group col-12">
                                        <label>Passport raqamlari:</label>
                                        <input type="text" name="passport_serial_number" class="form-control" placeholder="Passport raqamlari" value="<?=$student["passport_serial_number"]?>">
                                    </div>

                                    <?=getError("pinfl")?>
                                    <div class="form-group col-12">
                                        <label>PINFL:</label>
                                        <input type="text" name="pinfl" class="form-control" placeholder="PINFL" value="<?=$student["pinfl"]?>">
                                    </div>

                                    <?=getError("teacher_id")?>
                                    <div class="form-group col-12">
                                        <label>Ustozi:</label>
                                        <select name="teacher_id" class="form-control default-select form-control-lg">
                                        <? foreach ($db->in_array("SELECT * FROM teachers") as $teacher) { ?>
                                            <option value="<?=$teacher["id"]?>" <?=($teacher["id"] == $student["teacher_id"] ? 'selected=""' : '')?>><?=$teacher["first_name"] . " " . $teacher['last_name']?></option>
                                        <? } ?>                                        
                                        </select>
                                    </div>

                                    <?=getError("group_id")?>
                                    <div class="form-group col-12">
                                        <label>Guruhi:</label>
                                        <select name="group_id" data-actions-box="true" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow">
                                        <? foreach ($db->in_array("SELECT * FROM groups_list") as $group) { ?>
                                            <option value="<?=$group["id"]?>" <?=($group["id"] == $student["group_id"] ? 'selected=""' : '')?>><?=$group["name"]?></option>
                                        <? } ?>                                        
                                        </select>
                                    </div>

                                    <?
                                    if ($student["passport_image_id"] > 0) {
                                        $passport_image = fileArr($student["passport_image_id"]);

                                        if (in_array($passport_image["type"], ["png","jpg","jpeg"])) {
                                            if ($passport_image["file_folder"]) {
                                                echo '<image src="'.$passport_image["file_folder"].'" width="125px">';
                                            }
                                        }
                                    }
                                    ?>

                                    <?=getError("passport_scan_image")?>
                                    <div class="form-group col-12">
                                        <label for="passport_scan_image" class="form-label">Passport nusxasi (jpg, jpeg, png, pdf)</label>
                                        <input class="form-control" type="file" name="passport_scan_image" id="passport_scan_image">
                                    </div>

                                    <?
                                    if ($student["diplom_image_id"] > 0) {
                                        $diplom_image = fileArr($student["diplom_image_id"]);

                                        if (in_array($diplom_image["type"], ["png","jpg","jpeg"])) {
                                            if ($diplom_image["file_folder"]) {
                                                echo '<image src="'.$diplom_image["file_folder"].'" width="125px">';
                                            }
                                        }
                                    }
                                    ?>
                                    
                                    <?=getError("diplom_scan_image")?>
                                    <div class="form-group col-12">
                                        <label for="diplom_scan_image" class="form-label">Diplom nusxasi (jpg, jpeg, png, pdf)</label>
                                        <input class="form-control" type="file" name="diplom_scan_image" id="diplom_scan_image">
                                    </div>

                                    <?=getError("login")?>
                                    <div class="form-group col-12">
                                        <label>Logini</label>
                                        <input type="text" name="login" readonly class="form-control" placeholder="logini" value="<?=$student["code"]?>">
                                    </div>
                                    
                                    <?=getError("password")?>
                                    <div class="form-group col-12">
                                        <label>Paroli</label>
                                        <input type="text" name="password" readonly class="form-control" placeholder="paroli" value="<?=$student["passport_serial_number"]?>">
                                    </div>

                                    <?=getError("card_number")?>
                                    <div class="form-group col-12">
                                        <label>Karta raqami (turniket)</label>
                                        <input type="text" name="card_number" class="form-control" placeholder="" value="<?=$student["card_number"]?>">
                                    </div>
                                </div>

                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <a href="/addPayment?id=<?=$student["id"]?>" class="btn btn-success">To'lov qo'shish</a>
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