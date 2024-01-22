<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

if ($_REQUEST["type"] == $url[0]){
    validate(["name", "training_type", "science_id"]);
  

    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $added_group_id = $db->insert("subjects", [
            "creator_user_id" => $user_id,
            "name" => $_POST["name"],
            "training_type" => $_POST["training_type"],
            "science_id" => $_POST["science_id"],
        ]);
        
    
        header("Location: subjectsList/?page=1");
        exit;
    } else {
        // header("Content-type: text/plain");
        // print_r($errors);
        // exit;
    }
}

include "system/head.php";

$breadcump_title_1 = "Mavzular";
$breadcump_title_2 = "yangi mavzu qo'shish";
$form_title = "Yangi mavzu qo'shish";
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
                                        <label>Mavzu nomi</label>
                                        <textarea name="name" class="form-control" placeholder="Mavzu nomi"></textarea>
                                    </div>

                                    <?=getError("training_type")?>
                                    <div class="form-group col-12">
                                        <label>Mash'gulot turi</label>
                                        <select name="training_type" id="training_type" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <option value="1">Amaliy</option>
                                            <option value="2">Nazariy</option>
                                            <option value="3">Labaratoriya</option>
                                        </select>
                                    </div>

                                    <?=getError("science_id")?>
                                    <div class="form-group col-12">
                                        <label>Fanlar royxati</label>
                                        <select name="science_id" id="science_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? foreach ($db->in_array("SELECT * FROM sciences") as $science) { ?>
                                                <option value="<?=$science["id"]?>"><?=$science["name"]?></option>
                                            <? } ?>
                                        </select>
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

<?
include "system/end.php";
?>