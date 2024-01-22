<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

if ($_REQUEST["type"] == $url[0]){
    validate(["group_id"], "subject_id", "science_id", "subject_date");
  

    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $scienceSubject_id = $db->insert("science_subjects", [
            "creator_user_id" => $user_id,
            "group_id" => $_POST["group_id"],
            "subject_id" => $_POST["subject_id"],
            "science_id" => $_POST["science_id"],
            "subject_date" => $_POST["subject_date"],
        ]);

        // if($scienceSubject_id > 0) {
        //     $controlWorks = $db->insert("control_works", [
        //         "creator_user_id" => $user_id,
        //         "group_id" => $_POST["group_id"],
        //         "subject_id" => $_POST["subject_id"],
        //         "science_id" => $_POST["science_id"],
        //         "subject_date" => $_POST["subject_date"],
        //     ]);
        // }

        if($scienceSubject_id > 0) {
            header("Location: controlWorksList/?page=1");
            exit;
        }
    } else {
        // header("Content-type: text/plain");
        // print_r($errors);
        // exit;
    }
}

include "system/head.php";

$breadcump_title_1 = "Nazorat ishlari";
$breadcump_title_2 = "yangi nazorat ishi qo'shish";
$form_title = "Yangi nazorat ishi qo'shish";
?>

<!--**********************************
    Content body start
***********************************-->
<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)"><?=$breadcump_title_1?></a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?=$breadcump_title_2?></a></li>
            </ol>
        </div>
        <!-- row -->
        <div class="row">
            <div class="col-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform:none;"><?=$form_title?></h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form action="/<?=$url[0]?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="type" value="<?=$url[0]?>">
                                <div class="form-row">

                                    <?=getError("group_id")?>
                                    <div class="form-group col-12">
                                        <label>Guruhlar royxati</label>
                                        <select name="group_id" id="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? if($systemUser["role"] == "admin") { ?>
                                                <? foreach ($db->in_array("SELECT * FROM groups_list") as $group) { ?>
                                                    <option value="<?=$group["id"]?>"><?=$group["name"]?></option>
                                                <? } ?>
                                            <? } else if($systemUser["role"] == "teacher" && $systemUser["teacher_id"]) { 
                                                $teacher_groups = $db->in_array("SELECT * FROM group_teachers WHERE teacher_id = ?", [ $systemUser["teacher_id"] ]);
                                                    foreach( $teacher_groups as $teacher_group ) { 
                                                        $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $teacher_group["group_id"] ]); 
                                                    ?>
                                                        <option value="<?=$group["id"]?>"><?=$group["name"]?></option>
                                                    <? } ?>
                                               <? } ?>
                                        </select>
                                    </div>
                                    
                                    <?=getError("science_id")?>
                                    <div class="form-group col-12">
                                        <label>Fanlar royxati</label>
                                        <select name="science_id" id="science_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            
                                        </select>
                                    </div>

                                    <?=getError("subject_id")?>
                                    <div class="form-group col-12">
                                        <label>Mavzular royxati</label>
                                        <select id="subject_id" name="subject_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            
                                        </select>
                                    </div>
                                    
                                    <?=getError("subject_date")?>
                                    <div class="form-group col-12">
                                        <label>Mavzu sanasi</label>
                                        <input type="date" name="subject_date" class="form-control" placeholder="Mavzu sanasi" value="<?=$_POST["subject_date"]?>">
                                    </div>

                                </div>

                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <button id="addToGroup_teachers" type="click" class="btn btn-primary">Qo'shish</button>
                                </div>
                                
                            </form>
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
    
    $('#group_id').change(function() {
        var group_id = $(this).val();

        if(group_id) {
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
                        // if(data.sciences.length == 1) {
                        // }
                        data.sciences.forEach(science => {
                            $("#science_id").append(`<option value="${science.id}"> ${science.name}</option>`)
                        });
                        $('#science_id').change();
                        $('#science_id').selectpicker('refresh');
                    } else {
                        $("#science_id").html('');
                        $('#science_id').selectpicker('refresh');
                        $("#subject_id").html('');
                        $('#subject_id').selectpicker('refresh');
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
                    method: 'filterScienceFromSub',
                    science_id: science_id,
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
    
                        $("#subject_id").html('');
                        console.log(data.subjects.length);
                        data.subjects.forEach(subject => {
                            $("#subject_id").append(`<option value="${subject.id}"> ${subject.name}</option>`);
                        });
                        $("#subject_id").change();
                        $('#subject_id').selectpicker('refresh');
                    } else {
                        $(".modal-title").text("Bu fanga tegishli mavzu mavjud emas");
                        $(".modal").modal("show");
                        $("#subject_id").html('');
                        $('#subject_id').selectpicker('refresh');
                    }
                },
                error: function(e) {
                    alert("Xatolik yuzaga keldi");
                }
            })
        }
    });

    $('#group_id').change();
</script>

<?
include "system/end.php";
?>