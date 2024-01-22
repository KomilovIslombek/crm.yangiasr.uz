<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

// to'lovlarga student_id yozib chiqish uchun
if ($_GET["test7"]) {
    header("Content-type: text/plain");

    $payments = $db->in_array("SELECT * FROM payments");

    foreach ($payments as $payment) {
        $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $payment["code"] ]);

        if (!empty($student["id"])) {
            $db->update("payments", [
                "student_id" => $student["id"]
            ], [
                "id" => $payment["id"]
            ]);

            print_r([
                "payments", [
                    "student_id" => $student["id"]
                ], [
                    "id" => $payment["id"]
                ]
            ]);
        }
    }

    exit;
}

// dublikatlar topishga qaratilgan kod
if ($_GET["test6"]) {
    header("Content-type: text/plain");

    $arr = [];

    $students = $db->in_array("SELECT id, code, first_name, last_name, father_first_name, passport_serial_number, exam_lang, direction_id, learn_type_id FROM students");

    foreach ($students as $student) {
        $find_students = $db->in_array("SELECT id, code, first_name, last_name, father_first_name, passport_serial_number, exam_lang, direction_id, learn_type_id FROM students WHERE code = ?", [ $student["code"] ]);

        if (count($find_students) > 1) {
            foreach ($find_students as $key => $double_student) {
                // bu talabaga tegishli to'lovlar sonini chiqarish
                // $count = $db->assoc("SELECT COUNT(id) FROM payments WHERE code = ?", [ $double_student["code"] ])["COUNT(id)"];
                // $find_students[$key]["payments"] = $count;
                $q = '"id":"'.$double_student["id"].'"';

                $updates = $db->in_array("SELECT id, old_data FROM db_updated WHERE table_name = 'students' AND old_data LIKE '%".$q."%'");
                $updates2 = [];

                foreach ($updates as $key2 => $update) {
                    $old_datas = json_decode($update["old_data"], true);
                    // $updates[$key] = $old_datas;

                    foreach ($old_datas as $old_data) {
                        // $old_data["uptade_id"] = $update["id"];

                        if ($old_data["id"] == $double_student["id"]) {
                            // $old_data["update_id"] = $update["id"];
                            array_push($updates2, $old_data);
                        }
                    }
                }

                $updates3 = [];
                foreach ($updates2 as $update2) {
                    if ($update2["passport_serial_number"] != $double_student["passport_serial_number"]) {
                        array_push($updates3, $update2);
                    }
                }

                // $find_students[($key+1)]["updates"] = $updates2;
                // $find_students[($key+1)]["updates"] = $updates2;

                // $find_students[($key+1)]["updates"] = $updates3;
                $updates_last = end($updates3);
                $find_students[($key)]["updates"] = $updates_last;

                if ($updates_last["id"]) {
                    // unset($updates_last["id"]);

                    $request = $db5->assoc("SELECT id, code, first_name, last_name, father_first_name, passport_serial_number, exam_lang, direction_id FROM requests WHERE passport_serial_number = ?", [ $updates_last["passport_serial_number"] ]);

                    if (!empty($request["id"])) {
                        $direction = $db->assoc("SELECT * FROM directions WHERE id = ?", [
                            $updates_last["direction_id"]
                        ]);
                        $learn_type = $db->assoc("SELECT name FROM learn_types WHERE id = ?", [
                            $updates_last["learn_type_id"]
                        ]);

                        $new_code = idCode2(
                            $direction["number"],
                            $request["id"],
                            $updates_last["exam_lang"],
                            $learn_type["name"]
                        );

                        // $updates_last["new_code"] = $new_code;

                        // print_r([
                        //     "students",
                        //     "new_code" => $new_code,
                        //     "request" => $request,
                        //     "double_student" => $double_student,
                        //     "updates_last" => $updates_last,
                        //     [
                        //         "id" => $double_student["id"]
                        //     ]
                        // ]);

                        // $payments_count = $db->assoc("SELECT COUNT(id) FROM payments WHERE code = ?", [
                        //     $double_student["code"]
                        // ])["COUNT(id)"];
                        
                        $student_id = $updates_last["id"];
                        unset($updates_last["id"]);
                        $updates_last["code"] = $new_code;

                        $db->update("students", $updates_last, [
                            "id" => $double_student["id"]
                        ]);

                        // print_r([
                        //     "students", $updates_last, [
                        //         "id" => $double_student["id"]
                        //     ]
                        // ]);
                        
                    }
                }
            }

            array_push($arr, $find_students);
        }
    }

    // print_r($arr);
    exit;
}

