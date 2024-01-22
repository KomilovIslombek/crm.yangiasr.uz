<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

// if (!empty($_GET["test"])) {
//     $payments = $db->in_array("SELECT * FROM payments WHERE id IN(4003, 4004, 4005, 4006, 4007, 4008)");

//     foreach ($payments as $payment) {
//         $db->insert("payments", $payment);
//     }
// }

$page = (int)$_GET['page'];
if (empty($page)) $page = 1;

$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : null;
if (!$id) {echo"error id not found";return;}

$setting = $db->assoc("SELECT * FROM settings");

$student = $db->assoc("SELECT * FROM students WHERE id = ?", [ $id ]);
if (empty($student["id"])) exit(http_response_code(404));

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

$direction = $db->assoc("SELECT * FROM directions WHERE id = ?", [ $student["direction_id"] ]);
$learn_type = $db->assoc("SELECT * FROM learn_types WHERE id = ?", [ $student["learn_type_id"] ]);
$region = $db->assoc("SELECT * FROM regions WHERE id = ?", [ $student["region_id"] ]);
$payment_method = $db->assoc("SELECT * FROM payment_methods WHERE id = ?", [ $student["payment_method"] ]);
$student_teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $student["teacher_id"] ]);
// $group_user = $db->assoc("SELECT * FROM group_users WHERE student_code = ?", [ $student["code"] ]);
$get_group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $student["group_id"] ]);

include "system/head.php";

$breadcump_title_1 = "Talaba:";
$breadcump_title_2 = "$student[first_name] $student[last_name]";

$image = fileArr($student["image_id"]);
if ($image["thumb_image_id"]) $image = image($image["thumb_image_id"]);

$passport_image = fileArr($student["passport_image_id"]);
if ($image["thumb_image_id"]) $image = image($image["thumb_image_id"]);

$diplom_image = fileArr($student["diplom_image_id"]);
if ($image["thumb_image_id"]) $image = image($image["thumb_image_id"]);

if ($_REQUEST["type"] == "changePrivilege"){
    if ($student["id"]) {
        $db->update("students", [
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
        ], [
            "id" => $student["id"]
        ]);

        header("Location: /viewStudent/?id=" . $student["id"]);
        exit;
    }
}

?>

<!--**********************************
    Content body start
***********************************-->

