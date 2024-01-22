<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

if ($_REQUEST["type"] == $url[0]){
    validate(["name", "description", "group_id", "science_id", "subject_id"]);
  
    include "modules/uploadFile.php";
    
    $uploadedFile = uploadFile("file", "files/upload/assignments", ["pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "png", "jpg", "jpeg", "mp4"]);


    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $added_group_id = $db->insert("assignments", [
            "creator_user_id" => $user_id,
            "name" => $_POST["name"],
            "description" => $_POST["description"],
            "file_id" => $uploadedFile["file_id"],
            "from_date" => $_POST["from_date"],
            "to_date" => $_POST["to_date"],
            "group_id" => $_POST["group_id"],
            "science_id" => $_POST["science_id"],
            "subject_id" => $_POST["subject_id"],
        ]);
        
        
        header("Location: assignmentsList/?page=1");
        exit;
    } else {
        // header("Content-type: text/plain");
        // print_r($errors);
        // exit;
    }
}

include "system/head.php";

$breadcump_title_1 = "Topshiriqlar";
$breadcump_title_2 = "yangi topshiriq qo'shish";
$form_title = "Yangi topshiriq qo'shish";
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
                                    <?=getError("name")?>
                                    <div class="form-group col-12">
                                        <label>Topshiriq nomi</label>
                                        <input type="text" name="name" class="form-control" placeholder="Topshiriq nomi" value="<?=$_POST["name"]?>">
                                    </div>
                                    
                                    <?=getError("description")?>
                                    <div class="form-group col-12">
                                        <label>Topshiriq izohi</label>
                                        <input type="text" name="description" class="form-control" placeholder="Topshiriq izohi" value="<?=$_POST["description"]?>">
                                    </div>
                                    
                                    <?=getError("file")?>
                                    <div class="form-group col-12">
                                        <label for="file" class="form-label">Topshiriq file (pdf, doc, docx, xls, xlsx, ppt, pptx, txt, png, jpg, jpeg, mp4)</label>
                                        <input class="form-control" type="file" name="file" id="file">
                                    </div>

                                    <?=getError("from_date")?>
                                    <div class="form-group col-12">
                                        <label>Dan</label>
                                        <input type="datetime-local" name="from_date" class="form-control" placeholder="Dan" value="<?=$_POST["from_date"]?>">
                                    </div>
                                    
                                    <?=getError("to_date")?>
                                    <div class="form-group col-12">
                                        <label>Gacha</label>
                                        <input type="datetime-local" name="to_date" class="form-control" placeholder="Gacha" value="<?=$_POST["to_date"]?>">
                                    </div>
                                    
                                    <?=getError("group_id")?>
                                    <div class="form-group col-12">
                                        <label>Gurhlar:</label>
                                        <select  name="group_id" id="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
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
                                        <label>Fanlar:</label>
                                        <select name="science_id" id="science_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            
                                        </select>
                                    </div>
                                    
                                    <?=getError("subject_id")?>
                                    <div class="form-group col-12">
                                        <label>Mavzular:</label>
                                        <select name="subject_id" id="subject_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">

                                        </select>
                                    </div>

                                </div>

                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <button id="addAssignment" type="click" class="btn btn-primary">Qo'shish</button>
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
                        data.sciences.forEach(science => {
                            $("#science_id").append(`<option value="${science.id}"> ${science.name}</option>`)
                        });
                        $('#science_id').change();
                        $('#science_id').selectpicker('refresh');
                    } else {
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
                        $("#subject_id").change();
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

    $('#group_id').change();
</script>

<?
include "system/end.php";
?>