// ishlatildi
if ($_GET["test5"]) {
    header("Content-type: text/plain");

    $students = $db->in_array("SELECT id, code, first_name, last_name, father_first_name, passport_serial_number, exam_lang, direction_id, learn_type_id FROM students WHERE code LIKE '2023%'");

    foreach ($students as $student) {
        $request = $db5->assoc("SELECT id, code, first_name, last_name, father_first_name, passport_serial_number, exam_lang, direction_id FROM requests WHERE passport_serial_number = ?", [ $student["passport_serial_number"] ]);

        if (!empty($request["id"])) {
            $student["request"] = $request;

            if ($request["code"] != $student["code"] && strlen($student["code"]) < 9) {
                $direction = $db->assoc("SELECT * FROM directions WHERE id = ?", [ $student["direction_id"] ]);
                $learn_type = $db->assoc("SELECT name FROM learn_types WHERE id = ?", [ $student["learn_type_id"] ]);

                $new_code = idCode2($direction["number"], $request["id"], $student["exam_lang"], $learn_type["name"]);

                $student["new_code"] = $new_code;

                // bu talabaga tegishli to'lovlar sonini chiqarish
                $count = $db->assoc("SELECT COUNT(id) FROM payments WHERE code = ?", [ $student["code"] ])["COUNT(id)"];
                // $find_students[$key]["payments"] = $count;

                // print_r($student);
                echo "eski kod: " . $student["code"] . " => yangi kod: " . $new_code." - eski talabaning to'lovlari soni: $count\n\n";

                // $db->update("students", [
                //     "code" => $new_code
                // ], [
                //     "id" => $student["id"]
                // ]);
            }
        }

        // print_r($student);
    }

    exit;
}

if ($_GET["test4"]) {
    header("Content-type: text/plain");
    $payments = $db->in_array("SELECT * FROM payments");
    $errorPayments = [];

    foreach ($payments as $key => $payment) {
        $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $payment["code"] ]);
        
        if (empty($student["code"])) {
            if (strlen($payment["code"]) == 12) {
                $payment["code_mini"] = substr($payment["code"], 8);
            } else if (strlen($payment["code"]) == 11) {
                $payment["code_mini"] = substr($payment["code"], 7);
            }
            
            if (!empty($payment["code_mini"])) {
                $yangasr_student = $db5->assoc("SELECT id, code, first_name, last_name, father_first_name, passport_serial_number, direction_id FROM requests WHERE id = ?", [ $payment["code_mini"] ]);
    
                if (!empty($yangasr_student["id"])) {
                    $payment["yangiasr"] = $yangasr_student;

                    $crm_students = $db->in_array("SELECT id, code, first_name, last_name, father_first_name, passport_serial_number, direction_id FROM students WHERE passport_serial_number = ?", [ $yangasr_student["passport_serial_number"] ]);

                    if (count($crm_students) > 0) {
                        $payment["crm_students"] = $crm_students;
                    }
                }
            }

            print_r($payment);
        }
    }

    exit;
}

if ($_GET["test3"]) {
    header("Content-type: text/plain");
    $payments = $db->in_array("SELECT * FROM payments");
    $errorPayments = [];

    foreach ($payments as $key => $payment) {
        $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $payment["code"] ]);
        
        if (empty($student["code"])) {

            $updated = $db->assoc("SELECT * FROM db_updated WHERE table_name = 'students' AND where_data LIKE '%".$payment["code"]."%' ORDER BY id DESC LIMIT 1");

            if (!empty($updated["id"])) {
                // $payment["updated"] = $updated;
                $updated_query = json_decode($updated["query_data"], true);

                $updated_students = $db->in_array("SELECT id, code, first_name, last_name, father_first_name, passport_serial_number FROM students WHERE code = ?", [ $updated_query["code"] ]);


                $payment["updated_code"] = $updated_query["code"];

                if (!empty($updated_students[0]["id"])) {
                    foreach ($updated_students as $key => $updated_student) {
                        $yangiasr_student = $db5->assoc("SELECT id, code, first_name, last_name, father_first_name, passport_serial_number FROM requests WHERE passport_serial_number = ?", [ $updated_student["passport_serial_number"] ]);

                        if (!empty($yangiasr_student["id"])) {
                            $updated_students[$key]["founded"] = $yangiasr_student;
                        }
                    }

                    $payment["updated_students"] = $updated_students;
                }
            }

            print_r($payment);
            // array_push($errorPayments, $payment);
        } else {
            
        }
    }

    // print_r($errorPayments);
    exit;
}

