<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$query = "";
$query2 = "";
$g_query = "";
$queryCourse = "";

$studentsCodes = [];
$codee = $_GET["code"];
// Kontraktga to'lov qilgan va qilmaganlarni filterlash
$learn_types_arr = $db->in_array("SELECT * FROM learn_types");
$learn_types = [];
foreach ($learn_types_arr as $learn_type) {
    $learn_types[$learn_type["id"]] = $learn_type;
}

$directions_arr = $db->in_array("SELECT * FROM directions");
$directions = [];
foreach ($directions_arr as $direction) {
    $directions[$direction["id"]] = $direction;
}

if (!empty($_GET["from_date"])) {
    $from_date = date("Y-m-d", strtotime($_GET["from_date"]));
    $queryCourse .= " AND DATE(payment_date) >= '" . $from_date . "'";
}

if (!empty($_GET["to_date"])) {
    $to_date = date("Y-m-d", strtotime($_GET["to_date"]));
    $queryCourse .= " AND DATE(payment_date) <= '" . $to_date . "'";
}

if (!empty($_GET["regtype"])) {
    $query .= " AND reg_type = '" . $_GET["regtype"] . "'";
    $g_query .= " AND reg_type = '" . $_GET["regtype"] . "'";
    $query2 .= " AND reg_type = '" . $_GET["regtype"] . "'";
}

if (!empty($_GET["direction_id"])) {
    $query .= " AND direction_id = " . $_GET["direction_id"];
    $g_query .= " AND direction_id = " . $_GET["direction_id"];
    $query2 .= " AND direction_id = " . $_GET["direction_id"];
}

if (!empty($_GET["learn_type_id"])) {
    $query .= " AND learn_type_id = '" . $_GET["learn_type_id"] . "'";
    $g_query .= " AND learn_type_id = '" . $_GET["learn_type_id"] . "'";
    $query2 .= " AND learn_type_id = '" . $_GET["learn_type_id"] . "'";
}

if (!empty($_GET["year_id"])) {
    $query .= " AND year_of_admission = '" . $_GET["year_id"] . "'";
    $g_query .= " AND year_of_admission = '" . $_GET["year_id"] . "'";
    $query2 .= " AND year_of_admission = '" . $_GET["year_id"] . "'";
}

if (!empty($_GET["season"])) {
    $query .= " AND season = '" . $_GET["season"] . "'";
    $g_query .= " AND season = '" . $_GET["season"] . "'";
    $query2 .= " AND season = '" . $_GET["season"] . "'";
}

if (!empty($_GET["code"])) {
    $query .= " AND code = '" . $_GET["code"] . "'";
}

if (!empty($_GET["course_id"])) {
    $query .= " AND course_id = " . (int)$_GET["course_id"] . ""; // students uchun
    $g_query .= " AND course_id = " . (int)$_GET["course_id"] . ""; // students uchun
    $query2 .= " AND course_id = " . (int)$_GET["course_id"] . ""; // students select uchun

    // if( $_GET["course_id"] > $student["course_id"]) continue;
    
    // $queryCourse .= " AND course_id = '" . $_GET["course_id"] . "'"; // payments
}

if (empty($_GET["payment_course_id"])) {
    $_GET["payment_course_id"] = '1';
}

$queryCourse .= " AND course_id = '" . $_GET["payment_course_id"] . "'"; // payments

$courseId = $_GET["payment_course_id"];
if($courseId == 1) $courseId = ''; // imtiyoz uchun
// $startYear = "2022";
// $futureDate = date('Y', strtotime('+1 year', strtotime($startYear)) );

$sql = "SELECT * FROM students WHERE 1=1$query ORDER BY code ASC";
$sqlForSelect = "SELECT * FROM students WHERE 1=1$query2 ORDER BY code ASC";

$count = $db->assoc("SELECT COUNT(*) FROM students WHERE 1=1$query")["COUNT(*)"];

$sql .= " ";

$students = $db->in_array($sql);
$studentsForSelect = $db->in_array($sqlForSelect);

// $students3 = $db->in_array("SELECT * FROM students WHERE 1=1 ");
$all_groups = [];
$all_groups_arr = $db->in_array("SELECT * FROM groups_list");
foreach ($all_groups_arr as $all_group) {
    $all_groups[$all_group["id"]] = $all_group;
}

