<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

// if ($_GET["test2"]) {
//     header("Content-type: text/plain");

//     $db_updates = $db->in_array("SELECT * FROM db_updated WHERE table_name = ? ORDER BY created_date DESC", [
//         "students"
//     ]);

//     $updated_students = [];

//     foreach ($db_updates as $db_updated) {
//         $query_data = json_decode($db_updated["query_data"], true);
//         $old_data = json_decode($db_updated["old_data"], true)[0];
//         $code = ($query_data["code"] ? $query_data["code"] : $old_data["code"]);

//         if (strtotime($db_updated["created_date"]) < strtotime("2023-11-01")) continue;
//         // if ($query_data["direction_id"] && $old_data["direction_id"] && $query_data["direction_id"] != $old_data["direction_id"]) {
//         //     $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $code ]);
//         //     if ($student["year_of_admission"] != 2022) continue;

//         //     $old_direction = $db->assoc("SELECT * FROM directions WHERE id = ?", [ $old_data["direction_id"] ]);
//         //     $new_direction = $db->assoc("SELECT * FROM directions WHERE id = ?", [ $query_data["direction_id"] ]);

//         //     $updated_students[$code] = [
//         //         // "YANGI ID" => $query_data["code"],
//         //         "ID" => $code,
//         //         "Passport" => $old_data["passport_serial_number"],
//         //         "eski yo'nalish" => $old_direction["name"],
//         //         "yangi yo'nalish" => $new_direction["name"],
//         //         "o'zgargan sana" => $db_updated["created_date"]
//         //     ];
//         // } else 
//         if ($query_data["learn_type_id"] && $old_data["learn_type_id"] && $query_data["learn_type_id"] != $old_data["learn_type_id"]) {
//             $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $code ]);
//             if ($student["year_of_admission"] != 2022) continue;

//             $old_learn_type = $db->assoc("SELECT * FROM learn_types WHERE id = ?", [
//                 $old_data["learn_type_id"]
//             ]);

//             $new_learn_type = $db->assoc("SELECT * FROM learn_types WHERE id = ?", [
//                 $student["learn_type_id"]
//             ]);

//             $direction = $db->assoc("SELECT * FROM directions WHERE id = ?", [
//                 $student["direction_id"]
//             ]);

//             $kontakt_summasi = 0;
//             if ($new_learn_type["name"] == "Kunduzgi") {
//                 $kontakt_summasi = $direction["kunduzgi_narx"];
//             } else if ($new_learn_type["name"] == "Kechki") {
//                 $kontakt_summasi = $direction["kechki_narx"];
//             } else if ($new_learn_type["name"] == "Sirtqi") {
//                 $kontakt_summasi = $direction["sirtqi_narx"];
//             }

//             $eski_kontakt_summasi = 0;
//             if ($old_learn_type["name"] == "Kunduzgi") {
//                 $eski_kontakt_summasi = $direction["kunduzgi_narx"];
//             } else if ($old_learn_type["name"] == "Kechki") {
//                 $eski_kontakt_summasi = $direction["kechki_narx"];
//             } else if ($old_learn_type["name"] == "Sirtqi") {
//                 $eski_kontakt_summasi = $direction["sirtqi_narx"];
//             }

//             $updated_students[$student["id"]] = [
//                 // "YANGI ID" => $query_data["code"],
//                 "CODE" => $code,
//                 "Passport" => $old_data["passport_serial_number"],
//                 "hozirgi ta'lim yo'nalishi" => $direction["name"],
//                 "eski ta'lim shakli" => $old_learn_type["name"],
//                 "yangi ta'lim shakli" => $new_learn_type["name"],
//                 "o'zgargan sana" => $db_updated["created_date"],
//                 "hozirgi 1-kursshartnoma summasi" => $student["annual_contract_amount"],
//                 "hozirgi kontrakt summasi" => $kontakt_summasi,
//                 "eski kontakt summasi" => $eski_kontakt_summasi,
//                 "yangi 1-kurs shartnoma summasi" => $eski_kontakt_summasi
//             ];

            
//         }
//     }

