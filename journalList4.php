<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_REQUEST['page'];
if (empty($page)) $page = 1;


if (!empty($_REQUEST["page_count"])) {
    $page_count = $_REQUEST["page_count"];
} else {
    $page_count = 20;
}

$page_end = $page * $page_count;
$page_start = $page_end - $page_count;


// Moodle baza

$startdateCourse = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $_REQUEST["subject_id"] ]);
$breadCumpDate = date("Y-m-d", $startdateCourse["startdate"]);
$breadCumpEndDate = date("Y-m-d", $startdateCourse["enddate"]);
$tomorowDateCourse = date('Y-m-d', strtotime('+1 day', $startdateCourse["startdate"]));

// echo "<pre>";
// if($startdateCourse["enddate"] && $systemUser["role"] == "teacher" && $breadCumpEndDate >= date("Y-m-d") || $systemUser["role"] == "admin") {
//     echo "test 1". "<br>";
//     echo "Sana: ". $breadCumpEndDate;
//     exit;
// } else if(!$startdateCourse["enddate"] && $systemUser["role"] != "student" && $breadCumpDate == "2022-10-05" || $tomorowDateCourse == date("Y-m-d")) { 
//     echo "test 2". "<br>";
//     echo "Sana: ". $breadCumpDate;
//     exit;
// } else { 
//     echo "test 3". "<br>"; 
//     echo "breadCumpEndDate: ". $breadCumpEndDate . "<br>";
//     echo "breadCumpDate: ". $breadCumpDate . "<br>";
//     exit;
// }

if($systemUser["role"] != "student") {
    $groups = $db4->in_array("SELECT id, name FROM cohort");
    $enrol = $db4->assoc("SELECT * FROM enrol WHERE courseid = ? AND enrol = 'manual' AND roleid = 5", [ $_REQUEST["subject_id"] ]);
    $user_enroments = $db4->in_array("SELECT * FROM user_enrolments WHERE enrolid = ?", [ $enrol["id"] ]);
    
    $directions = $db4->in_array("SELECT id, name FROM course_categories WHERE parent = 0 ORDER BY sortorder ASC");
}

if (!empty($_REQUEST["subject_id"])) {
    $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = ? ORDER BY itemname DESC", [ $_REQUEST["subject_id"] ]);
    $JN = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE '%joriy nazorat%'", [ $_REQUEST["subject_id"] ]);
}

if($systemUser["role"] == "teacher" && $systemUser["teacher_id"]) {
    $teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $systemUser["teacher_id"] ]);
    $moodle_teacher = $db4->assoc("SELECT * FROM user WHERE email = ?", [ $teacher["email"] ]);
    $enroments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $moodle_teacher["id"] ]);
} 

if($systemUser["role"] == "student" && $systemUser["student_code"]){
    $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $systemUser["student_code"] ]);
    $moodle_student = $db4->assoc("SELECT * FROM user WHERE username = ?", [ $student["code"] ]);
    $enroments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $moodle_student["id"] ]);
    $moodle_student["userid"] = $moodle_student["id"];
    $user_enroments = [$moodle_student];
}

foreach ($user_enroments as $user_enroment) {
    $role_student = $db4->assoc("SELECT * FROM role_assignments WHERE userid = ?", [ $user_enroment["userid"] ]);
    if($role_student["roleid"] == 5) {
        $users_count += 1;
    }
}

if($systemUser["role"] != "student") {
    if (!empty($_REQUEST["submit"])) {
        // header("Content-type: text/plain");
        
        foreach ($_REQUEST["b"] as $student_id => $arr) {
            foreach ($arr as $item_id => $types) {
                $user_grade = 0;
                foreach ($grade_items as $grade_item) {
                    if($grade_item["itemtype"] != "course") {
                        if($grade_item["id"] != $JN["id"]) {
                            $grade = $db4->assoc("SELECT * FROM grade_grades WHERE itemid = ? AND userid = ?", [ $grade_item["id"], $student_id ]);
                            if($grade["finalgrade"]) {
                                (int)$user_grade += (int)number_format($grade["finalgrade"],3, "", "");
                            }
                        }
                    } else {
                        $yakuniy_baxo_id = $grade_item["id"];
                    }
                }
                $typeLeng = mb_strlen($types["jn"]); 
               
                if($typeLeng == 1) {
                    $types["jn"] = $types["jn"]."000";
                }elseif($typeLeng == 2) {
                    $types["jn"] = $types["jn"]."000";
                }
    
                $result = (int)$user_grade + (int)$types["jn"];
    
                
    
                $userGrade = $db4->assoc("SELECT * FROM grade_grades WHERE itemid = ? AND userid = ?", [ $item_id, $student_id ]);
                $yakuniyBaxolash = $db4->assoc("SELECT * FROM grade_grades WHERE itemid = ? AND userid = ?", [ $yakuniy_baxo_id, $student_id ]);
                if($userGrade["id"]) {
                    
                    $db4->update("grade_grades", [
                        "finalgrade" =>  number_format($types["jn"]),
                    ], [
                        "id" => $userGrade["id"]
                    ]);
    
                    // Yakuniy baxodi update qilish
                    $db4->update("grade_grades", [
                        "finalgrade" => str_replace(",",".", number_format($result)),
                    ], [
                        "id" => $yakuniyBaxolash["id"]
                    ]);
    
                } else {
                    if(!empty($types["jn"])) {
                        $grade_grade = $db4->insert("grade_grades", [
                            "finalgrade" => $types["jn"] != '' ? number_format($types["jn"]) : null,
                            "userid" => $student_id,
                            "itemid" => (int)$item_id,
                            "aggregationstatus" => "used",
                            "aggregationweight" => 1.00000,
                            "timemodified" => strtotime(date("Y-m-d H:i:s"))
                        ]);
                        
                        // Yakuniy baxodi update qilish
                        if($yakuniyBaxolash["id"]) {
                            $db4->update("grade_grades", [
                                "finalgrade" =>  str_replace(",",".", number_format($result)),
                            ], [
                                "id" => $yakuniyBaxolash["id"]
                            ]);
                        } else {
                            $grade_grade = $db4->insert("grade_grades", [
                                "userid" => $student_id,
                                "itemid" => $yakuniy_baxo_id,
                                "finalgrade" =>  str_replace(",",".", number_format($result)),
                                "aggregationstatus" => "used",
                                "aggregationweight" => 1.00000,
                                "timemodified" => strtotime(date("Y-m-d H:i:s"))
                            ]);
                        }
                    }
                }
                
            }
        }
        // exit;
    }
}

