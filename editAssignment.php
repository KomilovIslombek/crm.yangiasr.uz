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

$assignment = $db->assoc("SELECT * FROM assignments WHERE id = ?", [$id]);
if (!$assignment["id"]) {echo"error (assignment not found)";exit;}

if ($_REQUEST["type"] == $url[0]){
    validate(["name", "description", "group_id", "science_id", "subject_id"]);
    
    include "modules/uploadFile.php";
    
    $uploadedFile = uploadFileWithUpdate("file", "files/upload/assignments", ["pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "png", "jpg", "jpeg", "mp4"], false, false, $assignment["file_id"]);
    
    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $db->update("assignments", [
            "name" => $_POST["name"],
            "description" => $_POST["description"],
            "file_id" => $uploadedFile["file_id"],
            "from_date" => $_POST["from_date"],
            "to_date" => $_POST["to_date"],
            "group_id" => $_POST["group_id"],
            "science_id" => $_POST["science_id"],
            "subject_id" => $_POST["subject_id"],
        ], [
            "id" => $assignment["id"]
        ]);
        
        header("Location: /assignmentsList/?page=" . $page);
        exit;
    }
}

if ($_REQUEST["type"] == "deleteassignment") {   
    $db->delete("assignments", $assignment["id"], "id");

    $file = fileArr($assignment["file_id"]);
        if ($assignment["file_id"] > 0) {
            if ($file["thumb_image_id"] > 0) {
                delete_image($file["thumb_image_id"]);
            }
            delete_file($assignment["file_id"]);
        }

    header("Location: /assignmentsList/?page=" . $page);
    exit;
}


$breadcump_title_1 = "Topshiriqlar";
$breadcump_title_2 = "Topshiriqni tahrirlash";
$form_title = "Topshiriqni tahrirlash";

include "system/head.php";
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

        <!-- Guruhni nomini taxrirlash -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform:none;">Topshiriq tahrirlash</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form action="/<?=$url[0]?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="type" value="<?=$url[0]?>">
                                <input type="hidden" name="id" value="<?=$assignment["id"]?>">

                                <div class="form-row">
                                    <?=getError("name")?>
                                    <div class="form-group col-12">
                                        <label>Topshiriq nomi</label>
                                        <input type="text" name="name" class="form-control" placeholder="Topshiriq nomi" value="<?=$assignment["name"]?>">
                                    </div>
                                    
                                    <?=getError("description")?>
                                    <div class="form-group col-12">
                                        <label>Topshiriq izohi</label>
                                        <input type="text" name="description" class="form-control" placeholder="Topshiriq izohi" value="<?=$assignment["description"]?>">
                                    </div>

                                    <?
                                    if ($assignment["file_id"] > 0) {
                                        $file = fileArr($assignment["file_id"]);

                                        if (in_array($file["type"], ["pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "png", "jpg", "jpeg", "mp4"])) {
                                            if ($file["file_folder"]) {
                                                // echo '<image src="'.$file["file_folder"].'" width="125px">';
                                            }
                                        }
                                    }
                                    ?>

                                    <?=getError("file")?>
                                    <div class="form-group col-12">
                                        <label for="formFile" class="form-label">Topshiriq file (pdf, doc, docx, xls, xlsx, ppt, pptx, txt, png, jpg, jpeg, mp4)</label>
                                        <input class="form-control" type="file" name="file" id="formFile" accept="file/*">
                                    </div>

                                    <?=getError("from_date")?>
                                    <div class="form-group col-12">
                                        <label>Dan</label>
                                        <input type="datetime-local" name="from_date" class="form-control" placeholder="Dan" value="<?=$assignment["from_date"]?>">
                                    </div>
                                    
                                    <?=getError("to_date")?>
                                    <div class="form-group col-12">
                                        <label>Gacha</label>
                                        <input type="datetime-local" name="to_date" class="form-control" placeholder="Gacha" value="<?=$assignment["to_date"]?>">
                                    </div>

                                    <?=getError("group_id")?>
                                    <div class="form-group col-12">
                                        <label>Guruhlar</label>
                                        <select name="group_id" id="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? if($systemUser["role"] == "admin") { ?>
                                                <? foreach ($db->in_array("SELECT * FROM groups_list") as $group) { ?>
                                                    <option value="<?=$group["id"]?>" <?=($group["id"] == $assignment["group_id"] ? 'selected=""' : '')?> ><?=$group["name"]?></option>
                                                <? } ?>
                                            <? } else if($systemUser["role"] == "teacher" && $systemUser["teacher_id"]) { 
                                                    $teacher_groups = $db->in_array("SELECT * FROM group_teachers WHERE teacher_id = ?", [ $systemUser["teacher_id"] ]);
                                                    foreach( $teacher_groups as $teacher_group ) { 
                                                        $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $teacher_group["group_id"] ]); 
                                                    ?>
                                                        <option value="<?=$group["id"]?>" <?=($group["id"] == $assignment["group_id"] ? 'selected=""' : '')?> ><?=$group["name"]?></option>
                                                    <? } ?>
                                            <? } ?>
                                        </select>
                                    </div>
                                    
                                    <?=getError("science_id")?>
                                    <div class="form-group col-12">
                                        <label>Fanlar</label>
                                        <select name="science_id" id="science_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                           <? foreach ($db->in_array("SELECT * FROM group_sciences WHERE group_id = ?", [ $assignment["group_id"] ]) as $group_science) { 
                                                $science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [ $group_science["science_id"] ]);
                                            ?>
                                                <option value="<?=$science["id"]?>" <?=($science["id"] == $assignment["science_id"] ? 'selected=""' : '')?>><?=$science["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                    
                                    <?=getError("subject_id")?>
                                    <div class="form-group col-12">
                                        <label>Mavzular</label>
                                        <select name="subject_id" id="subject_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                           <? foreach ($db->in_array("SELECT * FROM subjects") as $subject) { ?>    
                                                <option value="<?=$subject["id"]?>" data-subtext="<?=$subject["subject_date"]?>" <?=($subject["id"] == $assignment["subject_id"] ? 'selected=""' : '')?>><?=$subject["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <button id="addToGroup_teachers" type="submit" class="btn btn-primary">Saqlash</button>
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
                    window.history.pushState('mavzu', 'Title', '/editAssignment/?id=<?=$assignment['id']?>');
                    $('#science_id').selectpicker('refresh');
                } else {
                    $("#science_id").html('');
                    $('#science_id').selectpicker('refresh');
                    alert("Bu guruhga tegishli fanlar mavjud emas");
                    // console.error(data);
                }
            },
            error: function() {
                alert("Xatolik yuzaga keldi");
            }
        })
    });

    $('#science_id').change(function() {
        var group_id = $("#group_id").val();
        var science_id = $(this).val();

        if(science_id) {
            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'filterScience2',
                    group_id: group_id,
                    science_id: science_id,
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
    
                        $("#subject_id").html('');
                        data.subjects.forEach(subject => {
                            $("#subject_id").append(`<option value="${subject.id}" data-subtext="${subject.date}"> ${subject.name}</option>`);
                        });
                        // $("#subject_id").change();
                        $('#subject_id').selectpicker('refresh');
                    } else {
                        if(data.text) {
                            $(".modal-title").text(data.text);
                            $("#subject_id").html('');
                            $('#subject_id').selectpicker('refresh');
                        } else {
                            $(".modal-title").text("Bu fanga tegishli mavzu mavjud emas");
                            $("#subject_id").html('');
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

<?
include "system/end.php";
?>