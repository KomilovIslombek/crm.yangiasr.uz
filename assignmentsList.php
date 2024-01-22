<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_GET['page'];
if (empty($page)) $page = 1;

$page_count = 20;
$page_end = $page * $page_count;
$page_start = $page_end - $page_count;

if (!empty($_GET["group_id"])) {
    $query .= " AND group_id = " . (int)$_GET["group_id"];
} 

if (!empty($_GET["science_id"])) {
    $query .= " AND science_id = " . (int)$_GET["science_id"];
}

if (!empty($_GET["subject_id"])) {
    $query .= " AND subject_id = " . (int)$_GET["subject_id"];
}

if($systemUser["role"] == "teacher") {
    $group_teacher = $db->assoc("SELECT * FROM group_teachers WHERE teacher_id = ?", [ $systemUser["teacher_id"] ]);
    if(!$group_teacher["group_id"]) {
        $query .= " AND group_id = " . 0;
    }
}

$count = (int)$db->assoc("SELECT COUNT(*) FROM `assignments` WHERE 1=1$query")["COUNT(*)"];

$assignments = $db->in_array("SELECT * FROM assignments WHERE 1=1$query ORDER BY id ASC LIMIT $page_start, $page_count");

include "system/head.php";

$breadcump_title_1 = "Topshiriqlar";
$breadcump_title_2 = "Topshiriqlar ro'yxati";
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

        <!-- start Filter -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="/<?=$url[0]?>" method="GET" id="filter">
                            <div class="basic-form row d-flex align-items-center">
                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Guruhlar:</label>
                                    <select name="group_id" id="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        <!-- <option value="">Barcha guruhlar</option> -->
                                            <? 
                                            if($systemUser["role"] == "student") {
                                                $student_groups = $db->in_array("SELECT * FROM group_users WHERE student_code = ?", [ $systemUser["student_code"] ] );
                                                foreach ($student_groups as $student_group) { 
                                                    $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $student_group["group_id"] ]); 
                                            ?>
                                                    <option
                                                        value="<?=$group["id"]?>"
                                                        <?=($_GET["group_id"] == $group["id"] ? 'selected=""' : '')?>
                                                    > <?=$group["name"]?></option>
                                                <? }
                                            } else if($systemUser["role"] == "teacher") {
                                                    $teahcer_groups = $db->in_array("SELECT * FROM group_teachers WHERE teacher_id = ?", [ $systemUser["teacher_id"] ] );
                                                    foreach ($teahcer_groups as $teahcer_group) { 
                                                        $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $teahcer_group["group_id"] ]); 
                                            ?> 
                                                    <option
                                                        value="<?=$group["id"]?>"
                                                        <?=($_GET["group_id"] == $group["id"] ? 'selected=""' : '')?>
                                                    ><?=($group["id"])?>  <?=$group["name"]?></option>
                                                <? } ?>
                                            <?  } else if($systemUser["role"] == "admin") {?>
                                                <option value="">Barchasi</option>
                                                <? foreach ($db->in_array("SELECT * FROM groups_list") as $group) { ?>
                                                    <option
                                                        value="<?=$group["id"]?>"
                                                        <?=($_GET["group_id"] == $group["id"] ? 'selected=""' : '')?>
                                                    ><?=($group["id"])?>  <?=$group["name"]?></option>
                                                <? } ?>
                                            <? } ?>
                                    </select>
                                </div>
                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Guruh fanlari:</label>
                                    <select name="science_id" id="science_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        
                                        
                                    </select>
                                </div>
                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Fan mavzulari:</label>
                                    <select name="subject_id" id="subject_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        <!-- <option value="">Barcha guruhlar</option> -->
                                        
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
                            <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>#id</th>
                                        <th>Nomi</th>
                                        <th>Izohi</th>
                                        <th>file</th>
                                        <th class="<?=$systemUser["role"] != 'student' ? 'd-none' : ''?>">javob berish</th>
                                        <th>guruhi</th>
                                        <th>fani</th>
                                        <th>mavzusi</th>
                                        <th>Qo'shilgan sana</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($assignments as $assignment){ ?>
                                        <?
                                            $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $assignment["group_id"] ]);
                                            $science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [ $assignment["science_id"] ]);
                                            $subject = $db->assoc("SELECT * FROM subjects WHERE id = ?", [ $assignment["subject_id"] ]);
                                            $assignment_file = fileArr($assignment["file_id"]);
                                        ?>
                               
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2"><?=$assignment["id"]?></td>
                                            <td class="py-2"><?=$assignment["name"]?></td>
                                            <td class="py-2"><?=$assignment["description"]?></td>
                                            <td class="py-2"><a href="<?=$assignment_file["file_folder"]?>" class="btn btn-outline-primary btn-xs" download>Yuklab olish</a></td>
                                            <td class="p-y2 <?=$systemUser["role"] != 'student' ? 'd-none' : ''?>">
                                                <? if(date("Y-m-d H:i:s") < $assignment["to_date"]) {?>
                                                    <a class=" btn btn-outline-info btn-xs" href="/answerAssignment/?id=<?=$assignment["id"]?>">Javob berish</a>
                                                <? } else { ?>
                                                    javob berishi <mark>muddati</mark> yakunlandi!
                                                <? } ?>
                                            </td>
                                            <td class="py-2"><?=$group["name"]?></td>
                                            <td class="py-2"><?=$science["name"]?></td>
                                            <td class="py-2"><?=$subject["name"]?></td>
                                            <td class="py-2"><?=$assignment["created_date"]?></td>
                                            <td class="py-2 text-end <?=$systemUser["role"] == "student" ? 'd-none' : ''?>">
                                                <div class="dropdown">  
                                                    <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                        <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right border py-0">
                                                        <div class="py-2">
                                                            <? if($systemUser["role"] == "admin") {?>
                                                                <a class="dropdown-item"  href="/editAssignment/?id=<?=$assignment["id"]?>&page=<?=$page?>">Tahrirlash</a>
                                                                <a class="dropdown-item text-danger" href="/editAssignment/?id=<?=$assignment["id"]?>&type=deleteassignment">O'chirish</a>
                                                            <? } else if($systemUser["role"] == "teacher") {?>
                                                                <a class="dropdown-item"  href="/editAssignment/?id=<?=$assignment["id"]?>&page=<?=$page?>">Tahrirlash</a>
                                                                <a class="dropdown-item text-danger" href="/editAssignment/?id=<?=$assignment["id"]?>&type=deleteassignment">O'chirish</a>
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

                        // count
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

<style>
    .bootstrap-select .dropdown-menu li.active small{
        color: #181f39!important;
    }
</style>

<?
include "system/scripts.php";
?>

<script>

    $('#group_id').change(function() {
        var group_id = $(this).val();
        console.log(group_id);
        
        if(!group_id) {
            $("#science_id").html('');
            $('#science_id').selectpicker('refresh');
            $("#subject_id").html('');
            $('#subject_id').selectpicker('refresh');
            updateTable();
        } else {
            updateTable();
            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'filterGroup',
                    group_id: group_id,
                },
                dataType: "json",
                success: function(data) {
                        if (data.ok == true) {
    
                        $("#science_id").html('');
                        data.sciences.forEach(science => {
                            $("#science_id").append(`<option value="${science.id}"> ${science.name}</option>`)
                        });
                        $('#science_id').change();
                        $("#form").removeClass('d-none');
                        $('#science_id').selectpicker('refresh');
                    } else {
                        $("#form").addClass('d-none');
                        $("#science_id").html('');
                        $('#science_id').selectpicker('refresh');
                        $(".modal-title").text("Bu guruhga tegishli fanlar mavjud emas");
                        $(".modal").modal("show");
                        // console.error(data);
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        }
    });

    $('#science_id').change(function() {
        var science_id = $(this).val();

        if(science_id) {
            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'filterScience',
                    page: "assignmentsList",
                    science_id: science_id,
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
    
                        $("#subject_id").html('');
                        console.log(data.subjects.length);
                        // if(data.subjects.length == 1) {
                        //     $("#subject_id").append(`<option value=""> </option>`);
                        // }
                        data.subjects.forEach(subject => {
                            $("#subject_id").append(`<option data-subtext="${subject.date}" value="${subject.id}"> ${subject.name}</option>`);
                        });
                        $("#subject_id").change();
                        $("#form").removeClass('d-none');
                        $('#subject_id').selectpicker('refresh');
                    } else {
                        if(data.text) {
                            $("#form").addClass('d-none');
                            alert(data.text);
                            $("#subject_id").html('');
                            $('#subject_id').selectpicker('refresh');
                        } else {
                            $(".modal-title").text("Bu fanga tegishli mavzu mavjud emas");
                            $(".modal").modal("show");
                            $("#subject_id").html('');
                            $("#form").addClass('d-none');
                            $('#subject_id').selectpicker('refresh');
                        }

                        // console.error(data);
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        }
    });

</script>
<? if($systemUser["role"] == "student" || $systemUser["role"] == "teacher") {?>
    <script>
        $("#group_id").change();
    </script>
<?
}
include "system/end.php";
?>