include "system/head.php";

$breadcump_title_1 = "Jurnal";
if($systemUser["role"] == "student") {
    $breadcump_title_2 = "Talaba $moodle_student[firstname]";
} else {
    $breadcump_title_2 = "Talabalar ro'yxati ($users_count ta)";
}

?>

<style>
    .ms-num {
        mso-number-format:General;
    }
    .ms-text{
        mso-number-format:"\@";/*force text*/
    }
    .bootstrap-select .dropdown-menu li.active small{
        color: #000 !important;
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
            <a href="javascript:void(0)" class="btn btn-primary me-1 rounded mb-sm-0 mb-2" id="exportToExcel">
                <i class="fa fa-upload me-3 scale5" aria-hidden="true"></i>Export
            </a>
                 <!-- <td class="py-2 text-end">
                    <div class="dropdown">
                        <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                            <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right border py-0">
                            <div class="py-2">
                                <a class="dropdown-item btn btn-danger rounded" id="addJoriy">Joriy qo'shish</a>
                            </div>
                        </div>
                    </div>
                </td> -->
            
        </div>

        <!-- start Filter -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form action="/<?=$url[0]?>" method="GET" id="filter">
                            <div class="basic-form row d-flex align-items-center">
                                <? if($systemUser["role"] == "admin") {?> 
                                    <!-- <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Guruhlar:</label>
                                        <select name="group_id" id="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                <? 
                                                    foreach ($groups as $group) { 
                                                ?>
                                                        <option
                                                            value="<?=$group["id"]?>"
                                                            <?=($_REQUEST["group_id"] == $group["id"] ? 'selected=""' : '')?>
                                                            data-subtext="<?=$group["id"]?>"
                                                        > <?=$group["name"]?></option>
                                                <? } ?>
                                                
                                        </select>
                                    </div> -->

                                    <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Yo'nalish nomi:</label>
                                        <select name="direction_id" id="direction_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                <? 
                                                    foreach ($directions as $direction) { 
                                                ?>
                                                        <option
                                                            value="<?=$direction["id"]?>"
                                                            <?=($_REQUEST["direction_id"] == $direction["id"] ? 'selected=""' : '')?>
                                                            data-subtext="<?=$direction["id"]?>"
                                                        > <?=$direction["name"]?></option>
                                                <? } ?>
                                        </select>
                                    </div>
                                <? } else { ?>
                                    <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Mavzular:</label>
                                    <select name="subject_id" id="subject_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? 
                                                foreach ($enroments as $enroment) { 
                                                    $enrol = $db4->assoc("SELECT * FROM enrol WHERE id = ?", [ $enroment["enrolid"] ]);
                                                    $subject = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $enrol["courseid"] ]);
                                            ?>
                                                    <option
                                                        value="<?=$subject["id"]?>"
                                                        title="<?=$subject["shortname"]?>"
                                                        <?=($_REQUEST["subject_id"] == $subject["id"] ? 'selected=""' : '')?>
                                                        data-subtext="<?=date("Y-m-d", $subject["startdate"])?>"
                                                    > <?=$subject["fullname"]?></option>
                                            <? } ?>
                                            
                                    </select>
                                    </div>
                                    
                                    <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Yo'nalish nomi:</label>
                                        <select name="direction_id" id="direction_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                               
                                        </select>
                                    </div>
                                <? } ?>

                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Ta'lim shakli:</label>
                                    <select name="learn_type_id" id="learn_type_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        
                                        
                                    </select>
                                </div>

                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Kurslar:</label>
                                    <select name="course_id" id="course_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Semester:</label>
                                    <select name="semester_id" id="semester_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        
                                    </select>
                                </div>
                                
                                <? if($systemUser["role"] != "teacher" && $systemUser["role"] != "student") {?> 
                                    <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Mavzu:</label>
                                        <select name="subject_id" id="subject_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            
                                        </select>
                                    </div>
                                <? } ?>
                                

                                <div class="col-xl-3 col-lg-3 col-sm-6 col-12" style="display:none;">
                                    <div class="form-group search-area d-lg-inline-flex col-12">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><a href="javascript:void(0)"><i class="flaticon-381-search-2"></i></a></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Qidirish...">
                                    </div>
                                </div>

                                
                            </div>
                        </form>
                        <div class="breadcrumb2">
                            Sana: <?=$breadCumpDate?>
                        </div>
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
                            <form method="POST" id="form">
                                <input type="hidden" id="learn_type_id" value="<?=$_REQUEST["learn_type_id"]?>">
                                <input type="hidden" id="course_id" value="<?=$_REQUEST["course_id"]?>">
                                <input type="hidden" id="semester_id" value="<?=$_REQUEST["semester_id"]?>">
                                <input type="hidden" id="subject_id" value="<?=$_REQUEST["subject_id"]?>">
                                <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                    <? if(!empty($_REQUEST["subject_id"])) {?>
                                    <thead>
                                        <tr>
                                            <th>id</th> 
                                            <th class="change_back" style="position: sticky; left: 0; z-index: 2;">F.I.SH</th>
                                            <th>DM</th>
                                            <?
                                            foreach ($grade_items as $grade_item) {
                                                if($grade_item["itemtype"] == "course") {
                                                    echo '<th>GPA</th>';
                                                    echo "<th>Yakuniy baxo </th>";
                                                } else {
                                                    echo '<th>'.$grade_item["itemname"].'</th>';
                                                }
                                            }
                                            ?>
                                            
                                        </tr>
                                    </thead>
                                    <tbody id="customers">
                                        <? foreach ($user_enroments as $user_enroment) {
                                            $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $user_enroment["userid"] ]);
                                            $role_student = $db4->assoc("SELECT * FROM role_assignments WHERE userid = ?", [ $student["id"] ]);
                                            $lessonVisit = $db->assoc("SELECT * FROM lessons_visits WHERE student_id = ? AND subject_id = ? AND subject_date = ?", [ $student["id"], $startdateCourse["id"], $breadCumpDate ]);
                                            
                                            // $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $course_user["userid"] ]);
                                                if($student["id"] && $role_student["roleid"] == 5) {
                                        ?>
                                            <tr>
                                                <td><?=$student["id"]?></td>
                                                <td class="change_back" style="position: sticky; left: 0; z-index: 2;"><?=$student["firstname"]. " " . $student["lastname"]?></td>
                                                <td class="py-2">
                                                    <div class="dropdown">
                                                        <span class="changeValue d-none"></span>
                                                        <? 
                                                            if($lessonVisit["id"] && $lessonVisit["status"] == 'keldi') {
                                                                if($startdateCourse["enddate"] && $systemUser["role"] == "teacher" && $breadCumpEndDate >= date("Y-m-d") || $systemUser["role"] == "admin") {
                                                        ?>
                                                                <? if ($_REQUEST["export"] == "excel") { 
                                                                    echo '+';
                                                                } else {?>
                                                                <button id="<?=($student["id"])?>" onclick="changeVisit(this)" class="changeVisit disp btn btn-sm btn-success " type="button" data-bs-toggle="dropdown">
                                                                    +
                                                                </button>
                                                                <? } ?>
                                                            <? } else if(!$startdateCourse["enddate"] && $systemUser["role"] != "student" && $breadCumpDate == date("Y-m-d") || $tomorowDateCourse == date("Y-m-d")) { ?>
                                                                <? if ($_REQUEST["export"] == "excel") { 
                                                                    echo '+';
                                                                } else {?>
                                                                <button id="<?=($student["id"])?>" onclick="changeVisit(this)" class="changeVisit disp btn btn-sm btn-success " type="button" data-bs-toggle="dropdown">
                                                                    +
                                                                </button>
                                                                <? } ?>
                                                            <? } else {?>
                                                                <? if ($_REQUEST["export"] == "excel") { 
                                                                    echo '+';
                                                                } else {?>
                                                                <button id="<?=($student["id"])?>" disabled="disabled" onclick="changeVisit(this)" class="changeVisit disp btn btn-sm btn-success " type="button" data-bs-toggle="dropdown">
                                                                    +
                                                                </button>
                                                                <? } ?>
                                                            <? } ?>
                                                        <?} else if($lessonVisit["id"] && $lessonVisit["status"] == 'sababli') {?>
                                                            <? if($startdateCourse["enddate"] && $systemUser["role"] == "teacher" && date("Y-m-d ", $startdateCourse["enddate"]) >= date("Y-m-d") || $systemUser["role"] == "admin") {?>
                                                                <? if ($_REQUEST["export"] == "excel") { 
                                                                    echo '*';
                                                                } else {?>
                                                                <button id="<?=($student["id"])?>" onclick="changeVisit(this)" class="changeVisit disp btn btn-sm btn-warning text-white" type="button" data-bs-toggle="dropdown">
                                                                    *
                                                                </button>
                                                                <? } ?>
                                                            <? } else if(!$startdateCourse["enddate"] && $systemUser["role"] != "student" && $breadCumpDate == date("Y-m-d") || $tomorowDateCourse == date("Y-m-d")) { ?>
                                                                <? if ($_REQUEST["export"] == "excel") { 
                                                                    echo '*';
                                                                } else {?>
                                                                <button id="<?=($student["id"])?>" onclick="changeVisit(this)" class="changeVisit disp btn btn-sm btn-warning text-white" type="button" data-bs-toggle="dropdown">
                                                                    *
                                                                </button>
                                                                <? } ?>
                                                            <? } else { ?>
                                                                <? if ($_REQUEST["export"] == "excel") { 
                                                                    echo '*';
                                                                } else {?>
                                                                <button id="<?=($student["id"])?>" disabled="disabled" onclick="changeVisit(this)" class="changeVisit disp btn btn-sm btn-warning text-white" type="button" data-bs-toggle="dropdown">
                                                                    *
                                                                </button>
                                                                <? } ?>
                                                            <? } ?>

                                                        <?} else if($lessonVisit["id"] && $lessonVisit["status"] == 'kelmadi') {?>
                                                            <? if($startdateCourse["enddate"] && $systemUser["role"] == "teacher" && date("Y-m-d ", $startdateCourse["enddate"]) >= date("Y-m-d") || $systemUser["role"] == "admin") {?>
                                                                <? if ($_REQUEST["export"] == "excel") { 
                                                                    echo '-';
                                                                } else {?>
                                                                <button id="<?=($student["id"])?>" onclick="changeVisit(this)" class="changeVisit disp btn btn-sm btn-danger " type="button" data-bs-toggle="dropdown">
                                                                    -
                                                                </button>
                                                                <? } ?>
                                                            <? } else if(!$startdateCourse["enddate"] && $systemUser["role"] != "student" && $breadCumpDate == date("Y-m-d") || $tomorowDateCourse == date("Y-m-d")) { ?>
                                                                <? if ($_REQUEST["export"] == "excel") { 
                                                                    echo '-';
                                                                } else {?>
                                                                <button id="<?=($student["id"])?>" onclick="changeVisit(this)" class="changeVisit disp btn btn-sm btn-danger " type="button" data-bs-toggle="dropdown">
                                                                    -
                                                                </button>
                                                                <? } ?>
                                                            <? } else { ?>
                                                                <? if ($_REQUEST["export"] == "excel") { 
                                                                    echo '-';
                                                                } else {?>
                                                                <button id="<?=($student["id"])?>" disabled="disabled" onclick="changeVisit(this)" class="changeVisit disp btn btn-sm btn-danger " type="button" data-bs-toggle="dropdown">
                                                                    -
                                                                </button>
                                                                <? } ?>
                                                            <? } ?>

                                                        <?} else if(!$lessonVisit["status"]) {?>
                                                            <? if($startdateCourse["enddate"] && $systemUser["role"] == "teacher" && $breadCumpEndDate >= date("Y-m-d") || $systemUser["role"] == "admin") {?>
                                                                <button id="<?=($student["id"])?>" style="background-color: rgba(41, 83, 232, 0.1); border-color: rgba(41, 83, 232, 0.1); color: #008f3b;" onclick="changeVisit(this)" class="changeVisit disp btn btn-sm btn-success" type="button" data-bs-toggle="dropdown">
                                                                    
                                                                </button>
                                                            <? } else if(!$startdateCourse["enddate"] && $systemUser["role"] != "student" && $breadCumpDate == date("Y-m-d") || $tomorowDateCourse == date("Y-m-d")) { ?>
                                                                <button id="<?=($student["id"])?>" style="background-color: rgba(41, 83, 232, 0.1); border-color: rgba(41, 83, 232, 0.1); color: #008f3b;" onclick="changeVisit(this)" class="changeVisit disp btn btn-sm btn-success" type="button" data-bs-toggle="dropdown">
                                                                    
                                                                </button>
                                                            <? } else { ?>
                                                                <button id="<?=($student["id"])?>" disabled="disabled" style="background-color: rgba(41, 83, 232, 0.1); border-color: rgba(41, 83, 232, 0.1); color: #008f3b;" onclick="changeVisit(this)" class="changeVisit disp btn btn-sm btn-success" type="button" data-bs-toggle="dropdown">
                                                                    
                                                                </button>
                                                            <? } ?>

                                                        <?}?>
                                                        <div class="dropdown-menu dropdown-menu-right border py-0">
                                                            <? if ($_REQUEST["export"] != "excel") { ?>
                                                                <div class="p-2 d-flex flex-column justify-content-center">
                                                                    <? if($startdateCourse["enddate"] && $systemUser["role"] == "teacher" && $breadCumpEndDate >= date("Y-m-d") || $systemUser["role"] == "admin") {?>
                                                                        <button onclick="clickButton(this); return false;" data-method="addLessonVisit" data-subject-id="<?=$_GET["subject_id"]?>" data-student-id="<?=$student["id"]?>" data-subject-date="<?=($breadCumpDate)?>" data-status="keldi" class="visit disp btn btn-sm btn-success">+ keldi</button>
                                                                        <button onclick="clickButton(this); return false;" data-method="addLessonVisit" data-subject-id="<?=$_GET["subject_id"]?>" data-student-id="<?=$student["id"]?>" data-subject-date="<?=($breadCumpDate)?>" data-status="sababli" class="visit disp btn btn-sm btn-warning my-1 text-white">* sababli</button>
                                                                        <button onclick="clickButton(this); return false;" data-method="addLessonVisit" data-subject-id="<?=$_GET["subject_id"]?>" data-student-id="<?=$student["id"]?>" data-subject-date="<?=($breadCumpDate)?>" data-status="kelmadi" class="visit disp btn btn-sm btn-danger">- kelmadi</button>
                                                                    <? } else if(!$startdateCourse["enddate"] && $systemUser["role"] != "student" && $breadCumpDate == date("Y-m-d") || $tomorowDateCourse == date("Y-m-d")) { ?>
                                                                        <button onclick="clickButton(this); return false;" data-method="addLessonVisit" data-subject-id="<?=$_GET["subject_id"]?>" data-student-id="<?=$student["id"]?>" data-subject-date="<?=($breadCumpDate)?>" data-status="keldi" class="visit disp btn btn-sm btn-success">+ keldi</button>
                                                                        <button onclick="clickButton(this); return false;" data-method="addLessonVisit" data-subject-id="<?=$_GET["subject_id"]?>" data-student-id="<?=$student["id"]?>" data-subject-date="<?=($breadCumpDate)?>" data-status="sababli" class="visit disp btn btn-sm btn-warning my-1 text-white">* sababli</button>
                                                                        <button onclick="clickButton(this); return false;" data-method="addLessonVisit" data-subject-id="<?=$_GET["subject_id"]?>" data-student-id="<?=$student["id"]?>" data-subject-date="<?=($breadCumpDate)?>" data-status="kelmadi" class="visit disp btn btn-sm btn-danger">- kelmadi</button>
                                                                    <? } else { ?>
                                                                        <button disabled="disabled" onclick="clickButton(this); return false;" data-method="addLessonVisit" data-subject-id="<?=$_GET["subject_id"]?>" data-student-id="<?=$student["id"]?>" data-subject-date="<?=($breadCumpDate)?>" data-status="keldi" class="visit disp btn btn-sm btn-success">+ keldi</button>
                                                                        <button disabled="disabled" onclick="clickButton(this); return false;" data-method="addLessonVisit" data-subject-id="<?=$_GET["subject_id"]?>" data-student-id="<?=$student["id"]?>" data-subject-date="<?=($breadCumpDate)?>" data-status="sababli" class="visit disp btn btn-sm btn-warning my-1 text-white">* sababli</button>
                                                                        <button disabled="disabled" onclick="clickButton(this); return false;" data-method="addLessonVisit" data-subject-id="<?=$_GET["subject_id"]?>" data-student-id="<?=$student["id"]?>" data-subject-date="<?=($breadCumpDate)?>" data-status="kelmadi" class="visit disp btn btn-sm btn-danger">- kelmadi</button>
                                                                    <? } ?>
                                                                </div>
                                                            <? } ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <? foreach ($grade_items as $grade_item) {
                                                    $grade_grade = $db4->assoc("SELECT * FROM grade_grades WHERE itemid = ? AND userid = ?", [ $grade_item["id"], $student["id"] ]);
                                                    // $grade_grades = $db4->in_array("SELECT * FROM grade_grades WHERE itemid = ?", [ $grade_item["id"] ]);
                                                    if($grade_item["itemtype"] == "course") {
                                                        $gpa = (int)number_format($grade_grade["finalgrade"], 0, "", "");
                                                        if($gpa >= 0 && $gpa <= 49) { 
                                                            echo '<td>F</td>';
                                                        } else if($gpa >= 50 && $gpa <= 59) {
                                                            echo '<td>D</td>';
                                                        } else if($gpa >= 60 && $gpa <= 64) {
                                                            echo '<td>C</td>';
                                                        } else if($gpa >= 65 && $gpa <= 69) {
                                                            echo '<td>C+</td>';
                                                        } else if($gpa >= 70 && $gpa <= 74) {   
                                                            echo '<td>V</td>';
                                                        } else if($gpa >= 75 && $gpa <= 79) {
                                                            echo '<td>V+</td>';
                                                        } else if($gpa >= 80 && $gpa <= 89) {
                                                            echo '<td>A</td>';
                                                        } else if($gpa >= 90) {
                                                            echo '<td>A+</td>';
                                                        }
                                                    }
                                                    ?>
                                                    <? if($JN["id"] == $grade_item["id"] && $systemUser["role"] != "student") {
                                                        if($startdateCourse["enddate"] && $systemUser["role"] == "teacher" && $breadCumpEndDate >= date("Y-m-d")) {
                                                    ?> 
                                                        <td><input type="number" class="form-control" name="b[<?=$student['id']?>][<?=$grade_item['id']?>][jn]" value="<?=$grade_grade["finalgrade"] ? number_format($grade_grade["finalgrade"],0, "", "") : ''?>" style="width:90px; height: 35px"></td>
                                                        <? } else if(!$startdateCourse["enddate"] && $systemUser["role"] != "student" && $breadCumpDate == date("Y-m-d") || $tomorowDateCourse == date("Y-m-d")) { ?>
                                                            <td><input type="number" class="form-control" name="b[<?=$student['id']?>][<?=$grade_item['id']?>][jn]" value="<?=$grade_grade["finalgrade"] ? number_format($grade_grade["finalgrade"],0, "", "") : ''?>" style="width:90px; height: 35px"></td>
                                                        <? } else if($systemUser["role"] != "admin"){ ?>
                                                            <td><?=$grade_grade["finalgrade"] ? number_format($grade_grade["finalgrade"],0, "", "") : ''?></td>
                                                        <? } else { ?>
                                                            <td><input type="number" class="form-control" name="b[<?=$student['id']?>][<?=$grade_item['id']?>][jn]" value="<?=$grade_grade["finalgrade"] ? number_format($grade_grade["finalgrade"],0, "", "") : ''?>" style="width:90px; height: 35px"></td>
                                                        <? } ?>
                                                    <? } else { ?>
                                                        <td><?=$grade_grade["finalgrade"] ? number_format($grade_grade["finalgrade"],2, ",", "") : ''?></td>
                                                    <? } ?>
                                                <? } ?>
                                            </tr>
                                                <? } ?>
                                        <? } ?>
                                    </tbody>
                                    <? } ?>
                                </table>
                                <? if($systemUser["role"] != "student" && $JN["id"]) { 
                                    if($startdateCourse["enddate"] && $systemUser["role"] == "teacher" && $breadCumpEndDate >= date("Y-m-d")) {
                                ?>
                                    <div class="d-flex justify-content-end">
                                        <input class="btn btn-primary m-2 disp_btn" name="submit" type="submit" value="Saqlash">
                                    </div>
                                    <? } else if(!$startdateCourse["enddate"] && $systemUser["role"] != "student" && $breadCumpDate == date("Y-m-d") || $tomorowDateCourse == date("Y-m-d") || $systemUser["role"] == "admin"){ ?>
                                        <div class="d-flex justify-content-end">
                                            <input class="btn btn-primary m-2 disp_btn" name="submit" type="submit" value="Saqlash">
                                        </div>
                                    <? } ?>
                                <? } ?>
                            </form>
                        </div>
                            
                        <!-- Pagination -->
                        
                        <!-- End Pagination -->
                    </div>
                </div>
            </div>
        </div>

        
        <!-- Export Excel table -->



        <!-- Export Excel table end -->
    </div>
