<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_REQUEST["page"];
if (empty($page)) $page = 1;

$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : null;
if (!$id) {echo"error id not found";return;}

$controlWork = $db->assoc("SELECT * FROM science_subjects WHERE id = ?", [$id]);
if (!$controlWork["id"]) {echo"error (controlWork not found)";exit;}

if ($_REQUEST["type"] == $url[0]){
    validate(["group_id", "science_id", "subject_id", "subject_date"]);
    
    include "modules/uploadFile.php";

    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $db->update("science_subjects", [
            "group_id" => $_POST["group_id"],
            "science_id" => $_POST["science_id"],
            "subject_id" => $_POST["subject_id"],
            "subject_date" => $_POST["subject_date"],
        ], [
            "id" => $controlWork["id"]
        ]);

        
        header("Location: controlWorksList/?page=" . $page);
        exit;
    } else {
        // header("Content-type: text/plain");
        // print_r($errors);
        // exit;
    }
}

if ($_REQUEST["type"] == "deletecontrolwork") {
    $db->delete("science_subjects", $controlWork["id"], "id");

    header("Location: /controlWorksList/?page=" . $page);
    exit;
}

include "system/head.php";

$breadcump_title_1 = "Nazorat ishlar";
$breadcump_title_2 = "Nazorat ishni tahrirlash";
$form_title = "Nazorat ishni tahrirlash";
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
                                <input type="hidden" name="page" value="<?=$page?>">
                                <input type="hidden" name="id" value="<?=$controlWork["id"]?>">
                                <input type="hidden" id="filtSubject_id" value="<?=$controlWork["subject_id"]?>">
                                <input type="hidden" name="type" value="<?=$url[0]?>">

                                <div class="form-row">

                                    <?=getError("group_id")?>
                                    <div class="form-group col-12">
                                        <label>Guruhlar royxati</label>
                                        <select name="group_id" id="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? if($systemUser["role"] == "admin") { ?>
                                                <? foreach ($db->in_array("SELECT * FROM groups_list") as $group) { ?>
                                                    <option value="<?=$group["id"]?>" <?=($group["id"] == $controlWork["group_id"] ? 'selected=""' : '')?> ><?=$group["name"]?></option>
                                                <? } ?>
                                            <? } else if($systemUser["role"] == "teacher" && $systemUser["teacher_id"]) { 
                                                    $teacher_groups = $db->in_array("SELECT * FROM group_teachers WHERE teacher_id = ?", [ $systemUser["teacher_id"] ]);
                                                    foreach( $teacher_groups as $teacher_group ) { 
                                                        $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $teacher_group["group_id"] ]); 
                                                    ?>
                                                        <option value="<?=$group["id"]?>" <?=($group["id"] == $controlWork["group_id"] ? 'selected=""' : '')?> ><?=$group["name"]?></option>
                                                    <? } ?>
                                            <? } ?>
                                        </select>
                                    </div>
                                    
                                    <?=getError("science_id")?>
                                    <div class="form-group col-12">
                                        <label>Fanlar royxati</label>
                                        <select name="science_id" id="science_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? foreach ($db->in_array("SELECT * FROM group_sciences WHERE group_id = ?", [ $controlWork["group_id"] ]) as $group_science) { ?>
                                                <? 
                                                $science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [ $group_science["science_id"] ]);    
                                                ?>
                                                <option value="<?=$science["id"]?>" <?=($science["id"] == $controlWork["science_id"] ? 'selected=""' : '')?> ><?=$science["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("subject_id")?>
                                    <div class="form-group col-12">
                                        <label>Mavzular royxati</label>
                                        <select id="subject_id" name="subject_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? foreach ($db->in_array("SELECT * FROM subjects WHERE science_id = ?", [ $controlWork["science_id"] ]) as $subject) { ?>
                                                <option value="<?=$subject["id"]?>" <?=($subject["id"] == $controlWork["subject_id"] ? 'selected=""' : '')?> ><?=$subject["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                    
                                    <?=getError("subject_date")?>
                                    <div class="form-group col-12">
                                        <label>Mavzu sanasi</label>
                                        <input type="date" name="subject_date" class="form-control" placeholder="Mavzu sanasi" value="<?=$controlWork["subject_date"]?>">
                                    </div>

                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <button type="submit" class="btn btn-primary">Saqlash</button>
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
                    $('#science_id').selectpicker('refresh');
                } else {
                    $("#science_id").html('');
                    $('#science_id').selectpicker('refresh');
                    $("#subject_id").html('');
                    $('#subject_id').selectpicker('refresh');
                    $(".modal-title").text("Bu guruhga teigishli fan mavjud emas");
                    $(".modal").modal("show");
                }
            },
            error: function() {
                alert("Xatolik yuzaga keldi");
            }
        })
    });

    $('#science_id').change(function() {
        var science_id = $(this).val();
        var subject_id = $("#filtSubject_id").val();
        console.log(subject_id);

        if(science_id) {
            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'filterScienceFromSub',
                    science_id: science_id,
                    subject_id: subject_id,
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
    
                        $("#subject_id").html('');
                        data.subjects.forEach(subject => {
                            $("#subject_id").append(`<option value="${subject.id}" ${subject.id == data.filtSubject_id ? 'selected=" "' : ''}> ${subject.name}</option>`); 
                        });
                        $('#subject_id').selectpicker('refresh');
                    } else {
                        $(".modal-title").text("Bu fanga tegishli mavzu mavjud emas");
                        $(".modal").modal("show");
                        $("#subject_id").html('');
                        $('#subject_id').selectpicker('refresh');
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        }
    });
</script>

<?
include "system/end.php";
?>