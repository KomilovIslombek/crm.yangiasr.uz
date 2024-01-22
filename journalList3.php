<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

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

$types = $db->in_array("SELECT * FROM types");
$type = $db->assoc("SELECT name FROM types WHERE id = ?", [ $_REQUEST["type_id"] ]);


if(!empty($_REQUEST["subject_id"])) {
    $subjects = $db4->in_array("SELECT * FROM course_sections WHERE course = ? AND name LIKE ? OR course = ? AND name LIKE ? ORDER BY date ASC", [ $_REQUEST["subject_id"], "%$type[name]%", $_REQUEST["subject_id"], str_replace("'", '', "%$type[name]%") ]);
    $btnSub = $db4->assoc("SELECT * FROM course_sections WHERE course = ? AND name LIKE ? OR course = ? AND name LIKE ?", [ $_REQUEST["subject_id"], "%$type[name]%", $_REQUEST["subject_id"], str_replace("'", '', "%$type[name]%") ]);
    $subCount = 0;
    $sCount = $db4->assoc("SELECT * FROM course_sections WHERE course = ? ORDER BY section DESC", [ $_REQUEST["subject_id"] ]);
    $noDateSub = $db4->assoc("SELECT * FROM course_sections WHERE course = ? AND name LIKE ? AND isnull(date) OR course = ? AND name LIKE ? AND isnull(date) OR date = ?", [ $_REQUEST["subject_id"], "%$type[name]%", $_REQUEST["subject_id"], str_replace("'", '', "%$type[name]%"), '0000-00-00' ]);
    $noDateSubjects = $db4->in_array("SELECT * FROM course_sections WHERE course = ? AND isnull(date) AND name LIKE ? OR course = ? AND isnull(date) AND name LIKE ? OR date = ? ORDER BY date ASC", [ $_REQUEST["subject_id"], "%$type[name]%", $_REQUEST["subject_id"], str_replace("'", '', "%$type[name]%"), '0000-00-00' ]);

    (int)$itemCount = $sCount["section"];
    (int)$arraCount = 15 - count($subjects);
    for ($i=1; $i <= $arraCount; $i++) { 
        array_push($subjects, '');
    }

    if($noDateSub) { 
        addCookie("subject", 'noDate');
    } else { 
        removeCookie("subject"); 
    } 
}

// Moodle baza

if($systemUser["role"] != "student") {
    $groups = $db4->in_array("SELECT id, name FROM cohort");
    $enrol = $db4->assoc("SELECT * FROM enrol WHERE courseid = ? AND enrol = 'manual' AND roleid = 5", [ $_REQUEST["subject_id"] ]);
    $user_enroments = $db4->in_array("SELECT * FROM user_enrolments WHERE enrolid = ?", [ $enrol["id"] ]);
    $directions = $db4->in_array("SELECT id, name FROM course_categories WHERE parent = 0 ORDER BY sortorder ASC");
}
    $oldCourses = $db4->in_array("SELECT id, name FROM `course_categories` WHERE name LIKE '%-kurs%'");
    $oldSemesters = $db4->in_array("SELECT id, name FROM `course_categories` WHERE name LIKE '%-semestr%'");

    $courses = [];
    $tmpC = array();
    foreach ($oldCourses as $course) {
        if (!in_array($course['name'], $tmpC)) {
            $courses[] = $course;
            $tmpC[] = $course['name'];
        }
    }

    $semesters = [];
    $tmpS = array();
    foreach ($oldSemesters as $semester) {
        if (!in_array($semester['name'], $tmpS)) {
            $semesters[] = $semester;
            $tmpS[] = $semester['name'];
        }
    }

// if (!empty($_REQUEST["subject_id"])) {
    // $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = ? ORDER BY itemname DESC", [ $_REQUEST["subject_id"] ]);
//     $JN = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE '%joriy nazorat%'", [ $_REQUEST["subject_id"] ]);
// }