if ($_GET["test"]) {
    header("Content-type: text/plain");
    $payments = $db->in_array("SELECT * FROM payments");
    $errorPayments = [];

    foreach ($payments as $key => $payment) {
        $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $payment["code"] ]);
        
        if (empty($student["code"])) {
            // $payment["code_mini"] = substr($payment["code"], 0, 4);
            if (strlen($payment["code"]) == 12) {
                $payment["code_mini"] = substr($payment["code"], 8);
            } else if (strlen($payment["code"]) == 11) {
                // $payment["code_mini"] = substr($payment["code"], 8);
                $payment["code_mini"] = substr($payment["code"], 7);
            }

            $request = $db5->assoc("SELECT id, code, first_name, last_name, father_first_name, passport_serial_number FROM requests WHERE id = ?", [ $payment["code_mini"] ]);
            if (!empty($request["id"])) {
                $payment["request"] = $request;

                $crm_student = $db->assoc("SELECT id, code, first_name, last_name, father_first_name, passport_serial_number FROM students WHERE passport_serial_number = ?", [ $request["passport_serial_number"] ]);
                
                if (!empty($crm_student["id"])) {
                    // $db->update("payments", [
                    //     "code" => $crm_student["code"]
                    // ], [
                    //     "id" => $payment["id"]
                    // ]);
                    // echo "$payment[id]-raqamli to'lov code-$payment[code] => $crm_student[code] ga o'zgartirildi\n\n";
                }
            }

            array_push($errorPayments, $payment);
        }
    }

    // print_r($errorPayments);
    exit;
}

// get old payments
if ($_GET["test2"]) {
    header("Content-type: text/plain");

    $db_old_host = 'localhost';
    $db_old_user = 'crm_yangiasr';
    $db_old_base = 'crm_yangiasr_old';
    $db_old_pass = 'fRaZCRzKrPgeeMt5';
    
    $db_old = new my_db("mysql:host=$db_old_host;dbname=$db_old_base",
        $db_old_user,
        $db_old_pass,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,
    ]);

    $old_payments = $db_old->in_array("SELECT * FROM payments");

    foreach ($old_payments as $old_payment) {
        $payment = $db->assoc("SELECT * FROM payments WHERE created_date = ?", [ $old_payment["created_date"] ]);

        if (empty($payment["id"])) {
            print_r($old_payment);
        }
    }

    
    exit;
}

$query = "";

if (!empty($_GET["from_date"])) {
    $from_date = date("Y-m-d H:i:s", strtotime($_GET["from_date"]." 00:00:00"));
    $query .= " AND payment_date >= '" . $from_date . "'";
}

if (!empty($_GET["to_date"])) {
    $to_date = date("Y-m-d H:i:s", strtotime($_GET["to_date"]." 23:59:59"));
    $query .= " AND payment_date <= '" . $to_date . "'";
}

if (!empty($_GET["course_id"])) {
    $queryCourse .= " AND course_id = " . (int)$_GET["course_id"] . "";  // is work

    // $queryCourse .= " AND NOT course_id < " . (int)$_GET["course_id"] . "";
    $query .= " AND course_id = " . (int)$_GET["course_id"] . "";;
}
$courseId = $_GET["course_id"];
if($courseId == 1) $courseId = '';

$all_dates = $db->in_array("SELECT DISTINCT(payment_date) FROM payments ORDER BY payment_date DESC");
// $count = count($all_dates);

$sql = "SELECT DISTINCT(payment_date) FROM payments WHERE 1=1$query ORDER BY payment_date DESC";
$dates = $db->in_array($sql);

