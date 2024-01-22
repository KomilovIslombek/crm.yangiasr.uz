<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_GET['page'];
$code = (int)$_REQUEST['code'];
if (empty($page)) $page = 1;

if (!empty($_GET["page_count"])) {
    $page_count = $_GET["page_count"];
} else {
    $page_count = 20;
}
// $page_count = 2000;
$page_end = $page * $page_count;
$page_start = $page_end - $page_count;


$queryForStudent = "";

if (!empty($_GET["regtype"])) {
    $queryForStudent .= " AND reg_type = '" . $_GET["regtype"] . "'";
}

if (!empty($_GET["course_id"])) {
    $queryForStudent .= " AND course_id = '" . $_GET["course_id"] . "'";
}

if (!empty($_GET["year_id"])) {
    $queryForStudent .= " AND year_of_admission = '" . $_GET["year_id"] . "'";
}

if (!empty($_GET["season"])) {
    $queryForStudent .= " AND season = '" . $_GET["season"] . "'";
}
// $sqlForSelect = "SELECT * FROM students WHERE 1=1$queryForStudent ORDER BY code ASC";
$sqlForSelect = "SELECT * FROM students WHERE 1=1$queryForStudent ORDER BY CASE WHEN last_name LIKE 'A%' THEN 0 ELSE 1 END, last_name";
$studentsForSelect = $db->in_array($sqlForSelect);
$studentCodes = [];
foreach ($studentsForSelect as $student) {
    array_push($studentCodes, $student["code"]);
}

$query = "";
if (!empty($_GET["q"])) {
    $q = mb_strtolower(trim($_GET["q"]));
    $q = str_replace("'", "\\"."'"."\\", $q);
    $query .= " AND (code LIKE '%".$q."%')";
}

if (!empty($_GET["from_date"])) {
    $from_date = date("Y-m-d H:i:s", strtotime($_GET["from_date"]." 00:00:00"));
    $query .= " AND payment_date >= '" . $from_date . "'";
}

if (!empty($_GET["to_date"])) {
    $to_date = date("Y-m-d H:i:s", strtotime($_GET["to_date"]." 23:59:59"));
    $query .= " AND payment_date <= '" . $to_date . "'";
}

if (!empty($_GET["payment_method_id"])) {
    $query .= " AND payment_method_id = " . $_GET["payment_method_id"];
}

if (!empty($_GET["code"])) {
    $query .= " AND code = '" . $_GET["code"] . "'";
} else {
    // $query .= " AND code IN(".implode(",", $studentCodes).")";
}

if (!empty($_GET["payment_course_id"])) {
    $query .= " AND course_id = '" . $_GET["payment_course_id"] . "'";
}

$sql = "SELECT * FROM payments WHERE id > 0$query ORDER BY id DESC LIMIT $page_start, $page_count";

$count = $db->assoc("SELECT COUNT(id) FROM payments WHERE id > 0$query")["COUNT(id)"];
// exit($sql);
$payments = $db->in_array($sql);
// echo "<pre>";
// print_r($studentCodes);
// echo "</pre>";
// exit;

// echo "<pre>";
// print_r($studentCodes);
// print_r($paymentsArr);
// echo "</pre>";
// exit;

// $payments = [];
// foreach ($paymentsArr as $paymentArr) {
//     if (empty($_GET["code"]) && in_array($paymentArr["code"], $studentCodes)) {
//         array_push($payments, $paymentArr);
//     }
// }

$setting = $db->assoc("SELECT * FROM settings");
// $countStudents = [];
// echo $count. "<br>";
// foreach ($payments as $payment){
//     $student = $db->assoc("SELECT * FROM students WHERE 1=1$queryForStudent AND code = ?", [ $payment["code"] ]);

//     // if($student["code"]) {
//         echo $payment["code"]. " ";
//         echo $student["code"].' '.$student["last_name"].' '.$student["first_name"].' <br>';
//         // array_push($countStudents, $student["code"]);
//     // }
// }
// exit;
// $count = count($countStudents);