//     print_r($updated_students);

//     foreach ($updated_students as $student_id => $update){
//         // print_r(["students", [
//         //     "annual_contract_amount" => $update["eski kontakt summasi"]
//         // ], [
//         //     "id" => $student_id
//         // ]]);

//         $db->update("students", [
//             "annual_contract_amount" => $update["eski kontakt summasi"]
//         ], [
//             "id" => $student_id
//         ]);
//     }

//     exit;
// }

if ($_GET["test"]) {
    header("Content-type: text/plain");

    $db_updates = $db->in_array("SELECT * FROM db_updated WHERE table_name = ? ORDER BY created_date DESC", [
        "students"
    ]);

    $updated_students = [];

    foreach ($db_updates as $db_updated) {
        $query_data = json_decode($db_updated["query_data"], true);
        $old_data = json_decode($db_updated["old_data"], true)[0];
        $code = ($query_data["code"] ? $query_data["code"] : $old_data["code"]);


        // if ($query_data["direction_id"] && $old_data["direction_id"] && $query_data["direction_id"] != $old_data["direction_id"]) {
        //     $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $code ]);
        //     if ($student["year_of_admission"] != 2022) continue;

        //     $old_direction = $db->assoc("SELECT * FROM directions WHERE id = ?", [ $old_data["direction_id"] ]);
        //     $new_direction = $db->assoc("SELECT * FROM directions WHERE id = ?", [ $query_data["direction_id"] ]);

        //     $updated_students[$code] = [
        //         // "YANGI ID" => $query_data["code"],
        //         "ID" => $code,
        //         "Passport" => $old_data["passport_serial_number"],
        //         "eski yo'nalish" => $old_direction["name"],
        //         "yangi yo'nalish" => $new_direction["name"],
        //         "o'zgargan sana" => $db_updated["created_date"]
        //     ];
        // } else 
        if ($query_data["learn_type_id"] && $old_data["learn_type_id"] && $query_data["learn_type_id"] != $old_data["learn_type_id"]) {
            $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $code ]);
            if ($student["year_of_admission"] != 2022) continue;

            $old_learn_type = $db->assoc("SELECT * FROM learn_types WHERE id = ?", [ $old_data["learn_type_id"] ]);
            $new_learn_type = $db->assoc("SELECT * FROM learn_types WHERE id = ?", [ $query_data["learn_type_id"] ]);

            $updated_students[$code] = [
                // "YANGI ID" => $query_data["code"],
                "ID" => $code,
                "Passport" => $old_data["passport_serial_number"],
                "eski ta'lim shakli" => $old_learn_type["name"],
                "yangi ta'lim shakli" => $new_learn_type["name"],
                "o'zgargan sana" => $db_updated["created_date"]
            ];
        }
    }

    print_r($updated_students);
    exit;
}

$page = (int)$_GET['page'];
if (empty($page)) $page = 1;

if (!empty($_GET["page_count"])) {
    $page_count = $_GET["page_count"];
} else {
    $page_count = 20;
}

$page_end = $page * $page_count;
$page_start = $page_end - $page_count;

$query = "";
$g_query = "";