$groups = [];
$groups_list = $db->in_array("SELECT DISTINCT(group_id) FROM students WHERE group_id IS NOT NULL$g_query AND status = 1");
foreach ($groups_list as $group_list) {
    $group = $all_groups[$group_list["group_id"]];

    if (!empty($group["id"])) {
        array_push($groups, $group);
    }
}

foreach ($students as $student_key => $student) {
    $tolangan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1$queryCourse AND amount > 0 AND code = ?", [
        $student["code"]
    ])["SUM(amount)"];
    $student["tolangan_summa"] = $tolangan_summa;
    // if($student["tolangan_summa"]) $have_been = 'yes';

    $qaytarilgan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1$queryCourse AND amount < 0 AND code = ?", [
        $student["code"]
    ])["SUM(amount)"];
    $student["qaytarilgan_summa"] = $qaytarilgan_summa;

    $students[$student_key] = $student;
}

if (!empty($_GET["contract_payment"])) {
    $students2 = [];

    foreach ($students as $student) { 
        $payed_amount = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1$queryCourse AND code = ?", [ $student["code"] ])["SUM(amount)"];
        $direction = $directions[$student["direction_id"]];
        $learn_type = $learn_types[$student["learn_type_id"]];

        if ($learn_type["name"] == "Kunduzgi") {
            $kontrakt_summasi = $direction["kunduzgi_narx"];
        } else if ($learn_type["name"] == "Kechki") {
            $kontrakt_summasi = $direction["kechki_narx"];
        } else if ($learn_type["name"] == "Sirtqi") {
            $kontrakt_summasi = $direction["sirtqi_narx"];
        }
      
        $imtiyoz_summasi = 0;
        // $privilege_percent = $student["privilege_percent$courseId"];
        // $imtiyoz_summasi = $privilege_percent * ($kontrakt_summasi / 100);
        $imtiyoz_summasi = $student["privilege_amount$courseId"];
        
        // $imtiyoz_summasi_umumiy += $imtiyoz_summasi;

        $qarzdorlik = 1 * $kontrakt_summasi - $imtiyoz_summasi - $student["tolangan_summa"];

        if ($_GET["contract_payment"] == "toliq-tolagan") {
            // exit("Qarzdorlik yo'q");
            if ($qarzdorlik == 0) {
                array_push($students2, $student);
            }
        } else if ($_GET["contract_payment"] == "toliq-tolamagan") {
            if ($qarzdorlik != 0 && $payed_amount > 0) {
                array_push($students2, $student);
            }
        } else if ($_GET["contract_payment"] == "umuman-tolamagan") {
            if ($payed_amount == 0 && $imtiyoz_summasi == 0) {
                array_push($students2, $student);
            }
        } else if($_GET["contract_payment"] == "barcha-qarzdorlar") {
            if ($qarzdorlik > 0) {
                array_push($students2, $student);
            }
        } else if ($_GET["contract_payment"] == "qaytarilgan-tolovlar") {
            if ($student["qaytarilgan_summa"]) {
                array_push($students2, $student);
            }
        }
    }
    
    $students = $students2;
    $disable_pagination = true;
    $requests_count = count($students);
}

include "system/head.php";

