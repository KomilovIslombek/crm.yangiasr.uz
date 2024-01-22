<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_GET['page'];
if (empty($page)) $page = 1;

$group_id = isset($_REQUEST["group_id"]) ? $_REQUEST["group_id"] : null;
if (!$group_id) {echo"error group_id not found";return;}

$group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $group_id ]);
if (empty($group["id"])) exit(http_response_code(404));

$get_group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $group_user["group_id"] ]);
$group_users = $db->in_array("SELECT * FROM group_users WHERE group_id = ?", [ $group["id"] ]);
$science = $db->assoc("SELECT * FROM group_sciences WHERE group_id = ?", [ $group["id"] ]);
$science_subjects = $db->in_array("SELECT * FROM science_subjects WHERE science_id = ?", [ $science["id"] ]);
$path = explode("/", $_REQUEST["group_id"]);


include "system/head.php";

$breadcump_title_1 = "Guruh:";
$breadcump_title_2 = "$group[name]";

// $image = fileArr($group_teacher["image_id"]);

// if ($_REQUEST["type"] == "deleteGroupStudent"){ 
//     if ($_POST["student_code"]) {
//         $db->delete("group_users", $_POST["student_code"], "student_code");

//         header("Location: /viewGroup/?group_id=" . $group["id"]);
//         exit;
//     }
// }
if ($_REQUEST["type"] == "addSubject"){ 
    $scienceSubject_id = $db->insert("science_subjects", [
        "creator_user_id" => $user_id,
        "subject_id" => $_REQUEST["subject_id"],
        "science_id" => $_REQUEST["science_id"],
        "subject_date" => $_REQUEST["subject_date"],
    ]);
    
    if ($scienceSubject_id > 0) {
        header("Location: ".$url[0]."/?group_id=".$_REQUEST["group_id"]);
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
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="profile-tab">
                            <div class="custom-tab-1">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item" role="presentation"><a href="#add-subject" data-page-href="add-subject" data-bs-toggle="tab" class="nav-link <?=$path[1] == 'add-subject' ? 'active' : '' ?>" aria-selected="true" role="tab">Mavzular</a>
                                    </li>
                                    <!-- <li class="nav-item" role="presentation"><a href="#payments-me" data-bs-toggle="tab" class="nav-link" aria-selected="false" tabindex="-1" role="tab">To'lovlar</a>
                                    </li> -->
                                    <li class="nav-item" role="presentation"><a href="#lesson-list" data-page-href="lesson-list" data-bs-toggle="tab" class="nav-link <?=$path[1] == 'lesson-list' ? 'active' : '' ?>" aria-selected="false" tabindex="-1" role="tab">Dars jadvali</a>
                                    </li>
                                    <li class="nav-item" role="presentation"><a href="#attendance" data-page-href="attendance" data-bs-toggle="tab" class="nav-link <?=$path[1] == 'attendance' ? 'active' : '' ?>" aria-selected="false" tabindex="-1" role="tab">Davomat</a>
                                    </li>
                                    <li class="nav-item" role="presentation"><a href="#students" data-page-href="students" data-bs-toggle="tab" class="nav-link <?=$path[1] == 'students' ? 'active' : '' ?>" aria-selected="false" tabindex="-1" role="tab">O'quvchilar</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <!-- addSubject - Fanga mavzu qoshish -->
                                    <div id="add-subject" class="tab-pane fade <?=$path[1] == 'add-subject' ? 'active show' : '' ?>" role="tabpanel">
                                        <div class="add-subject">
                                            <div class="pt-4 border-bottom-1 pb-2 d-flex justify-content-between align-items-center">
                                                <ol class="breadcrumb">
                                                    <h4 class="text-primary"><i class="fa-sharp fa-solid fa-bolt"></i> Mavzular</h4>
                                                </ol>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <table class="table mb-5  table-hover table-responsive" id="table1">
                                                    <thead class="bordered">
                                                        <tr>
                                                            <th scope="col">#id</th>
                                                            <th scope="col">#Mavzu qaysi fanga tegishliligi</th>
                                                            <th scope="col">Mavzu nomi</th>
                                                            <th scope="col">Mavzu sanasi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="customers">
                                                        <? foreach($science_subjects as $science_subject) { 
                                                            $subject = $db->assoc("SELECT * FROM subjects WHERE id = ?", [ $science_subject["subject_id"] ]);
                                                            $science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [ $science_subject["science_id"] ]);
                                                        ?>
                                                            <tr class="hover-dark">
                                                                <td class="table-light" scope="row"><?=$science_subject["id"]?></td>
                                                                <td class="table-light"><?=$science["name"]?></td>
                                                                <td class="table-light"><?=$subject["name"]?></td>
                                                                <td class="table-light"><?=$science_subject["subject_date"]?></td>
                                                            </tr>
                                                        <? } ?>
                                                    </tbody>
                                                </table>
                                                <form action="" method="POST" enctype="multipart/form-data">
                                                    <input type="hidden" name="type" value="addSubject">

                                                    <div class="form-row">
                                                        <?=getError("subject_id")?>
                                                        <div class="form-group col-12">
                                                            <label>Mavzular royxati</label>
                                                            <select name="subject_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                                <? foreach ($db->in_array("SELECT * FROM subjects") as $subject) { ?>
                                                                    <option value="<?=$subject["id"]?>"><?=$subject["name"]?></option>
                                                                <? } ?>
                                                            </select>
                                                        </div>
                                                        
                                                        <?=getError("science_id")?>
                                                        <div class="form-group col-12">
                                                            <label>Fanlar royxati</label>
                                                            <select name="science_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                                <? foreach ($db->in_array("SELECT * FROM group_sciences WHERE group_id = ?", [ $group["id"] ]) as $science) { 
                                                                    $science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [ $science["science_id"] ]);    
                                                                ?>
                                                                    <option value="<?=$science["id"]?>"><?=$science["name"]?></option>
                                                                <? } ?>
                                                            </select>
                                                        </div>
                                                        
                                                        <?=getError("subject_date")?>
                                                        <div class="form-group col-12">
                                                            <label>Mavzu sanasi</label>
                                                            <input type="date" name="subject_date" class="form-control" placeholder="Mavzu sanasi" value="<?=$_POST["subject_date"]?>">
                                                        </div>

                                                    <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                                        <button type="submit" class="btn btn-primary">Saqlash</button>
                                                    </div>
                                                
                                                </form>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <!-- Lessons list - Dars jadvali -->
                                    <div id="lesson-list" class="tab-pane fade <?=$path[1] == 'lesson-list' ? 'active show' : '' ?>" role="tabpanel">
                                        <div class="lesson-me">
                                            <div class="pt-4 border-bottom-1 pb-2 d-flex justify-content-between align-items-center">
                                                <h4 class="mb-0 text-primary"><i class="fa-solid fa-object-group"></i> Darslar</h4>
                                                <a href="javascript:void(0)" class="btn btn-primary rounded me-3 mb-sm-0 mb-2" id="exportToExcel2">
                                                    <i class="fa fa-upload me-3 scale5" aria-hidden="true"></i>Export
                                                </a>
                                            </div>
                                        </div>
                                        <hr>
                                        <? 
                                            $all_lessons_count = $group["all_lessons_count"];
                                            $lesson_start_date = $group["lesson_start_date"];
                                            $allowed_days = [
                                                "ya" => $group["ya"],
                                                "du" => $group["du"],
                                                "se" => $group["se"],
                                                "cho" => $group["cho"],
                                                "pa" => $group["pa"],
                                                "ju" => $group["ju"],
                                                "sha" => $group["sha"]
                                            ];
                                            $allowed_days_filtered = array_filter($allowed_days, function($val){
                                                if ($val == 1) return $val;
                                            });

                                            $week_days = [
                                                "Yakshanba",
                                                "Dushanba",
                                                "Seshanba",
                                                "Chorchanba",
                                                "Payshanba",
                                                "Juma",
                                                "Shanba"
                                            ];
                                              
                                            $week_days_eng = [
                                                "Sunday",
                                                "Monday",
                                                "Tuesday",
                                                "Wednesday",
                                                "Thursday",
                                                "Friday",
                                                "Saturday"
                                            ];
                                            
                                            $months = [
                                                "Yanvar",
                                                "Fevral",
                                                "Mart",
                                                "Aprel",
                                                "May",
                                                "Iyun",
                                                "Iyul",
                                                "Avgust",
                                                "Sentabr",
                                                "Oktabr",
                                                "Noyabr",
                                                "Dekabr",
                                            ];

                                            function getNextDayKey($lesson_start_date, $day_key) {
                                                // print_r([ $lesson_start_date, $day_key ]);
                                                
                                                global $allowed_days, $week_days_eng;
                                                
                                                $day_founded = false;
                                                $next_day_key = false;
                                                $num = 0;
                                                $first_allowed_day = false;
                                                
                                                foreach ($allowed_days as $week_name => $val) {
                                                    if ($val == 1 && !$first_allowed_day) $first_allowed_day = $week_days_eng[$num];
                                                
                                                    if ($day_key == $num) {
                                                    $day_founded = true;
                                                    }
                                                    
                                                    if ($val == 1 && $day_founded && $day_key != $num) {
                                                    // print_r([
                                                    //   "val" => $val,
                                                    //   "day_founded" => $day_founded,
                                                    //   "day_key" => $day_key,
                                                    //   "num" => $num
                                                    // ]);
                                                
                                                    $next_day_key = $num;
                                                    $day_founded = false;
                                                
                                                    // echo "next $week_days_eng[$next_day_key] $lesson_start_date\n";
                                                    return date("Y-m-d", strtotime("next $week_days_eng[$next_day_key] $lesson_start_date"));
                                                    }
                                                    $num++;
                                                }
                                                
                                                return date("Y-m-d", strtotime("next $first_allowed_day $lesson_start_date"));
                                            }

                                            $days = [];
                                            // $learning_months = [];

                                            // $all_arr = [];
                                            // $count_arr = [];

                                            $umumiy_arr = [];
                                            $ishchi_arr = [];
                                            

                                            foreach (range(1, $all_lessons_count) as $lesson) {
                                                
                                                // $lesson_month = date("m", strtotime($lesson_start_date));
                                                // // print( "oylar". $lesson_month . "<br>");
                                                // if (!in_array($lesson_month, $learning_months)) {
                                                //     array_push($learning_months, $lesson_month);
                                                // }
                                                
                                                $day_key = date("w", strtotime($lesson_start_date));
                                                // print( "kunlar". $day_key . "<br>");

                                                array_push($ishchi_arr, date("Y-m-d", strtotime($lesson_start_date)));
                                            
                                                if (count($ishchi_arr) == $group["one_monthly_lessons_count"]) {
                                                    array_push($umumiy_arr, $ishchi_arr);
                                                    $ishchi_arr = [];
                                                }

                                                array_push($days, date("Y-m-d", strtotime($lesson_start_date)));
                                                // print(date("Y-m-d", strtotime($lesson_start_date)));
                                                // echo "$lesson-dars) " . date("Y-m-d", strtotime($lesson_start_date)) . " " . $week_days[$day_key] . "\n";
                                                
                                                $next_date = getNextDayKey($lesson_start_date, $day_key);
                                                if (!$next_date) $next_date = getNextDayKey($lesson_start_date, $day_key);
                                                if ($next_date) $lesson_start_date = $next_date;
                                            }

                                            $learning_months = [];
                                            
                                            foreach ($db->in_array("SELECT * FROM science_subjects WHERE science_id = ?", [ $science["id"] ]) as $science_subject) { 
                                                $lesson_month = date("m", strtotime($science_subject["subject_date"]));
                                                // print( "oylar". $lesson_month . "<br>");
                                                if (!in_array($lesson_month, $learning_months)) {
                                                    array_push($learning_months, $lesson_month);
                                                }
                                            }
                                        ?>
                                        <div class="d-md-flex d-block mb-3 pb-3 mt-3 border-bottom">
                                            <div class="card-action card-tabs mb-md-0 mb-2  me-auto">
                                                <ul class="nav nav-tabs tabs-lg w-100">
                                                    <? foreach ($learning_months as $month) { ?>
                                                        <li class="nav-item">
                                                            <a href="#navpills-<?=$months[($month-1)]?>" class="lesson_links nav-link py-2 px-1 " data-bs-toggle="tab" aria-expanded="false"></span><?=$months[($month-1)]?></a>
                                                        </li>
                                                    <? } ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="tab-content" id="table2"> 
                                                <? foreach ($learning_months as $month) { ?>
                                                    <div class="tab-pane fade show "id="navpills-<?=$months[($month-1)]?>" role="tabpanel">
                                                        <div class="row loadmore-content" id="RecentActivitiesContent">
                                                            <table class="table table-striped table-hover table-bordered table-responsive-sm" >
                                                                <tbody>
                                                                    <tr class="remove_tr">
                                                                        <th>№</th>
                                                                        <th>Dars sanasi</th>
                                                                        <th>Dars kunlari</th>
                                                                        <th>Dars boshlanish vaqti</th>
                                                                        <th>Dars tugash vaqti</th>
                                                                        <th>Fan nomi</th>
                                                                    </tr>

                                                                    <?
                                                                    // foreach ($days as $key => $day) {
                                                                    foreach($db->in_array("SELECT * FROM science_subjects WHERE science_id = ?", [ $science["id"] ]) as $key => $science_subject) {
                                                                        $day = $science_subject["subject_date"];
                                                                        $science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [ $science_subject["science_id"] ]);
                                                                        if ($month != date("m", strtotime($day))) continue;
                                                                        
                                                                    ?>
                                                                        <tr>
                                                                            <th><?=($key+1)."-dars) "?></th>
                                                                            <th><?=($day)?></th>
                                                                            <th><?=$week_days[date("w", strtotime($day))]?></th>
                                                                            <td><?=date("H:i", strtotime("$group[lesson_passable_time]"))?></td>
                                                                            <td><?=date("H:i", strtotime("$group[lesson_end_time]"))?></td>
                                                                            <td><?=$science['name']?></td>
                                                                        </tr>
                                                                    <?
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                <? } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Davomat royxati -->
                                    <div id="attendance" class="tab-pane fade <?=$path[1] == 'attendance' ? 'active show' : '' ?>" role="tabpanel">
                                        <div class="attendance">
                                            <div class="pt-4 border-bottom-1 pb-2 d-flex justify-content-between align-items-center">
                                                <ol class="breadcrumb">
                                                    <h4 class=" text-primary"><i class="fa-solid fa-users"></i> Davomat (<?=count($group_users)?> ta o'quvchi)</h4>
                                                </ol>
                                                <a href="javascript:void(0)" class="btn btn-primary rounded me-3 mb-sm-0 mb-2" id="exportToExcel3">
                                                    <i class="fa fa-upload me-3 scale5" aria-hidden="true"></i>Export
                                                </a>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="d-md-flex d-block mb-3 pb-3 mt-3 border-bottom">
                                            <div class="card-action card-tabs mb-md-0 mb-2  me-auto">
                                                <ul class="nav nav-tabs tabs-lg w-100">
                                                    <? foreach ($learning_months as $month) { ?>
                                                        <li class="nav-item">
                                                            <a href="#navpils-<?=$months[($month-1)]?>" class="attendance_links nav-link py-2 px-1" data-bs-toggle="tab" aria-expanded="false"></span><?=$months[($month-1)]?></a>
                                                        </li>
                                                    <? } ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="tab-content" id="table3"> 
                                                <? foreach ($learning_months as $month) { ?>
                                                    <div class="tab-pane fade show "id="navpils-<?=$months[($month-1)]?>" role="tabpanel">
                                                        <div class="row loadmore-content" id="RecentActivitiesContent">
                                                            <table class="table table-striped table-hover table-bordered table-responsive-sm">
                                                                <tbody>
                                                                    <tr>
                                                                        <th>№</th>
                                                                        <th>ism familya</th>
                                                                        <!-- <th>Fan no mi</th> -->
                                                                        <?
                                                                        // foreach ($days as $key => $day) {
                                                                        foreach($db->in_array("SELECT * FROM science_subjects WHERE science_id = ?", [ $science["id"] ]) as $key => $science_subject) {
                                                                            $day = $science_subject["subject_date"];
                                                                            if ($month != date("m", strtotime($day))) continue;
                                                                            $month_day = (int)date("m", strtotime($day));
                                                                        ?>
                                                                        <th><?=date("d", strtotime($day)).'-'.$months[($month_day - 1)]?></th>
                                                                        <?
                                                                        }
                                                                        ?>
                                                                    </tr>

                                                                    <? 
                                                                    foreach($group_users as $group_user) { 
                                                                        $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $group_user["student_code"] ]);    
                                                                        $student_image = fileArr($student["image_id"]);
                                                                    ?>
                                                                        <tr>
                                                                            <td class=" p-2" scope="row"><?=$student["code"]?></th>
                                                                            <td class="text-primary p-2"><?=$student["first_name"]. " " . $student["last_name"]. "<br>" . $student["father_first_name"]?></th>
                                                                            <?
                                                                            // foreach ($days as $key => $day) {
                                                                            foreach($db->in_array("SELECT * FROM science_subjects WHERE science_id = ?", [ $science["id"] ]) as $key => $science_subject) {
                                                                                $day = $science_subject["subject_date"];
                                                                                if ($month != date("m", strtotime($day))) continue;
                                                                                $lessonVisit = $db->assoc("SELECT * FROM lessons_visits WHERE student_code = ? AND course_id = ? AND lesson_date = ?", [ $student["id"], $group["course_id"], ($day) ]);
                                                                            ?>
                                                                            
                                                                                <td>
                                                                                    <div class="dropdown">
                                                                                        <span class="changeValue d-none"></span>
                                                                                        <? 
                                                                                            if($lessonVisit["id"] && $lessonVisit["status"] == 'keldi') {
                                                                                        ?>
                                                                                        <button id="<?=($day)?>" class="changeVisit btn btn-sm btn-primary " type="button" data-bs-toggle="dropdown">
                                                                                            +
                                                                                        </button>
                                                                                        <?} else if($lessonVisit["id"] && $lessonVisit["status"] == 'sababli') {?>
                                                                                            <button id="<?=($day)?>" class="changeVisit btn btn-sm btn-warning text-white" type="button" data-bs-toggle="dropdown">
                                                                                                *
                                                                                            </button>
                                                                                        <?} else if($lessonVisit["id"] && $lessonVisit["status"] == 'kelmadi') {?>
                                                                                            <button id="<?=($day)?>" class="changeVisit btn btn-sm btn-danger " type="button" data-bs-toggle="dropdown">
                                                                                                -
                                                                                            </button>
                                                                                        <?} else if(!$lessonVisit["id"]) {?>
                                                                                            <button id="<?=($day)?>" style="background-color: rgba(41, 83, 232, 0.1); border-color: rgba(41, 83, 232, 0.1); color: #008f3b;" class="changeVisit btn btn-sm btn-primary" type="button" data-bs-toggle="dropdown">
                                                                                                
                                                                                            </button>
                                                                                        <?}?>
                                                                                        <div class="dropdown-menu dropdown-menu-right border py-0">
                                                                                            <div class="p-2 d-flex flex-column justify-content-center">
                                                                                                <button data-id="student<?=$student['id']?>date<?=(str_replace("-", "", $day))?>" data-method="addLessonVisit" data-course-id="<?=$group["course_id"]?>" data-student-id="<?=$student["id"]?>" data-lesson-date="<?=($day)?>" data-status="keldi" class="btn btn-sm btn-primary">+ keldi</button>
                                                                                                <button data-id="student<?=$student['id']?>date<?=(str_replace("-", "", $day))?>" data-method="addLessonVisit" data-course-id="<?=$group["course_id"]?>" data-student-id="<?=$student["id"]?>" data-lesson-date="<?=($day)?>" data-status="sababli" class="btn btn-sm btn-warning my-1 text-white">* sababli</button>
                                                                                                <button data-id="student<?=$student['id']?>date<?=(str_replace("-", "", $day))?>" data-method="addLessonVisit" data-course-id="<?=$group["course_id"]?>" data-student-id="<?=$student["id"]?>" data-lesson-date="<?=($day)?>" data-status="kelmadi" class="btn btn-sm btn-danger">- kelmadi</button>
                                                                                                <!-- <button id="student<?=$student['id']?>date<?=(str_replace("-", "", $day))?>" data-id="student<?=$student['id']?>date<?=(str_replace("-", "", $day))?>" data-method="addLessonVisit" data-course-id="<?=$group["course_id"]?>" data-student-id="<?=$student["id"]?>" data-lesson-date="<?=($day)?>" class="btn btn-sm <?=$lessonVisit["id"] ? 'btn-danger' : 'btn-primary'?>"><? if($lessonVisit["id"]) {echo "-";} else{ echo "+";}?></button> -->
                                                                                                <!-- <button id="student<?=$student['id']?>date<?=(str_replace("-", "", $day))?>" data-id="student<?=$student['id']?>date<?=(str_replace("-", "", $day))?>" data-method="<?=$lessonVisit["id"] != '' ? 'removeLessonVisit' : 'addLessonVisit'?>" data-course-id="<?=$group["course_id"]?>" data-student-id="<?=$student["id"]?>" data-lesson-date="<?=($day)?>" class="btn btn-sm <?=$lessonVisit["id"] ? 'btn-danger' : 'btn-primary'?>"><? if($lessonVisit["id"]) {echo "-";} else{ echo "+";}?></button> -->
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </td>
                                                                            <? 
                                                                            }
                                                                            ?>
                                                                        </tr>
                                                                    <?
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                <? } ?>
                                            </div>
                                        </div>
                                    </div>
                                     <!-- O'quvchilar royxati -->
                                     <div id="students" class="tab-pane fade <?=$path[1] == 'students' ? 'active show' : '' ?>" role="tabpanel">
                                        <div class="students">
                                            <div class="pt-4 border-bottom-1 pb-2 d-flex justify-content-between align-items-center">
                                                <ol class="breadcrumb">
                                                    <h4 class=" text-primary"><i class="fa-solid fa-users"></i> O'quvchilar ro'yxati (<?=count($group_users)?>)</h4>
                                                </ol>
                                                <a href="javascript:void(0)" class="btn btn-primary rounded me-3 mb-sm-0 mb-2" id="exportToExcel4">
                                                    <i class="fa fa-upload me-3 scale5" aria-hidden="true"></i>Export
                                                </a>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div style="min-height: 300px;" class="table-responsive">
                                                            <table class="table table-responsive-md mb-0 table-bordered" id="table4">
                                                                <thead>
                                                                    <tr>
                                                                        <th>ism familya</th>
                                                                        <th>Guruhi</th> <!-- Guruh(lar) -->
                                                                        <th>telefon</th>
                                                                        <th>Qoshilgan sana</th>
                                                                        <th>vaqti</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="customers">
                                                                        <? 
                                                                        foreach ($group_users as $group_user){ 
                                                                            $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $group_user["student_code"] ]);
                                                                            $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $group_user["group_id"] ]);
                                                                            // $group_course = $db->assoc("SELECT * FROM courses WHERE id = ?", [ $group["course_id"] ]);
                                                                        ?>
                                                            
                                                                        <tr class="btn-reveal-trigger">
                                                                            <td class="py-2"><?=$student["first_name"]. " " .$student["last_name"]?></td>
                                                                            <td class="py-2"><?=$group["name"]?></td>
                                                                            <td class="py-2"><?=$student["phone_1"]?></td>
                                                                            <td class="py-2"><?=$group_user["created_date"]?></td>
                                                                            <td class="py-2">
                                                                                <?
                                                                                    if($group["du"] != "" || 0) {
                                                                                        $days2 .= '-'.'Du';
                                                                                    }
                                                                                    if($group["se"] != "" || 0) {
                                                                                        $days2 .= '-'.'Se';
                                                                                    }
                                                                                    if($group["cho"] != "" || 0) {
                                                                                        $days2 .= '-'.'Ch';
                                                                                    }
                                                                                    if($group["pa"] != "" || 0) {
                                                                                        $days2 .= '-'.'Pa';
                                                                                    }
                                                                                    if($group["ju"] != "" || 0) {
                                                                                        $days2 .= '-'.'Ju';
                                                                                    }
                                                                                    if($group["sha"] != "" || 0) {
                                                                                        $days2 .= '-'.'Sh';
                                                                                    }
                                                                                    if($group["ya"] != "" || 0) {
                                                                                        $days2 .= '-'.'Ya';
                                                                                    }
                                                                                    echo $days2 != '' ? '(' .trim($days2, "-"). ')' : '';
                                                                                    $days2 = '';
                                                                                ?>
                                                                            </td>
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
                                <!-- Payments -->
                                
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

    $("*[data-page-href]").on("click", function(){
        var page = $(this).attr("data-page-href");
        var id = $(this).attr("href");
        var title = $(this).text();
        $(".tabe-pane").removeClass("show").removeClass("active"); // .hide();
        // $(".tab-pane").removeClass("show").removeClass("active");
        
        $("*[data-page-href]").removeClass("active");
        $(this).addClass("active");
        $(id).addClass("show").addClass("active").show();
        
        // $(".tab-pane").addClass("show").addClass("active");
        // $("#page-" + page).addClass("show").addClass("active");
        window.history.pushState(title, 'Title', '/viewGroup/?group_id=<?=$group['id']?>/' + page);
        id = '';
    });

    var url = '/<?=$url[0]?>/?group_id=<?=$_GET['group_id']?>';
    var url2 = '<?=$path[1]?>';
    // Dars jadvali
    var lessonLink = $(".lesson_links").first();
    $('a[href="#lesson-list"]').click(function () {
        lessonLink.tab("show");
    })
    if(url2 == 'lesson-list') {
        lessonLink.addClass('active')
        $("#table2 > .tab-pane").first().addClass('active');
    } 
    // Davomat
    var attendanceLink = $(".attendance_links").first();
    $('a[href="#attendance"]').click(function () {
        attendanceLink.tab("show");
    })
    if(url2 == 'attendance') {
        attendanceLink.addClass('active')
        $("#table3 > .tab-pane").first().addClass('active');
    }
    // api addLesson visit

    $(".changeVisit").on("click", function () {
        var changeValue = $(this).attr("id");
        $(".changeValue").attr("data-value-id", changeValue);
        // console.log($(".changeValue").attr("data-value-id"));
        // console.log(changeValue);
    })

    $("*[data-lesson-date]").click(function(e) {
        var method = $(this).attr("data-method"); // addLessonVisit
        var element_id = $(this).attr("data-id");
        var status = $(this).attr("data-status");
        var lesson_date = $(this).attr("data-lesson-date");
        var student_id = $(this).attr("data-student-id");
        var course_id = $(this).attr("data-course-id");
        // $(this).text($(this).text() == "-" ? "+" : "-");
        // console.log($(this).text());
        // console.log(student_id);
        // console.log(lesson_date);
        // console.log(method);
        // console.log(status);
        $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: method,
                    student_id: student_id,
                    lesson_date: lesson_date,
                    course_id: course_id,
                    status: status,
                    element_id: element_id,
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
                            $("#"+id).removeClass("btn-primary").removeClass("btn-warning").removeAttr("style").addClass("btn-danger");
                        } else if(simvol == "+"){
                            $("#"+id).text(simvol);
                            $("#"+id).removeClass("btn-danger").removeClass("btn-warning").removeAttr("style").addClass("btn-primary");
                        } else if(simvol == "*") {
                            $("#"+id).text(simvol);
                            $("#"+id).removeClass("btn-danger").removeClass("btn-primary").removeAttr("style").addClass("btn-warning text-white");
                        }
                        // console.log("#simvol "+simvol);
                        // console.log("#"+id);
                    } 
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
    })

    // Export to excel
    $("#exportToExcel").on("click", function(){

        $.get(url, function(data){
            var table = $(data).find("#table");
            // $(table).find("thead").find("th").last().remove();
            // $(table).find("tbody").find("tr").each(function(){
            //     $(this).find("td").last().remove();
            // });

            tableToExcel(
            $(table).prop("innerHTML")  
            );
        });
    });
    $("#exportToExcel2").on("click", function(){
        $.get(url, function(data){
            var table = $(data).find("#table2");
            $(table).find("tbody").find(".remove_tr").last().each(function(){
                $(this).find("th").remove();
            });

            tableToExcel(
            $(table).prop("innerHTML")  
            );
        });
    });
    $("#exportToExcel3").on("click", function(){
        $.get(url, function(data){
            var table = $(data).find("#table3");
            $(table).find("tbody").find(".remove_tr2").last().each(function(){
                $(this).find("th").remove();
            });
            // $(table).find("thead").find("th").last().remove();
            // $(table).find("tbody").find("tr").each(function(){
            //     $(this).find("td").last().remove();
            // });

            tableToExcel(
            $(table).prop("innerHTML")  
            );
        });
    });
    $("#exportToExcel4").on("click", function(){
        $.get(url, function(data){
            var table = $(data).find("#table4");
            // $(table).find("thead").find("th").last().remove();
            // $(table).find("tbody").find("tr").each(function(){
            //     $(this).find("td").last().remove();
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