<div class="content-body">
    <div class="container-fluid">
        <!-- Add Order -->
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)"><?=$breadcump_title_1?></a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?=$breadcump_title_2?></a></li>
            </ol>
        </div>
        <div class="row">
                    <div class="col-xl-4">
                        <div  class="card" style="height:580px;">
                            <div class="card-header">
                                <img src="<?=$image["file_folder"]?>" class="w-100" height="380px">
                            </div>
                            <div class="card-body">
                                <div class="profile-blog mb-5">
                                    <!-- <img src="images/profile/1.jpg" alt="" class="img-fluid mt-4 mb-4 w-100 b-radius"> -->
                                    <h2 style="text-align:center;"><a href="javascript:void(0);" class="text-black"><?=$student["first_name"] . " <br> " . $student["last_name"]?></a></h2>
                                </div>
                            </div>
                        </div>
                        <div style="height: auto" class="change_in575 card">
                            <div style="height: auto" class="change_in575 card-body ">
                                <div class="profile-personal-info">
                                    <h4 class="text-primary mb-4">Talaba haqida ma'lumot</h4>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Code <span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$student["code"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">ID <span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$student["id"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Tug'ilgan yili <span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$student["birth_date"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Jinsi <span class="pull-right d-none d-sm-block">:</span></h5>
                                        </div>
                                        <div class="col-7"><span><?=$student["sex"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Ta'lim shakli <span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$direction["short_name"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">O'qish turi <span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$student["reg_type"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Ta'lim turi <span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$learn_type["name"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Telefon raqami<span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$student["phone_1"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Passport seriya<span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$student["passport_serial_number"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Millati<span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$student["nation"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Viloyati<span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$region["name"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Manzili<span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$student["address"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Qabul yili<span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=($student["year_of_admission"])?> - <?=($student["year_of_admission"]+1)?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Kursi<span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=($student["course_id"])?>-kurs</span>
                                        </div>
                                    </div>
                                    
                                    
                                   
                                    <!-- <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-3">
                                            <h5 class="f-w-500">Year Experience <span class="pull-right d-none d-sm-block">:</span></h5>
                                        </div>
                                        <div class="col-sm-9"><span>07 Year Experiences</span>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                        <style>
                            @media (max-width: 575px){
                                .change_in575{
                                    height: 620px;
                                }
                                .mb-4 {
                                    margin-bottom: 0.70rem !important;
                                }
                            }
                        </style>
                    </div>
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="profile-tab">
                                    <div class="custom-tab-1">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li class="nav-item" role="presentation"><a href="#my-posts" data-bs-toggle="tab" class="nav-link active show" aria-selected="true" role="tab">Shaxsiy ma'lumot</a>
                                            </li>
                                            <li class="nav-item" role="presentation"><a href="#about-me" data-bs-toggle="tab" class="nav-link" aria-selected="false" tabindex="-1" role="tab">Imtiyozi</a>
                                            </li>
                                            <li class="nav-item" role="presentation"><a href="#payments-me" data-bs-toggle="tab" class="nav-link" aria-selected="false" tabindex="-1" role="tab">To'lovlar</a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <!-- Shaxsiy ma'lumot -->
                                            <div id="my-posts" class="tab-pane fade active show" role="tabpanel">
                                                <div class="my-post-content pt-3">
                                                <div class="col-lg-12">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h4 class="card-title"><i class="fa-solid fa-user-graduate"></i> Talaba</h4>
                                                        </div>
                                                        <div class="card-body">
                                                            <div style="min-height: 300px;" class="table-responsive">
                                                                <table class="table table-striped table-hover table-bordered table-responsive-sm">
                                                                    <tbody>
                                                                        <tr>
                                                                            <th>Student code</th>
                                                                            <td> <?=$student["code"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Student ID</th>
                                                                            <td> <?=$student["id"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>F.I.SH</th>
                                                                            <td><?=$student["last_name"]. " " . $student["first_name"]. " " . $student["father_first_name"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Fuqarolik</th>
                                                                            <td><span class="badge badge-success light"><?=$nation["name"]?></span></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Passport seria</th>
                                                                            <td><?=$student["passport_serial_number"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Tug'ilgan sanasi</th>
                                                                            <td class="color-success"><?=$student["birth_date"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Jinsi</th>
                                                                            <td class="color-success"><?=$student["sex"]?> 
                                                                            <? if($student["sex"] == 'erkak') {?>
                                                                                <i class="fa-solid fa-person"></i>
                                                                            <?} else {?>
                                                                                <i class="fa-solid fa-person-dress"></i>
                                                                            <?}?>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            
                                                            <div class="table-responsive mt-5">
                                                                <table class="table table-striped table-hover table-bordered table-responsive-sm">
                                                                    <tbody>
                                                                        <tr>
                                                                            <th>Viloyati</th>
                                                                            <td> <?=$region["name"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Manzili</th>
                                                                            <td><?=$student["address"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Telefon raqami</th>
                                                                            <td><a href="tel: <?=$student["phone_1"]?>" class="badge badge-success light"><?=$student["phone_1"]?></a></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Ta'lim yo'nalishi</th>
                                                                            <td><?=$direction["short_name"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Ta'lim shakli</th>
                                                                            <td class="color-success"><?=$learn_type["name"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Shartnoma raqami</th>
                                                                            <td class="color-success"><?=$student["contract_id"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Shartnoma summasi</th>
                                                                            <td class="color-success"><?=$student["annual_contract_amount"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>To'lov uslubi</th>
                                                                            <td class="color-success"><?=$payment_method["name"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Pinfl</th>
                                                                            <td class="color-success"><?=$student["pinfl"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Ustozi</th>
                                                                            <td class="color-success"><?=$student_teacher["name"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Guruhi</th>
                                                                            <td class="color-success"><?=$get_group["name"]?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-responsive-md">
                                                                <thead>
                                                                    <tr>
                                                                        <th><strong><i class="fa fa-file mb-2"></i> Fayl nomi</strong></th>
                                                                        <th><strong><i class="fa fa-eye mb-2"></i> Ko'rish</strong></th>
                                                                        <th><strong><i class="fa fa-download mb-2"></i> Skachat qilib olish</strong></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><strong>Talab 3x4 rasmi</strong></td>
                                                                        <td>
                                                                            <div class="col-auto mb-2">
                                                                                <a href="<?=$image["file_folder"]?>" class="btn btn-outline-primary btn-xs">Talabani-rasmi.</a>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="col-auto mb-2">
                                                                                <a href="<?=$image["file_folder"]?>" download class="btn btn-outline-primary btn-xs">Talabani-rasmi.</a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Passport rasmi</strong></td>
                                                                        <td>
                                                                            <div class="col-auto mb-2">
                                                                                <a href="<?=$passport_image["file_folder"]?>" class="btn btn-outline-primary btn-xs">Passport-rasmi.</a>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="col-auto mb-2">
                                                                                <a href="<?=$passport_image["file_folder"]?>" download class="btn btn-outline-primary btn-xs">Passport-rasmi.</a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Diplom rasmi</strong></td>
                                                                        <td>
                                                                            <div class="col-auto mb-2">
                                                                                <a href="<?=$diplom_image["file_folder"]?>" class="btn btn-outline-primary btn-xs">Diplom-rasmi.</a>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="col-auto mb-2">
                                                                                <a href="<?=$diplom_image["file_folder"]?>" download class="btn btn-outline-primary btn-xs">Diplom-rasmi.</a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                            <!-- About me -->
                                            <div id="about-me" class="tab-pane fade" role="tabpanel">
                                                <div class="profile-about-me">
                                                    <div class="pt-4 border-bottom-1 pb-3">
                                                        <h4 class="text-primary"><i class="fa-sharp fa-solid fa-bolt"></i> Imtiyozi</h4>
                                                    </div>
                                                </div>
                                                <table style="text-align:center;" class="table table-hover table-responsive">
                                                    <thead class="border">
                                                        <tr>
                                                            <th scope="col">#code</th>
                                                            <th scope="col">#id</th>
                                                            <th scope="col">1-kurs uchun imtiyozi</th>
                                                            <th scope="col">2-kurs uchun imtiyozi</th>
                                                            <th scope="col">3-kurs uchun imtiyozi</th>
                                                            <th scope="col">4-kurs uchun imtiyozi</th>
                                                            <!-- <th scope="col">imtiyoz summasi</th> -->
                                                            <th scope="col">imtiyoz sababi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="hover-dark">
                                                            <td class="table-light" scope="row"><?=$student["code"]?></td>
                                                            <td class="table-light" scope="row"><?=$student["id"]?></td>
                                                            <td class="table-light"><?=number_format($student["privilege_amount"])?></td>
                                                            <td class="table-light"><?=number_format($student["privilege_amount2"])?></td>
                                                            <td class="table-light"><?=number_format($student["privilege_amount3"])?></td>
                                                            <td class="table-light"><?=number_format($student["privilege_amount4"])?></td>
                                                            <td class="table-light"><?=$student["privilege_note"]?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <form action="" method="POST" enctype="multipart/form-data">
                                                    <input type="hidden" name="type" value="changePrivilege">
                                                    <input type="hidden" name="id" value="<?=$student["id"]?>">

                                                    <!-- <div class="form-row">
                                                            <?=getError("privilege_percent")?>
                                                        <div class="form-group col-12">
                                                            <label>Imtiyoz summasi</label>
                                                            <input type="number" step="0.01" name="privilege_percent" class="form-control" placeholder="imtiyoz summasi" value="<?=$student["privilege_percent"]?>" id="price-input">
                                                        </div>
                                                    </div>  -->

                                                    <div class="form-row">
                                                        <?=getError("annual_contract_amount")?>
                                                        <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                                            <label>Shartnoma summasi (1-kurs uchun)<br>(<?=($student["year_of_admission"])?> yil):</label>
                                                            <input type="text" <?=$disabled_amount_1 ? $disabled_amount_1 : ''?> name="<?=$disabled_amount_1 ? '' : 'annual_contract_amount'?>" class="form-control" placeholder="Shartnoma summasi (1-kurs uchun)" value="<?=number_format($student["annual_contract_amount"])?>" id="price-input">
                                                        </div>

                                                        <?=getError("annual_contract_amount_note")?>
                                                        <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                                            <label>Shartnoma izohi (1-kurs uchun)<br>(<?=($student["year_of_admission"])?> yil):</label>
                                                            <input type="text" name="<?=$disabled_amount_1 ? '' : 'annual_contract_amount_note'?>" class="form-control" placeholder="Shartnoma izohi (1-kurs uchun)" value="<?=$student["annual_contract_amount_note"]?>">
                                                        </div>
                                                    </div>

                                                    <div class="form-row">
                                                        <?=getError("annual_contract_amount2")?>
                                                        <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                                            <label>Shartnoma summasi (2-kurs uchun)<br>(<?=($student["year_of_admission"]+1)?> yil):</label>
                                                            <input type="text" <?=$disabled_amount_2 ? $disabled_amount_2 : ''?> name="<?=$disabled_amount_2 ? '' : 'annual_contract_amount2'?>" class="form-control" placeholder="Shartnoma summasi (2-kurs uchun)" value="<?=number_format($student["annual_contract_amount2"])?>" id="price-input">
                                                        </div>

                                                        <?=getError("annual_contract_amount_note_2")?>
                                                        <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                                            <label>Shartnoma izohi (2-kurs uchun)<br>(<?=($student["year_of_admission"])?> yil):</label>
                                                            <input type="text" name="<?=$disabled_amount_1 ? '' : 'annual_contract_amount_note_2'?>" class="form-control" placeholder="Shartnoma izohi (2-kurs uchun)" value="<?=$student["annual_contract_amount_note_2"]?>">
                                                        </div>
                                                    </div>

                                                    <div class="form-row">
                                                        <?=getError("annual_contract_amount3")?>
                                                        <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                                            <label>Shartnoma summasi (3-kurs uchun)<br>(<?=($student["year_of_admission"]+2)?> yil):</label>
                                                            <input type="text" <?=$disabled_amount_3 ? $disabled_amount_3 : ''?> name="<?=$disabled_amount_3 ? '' : 'annual_contract_amount3'?>" class="form-control" placeholder="Shartnoma summasi (3-kurs uchun)" value="<?=number_format($student["annual_contract_amount3"])?>" id="price-input">
                                                        </div>

                                                        <?=getError("annual_contract_amount_note_3")?>
                                                        <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                                            <label>Shartnoma izohi (3-kurs uchun)<br>(<?=($student["year_of_admission"])?> yil):</label>
                                                            <input type="text" name="<?=$disabled_amount_1 ? '' : 'annual_contract_amount_note_3'?>" class="form-control" placeholder="Shartnoma izohi (3-kurs uchun)" value="<?=$student["annual_contract_amount_note_3"]?>">
                                                        </div>
                                                    </div>

                                                    <div class="form-row">
                                                        <?=getError("annual_contract_amount4")?>
                                                        <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                                            <label>Shartnoma summasi (4-kurs uchun)<br>(<?=($student["year_of_admission"]+3)?> yil):</label>
                                                            <input type="text" <?=$disabled_amount_4 ? $disabled_amount_4 : ''?> name="<?=$disabled_amount_4 ? '' : 'annual_contract_amount4'?>" class="form-control" placeholder="Shartnoma summasi (4-kurs uchun)" value="<?=number_format($student["annual_contract_amount4"])?>" id="price-input">
                                                        </div>

                                                        <?=getError("annual_contract_amount_note_4")?>
                                                        <div class="form-group col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                                            <label>Shartnoma izohi (4-kurs uchun)<br>(<?=($student["year_of_admission"])?> yil):</label>
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

                                                        <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                                            <button type="submit" class="btn btn-primary">Saqlash</button>
                                                        </div>
                                                    </div>
                                                </form>
                                                
                                            </div>
                                        </div>
                                        <!-- Payments -->
                                        <div id="payments-me" class="tab-pane fade" role="tabpanel">
                                            <div class="payments-me">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <div class="table table-responsive">
                                                                <table class="table mb-0 table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>#id</th>
                                                                            <th>Talaba (CODE)</th>
                                                                            <!-- <th>Talaba (ID)</th> -->
                                                                            <th>F.I.SH</th>
                                                                            <th>To'lov miqdori</th>
                                                                            <th>To'lov uslubi</th>
                                                                            <th>To'lov sanasi</th>
                                                                            <th>Kursga</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="customers">
                                                                            <?
                                                                            $payments = $db->in_array("SELECT * FROM payments WHERE code = ?", [ $student["code"] ]);
                                                                            ?>
                                                                        <? foreach($payments as $payment) { ?>
                                                                            <?
                                                                                $payment_method = $db->assoc("SELECT * FROM payment_methods WHERE id = ?", [ $payment["payment_method_id"] ]);
                                                                            ?>
                                                                        
                                                                            <tr class="btn-reveal-trigger">
                                                                                <td class="py-2"><?=$payment["id"]?></td>
                                                                                <td class="py-2"><?=$student["code"]?></td>
                                                                                <!-- <td class="py-2"><?=$student["id"]?></td> -->
                                                                                <td class="py-2"><?=$student["last_name"] . " " . $student["first_name"] . " " . $student["father_first_name"]?></td>
                                                                                <td class="py-2"><?=number_format($payment["amount"])?></td>
                                                                                <td class="p-2"><?=$payment_method['name']?></td>
                                                                                <td class="py-2"><?=$payment["payment_date"]?></td>
                                                                                <td class="py-2"><?=$payment["course_id"]?></td>
                                                                            </tr>
                                                                        <? } ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>                  
                                            </div>
                                        </div>
                                    </div>
									
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

<?
include "system/end.php";
?>