$breadcump_title_1 = "Statistika";
$breadcump_title_2 = "Talabalar to'lovlari davri";
?>

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
                <i class="fa fa-upload me-3 scale5" aria-hidden="true"></i>Export
            </a>
        </div>
        <!-- Filter -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="/<?=$url[0]?>" method="GET" id="filter">
                            <div class="basic-form row d-flex align-items-center">

                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label for="from_date" title="<?=($from_date ? "[$from_date]" : "")?>">Dan (sana)</label>
                                    <input type="date" name="from_date" value="<?=$_GET["from_date"]?>" class="form-control" id="from_date" data-skip-this-input>
                                </div>
    
                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label for="to_date" title="<?=($to_date ? "[$to_date]" : "")?>">Gacha (sana)</label>
                                    
                                    <input type="date" name="to_date" value="<?=$_GET["to_date"]?>" class="form-control" id="to_date" data-skip-this-input>
                                </div>

                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Ta'lim Yo'nalishi</label>
                                    <select name="direction_id" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($db->in_array("SELECT * FROM directions") as $direction) { ?>
                                            <option value="<?=$direction["id"]?>" <?=($direction["id"] == $_GET["direction_id"] ? 'selected=""' : '')?> ><?=$direction["name"]?></option>
                                        <? } ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Ta'lim Shakli</label>
                                    <select name="learn_type_id" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($db->in_array("SELECT * FROM learn_types") as $learn_type) { ?>
                                            <option value="<?=$learn_type["id"]?>" <?=($learn_type["id"] == $_GET["learn_type_id"] ? 'selected=""' : '')?> ><?=$learn_type["name"]?></option>
                                        <? } ?>
                                    </select>
                                </div>  
                                
                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Kontrakt to'lovi</label>
                                    <select name="contract_payment" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <option <?=($_GET["contract_payment"] == "toliq-tolagan" ? 'selected=""' : '')?> value="toliq-tolagan">To'liq to'lagan</option>
                                        <option <?=($_GET["contract_payment"] == "toliq-tolamagan" ? 'selected=""' : '')?> value="toliq-tolamagan">To'liq to'lamagan</option>
                                        <option <?=($_GET["contract_payment"] == "umuman-tolamagan" ? 'selected=""' : '')?> value="umuman-tolamagan">Umuman to'lamagan</option>
                                        <option <?=($_GET["contract_payment"] == "barcha-qarzdorlar" ? 'selected=""' : '')?> value="barcha-qarzdorlar">Barcha qarzdorlar</option>
                                        <option <?=($_GET["contract_payment"] == "qaytarilgan-tolovlar" ? 'selected=""' : '')?> value="qaytarilgan-tolovlar">Qaytarilgan to'lovlar</option>
                                    </select>
                                </div>
                                
                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Qabul yili</label>
                                        <!-- <input type="text" value="<?=$_GET["year_id"]?>" name="year_id" maxlength="4" class="form-control form-control-lg" placeholder="Qabul yili"> -->

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
                                    <!-- <input type="text" value="<?=$_GET["course_id"]?>" name="course_id" maxlength="1" class="form-control form-control-lg" placeholder="Kursi" id="input-search"> -->

                                    <select name="course_id" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($coursesArr as $course_id => $value) { ?>
                                            <option value="<?=$course_id?>" <?=($course_id == $_GET["course_id"] ? 'selected=""' : '')?> ><?=$value?></option>
                                        <? } ?>
                                    </select>
                                </div>

                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Kursga to'lov</label>

                                    <select name="payment_course_id" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($coursesArr as $payment_course_id => $value) { ?>
                                            <option value="<?=$payment_course_id?>" <?=($payment_course_id == $_GET["payment_course_id"] ? 'selected=""' : '')?> ><?=$value?></option>
                                        <? } ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Fasllar</label>
                                    <select name="season" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach (["yozgi", "qishki"] as $season) { ?>
                                            <option value="<?=$season?>" <?=($season == $_GET["season"] ? 'selected=""' : '')?> ><?=$season?></option>
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

                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Talabalar</label>
                                    <select id="update_students" name="code" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        <!-- $student["code"] == $_GET["code"] -->
                                        <option value="">Barchasi</option>
                                        <? foreach ($studentsForSelect as $student) {
                                            if($student["code"]) {
                                                array_push($studentsCodes, $student["code"])
                                        ?>
                                            <option data-clear="<?=($student["code"] == $_GET["code"] ? '' : 'cleared')?>" value="<?=$student["code"]?>" data-subtext="<?=$student["code"]?>" <?=($student["code"] == $_GET["code"] ? 'selected=""' : '')?> ><?=$student["last_name"]. " " .$student["first_name"]. " ".$student["father_first_name"]?></option>
                                            <? } ?>
                                        <? } ?>
                                    </select>
                                </div>

                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Guruh:</label>
                                    <select id="group_id" name="group_id" data-live-search="true" class="mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        <option value="">Barcha guruhlar</option>
                                        <? foreach ($groups as $group) { ?>
                                            <option
                                                value="<?=$group["id"]?>"
                                                <?=($_GET["group_id"] == $group["id"] ? 'selected=""' : '')?>
                                            ><?=$group["name"]?></option>
                                        <? } ?>
                                    </select>
                                </div>

                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12" style="margin-top:12px;">
                                    <button type="button" class="btn btn-info btn-sm" id="submit-date" style="padding: 0.9rem 1.5rem;"><i class="flaticon-381-calendar-3"></i> Sana bo'yicha filterlash</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Filter -->
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div style="min-height: 300px;" class="table-responsive">
                            <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>#ID</th>
                                        <th>F.I.SH</th>
                                        <th>Ta'lim Yonalishi</th>
                                        <th>Ta'lim Shakli</th>
                                        <th>Shartnoma raqami</th>
                                        <th>Shartnoma sanasi</th>
                                        <th>PINFL</th>
                                        <th>Talaba soni</th>
                                        <th>Kontrakt summasi</th>
                                        <!-- <th>Imtiyoz Summasi</th> -->
                                        <th>To'landi</th>
                                        <th>Qaytarilgan summa</th>
                                        <!-- <th>Qarzdorlik</th> -->
                                        <?
                                        if ($_GET["export"] == "excel") {
                                            echo '<th>Holati</th>';
                                        }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <?
                                    $talaba_soni_umumiy = 0;
                                    $kontrakt_summasi_umumiy = 0;
                                    $tolov_miqdori_umumiy = 0;
                                    $imtiyoz_summasi_umumiy = 0;
                                    $tolangan_summa_umumiy = 0;
                                    $qarzdorlik_umumiy = 0;
                                    $qaytarilgan_summa_umumiy = 0;
                                    // $testi = 0;
                                    ?>

                                    <? foreach ($students as $student) {
                                        // $have_been = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1$queryCourse AND amount > 0 AND code = ?", [
                                            // $student["code"]
                                        // ])["SUM(amount)"];
                                        
                                        // if ($student["status"] == 0 && $student["tolangan_summa"] == 0) {

                                        // } else {
                                            $direction = $directions[$student["direction_id"]];
                                            $learn_type = $learn_types[$student["learn_type_id"]];
                                            
                                            $count += 1;
                                            
                                            $talaba_soni = 1;
                                            
                                            if ($learn_type["name"] == "Kunduzgi") {
                                                $kontrakt_summasi = $direction["kunduzgi_narx"];
                                            } else if ($learn_type["name"] == "Kechki") {
                                                $kontrakt_summasi = $direction["kechki_narx"];
                                            } else if ($learn_type["name"] == "Sirtqi") {
                                                $kontrakt_summasi = $direction["sirtqi_narx"];
                                            }

                                            if ($student["annual_contract_amount$courseId"] > 0) {
                                                $kontrakt_summasi = $student["annual_contract_amount$courseId"];
                                            }

                                            $kontrakt_summasi2 = $kontrakt_summasi;
                                            $tolov_miqdori = $talaba_soni * $kontrakt_summasi;

                                            $imtiyoz_summasi = 0;
                                            // $privilege_percent = $student["privilege_percent$courseId"];
                                            // $imtiyoz_summasi = $privilege_percent * ($kontrakt_summasi / 100);
                                            $imtiyoz_summasi = $student["privilege_amount$courseId"];
                                            

                                            $tolangan_summa = $student["tolangan_summa"];

                                            $qaytarilgan_summa = abs($student["qaytarilgan_summa"]);
                                            // $qaytarilgan_summa = abs($student["qaytarilgan_summa"]);

                                            if($tolangan_summa == 0 && $qaytarilgan_summa == 0) { // || $tolangan_summa > $kontrakt_summasi
                                                continue;
                                            }
                                            
                                            if($tolangan_summa == $qaytarilgan_summa && $student["status"] == 0) {
                                                // $imtiyoz_summasi = 0;
                                                $qarzdorlik = 0;
                                                $kontrakt_summasi = 0;
                                            } else {
                                                // $res = ($kontrakt_summasi - $imtiyoz_summasi);
                                                // $res = ($res - $tolangan_summa);
                                                // $qarzdorlik = ($res - $qaytarilgan_summa);
                                                
                                                $qarzdorlik = $kontrakt_summasi - $imtiyoz_summasi - ($tolangan_summa - $qaytarilgan_summa);
                                                if ($qarzdorlik == $kontrakt_summasi && $student["status"] == 0) {
                                                    $qarzdorlik = 0;
                                                    $kontrakt_summasi = 0;
                                                }

                                                
                                                // if ($qarzdorlik == $kontrakt_summasi && $student["status"] == 0) $kontrakt_summasi = 0;
                                                // $testi+=$qarzdorlik;
                                                // if($student["status"] == 0) $qarzdorlik = 0;
                                            }
                                            // $b = $qarzdorlik +abs($qarzdorlik);
                                            
                                            // if($b == '-' && $student["status"] == 0) {
                                            //     $qarzdorlik = 0;
                                            //     // $kontrakt_summasi = 0;
                                            //     // echo $b;
                                            //     // $kontrakt_summasi = 0;
                                            // }
                                            $tolangan_summa2 = $tolangan_summa;
                                            $imtiyoz_summasi2 = $imtiyoz_summasi;
                                            $qarzdorlik2 = $qarzdorlik;
                                            $qaytarilgan_summa2 = $qaytarilgan_summa;
                                            if ($qarzdorlik < 0 && $student["status"] == 0) {
                                                $qarzdorlik = 0;
                                                $kontrakt_summasi = 0;
                                                // $tolangan_summa = 0;
                                                // $qaytarilgan_summa = 0;
                                                $imtiyoz_summasi = 0;
                                            }

                                            $talaba_soni_umumiy += $talaba_soni;
                                            $tolov_miqdori_umumiy += $tolov_miqdori;
                                            $imtiyoz_summasi_umumiy += $imtiyoz_summasi;
                                            $tolangan_summa_umumiy += $tolangan_summa;
                                            $qaytarilgan_summa_umumiy += $qaytarilgan_summa;
                                            $kontrakt_summasi_umumiy += $kontrakt_summasi;
                                            $qarzdorlik_umumiy += $qarzdorlik;
                                        // }

                                        if ($tolangan_summa2 == $qaytarilgan_summa && $student["status"] == 0 || $qarzdorlik2 < 0 && $student["status"] == 0) {
                                            $no_edited = true;
                                        } else {
                                            $no_edited = false;
                                        }
                                        ?>

                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2 bg-<?=$student["status"] == 0 ? 'danger' : 'success'?>"><?=$student["code"]?></td>
                                            <td class="py-2">
                                                <?=$student["last_name"]?> <?=$student["first_name"]?>
                                                <?=$student["father_first_name"]?>
                                            </td>
                                            <td class="py-2">
                                                <?=$direction["short_name"]?>
                                            </td>
                                            <td class="py-2">
                                                <?=$learn_type["name"]?>
                                            </td>
                                            <td class="py-2">
                                                <?=$student["contract_id"]?>
                                            </td>
                                            <td class="py-2">
                                                <?=date("d.m.Y", strtotime($student["contract_date"]))?>
                                            </td>
                                            <td class="py-2">
                                                <?=$student["pinfl"]?>
                                            </td>
                                            <td class="py-2">1</td>
                                            <td class="p-2 <?=($no_edited ? 'text-danger' : '')?>"><?=($_GET["export"] == "excel" ? ($kontrakt_summasi2) : number_format($kontrakt_summasi2))?></td>
                                            <!-- <td class="py-2 <?=($no_edited ? 'text-danger' : '')?>"><?=($_GET["export"] == "excel" ? ($imtiyoz_summasi2) : number_format($imtiyoz_summasi2))?></td> -->
                                            <td class="py-2 <?=($qarzdorlik2 < 0 && $student["status"] == 0 ? 'text-danger' : '')?>"><?=($_GET["export"] == "excel" ? ($tolangan_summa2) : number_format($tolangan_summa2))?></td>
                                            <td class="py-2 <?=($qarzdorlik2 < 0 && $student["status"] == 0 ? 'text-danger' : '')?>"><?=($_GET["export"] == "excel" ? ($qaytarilgan_summa2) : number_format($qaytarilgan_summa2))?></td>
                                            <!-- <td class="py-2 <?=($no_edited ? 'text-danger' : '')?>"><?=($_GET["export"] == "excel" ? ($qarzdorlik) : number_format($qarzdorlik))?></td> -->
                                            <? if ($_GET["export"] == "excel") { ?>
                                                <td>
                                                    <?=($student["status"] == 0 ? "O'qimayapti" : "O'qiyapti")?>
                                                </td>
                                            <? } ?>
                                        </tr>
                                        <?
                                        
                                        // if($tolangan_summa != $qaytarilgan_summa && $student["status"] != 0) {
                                            // $res = ($kontrakt_summasi_umumiy - $imtiyoz_summasi_umumiy);
                                            // $res = ($res - $tolangan_summa_umumiy);
                                            // $qarzdorlik_umumiy = ($res - $qaytarilgan_summa_umumiy);
                                            // $qarzdorlik_umumiy += $qarzdorlik;
                                        // }
                                        ?>
                                    <? } ?>
                                    
                                    <tr class="btn-reveal-trigger">
                                        <th class="py-2">Jami:</th>
                                        <th class="py-2"></th>
                                        <th class="py-2"></th>
                                        <th class="py-2"></th>
                                        <th class="py-2"></th>
                                        <th class="py-2"></th>
                                        <th class="py-2"></th>
                                        <th class="p-2"><?=$talaba_soni_umumiy?></th>
                                        <th class="p-2"><?=($_GET["export"] == "excel" ? ($kontrakt_summasi_umumiy) : number_format($kontrakt_summasi_umumiy))?></th>
                                        <!-- <th class="py-2"><?=($_GET["export"] == "excel" ? ($imtiyoz_summasi_umumiy) : number_format($imtiyoz_summasi_umumiy))?></th> -->
                                        <th class="py-2"><?=($_GET["export"] == "excel" ? ($tolangan_summa_umumiy) : number_format($tolangan_summa_umumiy))?></th>
                                        <th class="py-2"><?=($_GET["export"] == "excel" ? (abs($qaytarilgan_summa_umumiy)) : number_format(abs($qaytarilgan_summa_umumiy)))?></th>
                                        <!-- <th class="py-2" title="<?=number_format($kontrakt_summasi_umumiy - $imtiyoz_summasi_umumiy - $tolangan_summa_umumiy - $qaytarilgan_summa_umumiy)?>"><?=($_GET["export"] == "excel" ? ($qarzdorlik_umumiy) : number_format($qarzdorlik_umumiy))?></th> -->
                                        <? if ($_GET["export"] == "excel") { ?>
                                            <th></th>
                                        <? } ?>
                                    </tr>
                                    
                                    <tr>
                                        <th>#ID</th>
                                        <th>F.I.SH</th>
                                        <th>Ta'lim Yonalishi</th>
                                        <th>Ta'lim Shakli</th>
                                        <th>Shartnoma raqami</th>
                                        <th>Shartnoma sanasi</th>
                                        <th>PINFL</th>
                                        <th>Talaba soni</th>
                                        <th>Kontrakt summasi</th>
                                        <!-- <th>Imtiyoz Summasi</th> -->
                                        <th>To'landi</th>
                                        <th>Qaytarilgan summa</th>
                                        <!-- <th>Qarzdorlik</th> -->
                                        <? if ($_GET["export"] == "excel") { ?>
                                            <th>Holati</th>
                                        <? } ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?
                        include "modules/pagination.php";

                        $count = (int)$db->assoc("SELECT COUNT(*) FROM direction_learn_types")["COUNT(*)"];

                        // $count = (int)$db->assoc("SELECT COUNT(*) FROM users WHERE role = 'student'")["COUNT(*)"];
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
        var url = '/<?=$url[0]?>?' + q + "&export=excel&page_count=1000000";

        $.get(url, function(data){
            var table = $(data).find("#table");
            // $(table).find("thead").find("th").last().remove();
            // $(table).find("tbody").find("tr").each(function(){
                // $(this).find("td").last().remove();
            // });
            tableToExcel(
                $(table).prop("innerHTML")
            );
        });
    });
</script>

<?
include "system/end.php";
?>