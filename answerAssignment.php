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

$answerAssignment = $db->assoc("SELECT * FROM answer_assignments WHERE assignment_id = ? AND student_id = ?", [ $assignment["id"], $systemUser["student_code"] ]);


if ($_REQUEST["type"] == $url[0]){
    include "modules/uploadFile.php";

    if($answerAssignment["id"]) {
        $uploadedFile = uploadFileWithUpdate("file", "files/upload/answer_assignments", ["pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "png", "jpg", "jpeg", "mp4"], false, false, $answerAssignment["file_id"]);
    } else {
        $uploadedFile = uploadFile("file", "files/upload/answer_assignments", ["pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "png", "jpg", "jpeg", "mp4"]);
    }
    
    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        if($answerAssignment["id"]) {
            $db->update("answer_assignments", [
                "assignment_id" => $assignment["id"],
                "student_id" => $systemUser["student_code"],
                "file_id" => $uploadedFile["file_id"],
            ], [
                "id" => $answerAssignment["id"]
            ]);
        } else {
            $db->insert("answer_assignments", [
                "creator_user_id" => $user_id,
                "assignment_id" => $assignment["id"],
                "student_id" => $systemUser["student_code"],
                "file_id" => $uploadedFile["file_id"],
            ]);
        }
        
        header("Location: /assignmentsList/?page=" . $page);
        exit;
    }
}


$breadcump_title_1 = "Topshiriq";
$breadcump_title_2 = "Topshiriqa javob berish";
$form_title = "Topshiriqga javob berish";

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
                        <h4 class="card-title" style="text-transform:none;">Topshiriqa javob qoldirish</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form action="/<?=$url[0]?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="type" value="<?=$url[0]?>">
                                <input type="hidden" name="id" value="<?=$assignment["id"]?>">

                                <div class="form-row">

                                    <?
                                    if ($answerAssignment["file_id"] > 0) {
                                        $file = fileArr($answerAssignment["file_id"]);

                                        if (in_array($file["type"], ["pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "png", "jpg", "jpeg", "mp4"])) {
                                            if ($file["file_folder"]) {
                                                // echo '<image src="'.$file["file_folder"].'" width="125px">';
                                            }
                                        }
                                    }
                                    ?>

                                    <?=getError("file")?>
                                    <div class="form-group col-12">
                                        <label for="formFile" class="form-label">Topshiriq file (pdf, doc, docx, xls, xlsx, ppt, pptx, txt)</label>
                                        <input class="form-control" type="file" name="file" id="formFile" accept="file/*">
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


<?
include "system/end.php";
?>