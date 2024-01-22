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
// if (!$group_id) {echo"error groups_id not found";return;}

$subject = $db->assoc("SELECT * FROM subjects WHERE id = ?", [$id]);
if (!$subject["id"]) {echo"error (subjects not found)";exit;}

if ($_REQUEST["type"] == $url[0]){
    validate(["name", "training_type", "science_id"]);
    
    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $db->update("subjects", [
            "name" => $_POST['name'],
            "training_type" => $_POST['training_type'],
            "science_id" => $_POST['science_id'],
        ], [
            "id" => $subject["id"]
        ]);
        
        header("Location: /subjectsList/?page=" . $page);
        exit;
    }
}

if ($_REQUEST["type"] == "deletesubject") {   
    $db->delete("subjects", $subject["id"]);
    header("Location: /subjectsList/?page=" . $page);
    exit;
}


$breadcump_title_1 = "Mavzular";
$breadcump_title_2 = "Mavzuni tahrirlash";
$form_title = "Mavzuni tahrirlash";

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

        <!-- Mavzu nomini taxrirlash -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform:none;">Mavzu nomini tahrirlash</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form action="/<?=$url[0]?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="type" value="<?=$url[0]?>">
                                <input type="hidden" name="id" value="<?=$subject["id"]?>">

                                <div class="form-row">
                                    <?=getError("name")?>
                                    <div class="form-group col-12">
                                        <label>Mavzu nomi</label>
                                        <textarea name="name" class="form-control" placeholder="Mavzu nomi"><?=$subject["name"]?></textarea>
                                    </div>

                                    <?=getError("training_type")?>
                                    <div class="form-group col-12">
                                        <label>Mash'gulot turi</label>
                                        <select name="training_type" id="training_type" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <option value="1" <?=((int)$subject["training_type"] == 1 ? 'selected=""' : '')?>>Amaliy</option>
                                            <option value="2" <?=((int)$subject["training_type"] == 2 ? 'selected=""' : '')?>>Nazariy</option>
                                            <option value="3" <?=((int)$subject["training_type"] == 3 ? 'selected=""' : '')?>>Labaratoriya</option>
                                        </select>
                                    </div>

                                    <?=getError("science_id")?>
                                    <div class="form-group col-12">
                                        <label>Fanlar royxati</label>
                                        <select name="science_id" id="science_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? foreach ($db->in_array("SELECT * FROM sciences") as $science) { ?>
                                                <option value="<?=$science["id"]?>" <?=($science["id"] == $subject["science_id"] ? 'selected=""' : '')?> ><?=$science["name"]?></option>
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

include "system/end.php";
?>