</div>
<!--**********************************
    Content body end
***********************************-->
<? if ($_REQUEST["export"] != "excel") { ?>
    <style>
        .change_back{
            background: white !important;
        }
    </style>
<? } else {?>
    <style>
        .change_back{
            background: none !important;
        }
    </style>
<? } ?>
<style>

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }

    /* Firefox */
    input[type=number] {
    -moz-appearance: textfield;
    }
</style>

<?
include "system/scripts.php";
 if($systemUser["role"] == "admin") {
?>
    <script> 
        // Davomat start
    
        function changeVisit(elm) {
            var changeValue = $(elm).attr("id");
            $(".changeValue").attr("data-value-id", changeValue);
            // console.log($(".changeValue").attr("data-value-id"));
            console.log(changeValue);
        }

        function clickButton(elm) {
            console.log(elm);

            var method = $(elm).attr("data-method"); // addLessonVisit
            var status = $(elm).attr("data-status");
            var student_id = $(elm).attr("data-student-id");
            // var group_id = $(elm).attr("data-group-id");
            // var science_id = $(elm).attr("data-science-id");
            var subject_id = $(elm).attr("data-subject-id");
            var subject_date = $(elm).attr("data-subject-date");
            
            // console.log(student_id);
            // console.log(subject_id);
            // console.log(subject_date);
            // console.log(method);
            // console.log(status);

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: method,
                    student_id: student_id,
                    subject_id: subject_id,
                    subject_date: subject_date,
                    status: status,
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
                        var id = $(".changeValue").attr("data-value-id");
                        var method = data.method;
                        var simvol = data.simvol;
                        
                        $("#"+id).text(simvol);
                        if(simvol == "-") {
                            $("#"+id).text(simvol);
                            $("#"+id).removeClass("btn-success").removeClass("btn-warning").removeAttr("style").addClass("btn-danger");
                        } else if(simvol == "+"){
                            $("#"+id).text(simvol);
                            $("#"+id).removeClass("btn-danger").removeClass("btn-warning").removeAttr("style").addClass("btn-success");
                        } else if(simvol == "*") {
                            $("#"+id).text(simvol);
                            $("#"+id).removeClass("btn-danger").removeClass("btn-success").removeAttr("style").addClass("btn-warning text-white");
                        }
                    } else {
                        $(".modal-title").text("Noqonuniy ishlar yo'l qo'ymmaslikka ilitmos qilib qo'lamiz! Aks holda ......");
                        $(".modal").modal("show");
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
            
        }

        // Davomat end
        var getLearnId = <?=$_REQUEST["learn_type_id"] ? $_REQUEST["learn_type_id"] : 0?>;
        var getCourseId = <?=$_REQUEST["course_id"] ? $_REQUEST["course_id"] : 0?>;
        var getSemesterId = <?=$_REQUEST["semester_id"] ? $_REQUEST["semester_id"] : 0?>;
        var getSubjectId = <?=$_REQUEST["subject_id"] ? $_REQUEST["subject_id"] : 0?>;
        var getDirectionId = <?=$_REQUEST["direction_id"] ? $_REQUEST["direction_id"] : 0?>;

        
        $('#addJoriy').click(function() {
            var course_id = $("#course_id").val();
            console.log( "joriy: " + getSubjectId);
            console.log( "joriy: direction " + getDirectionId);

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'addJoriy',
                    course_id: getSubjectId,
                    direction_id: getDirectionId,
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
                        $(".modal-title").text(data.text);
                        $(".modal").modal("show");
                    
                        location.reload();
                    } else {
                        alert("No success");
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        });

        $("#exportToExcel").on("click", function(){
            var q = $( "#filter" ).serialize();
            var url = '/<?=$url[0]?>?' + q + "&page_count=1000000&export=excel";

            $.get(url, function(data){
                var table = $(data).find("#table");
                // $(table).find("thead").find("th")
                // $(table).find("thead").find("th").last().remove();
                // $(table).find("tbody").find("tr").each(function(){
                //     $(this).find("td").last().remove();
                // });

                tableToExcel(
                $(table).prop("innerHTML")  
                );
            });
        });
        var semesterId = <?=$_GET["semester_id"] ? $_GET["semester_id"] : 0?>;
        var courseId = <?=$_GET["course_id"] ? $_GET["course_id"] : 0?>;
        var learn_typeId = <?=$_GET["learn_type_id"] ? $_GET["learn_type_id"] : 0?>;
        // $('#group_id').change(function() {
        //     var group_id = $(this).val();
        //     updateTable();

        //     $.ajax({
        //         url: '/api',
        //         type: "POST",
        //         data: {
        //             method: 'filterGroup1',
        //             group_id: group_id,
        //         },
        //         dataType: "json",
        //         success: function(data) {
        //             if (data.ok == true) {
        //                 semesterId = data.semester.id;
        //                 courseId = data.whereCourse.id;
        //                 learn_typeId = data.learn_type.id;
                        
        //                 console.log();
        //                 $("#direction_id").html('');
        //                 // data.learn_types.forEach(learn_type => {
        //                     $("#direction_id").append(`<option value="${data.direction.id}"> ${data.direction.name}</option>`)
        //                 // });
        //                 $("#form").removeClass('d-none');
        //                 $('#direction_id').change();
        //                 $('#direction_id').selectpicker('refresh');
        //             } else {
        //                 console.log(data.errorCourse);
        //                 $("#form").addClass('d-none');
        //                 $("#direction_id").html('');
        //                 $('#direction_id').selectpicker('refresh');
        //                 $("#learn_type_id").html('');
        //                 $('#learn_type_id').selectpicker('refresh');
        //                 $("#course_id").html('');
        //                 $('#course_id').selectpicker('refresh');
        //                 $("#semester_id").html('');
        //                 $('#semester_id').selectpicker('refresh');
        //                 $("#subject_id").html('');
        //                 $('#subject_id').selectpicker('refresh');
        //                 $(".modal-title").text(`Bu Guruhga tegishli yo'nalishlar mavjud emas ${data.errorCourse}`);
        //                 $(".modal").modal("show");
        //             }
        //         },
        //         error: function() {
        //             alert("Xatolik yuzaga keldi");
        //         }
        //     })
        // });

        $('#direction_id').change(function() {
            var direction_id = $(this).val();
            // updateTable();

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'filterDirection',
                    direction_id: direction_id,
                },
                dataType: "json",
                success: function(data) {
                        if (data.ok == true) {
                        console.log("semesterid: "+ semesterId);        

                        $("#learn_type_id").html('');
                        data.learn_types.forEach(learn_type => {
                            if(learn_type.id == learn_typeId || learn_type.id == getLearnId) {
                                $("#learn_type_id").append(`<option value="${learn_type.id}" selected=''> ${learn_type.name}</option>`)
                            } else {
                                $("#learn_type_id").append(`<option value="${learn_type.id}"> ${learn_type.name}</option>`)
                            }
                        });
                        $('#learn_type_id').change();
                        $("#form").removeClass('d-none');
                        $('#learn_type_id').selectpicker('refresh');
                    } else {
                        $("#form").addClass('d-none');
                        $("#learn_type_id").html('');
                        $('#learn_type_id').selectpicker('refresh');
                        $("#course_id").html('');
                        $('#course_id').selectpicker('refresh');
                        $("#semester_id").html('');
                        $('#semester_id').selectpicker('refresh');
                        $("#subject_id").html('');
                        $('#subject_id').selectpicker('refresh');
                        $(".modal-title").text("Bu Yo'nalishga tegishli ta'lim turlari mavjud emas");
                        $(".modal").modal("show");
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        });
        
        $('#learn_type_id').change(function() {
            $(this).removeAttr("selected");
            var learn_type_id = $(this).val();
            // updateTable();

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'filterLearnType',
                    learn_type_id: learn_type_id,
                },
                dataType: "json",
                success: function(data) {
                        if (data.ok == true) {

                        $("#course_id").html('');
                        data.courses.forEach(course => {
                            if(course.id == courseId || course.id == getCourseId) {
                                $("#course_id").append(`<option value="${course.id}" selected=''> ${course.name}</option>`)
                            } else {
                                $("#course_id").append(`<option value="${course.id}"> ${course.name}</option>`)
                            }
                        });
                        $('#course_id').change();
                        $("#form").removeClass('d-none');
                        $('#course_id').selectpicker('refresh');
                    } else {
                        $("#form").addClass('d-none');
                        $("#course_id").html('');
                        $('#course_id').selectpicker('refresh');
                        $("#semester_id").html('');
                        $('#semester_id').selectpicker('refresh');
                        $("#subject_id").html('');
                        $('#subject_id').selectpicker('refresh');
                        $(".modal-title").text("Bu Ta'lim turiga tegishli  kurs emas");
                        $(".modal").modal("show");
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        });
        
        $('#course_id').change(function() {
            $(this).removeAttr("selected");
            var course_id = $(this).val();
            // updateTable();
            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'filterCourse',
                    course_id: course_id,
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {

                        $("#semester_id").html('');
                        data.semesters.forEach(semester => {
                            if(semester.id == semesterId || semester.id == getSemesterId) {
                                $("#semester_id").append(`<option value="${semester.id}" selected> ${semester.name}</option>`)
                            } else {
                                $("#semester_id").append(`<option value="${semester.id}"> ${semester.name}</option>`)
                            }
                        });
                        $('#semester_id').change();
                        $("#form").removeClass('d-none');
                        $('#semester_id').selectpicker('refresh');
                    } else {
                        $("#form").addClass('d-none');
                        $("#semester_id").html('');
                        $('#semester_id').selectpicker('refresh');
                        $("#subject_id").html('');
                        $('#subject_id').selectpicker('refresh');
                        $(".modal-title").text("Bu Kursga tegishli Semester turlari mavjud emas");
                        $(".modal").modal("show");
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        });
        
        $('#semester_id').change(function() {
            $(this).removeAttr("selected");
            var semester_id = $(this).val();

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'filterSemester',
                    semester_id: semester_id,
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
                        updateTable();
                        $("#subject_id").html('');

                        data.subjects.forEach(subject => {
                            if(subject.id == getSubjectId) {
                                $("#subject_id").append(`<option value="${subject.id}" selected='' title="${subject.shortname}" data-subtext="${subject.startdate}"> ${subject.fullname}</option>`)
                            } else {
                                $("#subject_id").append(`<option value="${subject.id}" title="${subject.shortname}" data-subtext="${subject.startdate}"> ${subject.fullname}</option>`)
                            }
                        });
                        
                        $('#subject_id').change();
                        updateTable();
                        $("#form").removeClass('d-none');
                        $('#subject_id').selectpicker('refresh');
                    } else {
                        $("#form").addClass('d-none');
                        $("#subject_id").html('');
                        $('#subject_id').selectpicker('refresh');
                        $(".modal-title").text("Bu Semester ga tegishli mavzular mavjud emas");
                        $(".modal").modal("show");
                    }

                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        });

        $("#direction_id").change();

    </script>

<? } else {?>
    <script>
        // Davomat start
    
        function changeVisit(elm) {
            var changeValue = $(elm).attr("id");
            $(".changeValue").attr("data-value-id", changeValue);
            // console.log($(".changeValue").attr("data-value-id"));
            console.log(changeValue);
        }

        function clickButton(elm) {
            console.log(elm);

            var method = $(elm).attr("data-method"); // addLessonVisit
            var status = $(elm).attr("data-status");
            var student_id = $(elm).attr("data-student-id");
            var subject_id = $(elm).attr("data-subject-id");
            var subject_date = $(elm).attr("data-subject-date");
            

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: method,
                    student_id: student_id,
                    subject_id: subject_id,
                    subject_date: subject_date,
                    status: status,
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
                        var id = $(".changeValue").attr("data-value-id");
                        var method = data.method;
                        var simvol = data.simvol;
                        
                        $("#"+id).text(simvol);
                        if(simvol == "-") {
                            $("#"+id).text(simvol);
                            $("#"+id).removeClass("btn-success").removeClass("btn-warning").removeAttr("style").addClass("btn-danger");
                        } else if(simvol == "+"){
                            $("#"+id).text(simvol);
                            $("#"+id).removeClass("btn-danger").removeClass("btn-warning").removeAttr("style").addClass("btn-success");
                        } else if(simvol == "*") {
                            $("#"+id).text(simvol);
                            $("#"+id).removeClass("btn-danger").removeClass("btn-success").removeAttr("style").addClass("btn-warning text-white");
                        }
                    } else {
                        $(".modal-title").text("Noqonuniy ishlar yo'l qo'ymmaslikka ilitmos qilib qo'lamiz! Aks holda ......");
                        $(".modal").modal("show");
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
            
        }

        // Davomat end

        var getLearnId = <?=$_REQUEST["learn_type_id"] ? $_REQUEST["learn_type_id"] : 0?>;
        var getCourseId = <?=$_REQUEST["course_id"] ? $_REQUEST["course_id"] : 0?>;
        var getSemesterId = <?=$_REQUEST["semester_id"] ? $_REQUEST["semester_id"] : 0?>;
        var getSubjectId = <?=$_REQUEST["subject_id"] ? $_REQUEST["subject_id"] : 0?>;
        var getDirectionId = <?=$_REQUEST["direction_id"] ? $_REQUEST["direction_id"] : 0?>;

        $("#exportToExcel").on("click", function(){
            var q = $( "#filter" ).serialize();
            var url = '/<?=$url[0]?>?' + q + "&page_count=1000000&export=excel";

            $.get(url, function(data){
                var table = $(data).find("#table");
                // $(table).find("thead").find("th")
                // $(table).find("thead").find("th").last().remove();
                // $(table).find("tbody").find("tr").each(function(){
                //     $(this).find("td").last().remove();
                // });

                tableToExcel(
                $(table).prop("innerHTML")  
                );
            });
        });

        $('#subject_id').change(function() {
            var subject_id = $(this).val();
            updateTable();

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'filterSubjTeach',
                    subject_id: subject_id,
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
                        $("#course_id").html('');
                        $("#semester_id").html('');
                        $("#learn_type_id").html('');
                        $("#direction_id").html('');

                        if(data.whereCourse.id == getCourseId) {
                            $("#course_id").append(`<option value="${data.whereCourse.id}" selected> ${data.whereCourse.name}</option>`)
                        } else {
                            $("#course_id").append(`<option value="${data.whereCourse.id}"> ${data.whereCourse.name}</option>`)
                        }

                        if(data.semester.id == getSemesterId) {
                            $("#semester_id").append(`<option value="${data.semester.id}" selected> ${data.semester.name}</option>`)
                        } else {
                            $("#semester_id").append(`<option value="${data.semester.id}"> ${data.semester.name}</option>`)
                        }

                        if(data.learn_type.id == getLearnId) {
                            $("#learn_type_id").append(`<option value="${data.learn_type.id}" selected> ${data.learn_type.name}</option>`)
                        } else {
                            $("#learn_type_id").append(`<option value="${data.learn_type.id}"> ${data.learn_type.name}</option>`)
                        }

                        if(data.direction.id == getDirectionId) {
                            $("#direction_id").append(`<option value="${data.direction.id}" selected> ${data.direction.name}</option>`)
                        } else {
                            $("#direction_id").append(`<option value="${data.direction.id}"> ${data.direction.name}</option>`)
                        }
                        
                        $("#form").removeClass('d-none');
                        // $('#direction_id').change();
                        $('#course_id').selectpicker('refresh');
                        $('#semester_id').selectpicker('refresh');
                        $('#learn_type_id').selectpicker('refresh');
                        $('#direction_id').selectpicker('refresh');
                    } else {
                        $("#form").addClass('d-none');
                        $("#direction_id").html('');
                        $('#direction_id').selectpicker('refresh');
                        $("#learn_type_id").html('');
                        $('#learn_type_id').selectpicker('refresh');
                        $("#course_id").html('');
                        $('#course_id').selectpicker('refresh');
                        $("#semester_id").html('');
                        $('#semester_id').selectpicker('refresh');
                        // $("#subject_id").html('');
                        // $('#subject_id').selectpicker('refresh');
                        $(".modal-title").text("Bu Mavzuga tegishli yo'nalishlar mavjud emas");
                        $(".modal").modal("show");
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        });

        $("#subject_id").change();
    </script>
<?
}
include "system/end.php";
?>