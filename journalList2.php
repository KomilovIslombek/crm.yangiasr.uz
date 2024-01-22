<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
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


// Moodle baza

// $query = "";

if (!empty($_GET["subject_id"])) {
    $query .= " AND courseid = " . (int)$_GET["subject_id"];
} else {
    $query .= " AND courseid = 121";
}

// $groups = $db4->in_array("SELECT id, name FROM cohort");

$directions = $db4->in_array("SELECT id, name FROM course_categories WHERE parent = 0 ORDER BY sortorder ASC");

$grade_items = $db4->in_array("SELECT * FROM grade_items WHERE 1=1$query ORDER BY itemname DESC");
// $grade_items = $db4->in_array("SELECT * FROM grade_items WHERE courseid = ? ORDER BY itemname DESC", [ 121 ]);

$course_users = $db4->in_array("SELECT * FROM user_lastaccess WHERE 1=1$query");
// $course_users = $db4->in_array("SELECT * FROM user_lastaccess WHERE courseid = ?", [ 121 ]);

include "system/head.php";

$breadcump_title_1 = "Jurnal";
$breadcump_title_2 = "Elektron jurnal";
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
                                <!-- <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Guruhlar:</label>
                                    <select name="group_id" id="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <option value="">Barcha guruhlar</option>
                                            <? 
                                                foreach ($groups as $group) { 
                                            ?>
                                                    <option
                                                        value="<?=$group["id"]?>"
                                                        <?=($_GET["group_id"] == $group["id"] ? 'selected=""' : '')?>
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
                                                        <?=($_GET["direction_id"] == $direction["id"] ? 'selected=""' : '')?>
                                                    > <?=$direction["name"]?></option>
                                            <? } ?>
                                            
                                    </select>
                                </div>

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
                                
                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Mavzu:</label>
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

                                <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                    <thead>
                                        <tr>
                                            <th>id</th> 
                                            <th>F.I.SH</th>
                                            <?
                                            foreach ($grade_items as $grade_item) {
                                                if($grade_item["itemtype"] == "course") {
                                                    echo '<th>GPA</th>';
                                                    echo '<th>Yakuniy baxo</th>';
                                                } else {
                                                    echo '<th>'.$grade_item["itemname"].'</th>';
                                                }
                                            }
                                            ?>
                                            
                                        </tr>
                                    </thead>
                                    <tbody id="customers">
                                        <? foreach ($course_users as $course_user) {
                                            // $group_student = $db4->assoc("SELECT * FROM cohort_members WHERE cohortid = ? AND userid = ?", [ $_GET["group_id"], $course_user["userid"] ]);
                                            $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $course_user["userid"] ]);
                                            // $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $course_user["userid"] ]);
                                                if($student["id"]) {
                                        ?>
                                            <tr>
                                                <td><?=$student["id"]?></td>
                                                <td><?=$student["firstname"]. " " . $student["lastname"]?></td>
                                                <? foreach ($grade_items as $grade_item) {
                                                    $grade_grade = $db4->assoc("SELECT * FROM grade_grades WHERE itemid = ? AND userid = ?", [ $grade_item["id"], $student["id"] ]);
                                                    // $grade_grades = $db->in_array("SELECT * FROM grade_grades WHERE itemid = ?", [ $grade_item["id"] ]);
                                                    if($grade_item["itemtype"] == "course") {
                                                        $gpa = number_format($grade_grade["finalgrade"], 0, "", "");
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
                                                    <td><?=$grade_grade["finalgrade"] ? number_format($grade_grade["finalgrade"],0, "", "") : ''?></td>
                                                <? } ?>
                                            </tr>
                                                <? } ?>
                                        <? } ?>
                                    </tbody>
                                </table>
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

?>
<script>

    $("#exportToExcel").on("click", function(){
        var q = $( "#filter" ).serialize();
        var url = '/<?=$url[0]?>?' + q + "&page_count=1000000&export=excel";

        $.get(url, function(data){
            var table = $(data).find("#table2");
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
    
    $('#direction_id').change(function() {
        var direction_id = $(this).val();
        updateTable();

        console.log(direction_id);
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

                    $("#learn_type_id").html('');
                    data.learn_types.forEach(learn_type => {
                        $("#learn_type_id").append(`<option value="${learn_type.id}"> ${learn_type.name}</option>`)
                    });
                    $('#learn_type_id').change();
                    $("#form").removeClass('d-none');
                    $('#learn_type_id').selectpicker('refresh');
                } else {
                    $("#form").addClass('d-none');
                    $("#learn_type_id").html('');
                    $('#learn_type_id').selectpicker('refresh');
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
        var learn_type_id = $(this).val();
        updateTable();

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
                        $("#course_id").append(`<option value="${course.id}"> ${course.name}</option>`)
                    });
                    $('#course_id').change();
                    $('#course_id').selectpicker('refresh');
                } else {
                    $("#course_id").html('');
                    $('#course_id').selectpicker('refresh');
                    $(".modal-title").text("Bu Yo'nalishga tegishli ta'lim turlari mavjud emas");
                    $(".modal").modal("show");
                }
            },
            error: function() {
                alert("Xatolik yuzaga keldi");
            }
        })
    });
    
    $('#course_id').change(function() {
        var course_id = $(this).val();
        updateTable();

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
                        $("#semester_id").append(`<option value="${semester.id}"> ${semester.name}</option>`)
                    });
                    $('#semester_id').change();
                    $('#semester_id').selectpicker('refresh');
                } else {
                    $("#semester_id").html('');
                    $('#semester_id').selectpicker('refresh');
                    $(".modal-title").text("Bu Yo'nalishga tegishli ta'lim turlari mavjud emas");
                    $(".modal").modal("show");
                }
            },
            error: function() {
                alert("Xatolik yuzaga keldi");
            }
        })
    });
    
    $('#semester_id').change(function() {
        var semester_id = $(this).val();
        updateTable();

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

                    $("#subject_id").html('');
                    data.subjects.forEach(subject => {
                        $("#subject_id").append(`<option value="${subject.id}" data-subtext="${subject.shortname}"> ${subject.fullname}</option>`)
                    });
                    $('#subject_id').change();
                    $('#subject_id').selectpicker('refresh');
                } else {
                    $("#subject_id").html('');
                    $('#subject_id').selectpicker('refresh');
                    $(".modal-title").text("Kurs mavjud emas");
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

<?
include "system/end.php";
?>