// $payments = $db->in_array("SELECT * FROM payments 1=1$queryCourse");
$payments = $db->assoc("SELECT COUNT(id) FROM payments WHERE 1=1$queryCourse")["COUNT(id)"];

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

$students = $db->in_array("SELECT * FROM students");


foreach ($students as $student_key => $student) {
    $tolangan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1$queryCourse AND amount > 0 AND code = ?", [
        $student["code"]
    ])["SUM(amount)"];
    $student["tolangan_summa"] = $tolangan_summa;
    
    $umuman_tolaganmi_ozi = $db->assoc("SELECT SUM(amount) FROM payments WHERE amount > 0 AND code = ?", [
        $student["code"]
    ])["SUM(amount)"];

    $qaytarilgan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1$queryCourse AND amount < 0 AND code = ?", [
        $student["code"]
    ])["SUM(amount)"];
    $student["qaytarilgan_summa"] = $qaytarilgan_summa;

    $students[$student_key] = $student;
}

$count = 0;
$talaba_soni_umumiy = 0;
$kontrakt_summasi_umumiy = 0;
$tolov_miqdori_umumiy = 0;
$imtiyoz_summasi_umumiy = 0;
$tolangan_summa_umumiy = 0;
$qarzdorlik_umumiy = 0;
$qaytarilgan_summa_umumiy = 0;
$qolgan_summa_umumiy = 0;


foreach ($students as $student) {
    // if($umuman_tolaganmi_ozi != '' && $student["status"] == 1) {
    // if($student["tolangan_summa"] != '' && $student["status"] == 1) {
   
    $direction = $directions[$student["direction_id"]];
    $learn_type = $learn_types[$student["learn_type_id"]];

    $count += 1;

    $talaba_soni = 1;
    $talaba_soni_umumiy += $talaba_soni;
    
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


    $imtiyoz_summasi = 0;
    // $privilege_percent = $student["privilege_percent$courseId"];
    // $imtiyoz_summasi = $privilege_percent * ($kontrakt_summasi / 100);
    $imtiyoz_summasi = $student["privilege_amount$courseId"];
    


    $tolangan_summa = $student["tolangan_summa"];
    $qaytarilgan_summa = $student["qaytarilgan_summa"];
    $qolgan_summa = $tolangan_summa - $qaytarilgan_summa;


    
    if($tolangan_summa == $qaytarilgan_summa && $student["status"] == 0) {
        $qarzdorlik = 0;
        $kontrakt_summasi = 0;
    } else {
        $qarzdorlik = $kontrakt_summasi - $imtiyoz_summasi - ($tolangan_summa - $qaytarilgan_summa);
        if ($qarzdorlik == $kontrakt_summasi && $student["status"] == 0) {
            $qarzdorlik = 0;
            $kontrakt_summasi = 0;
        }
    }

    $tolangan_summa2 = $tolangan_summa;
    $imtiyoz_summasi2 = $imtiyoz_summasi;
    $qaytarilgan_summa2 = $qaytarilgan_summa;
    $qarzdorlik2 = $qarzdorlik;
    if ($qarzdorlik < 0 && $student["status"] == 0) {
        $qarzdorlik = 0;
        $kontrakt_summasi = 0;
        $tolangan_summa = 0;
        $qaytarilgan_summa = 0;
        $imtiyoz_summasi = 0;
    }
    $tolov_miqdori = $talaba_soni * $kontrakt_summasi;


    // umumiylar start
                                                
    $kontrakt_summasi_umumiy += $kontrakt_summasi;
    $imtiyoz_summasi_umumiy += $imtiyoz_summasi;

    $tolangan_summa_umumiy += $tolangan_summa;

    $qaytarilgan_summa_umumiy += $qaytarilgan_summa;
    
    $qolgan_summa_umumiy += $qolgan_summa;
    // umumiylar end

    $tolov_miqdori_umumiy += $tolov_miqdori;
    $qolgan_summa_umumiy += $qolgan_summa;

    if(!empty($_GET["course_id"]) && $student["course_id"] == $_GET["course_id"]) {
        $qarzdorlik_umumiy += $qarzdorlik;
    } else if(empty($_GET["course_id"])){
        $qarzdorlik_umumiy += $qarzdorlik;
    }
    $qaytarilgan_summa2 = abs($qaytarilgan_summa2);
}
include "system/head.php";