if($systemUser["role"] == "teacher" && $systemUser["teacher_id"]) {
    $teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $systemUser["teacher_id"] ]);
    $moodle_teacher = $db4->assoc("SELECT * FROM user WHERE email = ?", [ $teacher["email"] ]);
    $enroments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $moodle_teacher["id"] ]);

    
    $groups = [];
    $tmpTG = array();

    foreach ($enroments as $user_enrolment) {
        $enrolment_user = $db4->assoc("SELECT * FROM user_enrolments WHERE enrolid = ? AND userid != ?", [ $user_enrolment["enrolid"], $user_enrolment["userid"] ]);
        
        $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $enrolment_user["userid"] ]);
        $role_student = $db4->assoc("SELECT * FROM role_assignments WHERE userid = ?", [ $student["id"] ]);
        if($student["id"] && $role_student["roleid"] == 5) {
            $cohort_member = $db4->assoc("SELECT * FROM cohort_members WHERE userid = ?", [ $student['id'] ]);
            $cohort = $db4->assoc("SELECT * FROM cohort WHERE id = ?", [ $cohort_member["cohortid"] ]);
            if (!in_array($cohort['id'], $tmpTG)) {
                $groups[] = $cohort;
                $tmpTG[] = $cohort['id'];
            }
        }
    }
    // echo "<pre>";
    // print_r($enroments);
    // print_r($groups);
    // exit;
} 

if($systemUser["role"] == "student" && $systemUser["student_code"]){
    $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $systemUser["student_code"] ]);
    $moodle_student = $db4->assoc("SELECT * FROM user WHERE username = ?", [ $student["code"] ]);
    $enroments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $moodle_student["id"] ]);
    $moodle_student["userid"] = $moodle_student["id"];
    $user_enroments = [$moodle_student];

    
    $groups = [];
    $tmpG = array();
    
    foreach ($enroments as $user_enrolment) {
        // $enrolment_user = $db4->assoc("SELECT * FROM user_enrolments WHERE enrolid = ? AND userid != ?", [ $user_enrolment["enrolid"], $user_enrolment["userid"] ]);
        $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $user_enrolment["userid"] ]);
        $role_student = $db4->assoc("SELECT * FROM role_assignments WHERE userid = ?", [ $student["id"] ]);
        if($student["id"] && $role_student["roleid"] == 5) {
            $cohort_member = $db4->assoc("SELECT * FROM cohort_members WHERE userid = ?", [ $student['id'] ]);
            $cohort = $db4->assoc("SELECT * FROM cohort WHERE id = ?", [ $cohort_member["cohortid"] ]);
            if (!in_array($cohort['id'], $tmpG)) {
                $groups[] = $cohort;
                $tmpG[] = $cohort['id'];
            }
        }
    }
    // echo "<pre>";
    // print_r($enroments);
    // print_r($groups);
    // exit;
}


foreach ($user_enroments as $user_enroment) {
    $role_student = $db4->assoc("SELECT * FROM role_assignments WHERE userid = ?", [ $user_enroment["userid"] ]);
    if($role_student["roleid"] == 5) {
        $users_count += 1;
    }
}

if($systemUser["role"] != "student") {
    if($_REQUEST["addSubject"]) {
        foreach ($_POST["subject"] as $key => $arr) {
            // echo "<pre>";
            // print_r($arr);
            // echo $arr["id"];
            // echo $arr["name"]. " ";
            // echo $arr["date"]. "<br>";
          
            if(!empty($arr["id"]) && $arr["date"] && $arr["name"]){
                $db4->update("course_sections", [
                    "date" => $arr["date"],
                    "name" => $arr["name"],
                    "timemodified" => strtotime(date("Y-m-d H:i:s"))
                ], [
                    "id" => $arr["id"]
                ]);
            } else if($arr["date"] && $arr["name"]){
                $itemCount += 1;
                    
                $course_section = $db4->insert("course_sections", [
                    "course" => $_REQUEST["subject_id"],
                    "section" => $itemCount,
                    "name" => $arr["name"],
                    "date" => $arr["date"],
                    "timemodified" => strtotime(date("Y-m-d H:i:s"))
                ]);
                
                // $course_section ? header("Location: /journalList4") : '';
            }
        }

        // exit;
    }
    if (!empty($_REQUEST["submit"])) {
       
        foreach ($_REQUEST["b"] as $student_id => $arr) {
            foreach ($arr as $item_id => $type) {
                // item_id -- subject_id 
                // $_REQUEST[subject_id] -- science_id 

                if(!empty($type["baxo"])) {
                    $item_date = $db4->assoc("SELECT * FROM course_sections WHERE id = ?", [ $item_id ] );
                    $lessonVisit = $db->assoc("SELECT * FROM lessons_visits WHERE science_id = ? AND subject_id = ? AND type_id = ? AND student_id = ?", 
                    [ $_REQUEST["subject_id"], $item_id, $_REQUEST["type_id"], $student_id ]);
                    
                    if(!empty($lessonVisit['id'])) {
                        $db->update("lessons_visits", [
                            "finalgrade" =>  $type["baxo"],
                        ], [
                            "id" => $lessonVisit["id"]
                        ]);
                    } else {
                        $lessons_visit = $db->insert("lessons_visits", [
                            "finalgrade" => $type["baxo"],
                            "student_id" => $student_id,
                            "science_id" => $_REQUEST["subject_id"],
                            "subject_id" => (int)$item_id,
                            "subject_date" => $item_date["date"],
                            "type_id" => $_REQUEST["type_id"],
                        ]);
                    }
                }
                
            }
        }
        // exit;
        // header("Location: /journalList4");
    }
}