// Filterlar
if (!empty($_GET["q"])) {
    $_GET["q"] = str_replace(" ", "", $_GET["q"]);
    $q = mb_strtolower(trim($_GET["q"]));
    $q = str_replace("'", "\\"."'"."\\", $q);

    switch ($_GET["search_type"]) {
        case "first_name":
            $query .= " AND (code LIKE '%".$q."%' OR CONCAT(first_name,last_name,father_first_name) LIKE '%".$q."%' OR CONCAT(father_first_name,last_name,first_name) LIKE '%".$q."%')";
        break;

        case "phone":
            $query .= " AND (REPLACE(REPLACE(phone_1, '+', ''), '-', '') LIKE '%".str_replace("-", "", $q)."%' OR REPLACE(REPLACE(phone_2, '+', ''), '-', '') LIKE '%".str_replace("-", "", $q)."%')";
        break;

        case "contract_date":
            $query .= " AND (contract_date = '".date("Y-m-d", strtotime($_GET["q"]))."')";
        break;

        case "contract_id":
            $query .= " AND (contract_id LIKE '%".$q."%')";
        break;

        case "passport_serial_number ":
            $query .= " AND (passport_serial_number  LIKE '%".$q."%')";
        break;

        case "pinfl":
            $query .= " AND (pinfl LIKE '%".$q."%')";
        break;
    }
    
    // $query .= " AND (code LIKE '%".$q."%' OR CONCAT(first_name,last_name,father_first_name) LIKE '%".$q."%' OR CONCAT(father_first_name,last_name,first_name) LIKE '%".$q."%' OR REPLACE(REPLACE(phone_1, '+', ''), '-', '') LIKE '%".str_replace("-", "", $q)."%' OR REPLACE(REPLACE(phone_2, '+', ''), '-', '') LIKE '%".str_replace("-", "", $q)."%')";
}

if (!empty($_GET["group_id"])) {
    $query .= " AND group_id = " . $_GET["group_id"] . "";
}

if (!empty($_GET["regtype"])) {
    $query .= " AND reg_type = '" . $_GET["regtype"] . "'";
    $g_query .= " AND reg_type = '" . $_GET["regtype"] . "'";
}

if (!empty($_GET["direction_id"])) {
    $query .= " AND direction_id = " . (int)$_GET["direction_id"];
    $g_query .= " AND direction_id = " . (int)$_GET["direction_id"];
}

if (!empty($_GET["learn_type_id"])) {
    $query .= " AND learn_type_id = '" . (int)$_GET["learn_type_id"] . "'";
    $g_query .= " AND learn_type_id = '" . (int)$_GET["learn_type_id"] . "'";
}

if (!empty($_GET["status"])) {
    $query .= " AND status = " . ($_GET["status"] == "oqiyapti" ? 1 : 0);
}

if (!empty($_GET["season"])) {
    $query .= " AND season = '" . $_GET["season"] . "'";
    $g_query .= " AND season = '" . $_GET["season"] . "'";
}

if (!empty($_GET["pinfl"])) {
    if ($_GET["pinfl"] == "kiritilgan") {
        $query .= " AND pinfl IS NOT NULL AND NOT pinfl = ''";
    } else if ($_GET["pinfl"] == "kiritilmagan") {
        $query .= " AND (pinfl IS NULL OR pinfl = '')";
    }
}

if (!empty($_GET["year_id"])) {
    $query .= " AND year_of_admission = '" . $_GET["year_id"] . "'";
    $g_query .= " AND year_of_admission = '" . $_GET["year_id"] . "'";
}

if (!empty($_GET["course_id"])) {
    $query .= " AND course_id = " . (int)$_GET["course_id"] . "";
    $g_query .= " AND course_id = " . (int)$_GET["course_id"] . "";
}

$sql = "SELECT * FROM students WHERE 1=1$query ORDER BY code ASC";

$count = $db->assoc("SELECT COUNT(*) FROM students WHERE 1=1$query")["COUNT(*)"];

$sql .= " LIMIT $page_start, $page_count";
$students = $db->in_array($sql);

include "system/head.php";

$breadcump_title_1 = "Talabalar";
$breadcump_title_2 = "Talabalar ro'yxati ($count ta)";

$directions_arr = $db->in_array("SELECT * FROM directions");
$directions = [];
foreach ($directions_arr as $direction_arr) {
    $directions[$direction_arr["id"]] = $direction_arr;
}

$learn_types_arr = $db->in_array("SELECT * FROM learn_types");
$learn_types = [];
foreach ($learn_types_arr as $learn_type_arr) {
    $learn_types[$learn_type_arr["id"]] = $learn_type_arr;
}

$regions_arr = $db->in_array("SELECT * FROM regions");
$regions = [];
foreach ($regions_arr as $region_arr) {
    $regions[$region_arr["id"]] = $region_arr;
}