$breadcump_title_1 = "To'lovlar";
$breadcump_title_2 = "To'lovlar ro'yxati";
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

        <div class="row">
            <div class="col-xl-3 float-right">
                <div class="card fun">
                    <div class="card-body">
                        <div class="media align-items-center">
                            <div class="media-body me-3">
                                <h4 class="num-text text-black font-w600"><?=$payments?></h4>
                                <span class="fs-14 text-muted">Umumiy to'lovlar soni</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 float-right">
                <div class="card fun">
                    <div class="card-body">
                        <div class="media align-items-center">
                            <div class="media-body me-3">
                                <h4 style="font-size: 30px;" class="num-text text-black font-w600"><?=number_format($tolangan_summa_umumiy)?></h4>
                                <span class="fs-14 text-muted">Umumiy to'lovlar summasi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 float-right">
                <div class="card fun">
                    <div class="card-body">
                        <div class="media align-items-center">
                            <div class="media-body me-3">
                                <h4 style="font-size: 30px;" class="num-text text-black font-w600"><?=number_format($qarzdorlik_umumiy)?></h4>
                                <span class="fs-14 text-muted">Umumiy qarzdorlik summasi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 float-right">
                <div class="card fun">
                    <div class="card-body">
                        <div class="media align-items-center">
                            <div class="media-body me-3">
                                <h4 style="font-size: 30px;" class="num-text text-black font-w600"><?=number_format($imtiyoz_summasi_umumiy)?></h4>
                                <span class="fs-14 text-muted">Umumiy imtiyoz summasi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
        <!-- Filter -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="basic-form row d-flex align-items-center">
                        
                            <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                <label>Kurslar</label>
                                <select name="course_id" id="course_id" class="form-control default-select form-control-lg">
                                    <option value="">Barchasi</option>
                                    <? foreach ($coursesArr as $course_id => $value) { ?>
                                        <option value="<?=$course_id?>" <?=($course_id == $_GET["course_id"] ? 'selected=""' : '')?> ><?=$value?></option>
                                    <? } ?>
                                </select>
                            </div>

                            <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                <label for="from_date" title="<?=($from_date ? "[$from_date]" : "")?>">Dan (sana)</label>
                                <input type="date" name="from_date" value="<?=$_GET["from_date"]?>" class="form-control" id="from_date" data-skip-this-input>
                            </div>

                            <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                <label for="to_date" title="<?=($to_date ? "[$to_date]" : "")?>">Gacha (sana)</label>
                                
                                <input type="date" name="to_date" value="<?=$_GET["to_date"]?>" class="form-control" id="to_date" data-skip-this-input>
                            </div>

                            <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12" style="margin-top:32px;">
                                <button class="btn btn-info" id="submit-date" style="padding: 0.9rem 1.5rem;"><i class="flaticon-381-calendar-3"></i> Sana bo'yicha filterlash</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Filter -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="table">
                                <table class="table mb-0 table-bordered" id="table">
                                    <thead>
                                        <tr>
                                            <th>Sana</th>
                                            <th>To'lovlar soni</th>
                                            <th>Pul o'tkazish</th>
                                            <th>Naqd</th>
                                            <th>Plastik</th>
                                            <th>Barchasi</th>
                                            <th>Qaytarilgan summa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customers">
                                        <?php
                                        $pul_otkazish_jami = 0;
                                        $naqd_jami = 0;
                                        $plastik_jami = 0;
                                        $all_payment = 0;
                                        $jami_qaytarilgan_summa = 0;
                                        ?>
                                        <? foreach ($dates as $date){ ?>
                                            <?
                                                $kunlikPayment = 0;
                                                $payment_date = $date["payment_date"];
                                                // $payment_method = $db->assoc("SELECT * FROM payment_methods WHERE id = ?", [ $payment["payment_method_id"] ]);
                                                // $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $payment["code"] ]);
                                                $pul_otkazish = $db->assoc("SELECT SUM(amount) AS summa FROM payments WHERE 1=1$queryCourse AND payment_method_id = 1 AND DATE(payment_date) = '$payment_date'")["summa"];
                                                $pul_otkazish_jami += $pul_otkazish;
    
                                                $naqd = $db->assoc("SELECT SUM(amount) AS summa FROM payments WHERE 1=1$queryCourse AND payment_method_id = 2 AND DATE(payment_date) = '$payment_date'")["summa"];
                                                $naqd_jami += $naqd;
    
                                                $plastik = $db->assoc("SELECT SUM(amount) AS summa FROM payments WHERE 1=1$queryCourse AND payment_method_id = 4 AND DATE(payment_date) = '$payment_date'")["summa"];
                                                $plastik_jami += $plastik;
    
                                                $payment_count = $db->assoc("SELECT COUNT(*) FROM payments WHERE 1=1$queryCourse AND payment_date = ?", [ $date["payment_date"] ]);
    
                                                $jamiPayment = ($pul_otkazish_jami + ($naqd_jami + $plastik_jami));
                                               
                                                $all_payment += $payment_count["COUNT(*)"]; 

                                                $qaytarilgan_summa = $db->assoc("SELECT SUM(amount) AS summa FROM payments WHERE 1=1$queryCourse AND amount < 0 AND DATE(payment_date) = '$payment_date'")["summa"];

                                                $jami_qaytarilgan_summa += $qaytarilgan_summa;
                                           ?>
                                            
                                            <tr class="btn-reveal-trigger">
                                                <td class="py-2"><?=$date["payment_date"]?></td>
                                                <td class="py-2"><?=$payment_count["COUNT(*)"]?></td>
                                                <td class="py-2"><?=number_format($pul_otkazish)?></td>
                                                <td class="py-2"><?=number_format($naqd)?></td>
                                                <td class="py-2"><?=number_format($plastik)?></td>
                                                <td class="py-2"><?=number_format(($pul_otkazish + ($naqd + $plastik)))?></td>
                                                <td class="py-2"><?=number_format($qaytarilgan_summa)?></td>
                                            </tr>
                                        <? } ?>
    
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2">Jami</td>
                                            <td class="py-2"><?=$all_payment?></td>
                                            <td class="py-2"><?=number_format($pul_otkazish_jami)?></td>
                                            <td class="py-2"><?=number_format($naqd_jami)?></td>
                                            <td class="py-2"><?=number_format($plastik_jami)?></td>
                                            <td class="py-2"><?=number_format($jamiPayment)?></td>
                                            <td class="py-2"><?=number_format($jami_qaytarilgan_summa)?></td>
                                        </tr>
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