$noDateSub2 = $db4->assoc("SELECT * FROM course_sections WHERE course = ? AND name LIKE ? AND isnull(date) OR course = ? AND name LIKE ? AND isnull(date) OR date = ?", [ $_REQUEST["subject_id"], "%$type[name]%", $_REQUEST["subject_id"], str_replace("'", '', "%$type[name]%"), '0000-00-00' ]);

include "system/head.php";

$breadcump_title_1 = "Jurnal";
if($systemUser["role"] == "student") {
    $breadcump_title_2 = "Talaba $moodle_student[firstname]";
} else {
    $breadcump_title_2 = "Talabalar ro'yxati ($users_count ta) ";
}

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
            <a href="javascript:void(0)" class="btn btn-primary me-1 rounded mb-sm-0 mb-2" id="exportToExcel">
                <i class="fa fa-upload me-3 scale5" aria-hidden="true"></i>Export
            </a>
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
                                                        > <?=$group["name"]?></option>
                                                <? } ?>
                                        </select>
                                    </div> -->
                                    
                                    <!-- <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
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
                                    </div> -->
                                <? } else if($systemUser["role"] != "student"){ ?>
                                    <!-- <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Guruhlar:</label>
                                        <select name="group_id" id="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                <? 
                                                    foreach ($groups as $group) { 
                                                ?>
                                                        <option
                                                            value="<?=$group["id"]?>"
                                                            <?=($_REQUEST["group_id"] == $group["id"] ? 'selected=""' : '')?>
                                                        > <?=$group["name"]?></option>
                                                <? } ?>
                                        </select>
                                    </div> -->

                                    <!-- <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Fanlar:</label>
                                        <select name="subject_id" id="subject_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? 
                                                foreach ($sciences as $science) { 
                                                    // $enrol = $db4->assoc("SELECT * FROM enrol WHERE id = ?", [ $enroment["enrolid"] ]);
                                                    // $subject = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $enrol["courseid"] ]);
                                            ?>
                                                    <option
                                                        value="<?=$science["id"]?>"
                                                        title="<?=$science["shortname"]?>"
                                                        <?=($_REQUEST["subject_id"] == $science["id"] ? 'selected=""' : '')?>
                                                        data-subtext="<?=date("Y-m-d", $science["startdate"])?>"
                                                    > <?=$science["fullname"]?></option>
                                            <? } ?>
                                        </select>
                                    </div> -->
                                                             
                                    <!-- <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Yo'nalish nomi:</label>
                                        <select name="direction_id" id="direction_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            
                                        </select>
                                    </div> -->
                                <? } ?>
                                <? if($systemUser["role"] != "student") { ?>
                                    <!-- <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Ta'lim shakli:</label>
                                        <select name="learn_type_id" id="learn_type_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            
                                        </select>
                                    </div> -->
                                <? } ?>

                                    <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Guruhlar:</label>
                                        <select name="group_id" id="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                <? 
                                                    foreach ($groups as $group) { 
                                                ?>
                                                        <option
                                                            value="<?=$group["id"]?>"
                                                            <?=($_REQUEST["group_id"] == $group["id"] ? 'selected=""' : '')?>
                                                        > <?=$group["name"]?></option>
                                                <? } ?>
                                        </select>
                                    </div>
                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Kurslar:</label>
                                    <select name="course_id" id="course_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        <? 
                                            foreach ($courses as $course) { 
                                        ?>
                                                <option
                                                    value="<?=$course["id"]?>"
                                                    <?=($_REQUEST["course_id"] == $course["id"] ? 'selected=""' : '')?>
                                                > <?=$course["name"]?></option>
                                        <? } ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Semester:</label>
                                    <select name="semester_id" id="semester_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        <? 
                                            foreach ($semesters as $semester) { 
                                        ?>
                                                <option
                                                    value="<?=$semester["id"]?>"
                                                    <?=($_REQUEST["semester_id"] == $semester["id"] ? 'selected=""' : '')?>
                                                > <?=$semester["name"]?></option>
                                        <? } ?>
                                    </select>
                                </div>
                                
                                <? if($systemUser["role"] == "student") {?>
                                    <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Turi:</label>
                                        <select name="type_id" id="type_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                <? 
                                                    foreach ($types as $type) { 
                                                ?>
                                                        <option
                                                            value="<?=$type["id"]?>"
                                                            <?=($_REQUEST["type_id"] == $type["id"] ? 'selected=""' : '')?>
                                                        > <?=$type["name"]?></option>
                                                <? } ?>
                                        </select>
                                    </div>

                                <? } ?>

                                    <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Fanlar:</label>
                                        <select name="subject_id" id="subject_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            
                                        </select>
                                    </div>

                                <? if($systemUser["role"] != "student") {?>
                                    <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Turi:</label>
                                        <select name="type_id" id="type_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                <? 
                                                    foreach ($types as $type) { 
                                                ?>
                                                        <option
                                                            value="<?=$type["id"]?>"
                                                            <?=($_REQUEST["type_id"] == $type["id"] ? 'selected=""' : '')?>
                                                        > <?=$type["name"]?></option>
                                                <? } ?>
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
                    </div>
                </div>
            </div>
        </div>
        <!-- end Filter -->

        <!-- Open modal for add date to Subject start -->
        
        <!-- Modal start -->
        <? if($systemUser["role"] != "student") { ?>
        <div class="modal fade" id="subModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?=$noDateSub ? 'Mavzularga sanasini kiriting' : 'Mavzu qo\'shish'?></h5>
                        <button type="button" class="close" data-bs-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="col-3">
                            <form method="POST">
                                <table style="min-width: 27.2rem !important;" class="table table-responsive-md mb-0 table-bordered">
                                    <tbody class="modal_items1">
                                        <?  foreach ($noDateSubjects as $noDateSubject) { ?>
                                            <?  $seeDate = $db4->assoc("SELECT date FROM course_sections WHERE id = ?", [ $noDateSubject["id"] ]);
                                                if($seeDate["date"] == '0000-00-00' || $seeDate["date"] == '') {?>
                                                <tr>
                                                    <td><input type="date" class="form-control" name="subject[<?=$noDateSubject["id"]?>][date]" style="opacity:1; width:100%; min-height: 55px;" id="date" /> </td>
                                                    <td>
                                                        <input type="text" class="form-control " name="subject[<?=$noDateSubject["id"]?>][name]" style=" height: 35px" value="<?=$noDateSubject["name"]?>">
                                                        <input type="hidden"  name="subject[<?=$noDateSubject["id"]?>][id]" style=" height: 35px" value="<?=$noDateSubject["id"]?>">
                                                    </td>
                                                </tr>
                                            <?  } ?>
                                        <?  } ?>
                                    </tbody>
                                </table>
                                <input type="submit" class="btn btn-primary m-2 " name="addSubject" value="Qo'shish">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <? } ?>
        <div class="modal fade" id="subModal2">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mavzu qo'shish</h5>
                        <button type="button" class="close" data-bs-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="col-3">
                            <form method="POST">
                                <table style="min-width: 27.2rem !important;" class="table table-responsive-md mb-0 table-bordered" id="subModal2Table">
                                    <tbody class="modal_items2">
                                        <? foreach ($subjects as $subject) {
                                            $subCount += 1;
                                            if($subject != '') { 
                                        ?>
                                                <tr>
                                                <? if($systemUser["role"] != "student") { ?>
                                                    <td><input type="date" data-who="date" name="subject[<?=$subject["id"]?>][date]" class="form-control modal_date<?=$subCount?>" value="<?=$subject["date"] != '0000-00-00' || $subject["date"] != null ? $subject["date"] : ''?>" style="opacity:1; width:100%; min-height: 55px;"/></td>
                                                    <td>
                                                        <input type="text" data-who="name" class="form-control modal_name<?=$subCount?>" name="subject[<?=$subject["id"]?>][name]" style=" height: 35px" value="<?=$subject["name"]?>">
                                                        <input type="hidden"  name="subject[<?=$subject["id"]?>][id]" style=" height: 35px" value="<?=$subject["id"]?>">
                                                    </td>
                                                <? } else {?>
                                                    <td><input disabled="disabled" type="date" data-who="date" name="subject[<?=$subject["id"]?>][date]" class="form-control modal_date<?=$subCount?>" value="<?=$subject["date"] != '0000-00-00' || $subject["date"] != null ? $subject["date"] : ''?>" style="opacity:1; width:100%; min-height: 55px;"/></td>
                                                    <td>
                                                        <input disabled="disabled" type="text" data-who="name" class="form-control modal_name<?=$subCount?>" name="subject[<?=$subject["id"]?>][name]" style=" height: 35px" value="<?=$subject["name"]?>">
                                                        <input disabled="disabled" type="hidden"  name="subject[<?=$subject["id"]?>][id]" style=" height: 35px" value="<?=$subject["id"]?>">
                                                    </td>
                                                <? } ?>
                                                </tr>
                                            <? } else { ?>
                                                <tr>
                                                    <? if($systemUser["role"] != "student") { ?>
                                                        <td><input type="date" oninput="onInput(this);" data-id="<?=$subCount?>" name="subject[]" class="form-control modal_date<?=$subCount?>" value="<?=$subject["date"] != '0000-00-00' || $subject["date"] != null ? $subject["date"] : ''?>" style="opacity:1; width:100%; min-height: 55px;"/></td>
                                                        <td><input type="text" class="form-control modal_name<?=$subCount?>" name="subject[]" style=" height: 35px" /></td>
                                                    <? } else {?>
                                                        <td><input disabled="disabled" type="date" oninput="onInput(this);" data-id="<?=$subCount?>" name="subject[]" class="form-control modal_date<?=$subCount?>" value="<?=$subject["date"] != '0000-00-00' || $subject["date"] != null ? $subject["date"] : ''?>" style="opacity:1; width:100%; min-height: 55px;"/></td>
                                                        <td><input disabled="disabled" type="text" class="form-control modal_name<?=$subCount?>" name="subject[]" style=" height: 35px" /></td>
                                                    <? } ?>
                                                </tr>
                                            <? } ?>
                                        <? } ?>
                                    </tbody>
                                </table>
                                <? if($systemUser["role"] != "student") { ?>
                                    <input type="submit" class="btn btn-primary m-2 " name="addSubject" value="Qo'shish">
                                <? } ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal end -->

        <!-- Open modal for add date to Subject end -->

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
                                <input type="hidden" id="type_id" value="<?=$_REQUEST["type_id"]?>">
                                <input type="hidden" class="modalDate" name="modalDate">
                                <input type="hidden" class="modalName" name="modalName">

                                <style>
                                    .tableWide-wrapper {
                                        overflow-x: auto;
                                        /* border-right: 2px solid #797979; */
                                        box-sizing: border-box;
                                        width: 100%;
                                        margin-bottom:20px;
                                    }
                                    .tableScrollTop {
                                        overflow-x: scroll;
                                        overflow-y: hidden;
                                        box-sizing: border-box;
                                        margin: 0;
                                        height:20px;
                                        width: 100%;
                                    }
                                </style>
                                
                                <div class="tableScrollTop dataTables_scrollBody"><div class="setWidth" style=" height:20px;"></div></div>
                                <div class="tableWide-wrapper dataTables_scrollBody">
                                    <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                        <? $student_count = 0; ?>
                                        <? if(!empty($_REQUEST["subject_id"])) {?>
                                        <thead>
                                            <tr>
                                                <th>№</th> 
                                                <th class="change_back" style="position: sticky; left: 0; z-index: 2;">F.I.SH</th>
                                                <?
                                                $subCount = 0;
                                                foreach ($subjects as $subject) {
                                                    $subCount += 1;
                                                    if($subject != "" && $subject["date"] != '' && $subject["date"] != '0000-00-00') {
                                                        echo '<th style="writing-mode: tb-rl; transform: rotate(-180deg);">'.date("m.d", strtotime($subject["date"]) ).'</th>';
                                                    } else if(!$subject["id"]) {
                                                        echo '<th class="p-0" data-id='.$subCount.' onclick="addSubject(this);"><input type="date" style="width:100%; min-height: 57px;" id="date" onchange="dateChange(this);" /></th>';
                                                    } else {
                                                        echo '<th class="p-0" data-id='.$subCount.' onclick="addSubject(this);"><input type="date" style="width:100%; min-height: 57px;" id="date" onchange="dateChange2(this);" /></th>';
                                                    }
                                                }
                                                ?>
                                                <th>JN</th>
                                                <th>ON</th>
                                                <th>YN</th>
                                            </tr>
                                        </thead>
                                        <tbody id="customers">
                                            <tr>
                                                <?
                                                    $subCount = 0;
    
                                                    echo '<th></th>';
                                                    echo '<th><button class="btn btn-sm btn-primary mx-2" onclick="openSubj(this);" type="button">Mavzular</button></th>';
                                                    foreach ($subjects as $subject) {
                                                        $subCount += 1;
                                                        echo '<th>'.$subCount.'</th>';
                                                    }
                                                    echo '<th></th>';
                                                    echo '<th></th>';
                                                    echo '<th></th>';
                                                ?>
                                            </tr>
                                            <? foreach ($user_enroments as $user_enroment) {
                                                $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $user_enroment["userid"] ]);
                                                $role_student = $db4->assoc("SELECT * FROM role_assignments WHERE userid = ?", [ $student["id"] ]);
                                                $JN = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'joriy nazorat%'", [ $_REQUEST["subject_id"] ]);
                                                $O1 = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'oraliq 1%' OR courseid = ? AND itemname LIKE 'oraliq nazorat%'", [ $_REQUEST["subject_id"], $_REQUEST["subject_id"] ]);
                                                $YN = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'yakuniy nazorat%'", [ $_REQUEST["subject_id"] ]);
                                            
                                                $jn_grade = $db4->assoc("SELECT * FROM grade_grades WHERE itemid = ? AND userid = ?", [ $JN["id"], $student["id"] ]);
                                                $o1_grade = $db4->assoc("SELECT * FROM grade_grades WHERE itemid = ? AND userid = ?", [ $O1["id"], $student["id"] ]);
                                                $yn_grade = $db4->assoc("SELECT * FROM grade_grades WHERE itemid = ? AND userid = ?", [ $YN["id"], $student["id"] ]);
    
                                                    if($student["id"] && $role_student["roleid"] == 5) {
                                                    $student_count += 1;
                                            ?>
                                                <tr>
                                                    <td><?=$student_count?></td>
                                                    <td class="change_back" style="position: sticky; left: 0; z-index: 2;"><?=$student["firstname"]. " " . $student["lastname"]?></td>
                                                    <? foreach ($subjects as $subject) {
                                                        $lesson_visit = $db->assoc("SELECT * FROM lessons_visits WHERE science_id = ? AND subject_id = ? AND type_id = ? AND student_id = ?", 
                                                        [ $_REQUEST["subject_id"], $subject["id"], $_REQUEST["type_id"], $student["id"] ]);
                                                        if($subject != "" && $subject["date"] != "" && $subject["date"] != "0000-00-00") { ?>
                                                            <? if($systemUser["role"] == "teacher" && $subject["date"] == date("Y-m-d") || $systemUser["role"] == "admin") { ?>
                                                                <td><input type="text" class="form-control" max="4" name="b[<?=$student['id']?>][<?=$subject['id']?>][baxo]" value="<?=$lesson_visit["finalgrade"] ? number_format($lesson_visit['finalgrade'], 2, ".", "") : ''?>" style="width:60px; height: 35px"></td>
                                                            <? } else if($systemUser["role"] == "student" || $systemUser["role"] == "teacher" && $subject["date"] != date("Y-m-d")) { ?>
                                                                <td><input type="text" disabled="disabled" class="form-control" value="<?=$lesson_visit['finalgrade']?>" style="width:60px; height: 35px"></td>
                                                            <? } ?>
                                                        <? } else {?>
                                                            <td></td>
                                                        <? } ?>
                                                    <? } ?>
                                                    <td><?=$jn_grade["finalgrade"] ? number_format($jn_grade["finalgrade"],2, ",", "") : ''?></td>
                                                    <td><?=$o1_grade["finalgrade"] ? number_format($o1_grade["finalgrade"],2, ",", "") : ''?></td>
                                                    <td><?=$yn_grade["finalgrade"] ? number_format($yn_grade["finalgrade"],2, ",", "") : ''?></td>
                                                </tr>
                                                    <? } ?>
                                            <? } ?>
                                        </tbody>
                                        <? } ?>
                                    </table>
                                </div>
                                <? if($systemUser["role"] != "student") { ?>
                                    <div class="d-flex justify-content-end">
                                        <input class="btn btn-primary m-2 disp_btn" name="submit" type="submit" value="Saqlash">
                                    </div>
                                <? } ?>
                            </form>
                        </div>
                            
                        <!-- Pagination -->
                        
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
    .ms-num {
        mso-number-format:General;
    }
    .ms-text{
        mso-number-format:"\@";/*force text*/
    }

    .bootstrap-select .dropdown-menu li.active small{
        color: #000 !important;
    }
    input[type=date]{
        position:relative;
        overflow:hidden;
        opacity: 0;
    }
    input[type=date]::-webkit-calendar-picker-indicator{
        display:block;
        top: 0;
        left:0;
        background: #0000;
        position:absolute;
        transform: scale(12)
    }
    #addSubject{
        cursor: pointer;
    }
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
if($systemUser["role"] == "admin" || $systemUser["role"] == "teacher") {
   if($noDateSub2 && !empty($noDateSub2["id"])) { 
?>
    <script>
        $(document).ready(function () {
            // $("#subModal").modal("show");
            // if(subject == 'noDate') {
                // $(".modal_items1").removeClass("d-none");
                // $(".modal_items2").addClass("d-none");
                $("#subModal").modal("show");
            // }
            // $("#subModal" ).on('shown.bs.modal', function(){
            //     // if(subject == 'noDate') {
            //         // $("#subModal").modal("hide");
            //         $("#subModal").modal("show");
            //         console.log('one shown '+ subject);
            //     // }
            // });

            // $("#subModal" ).on('hidden.bs.modal', function(){
            //     var subject2 = getCookie("subject");

            //     // if(subject2 == 'noDate') {
            //         $("#subModal").modal("show");
            //         console.log('again shown');
            //     // }
            // });
        })
    </script>
<? } 
} ?>

    <script> 
        // Subject start
        function openSubj() {
            $("#subModal2").modal("show");
        }
        function onInput(elm) {
            var attName = $(elm).attr('name');
            var attId = $(elm).attr('data-id');

            var unixTime = Math.floor(new Date($(elm).val()).getTime() / 1000);
            if(unixTime != NaN) {
                $(elm).attr('name', 'subject['+unixTime+'][date]')
                $('.modal_name'+attId).attr('name', 'subject['+unixTime+'][name]')
            }
            console.log($(elm).val());
            console.log(unixTime);
        }
        
        function addSubject(elm) {
            var modalDate = $(elm).attr("data-id");

            $(".modalDate").val('modal_date'+modalDate);
            $(".modalName").val('modal_name'+modalDate);
            console.log(modalDate);
        }

        function dateChange(e) {
            var dateValue = $(e).val();
            var modalDataVal = $(".modalDate").val();
            var modalNameVal = $(".modalName").val();
            // var attrName = $('.'+modalNameVal).attr('name', 'subject[noId][name]');
            var unixTime = Math.floor(new Date(dateValue).getTime() / 1000);
            var attrDate = $('.'+modalDataVal).attr('name', 'subject['+unixTime+'][date]');
            var attrName = $('.'+modalNameVal).attr('name', 'subject['+unixTime+'][name]');
            $(".openSubjects").removeClass("d-none");

            // $(".modal_items1").addClass("d-none");
            // $(".modal_items1").html("");
            // $(".modal_items2").removeClass("d-none");
            $('.'+modalDataVal).val(dateValue);
            $("#subModal2").modal("show");
        }
        
        function dateChange2(e) {
            var dateValue = $(e).val();
            var modalDataVal = $(".modalDate").val();
            var modalNameVal = $(".modalName").val();

            console.log(modalDataVal);
            // console.log(modalNameVal);
            // var attrName = $('.'+modalNameVal).attr('name', 'subject[noId][name]');
            // // var unixTime = Math.floor(new Date(dateValue).getTime() / 1000);
            // var attrDate = $('.'+modalDataVal).attr('name', 'subject['+unixTime+'][date]');
            // var attrName = $('.'+modalNameVal).attr('name', 'subject['+unixTime+'][name]');
            // $(".openSubjects").removeClass("d-none");

            // // $(".modal_items1").addClass("d-none");
            // // $(".modal_items1").html("");
            // // $(".modal_items2").removeClass("d-none");
            $('.'+modalDataVal).val(dateValue);
            $("#subModal2").modal("show");
        }

        // Subject end
        var groupId = <?=$_REQUEST["group_id"] ? $_REQUEST["group_id"] : 0?>;
        // var getLearnId = <?=$_REQUEST["learn_type_id"] ? $_REQUEST["learn_type_id"] : 0?>;
        var getCourseId = <?=$_REQUEST["course_id"] ? $_REQUEST["course_id"] : 0?>;
        var getSemesterId = <?=$_REQUEST["semester_id"] ? $_REQUEST["semester_id"] : 0?>;
        var getSubjectId = <?=$_REQUEST["subject_id"] ? $_REQUEST["subject_id"] : 0?>;

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
        $('#group_id').change(function() {

            $('#course_id').change();
            $('#course_id').selectpicker('refresh');

            // $.ajax({
            //     url: '/api',
            //     type: "POST",
            //     data: {
            //         method: 'filterGroup1',
            //         group_id: group_id,
            //         course_id: courseId,
            //         semester_id: semesterId,
            //     },
            //     dataType: "json",
            //     success: function(data) {
            //         if (data.ok == true) {

            //             // semesterId = data.semester.id;
            //             // courseId = data.whereCourse.id;
            //             // learn_typeId = data.learn_type.id;
                        
            //             // $("#subject_id").html('');
            //             // data.subjects.forEach(subject => {
            //             //     if(subject.id == getSubjectId) {
            //             //         $("#subject_id").append(`<option value="${subject.id}" selected='' title="${subject.shortname}" data-subtext="${subject.startdate}"> ${subject.fullname}</option>`)
            //             //     } else {
            //             //         $("#subject_id").append(`<option value="${subject.id}" title="${subject.shortname}" data-subtext="${subject.startdate}"> ${subject.fullname}</option>`)
            //             //     }
            //             // });
            //             updateTable();

            //             $("#form").removeClass('d-none');
            //             $('#course_id').change();
            //             $('#course_id').selectpicker('refresh');
            //         } else {
            //             console.log(data.errorCourse);
            //             $("#form").addClass('d-none');
            //             // $("#course_id").html('');
            //             // $('#course_id').selectpicker('refresh');
            //             // $("#semester_id").html('');
            //             // $('#semester_id').selectpicker('refresh');
            //             $("#subject_id").html('');
            //             $('#subject_id').selectpicker('refresh');
            //             $(".modal-title").text(`Bu Guruhga tegishli fanlar mavjud emas`);
            //             $(".modal_err").modal("show");
            //         }
            //     },
            //     error: function() {
            //         alert("Xatolik yuzaga keldi");
            //     }
            // })
        });
        
        $('#course_id').on("change", function() {

            $('#semester_id').change();
            $('#semester_id').selectpicker('refresh');

            // $.ajax({
            //     url: '/api',
            //     type: "POST",
            //     data: {
            //         method: 'filterCourse',
            //         group_id: group_id,
            //         course_id: course_id,
            //         semester_id: semester_id,
            //     },
            //     dataType: "json",
            //     success: function(data) {
            //         if (data.ok == true) {

            //             // $("#subject_id").html('');
            //             // data.subjects.forEach(subject => {
            //             //     if(subject.id == getSubjectId) {
            //             //         $("#subject_id").append(`<option value="${subject.id}" selected='' title="${subject.shortname}" data-subtext="${subject.startdate}"> ${subject.fullname}</option>`)
            //             //     } else {
            //             //         $("#subject_id").append(`<option value="${subject.id}" title="${subject.shortname}" data-subtext="${subject.startdate}"> ${subject.fullname}</option>`)
            //             //     }
            //             // });

            //             updateTable();
            //             $("#form").removeClass('d-none');
            //             $('#semester_id').change();
            //             $('#semester_id').selectpicker('refresh');
            //         } else {
            //             console.log(data.errorCourse);
            //             $("#form").addClass('d-none');
            //             $("#subject_id").html('');
            //             $('#subject_id').selectpicker('refresh');
            //             $(".modal-title").text(`Bu kursga tegishli fanlar mavjud emas `);
            //             $(".modal_err").modal("show");
            //         }
            //     },
            //     error: function() {
            //         alert("Xatolik yuzaga keldi");
            //     }
            // })
        });
        
        $('#semester_id').change(function() {
            var group_id = $("#group_id :selected").val();
            var course_id = $("#course_id :selected").val();
            var semester_id = $(this).val();

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'filterSemester2',
                    group_id: group_id,
                    course_id: course_id,
                    semester_id: semester_id,
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {

                        $("#subject_id").html('');
                        data.subjects.forEach(subject => {
                            if(subject.id == getSubjectId) {
                                $("#subject_id").append(`<option value="${subject.id}" selected='' title="${subject.shortname}" data-subtext="${subject.startdate}"> ${subject.fullname}</option>`)
                            } else {
                                $("#subject_id").append(`<option value="${subject.id}" title="${subject.shortname}" data-subtext="${subject.startdate}"> ${subject.fullname}</option>`)
                            }
                        });
                        
                        updateTable();
                        $("#form").removeClass('d-none');
                        $('#subject_id').change();
                        $('#subject_id').selectpicker('refresh');
                    } else {
                        console.log(data.errorCourse);
                        $("#form").addClass('d-none');
                        $("#subject_id").html('');
                        $('#subject_id').selectpicker('refresh');
                        $(".modal-title").text(`Bu Semestr tegishli fanlar mavjud emas `);
                        $(".modal_err").modal("show");
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        });

        $("#group_id").change();

        $(".tableScrollTop,.tableWide-wrapper").scroll(function(){
            $(".tableWide-wrapper,.tableScrollTop")
                .scrollLeft($(this).scrollLeft());
        });
    </script>

<?
include "system/end.php";
?>