$payment_methods = $db->in_array("SELECT * FROM payment_methods");
foreach ($payment_methods as $key => $val) {
    $payment_methods[$val["id"]] = $val;
}

if (!empty($_GET["json"])) {
    header("Content-type: text/plain");
    // $payments = $db->in_array
    echo json_encode($payments, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!empty($_GET["insert_payments"])) {
    $payments_json = '[{"id":"5069","creator_user_id":"9","code":"1023969","amount":"9500000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"10","learn_type_id":"1","payment_date":"2023-11-09","created_date":"2023-11-10 12:08:10","update_date":null},{"id":"5068","creator_user_id":"9","code":"20237125772","amount":"8500000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"1","direction_id":"7","learn_type_id":"2","payment_date":"2023-11-09","created_date":"2023-11-10 12:06:50","update_date":null},{"id":"5067","creator_user_id":"9","code":"6013221","amount":"7000000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"1","learn_type_id":"3","payment_date":"2023-11-09","created_date":"2023-11-10 12:04:03","update_date":null},{"id":"5066","creator_user_id":"9","code":"7012964","amount":"7000000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"6","learn_type_id":"3","payment_date":"2023-11-09","created_date":"2023-11-10 12:00:04","update_date":null},{"id":"5065","creator_user_id":"9","code":"2011216","amount":"7000000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"2","learn_type_id":"3","payment_date":"2023-11-09","created_date":"2023-11-10 11:59:10","update_date":null},{"id":"5064","creator_user_id":"9","code":"6014248","amount":"7000000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"1","learn_type_id":"3","payment_date":"2023-11-09","created_date":"2023-11-10 11:58:35","update_date":null},{"id":"5063","creator_user_id":"9","code":"3013490","amount":"6000000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"3","learn_type_id":"3","payment_date":"2023-11-09","created_date":"2023-11-10 11:57:30","update_date":null},{"id":"5062","creator_user_id":"9","code":"5014121","amount":"5000000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"4","learn_type_id":"3","payment_date":"2023-11-09","created_date":"2023-11-10 11:55:41","update_date":null},{"id":"5061","creator_user_id":"9","code":"1010710","amount":"4250000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"7","learn_type_id":"2","payment_date":"2023-11-09","created_date":"2023-11-10 11:48:56","update_date":null},{"id":"5060","creator_user_id":"9","code":"6013964","amount":"4000000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"1","learn_type_id":"3","payment_date":"2023-11-09","created_date":"2023-11-10 11:48:17","update_date":null},{"id":"5059","creator_user_id":"9","code":"1014598","amount":"3000000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"7","learn_type_id":"2","payment_date":"2023-11-09","created_date":"2023-11-10 11:46:58","update_date":null},{"id":"5058","creator_user_id":"9","code":"1014831","amount":"3000000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"7","learn_type_id":"1","payment_date":"2023-11-09","created_date":"2023-11-10 11:46:09","update_date":null},{"id":"5057","creator_user_id":"9","code":"20232139408","amount":"2000000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"1","direction_id":"2","learn_type_id":"3","payment_date":"2023-11-09","created_date":"2023-11-10 11:45:14","update_date":null},{"id":"5056","creator_user_id":"9","code":"7012595","amount":"1000000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"6","learn_type_id":"3","payment_date":"2023-11-09","created_date":"2023-11-10 11:43:19","update_date":null},{"id":"5055","creator_user_id":"9","code":"1014669","amount":"500000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"7","learn_type_id":"1","payment_date":"2023-11-09","created_date":"2023-11-10 11:40:11","update_date":null},{"id":"5054","creator_user_id":"9","code":"1014669","amount":"250000","payment_method_id":"1","return_payment":"0","privilege_percent":null,"privilege_note":null,"course_id":"2","direction_id":"7","learn_type_id":"1","payment_date":"2023-11-09","created_date":"2023-11-10 11:39:35","update_date":null}]';

    header("Content-type: text/plain");
    
    $payments_json_arr = json_decode($payments_json, true);

    foreach ($payments_json_arr as $payment_arr) {
        unset($payment_arr["id"]);
        print_r($payment_arr);
        // $db->insert("payments", $payment_arr);
    }

    exit;
}

include "system/head.php";

$breadcump_title_1 = "To'lovlar";
$breadcump_title_2 = "To'lovlar ro'yxati ($count ta)";
?>

<!--**********************************
    Content body start
***********************************-->
<div class="content-body">
    <div class="container-fluid">
        <!-- Add Order -->
        <div class="page-titles d-flex justify-content-between align-items-center flex-wrap-wrap">
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
                                    <label>To'lov uslubi</label>
                                    <select name="payment_method_id" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($db->in_array("SELECT * FROM payment_methods") as $payment_method) { ?>
                                            <option value="<?=$payment_method["id"]?>"><?=$payment_method["name"]?></option>
                                        <? } ?>
                                    </select>
                                </div>

                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Kurs bo'yicha to'lov</label>
                                    <select name="payment_course_id" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($coursesArr as $course_id => $value) { ?>
                                            <option value="<?=$course_id?>" <?=($course_id == $_GET["payment_course_id"] ? 'selected=""' : '')?> ><?=$value?></option>
                                        <? } ?>
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
                                        <option value="">Barchasi</option>
                                        <? foreach ($studentsForSelect as $student) { ?>
                                            <option value="<?=$student["code"]?>" data-subtext="<?=$student["code"]?>" <?=($student["code"] == $_GET["code"] ? 'selected=""' : '')?> ><?=$student["last_name"]. " " .$student["first_name"]. " ".$student["father_first_name"]?></option>
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
                            <table class="table mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>#id</th>
                                        <th>Talaba (ID)</th>
                                        <th>FISH</th>
                                        <th>To'lov miqdori</th>
                                        <th>To'lov uslubi</th>
                                        <th>Qaysi kursga</th>
                                        <th>To'lov sanasi</th>
                                        <th>Qo'shilgan sanasi</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($payments as $payment){ ?>
                                        <?
                                        $payment_method = $payment_methods[$payment["payment_method_id"]];
                                        $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $payment["code"] ]);
                                        ?>
                                        
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2"><?=$payment["id"]?></td>
                                            <td class="py-2"><?=$payment["code"]?></td>
                                            <td class="py-2"><?=$student["last_name"] . " " . $student["first_name"] . " " . $student["father_first_name"]?></td>
                                            <td class="py-2"><?=($_GET["export"] == "excel" ? $payment["amount"] : number_format($payment["amount"]))?></td>
                                            <td class="p-2"><?=$payment_method["name"]?></td>
                                            <td class="p-2"><?=$payment["course_id"]?>-kursga</td>
                                            <td class="py-2"><?=$payment["payment_date"]?></td>
                                            <td class="py-2"><?=$payment["created_date"]?></td>
                                            <td class="py-2 text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                        <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right border py-0">
                                                        <div class="py-2">
                                                            <a class="dropdown-item"  href="/editPayment/?payment_id=<?=$payment["id"]?>&page=<?=$page?>">Tahrirlash</a>
                                                            
                                                            <? if (!empty($setting["from_date"]) && $payment["payment_date"] <= $setting["from_date"] && $systemUser["id"] != 1 && $payment["payment_date"] != "00-00-0000" && $payment["payment_date"] != "0000-00-00") { ?>
                                                                
                                                            <? } else { ?>
                                                                <a class="dropdown-item text-danger" href="/editPayment/?payment_id=<?=$payment["id"]?>&type=deletePayment&page=<?=$page?>">O'chirish</a>
                                                            <? } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
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
        var url = '/<?=$url[0]?>?' + q + "&export=excel&page_count=1000000";

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
</script>

<?
include "system/end.php";
?>