<script>
    $("#course_id").change(function () {
        var url = '<?=$url[0]?>/?';
        url = url + "course_id=" + $("#course_id").val();
        url = url + "&from_date=" + $("#from_date").val();
        url = url + "&to_date=" + $("#to_date").val();
        url = url + "&page=1";
        $("#preloader").css("background", "transparent");
        $(".sk-three-bounce").css("background", "transparent");
        $("#preloader").css("backdrop-filter", "blur(10px)");
        $(".sk-three-bounce").css("backdrop-filter", "blur(10px)");
        $("#preloader").css("display", "block");
        $("#preloader").css("z-index", "9");
        window.location = url;
    });
    $("#submit-date").on("click", function(){
        var url = '<?=$url[0]?>/?';
        // url = url + "direction_id=" + findGetParameter("direction_id");
        // url = url + "&learn_type=" + findGetParameter("learn_type");
        // url = url + "&q=" + findGetParameter("q");
        // url = url + "&payment=" + findGetParameter("payment");
        // url = url + "&suhbat=" + findGetParameter("suhbat");;
        url = url + "course_id=" + $("#course_id").val();
        url = url + "&from_date=" + $("#from_date").val();
        url = url + "&to_date=" + $("#to_date").val();
        url = url + "&page=1";
        // console.log(url);
        $("#preloader").css("background", "transparent");
        $(".sk-three-bounce").css("background", "transparent");
        $("#preloader").css("backdrop-filter", "blur(10px)");
        $(".sk-three-bounce").css("backdrop-filter", "blur(10px)");
        $("#preloader").css("display", "block");
        $("#preloader").css("z-index", "9");
        window.location = url;
    });
</script>

<?
include "system/end.php";
?>