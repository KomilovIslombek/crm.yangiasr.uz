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

// $startdateCourse = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $_REQUEST["subject_id"] ]);
// $breadCumpDate = date("Y-m-d", $startdateCourse["startdate"]);
// $breadCumpEndDate = date("Y-m-d", $startdateCourse["enddate"]);
// $tomorowDateCourse = date('Y-m-d', strtotime('+1 day', $startdateCourse["startdate"]));


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

if (!empty($_REQUEST["subject_id"])) {
    $O1 = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'oraliq 1%' OR courseid = ? AND itemname LIKE 'oraliq nazorat%'", [ $_REQUEST["subject_id"], $_REQUEST["subject_id"] ]);
    $YN = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'yakuniy nazorat%'", [ $_REQUEST["subject_id"] ]);
    $YB = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemtype = ?", [ $_REQUEST["subject_id"], 'course' ]);
    $JN = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'joriy nazorat%'", [ $_REQUEST["subject_id"] ]);

    $JQ = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'joriy qayta topshirish%'", [ $_REQUEST["subject_id"] ]);
    $OB = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'oraliq baholash%' OR courseid = ? AND itemname LIKE 'oraliq baxolash%'", [ $_REQUEST["subject_id"], $_REQUEST["subject_id"] ]);
    $OQ1 = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'Oraliq qayta 1%'", [ $_REQUEST["subject_id"] ]);
    
    $grade_items = [];


    $JN["id"] ? array_push($grade_items, $JN) : '';
    $O1["id"] ? array_push($grade_items, $O1) : '';
    $OQ1["id"] ? array_push($grade_items, $OQ1) : '';
    $JQ["id"] ? array_push($grade_items, $JQ) : '';
    $OB["id"] ? array_push($grade_items, $OB) : '';
    $YN["id"] ? array_push($grade_items, $YN) : '';
    $YB["id"] ? array_push($grade_items, $YB) : '';

    
    // if($O1["id"] && $YN["id"]) {
    //     if($JN["id"]){
    //         $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = $_REQUEST[subject_id] AND id = $O1[id] OR courseid = $_REQUEST[subject_id] AND id = $YN[id]  
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy qayta topshirish%' OR courseid = $_REQUEST[subject_id] AND id = $JN[id] OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq 1%'
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq qayta 1%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baholash%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baxolash%' OR courseid = $_REQUEST[subject_id] AND itemtype = 'course' ORDER BY itemname DESC
    //         "); 
    //     } else {
    //         $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = $_REQUEST[subject_id] AND id = $O1[id] OR courseid = $_REQUEST[subject_id] AND id = $YN[id]  
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy qayta topshirish%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy nazorat%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq 1%'
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq qayta 1%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baholash%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baxolash%' OR courseid = $_REQUEST[subject_id] AND itemtype = 'course' ORDER BY itemname DESC
    //         "); 
    //     }
    // } else if($O1["id"]) {
    //     if($JN["id"]){
    //         $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = $_REQUEST[subject_id] AND id = $O1[id] OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'yakuniy nazorat%' 
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy qayta topshirish%' OR courseid = $_REQUEST[subject_id] AND id = $JN[id] OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq 1%'
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq qayta 1%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baholash%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baxolash%' OR courseid = $_REQUEST[subject_id] AND itemtype = 'course' ORDER BY itemname DESC
    //         "); 
    //     } else {
    //         $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = $_REQUEST[subject_id] AND id = $O1[id] OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'yakuniy nazorat%' 
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy qayta topshirish%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy nazorat%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq 1%'
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq qayta 1%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baholash%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baxolash%' OR courseid = $_REQUEST[subject_id] AND itemtype = 'course' ORDER BY itemname DESC
    //         "); 
    //     }
    // } else if($YN["id"]) {
    //     if($JN["id"]){
    //         $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq 1%' OR courseid = $_REQUEST[subject_id] AND id = $YN[id]
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy qayta topshirish%' OR courseid = $_REQUEST[subject_id] AND id = $JN[id] OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq 1%'
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq qayta 1%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baholash%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baxolash%' OR courseid = $_REQUEST[subject_id] AND itemtype = 'course' ORDER BY itemname DESC
    //         ");
    //     } else {
    //         $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq 1%' OR courseid = $_REQUEST[subject_id] AND id = $YN[id]
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy qayta topshirish%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy nazorat%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq 1%'
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq qayta 1%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baholash%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baxolash%' OR courseid = $_REQUEST[subject_id] AND itemtype = 'course' ORDER BY itemname DESC
    //         "); 
    //     }
    // } else {
    //     if($JN["id"]){
    //         $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq 1%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'yakuniy nazorat%'  
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy qayta topshirish%' OR courseid = $_REQUEST[subject_id] AND id = $JN[id] OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq 1%'
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq qayta 1%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baholash%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baxolash%' OR courseid = $_REQUEST[subject_id] AND itemtype = 'course' ORDER BY itemname DESC
    //         "); 
    //     } else {
    //         $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq 1%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'yakuniy nazorat%'  
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy qayta topshirish%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy nazorat%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq 1%'
    //         OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq qayta 1%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baholash%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baxolash%' OR courseid = $_REQUEST[subject_id] AND itemtype = 'course' ORDER BY itemname DESC
    //         "); 
    //     }
    // }

    // $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = $_REQUEST[subject_id] AND id = $ON[id] OR courseid = $_REQUEST[subject_id] AND id = $YN[id]
    // OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq qayta 1%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'joriy baxolash%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baholash%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE 'oraliq baxolash%' OR courseid = $_REQUEST[subject_id] AND itemtype = 'course' ORDER BY itemname DESC");

    // $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = $_REQUEST[subject_id]
    // AND itemname LIKE '%joriy nazorat%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE '%oraliq 1%' AND itemtype = 'course'
    // OR courseid = $_REQUEST[subject_id] AND itemname LIKE '%yakuniy nazorat%' OR courseid = $_REQUEST[subject_id]
    // OR courseid = $_REQUEST[subject_id] AND itemname LIKE '%joriy baxolash%' OR courseid = $_REQUEST[subject_id] AND itemname LIKE '%oraliq 1%'
    // OR courseid = $_REQUEST[subject_id] AND itemname LIKE '%oraliq qayta 1%' ORDER BY itemname DESC");
    // $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = ? ORDER BY itemname DESC", [ $_REQUEST["subject_id"] ]);
    // $JN = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE '%joriy nazorat%'", [ $_REQUEST["subject_id"] ]);
    // $JQ = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'joriy qayta topshirish%'", [ $_REQUEST["subject_id"] ]);
    // $JB = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'Joriy baholash%' OR courseid = ? AND itemname LIKE 'Joriy baxolash%'", [ $_REQUEST["subject_id"], $_REQUEST["subject_id"] ]);
    // $OB = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'oraliq baholash%' OR courseid = ? AND itemname LIKE 'oraliq baxolash%'", [ $_REQUEST["subject_id"], $_REQUEST["subject_id"] ]);
    // $OQ1 = $db4->assoc("SELECT * FROM grade_items WHERE courseid = ? AND itemname LIKE 'Oraliq qayta 1%'", [ $_REQUEST["subject_id"] ]);
    
}

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
                        if($grade_item["id"]) {
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

                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Fanlar:</label>
                                    <select name="subject_id" id="subject_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        
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
                            <form method="POST" id="form">
                                <input type="hidden" id="learn_type_id" value="<?=$_REQUEST["learn_type_id"]?>">
                                <input type="hidden" id="course_id" value="<?=$_REQUEST["course_id"]?>">
                                <input type="hidden" id="semester_id" value="<?=$_REQUEST["semester_id"]?>">
                                <input type="hidden" id="subject_id" value="<?=$_REQUEST["subject_id"]?>">

                                <div class="tableScrollTop dataTables_scrollBody"><div class="setWidth" style="height:20px;"></div></div>
                                <div class="tableWide-wrapper dataTables_scrollBody">
                                    <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                        <? $student_count = 0; ?>
                                        <? if(!empty($_REQUEST["subject_id"])) {?>
                                        <thead>
                                            <tr>
                                                <th>№</th>
                                                <th class="change_back" style="position: sticky; left: 0; z-index: 2;">F.I.SH</th>
                                                <th title="Joriy nazorat">JB</th>
                                                <? 
                                                if(!$JN["id"]) {?>    
                                                    <th title="Joriy nazorat" class="bg-danger">JN</th>
                                                <? } 
                                                if(!$JQ["id"]) { ?>
                                                    <th title="Joriy qayta topshirish" class="bg-danger">JQ</th>
                                                <? } 
                                                if(!$OB["id"]) { ?>
                                                    <th title="Oraliq baholash" class="bg-danger">OB</th>
                                                <? }
                                                if(!$O1["id"]) {?>
                                                    <th title="Oraliq 1" class="bg-danger">O1</th>
                                                <? } 
                                                if(!$OQ1["id"] && !$O1["id"]) { ?>
                                                    <th title="Oraliq qayta 1" class="bg-danger">OQ1</th>
                                                <? } 
                                                if(!$YN["id"]) { ?>
                                                    <th title="Yakuniy nazorat" class="bg-danger">YN</th>
                                                <?
                                                }
                                                foreach ($grade_items as $grade_item) {
                                                    if($grade_item["itemtype"] == "course") {
                                                        echo "<th title='Yakuniy baho'>YB </th>";
                                                        echo "<th title='Umumiy baho'>UB </th>";
                                                        echo '<th>GPA</th>';
                                                    } else {
                                                        if($YN["id"] == $grade_item["id"]) {
                                                            echo "<th title='Yakuniy nazorat'>YN</th>";
                                                        } else if($O1["id"] ==  $grade_item["id"]) {
                                                            echo "<th title=".$grade_item["itemname"].">O1</th>";
                                                            echo '<th title="Oraliq qayta 1" class="bg-danger">OQ1</th>';
                                                        }  else if($JN["id"] == $grade_item["id"]) {
                                                            echo "<th title='Joriy nazorat'>JN</th>";
                                                        } else {
                                                            echo '<th>'.$grade_item["itemname"].'</th>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody id="customers">
                                            <? foreach ($user_enroments as $user_enroment) {
                                                $resultStudJb = null;
                                                $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $user_enroment["userid"] ]);
                                                $role_student = $db4->assoc("SELECT * FROM role_assignments WHERE userid = ?", [ $student["id"] ]);
                                                $gradesStudentJbs = $db->in_array("SELECT finalgrade FROM lessons_visits WHERE science_id = ? AND student_id = ?", [ $_REQUEST["subject_id"], $student["id"] ]);
                                                // $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $course_user["userid"] ]);
                                                if($gradesStudentJbs != '') {
                                                    foreach($gradesStudentJbs as $gradesStudentJn) {
                                                        $resultStudJb += $gradesStudentJn["finalgrade"];
                                                    }
                                                }
                                                    if($student["id"] && $role_student["roleid"] == 5) {
                                                        $student_count += 1;
                                                        $yakuniyBaxo = 0;
                                                        $umumiyBaxo = 0;
                                            ?>
                                                <tr>
                                                    <td><?=$student_count?></td>
                                                    <td class="change_back" style="position: sticky; left: 0; z-index: 2;"><?=$student["firstname"]. " " . $student["lastname"]?></td>
                                                    <td><?=$resultStudJb ? $resultStudJb : ''?></td>
                                                    <? 
                                                    if(!$JN["id"]) {?>    
                                                        <td class="bg-danger"></td>
                                                    <? } 
                                                    if(!$JQ["id"]) { ?>
                                                        <td class="bg-danger"></td>
                                                    <? } 
                                                    if(!$OB["id"]) { ?>
                                                        <td class="bg-danger"></td>
                                                    <? }
                                                    if(!$O1["id"]) {?>
                                                        <td class="bg-danger"></td>
                                                    <? } 
                                                    if(!$OQ1["id"] && !$O1["id"]) { ?>
                                                        <td class="bg-danger"></td>
                                                    <? } 
                                                    if(!$YN["id"]) { ?>
                                                        <td class="bg-danger"></td>
                                                    <? } ?>
                                                    <? foreach ($grade_items as $grade_item) {
                                                        $grade_grade = $db4->assoc("SELECT * FROM grade_grades WHERE itemid = ? AND userid = ?", [ $grade_item["id"], $student["id"] ]);
                                                        // $grade_grades = $db4->in_array("SELECT * FROM grade_grades WHERE itemid = ?", [ $grade_item["id"] ]);
                                                        
                                                        if($OB["id"] && $OB["id"] == $grade_item["id"]) {
                                                            $umumiyBaxo += (int)number_format($grade_grade["finalgrade"], 0, "", "");
                                                        }
                                                        if($grade_item["itemtype"] != "course") {
                                                            $yakuniyBaxo += (int)number_format($grade_grade["finalgrade"], 0, "", "");
                                                        }
                                                           if($grade_item["itemtype"] != "course" ) {
                                                            if($O1["id"] && $O1["id"] == $grade_item["id"]) {
                                                        ?>
                                                            <td><?=$grade_grade["finalgrade"] ? number_format($grade_grade["finalgrade"],2, ",", "") : ''?></td>
                                                            <td class="bg-danger"></td>
                                                            <? } else { ?>
                                                            <td><?=$grade_grade["finalgrade"] ? number_format($grade_grade["finalgrade"],2, ",", "") : ''?></td>
                                                            <? } ?>
                                                        <? } else {?>
                                                            <?$umumiyBaxo += (int)$resultStudJb;?>
                                                            <?$umumiyBaxo += (int)$yakuniyBaxo;?>
                                                            <td><?=$yakuniyBaxo ? number_format($yakuniyBaxo, 2, ",", "") : ''?></td>
                                                            <td><?=$umumiyBaxo ? number_format($umumiyBaxo, 2, ",", "") : ''?></td>
                                                        <? } ?>
                                                        <?
                                                            if($grade_item["itemtype"] == "course") {
                                                                $gpa = (int)$umumiyBaxo;
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
                                                    <? } ?>
                                                </tr>
                                                    <? } ?>
                                            <? } ?>
                                        </tbody>
                                        <? } ?>
                                    </table>
                                </div>

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
    .tableWide-wrapper {
        overflow-x: auto !important;
        box-sizing: border-box !important;
        width: 100% !important;
        margin-bottom:20px !important;
    }
    .tableScrollTop {
        overflow-x: auto !important;
        box-sizing: border-box;
        overflow-y: hidden !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        height:20px !important;
        width: 100% !important;
    }
    .ms-num {
        mso-number-format:General;
    }
    .ms-text{
        mso-number-format:"\@";/*force text*/
    }
    .bootstrap-select .dropdown-menu li.active small{
        color: #000 !important;
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
 if($systemUser["role"] == "admin") {
?>
    <script> 

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
            $("#subject_id").html('');
            $('#subject_id').selectpicker('refresh');
            $('#course_id').change();
            $('#course_id').selectpicker('refresh');

        });
        
        $('#course_id').on("change", function() {
            $("#subject_id").html('');
            $('#subject_id').selectpicker('refresh');
            $('#semester_id').change();
            $('#semester_id').selectpicker('refresh');

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
                        
                        if(data.subjects != '') {
                            updateTable();
                        }
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

<? } else {?>
    <script>

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
            $("#subject_id").html('');
            $('#subject_id').selectpicker('refresh');
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
            $("#subject_id").html('');
            $('#subject_id').selectpicker('refresh');
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
                        if(data.subjects != '') {
                            updateTable();
                        }
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
}
include "system/end.php";
?>