$payment_methods = [
    "monthly" => "oyma oy",
    "half" => "yarim",
    "full" => "to'liq"
];

$groups = [];
$groups_list = $db->in_array("SELECT DISTINCT(group_id) FROM students WHERE group_id IS NOT NULL$g_query AND status = 1");
foreach ($groups_list as $group_list) {
    $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $group_list["group_id"] ]);
    if (!empty($group["id"])) {
        array_push($groups, $group);
    }
}
?>

<style>
    .ms-num {
        mso-number-format:General;
    }
    .ms-text{
        mso-number-format:"\@";/*force text*/
    }
</style>

<!--**********************************
    Content body start
***********************************-->

<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles d-flex justify-content-between">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)"><?=$breadcump_title_1?></a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?=$breadcump_title_2?></a></li>
            </ol>
            <a href="javascript:void(0)" class="btn btn-primary rounded me-3 mb-sm-0 mb-2" id="exportToExcel">
                <i class="fa fa-upload me-3 scale5" aria-hidden="true" title="<?=$query?>"></i>Export
            </a>
        </div>
        <div class="modal fade" id="exportModal" style="display: none;" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <input type="checkbox" class="form-check-input" id="select_all" required="">
                            <label class="form-check-label" for="select_all">Barchasini tanlash</label>
                        </h5>
                        <button type="button" class="close" data-bs-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="col-xl-4 col-xxl-6 col-6">
                                <div class="form-check custom-checkbox mb-3">
                                    <input type="checkbox" class="form-check-input" id="check_code" required="">
                                    <label class="form-check-label" for="check_code">code</label>
                                </div>
                            </div>
                            <div class="w-100">
                                <div class="form-check custom-checkbox mb-3">
                                    <input type="checkbox" class="form-check-input" id="check_fish" required="">
                                    <label class="form-check-label" for="check_fish">F.I.SH</label>
                                </div>
                            </div>
                            <div class="w-100">
                                <div class="form-check custom-checkbox mb-3">
                                    <input type="checkbox" class="form-check-input" id="check_phone" required="">
                                    <label class="form-check-label" for="check_phone">Telefon raqami</label>
                                </div>
                            </div>
                            <div class="w-100">
                                <div class="form-check custom-checkbox mb-3">
                                    <input type="checkbox" class="form-check-input" id="check_payments" required="">
                                    <label class="form-check-label" for="check_payments">To'lovlari</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-primary">Keyingi Modal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- start Filter -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="/<?=$url[0]?>" method="GET" id="filter">
                            <div class="basic-form row d-flex align-items-center">
                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Qidiruv turi:</label>
                                    <select name="search_type" class="form-control form-control-lg">
                                        <option value="first_name" <?=($_GET["search_type"] == "first_name" ? 'selected=""' : '')?>>CODE, Ism, Familiya bo'yicha</option>
                                        <option value="phone" <?=($_GET["search_type"] == "phone" ? 'selected=""' : '')?>>Telefon raqami bo'yicha</option>
                                        <option value="contract_date" <?=($_GET["search_type"] == "contract_date" ? 'selected=""' : '')?>>Shartnoma sanasi bo'yicha</option>
                                        <option value="contract_id" <?=($_GET["search_type"] == "contract_id" ? 'selected=""' : '')?>>Shartnoma raqami bo'yicha</option>
                                        <option value="passport_serial_number " <?=($_GET["search_type"] == "passport_serial_number " ? 'selected=""' : '')?>>Passport seriya bo'yicha</option>
                                        <option value="pinfl" <?=($_GET["search_type"] == "pinfl" ? 'selected=""' : '')?>>PINFL bo'yicha</option>
                                    </select>
                                </div>

                                <div class="form-group col-xl-8 col-lg-8 col-sm-6 col-12">
                                    <label>Qidirish:</label>
                                    <input type="text" name="q" class="form-control form-control-lg" placeholder="Qidirish..." id="input-search" value="<?=$_GET["q"]?>">
                                </div>

                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Ta'lim yo'nalish:</label>
                                    <select name="direction_id" class="form-control form-control-lg">
                                        <option value="">Barcha yo'nalishlar</option>
                                        <? foreach ($db->in_array("SELECT * FROM directions") as $direction) { ?>
                                            <option
                                                value="<?=$direction["id"]?>"
                                                <?=($_GET["direction_id"] == $direction["id"] ? 'selected=""' : '')?>
                                            ><?=$direction["name"]?></option>
                                        <? } ?>
                                    </select>
                                </div>

                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Ta'lim shakli:</label>
                                    <select name="learn_type_id" class="form-control form-control-lg">
                                        <option value="">Barcha ta'lim shakllari</option>
                                        <? foreach ($db->in_array("SELECT * FROM learn_types") as $learn_type) { ?>
                                            <option
                                                value="<?=$learn_type["id"]?>"
                                                <?=($_GET["learn_type_id"] == $learn_type["id"] ? 'selected=""' : '')?>
                                            ><?=$learn_type["name"]?></option>
                                        <? } ?>
                                    </select>
                                </div>

                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Status:</label>
                                    <select name="status" class="form-control form-control-lg">
                                        <option value="">Barchasi</option>
                                        <option value="oqiyapti" <?=$_GET["status"] == "oqiyapti" ? 'selected=""' : ''?>>O'qiyapti</option>
                                        <option value="oqimayapti" <?=$_GET["status"] == "oqimayapti" ? 'selected=""' : ''?>>O'qimayapti</option>
                                    </select>
                                </div>

                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>PINFL:</label>
                                    <select name="pinfl" class="form-control form-control-lg">
                                        <option value="">Barchasi</option>
                                        <option value="kiritilgan" <?=$_GET["pinfl"] == "kiritilgan" ? 'selected=""' : ''?>>Kiritilgan</option>
                                        <option value="kiritilmagan" <?=$_GET["pinfl"] == "kiritilmagan" ? 'selected=""' : ''?>>Kiritilmagan</option>
                                    </select>
                                </div>
                               
                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Mavsum:</label>
                                    <select name="season" class="form-control form-control-lg">
                                        <option value="">Barchasi</option>
                                        <option value="yozgi" <?=$_GET["season"] == "yozgi" ? 'selected=""' : ''?>>Yozgi</option>
                                        <option value="qishki" <?=$_GET["season"] == "qishki" ? 'selected=""' : ''?>>Qishki</option>
                                    </select>
                                </div>

                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Qabul yili</label>
                                    <select name="year_id" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($years as $year) { 
                                            if(mb_strlen($year) > 3) {
                                        ?>
                                            <option value="<?=$year?>" <?=($year == $_GET["year_id"] ? 'selected=""' : '')?> ><?=$year. ' - '. ($year + 1)?></option>
                                        <? } 
                                          } 
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Kurslar</label>
                                    <select name="course_id" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($coursesArr as $course_id => $value) { ?>
                                            <option value="<?=$course_id?>" <?=($course_id == $_GET["course_id"] ? 'selected=""' : '')?> ><?=$value?></option>
                                        <? } ?>
                                    </select>
                                </div>

                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>O'qish turi</label>
                                    <select name="regtype" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>

                                        <option value="oddiy" <?=$_GET["regtype"] == "oddiy" ? 'selected=""' : ''?>>Oddiy</option>
                                        <option value="oqishni-kochirish" <?=$_GET["regtype"] == "oqishni-kochirish" ? 'selected=""' : ''?>>O'qishni kochirish</option>
                                        <option value="ikkinchi-mutaxassislik" <?=$_GET["regtype"] == "ikkinchi-mutaxassislik" ? 'selected=""' : ''?>>Ikkinchi mutaxassislik</option>
                                    </select>
                                </div>
                                
                                <div class="col-xl-3 col-lg-3 col-sm-6 col-12" style="display:none;">
                                    <div class="form-group search-area d-lg-inline-flex col-12">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><a href="javascript:void(0)"><i class="flaticon-381-search-2"></i></a></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Qidirish...">
                                    </div>
                                </div>

                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Guruh:</label>
                                    <select id="group_id" name="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        <option value="">Barcha guruhlar</option>
                                        <? foreach ($groups as $group) { ?>
                                            <option
                                                value="<?=$group["id"]?>"
                                                <?=($_GET["group_id"] == $group["id"] ? 'selected=""' : '')?>
                                            ><?=$group["name"]?></option>
                                        <? } ?>
                                    </select>
                                </div>

                                <!-- <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <a href="export.php" class="btn btn-sm btn-success" id="submit-date" style="margin-top: 17px;padding: 0.9rem 1.5rem;"><i class="icon-file5"></i> Barcha tabalarni olish (EXCEL)</a>
                                </div> -->
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end Filter -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div style="min-height: 300px;" class="table-responsive">
                            <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>code</th>
                                        <? if ($_REQUEST["export"] == "excel") { ?>
                                            <th>Mavsumi</th>
                                            <th>Ism</th>
                                            <th>Familiya</th>
                                            <th>Otasining ismi</th>
                                            <th>Tug'ilgan sanasi</th>
                                            <th>Jinsi</th>
                                            <th>Ta'lim yo'nalishi</th>
                                            <th>Ta'lim shakli</th>
                                            <th>Telefon raqami</th>
                                            <th>Qo'shimcha telefon raqami</th>
                                            <th>Viloyati</th>
                                            <th>Manzili</th>
                                            <th>Fuqaroligi</th>
                                            <th>Statusi</th>
                                            <th>Shartnoma raqami</th>
                                            <th>Shartnoma summasi (1-yil uchun)</th>
                                            <th>To'lov uslubi</th>
                                            <th>Passport raqamlari</th>
                                            <th>PINFL</th>
                                            <th>Ustozi</th>
                                            <th>Guruhi</th>
                                            <th>O'qish turi</th>
                                            <th></th>
                                        <? } else { ?>
                                            <th>F.I.SH</th>
                                            <th>Mavsumi</th>
                                            <th>Telefon raqami</th>
                                            <th>To'lovlari</th>
                                            <th>Tug'ilgan sanasi</th>
                                            <th>Guruhi</th>
                                            <th>Shartnoma raqami</th>
                                            <th>Shartnoma sanasi</th>
                                            <th>PINFL</th>
                                            <th></th>
                                        <? } ?>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($students as $student){ ?>
                                        <?
                                        $teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $student["teacher_id"] ]);

                                        $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $student["group_id"] ]);
                                        ?>
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2 bg-<?=$student["status"] == 0 ? 'danger' : 'success'?>"><?=$student["code"]?></td>
                                            
                                            <? if ($_REQUEST["export"] == "excel") { ?>
                                                <td class="py-2"><?=$student["season"]?></td>
                                                <td class="py-2"><?=$student["first_name"]?></td>
                                                <td class="py-2"><?=$student["last_name"]?></td>
                                                <td class="py-2"><?=$student["father_first_name"]?></td>
                                                <td class="py-2"><?=$student["birth_date"]?></td>
                                                <td class="py-2"><?=$student["sex"]?></td>
                                                <td class="py-2"><?=$directions[$student["direction_id"]]["name"]?></td>
                                                <td class="py-2"><?=$learn_types[$student["learn_type_id"]]["name"]?></td>
                                                <td class="py-2"><?=str_replace("+998", "(998)", $student["phone_1"])?></td>
                                                <td class="py-2"><?=str_replace("+998", "(998)", $student["phone_2"])?></td>
                                                <td class="py-2"><?=$regions[$student["region_id"]]["name"]?></td>
                                                <td class="py-2"><?=$student["address"]?></td>
                                                <td class="py-2"><?=$student["nation"]?></td>
                                                <td class="py-2"><?=($student["status"] == 1 ? "O'qiyapti" : "O'qimayapti")?></td>
                                                <td class="py-2"><?=$student["contract_id"]?></td>
                                                <td class="py-2"><?=number_format($student["annual_contract_amount"])?></td>
                                                <td class="py-2"><?=$payment_methods[$student["payment_method"]]?></td>
                                                <td class="py-2"><?=$student["passport_serial_number"]?></td>
                                                <td class="py-2 ms-text">
                                                    PINFIL: <?=$student["pinfl"]?>
                                                </td>
                                                <td class="py-2"><?=$teacher["first_name"] . " " . $teacher["last_name"]?></td>
                                                <td class="py-2"><?=(!empty($group["id"]) ? $group["name"] : " biriktirilmagan")?></td>
                                                <td class="py-2"><?=$student["reg_type"]?></td>
                                                <td></td>
                                            <? } else { ?>
                                                <td class="py-2">
                                                    <a href="/viewStudent/?id=<?=$student["id"]?>">
                                                        <?=($student["last_name"] . " " . $student["first_name"] . " " . $student["father_first_name"])?>
                                                    </a>
                                                </td>
                                                <td class="py-2"><?=$student["season"]?></td>
                                                <td class="py-2"><?=$student["phone_1"]?></td>
                                                <td class="py-2"><a href="/paymentsList?code=<?=$student["code"]?>&page=1" class="btn btn-sm btn-primary">To'lovlari </a></td>
                                                <td class="py-2"><?=$student["birth_date"]?></td>
                                                <td class="py-2"><?=(!empty($group["id"]) ? $group["name"] : " biriktirilmagan")?></td>
                                                <td class="py-2"><?=$student["contract_id"]?></td>
                                                <td class="py-2"><?=$student["contract_date"]?></td>
                                                <td class="py-2"><?=$student["pinfl"]?></td>
                                                <td class="py-2 text-end">
                                                    <div class="dropdown">
                                                        <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                            <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right border py-0">
                                                            <div class="py-2">
                                                                <a class="dropdown-item" href="/viewStudent/?id=<?=$student["id"]?>">Talabani ma'lumotlarini ko'rish</a>
                                                                <a class="dropdown-item" href="/editStudent/?id=<?=$student["id"]?>&page=<?=$page?>">Tahrirlash</a>
                                                                <a class="dropdown-item" href="/addPayment/?id=<?=$student["id"]?>">To'lov qo'shish</a>
                                                                <!-- <a class="dropdown-item text-danger" href="/editStudent/?id=<?=$student["id"]?>&type=deleteStudent">O'chirish</a> -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            <? } ?>
                                        </tr>
                                    <? } ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?
                        include "modules/pagination.php";
                        echo pagination($count, $url[0]."/", $page_count); 
                        ?>
                        <!-- End Pagination -->
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
    $("#exportToExcel").on("click", function(){
        var q = $( "#filter" ).serialize();
        var url = '/<?=$url[0]?>?' + q + "&page_count=1000000&export=excel";

        $.get(url, function(data){
            var table = $(data).find("#table");
            $(table).find("thead").find("th").last().remove();
            $(table).find("tbody").find("tr").each(function(){
                $(this).find("td").last().remove();
            });

            tableToExcel(
              $(table).prop("innerHTML")  
            );
        });
    });

    // $("#filter").find("select").on("change", function(){
    //     updateTable();
    // });

    // function updateTable() {
    //     var q = $( "#filter" ).serialize();
    //     var url = '/<?=$url[0]?>?' + q;
    //     $.ajax({
    //         url: url,
    //         type: "GET",
    //         dataType: "html",
    //         success: function(data) {
    //             window.history.pushState($(data).find("title").text(), "Title", url);
    //             // console.log(data);
    //             $("#table").html($(data).find("#table").html());
    //             $("#pagination-wrapper").html($(data).find("#pagination-wrapper").html());
    //         }
    //     })
    // }

    // $("#input-search").on("input", function(){
    //     updateTable();
    // });

    // $("#select_all").on("click", function(){
    //     $("#exportModal").find(".modal-body").find("*[type='checkbox']").prop("checked", $(this).prop("checked"));
    // });


    // exportModal
</script>

<?
include "system/end.php";
?>