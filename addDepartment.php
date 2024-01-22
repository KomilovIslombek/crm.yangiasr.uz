<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

if ($_REQUEST["type"] == $url[0]){
    validate(["name"]);
    
    // $department = $db->assoc("SELECT id FROM departments WHERE `name` = ?", [ $_POST["name"]]);
    include "modules/uploadFile.php";

    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $department_id = $db->insert("departments", [
            "creator_user_id" => $user_id,
            "name" => $_POST["name"],
        ]);

        if ($department_id > 0) {
            foreach($_POST["sciences_id"] as $sciences_id) {
                $db->insert("department_sciences", [
                    "creator_user_id" => $user_id,
                    "department_id" => $department_id,
                    "science_id" => $sciences_id,
                ]);
            }
    
            foreach($_POST["teachers_id"] as $teachers_id) {
                $db->insert("department_teachers", [
                    "creator_user_id" => $user_id,
                    "department_id" => $department_id,
                    "teacher_id" => $teachers_id,
                ]);
            }

            foreach($_POST["groups_id"] as $groups_id) {
                $db->insert("department_groups", [
                    "creator_user_id" => $user_id,
                    "department_id" => $department_id,
                    "group_id" => $groups_id,
                ]);
            }
        }
        
        header("Location: departmentsList/?page=1");
    } else {
        header("Content-type: text/plain");
        print_r($errors);
        exit;
    }
}

include "system/head.php";

$breadcump_title_1 = "Kafedralar";
$breadcump_title_2 = "yangi kafedra qo'shish";
$form_title = "Yangi kafedra qo'shish";
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
            <div class="col-lg-8 col-sm-12">
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
                                        <label>Kafedra nomi:</label>
                                        <input type="text" name="name" class="form-control" placeholder="Kafedra nomi" value="<?=$_POST["name"]?>">
                                    </div>

                                    <?=getError("sciences_id")?>
                                    <div class="form-group col-12">
                                        <label>Fanlar royxati</label>
                                        <select multiple name="sciences_id[]" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? foreach ($db->in_array("SELECT * FROM sciences") as $science) { ?>
                                                <option value="<?=$science["id"]?>"><?=$science["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("teachers_id")?>
                                    <div class="form-group col-12">
                                        <label>Ustozlar royxati</label>
                                        <select multiple name="teachers_id[]" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? foreach ($db->in_array("SELECT * FROM teachers") as $teacher) { ?>
                                                <option value="<?=$teacher["id"]?>"><?=$teacher["first_name"] ." ". $teacher["last_name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                    
                                    <?=getError("groups_id")?>
                                    <div class="form-group col-12">
                                        <label>Guruhlar royxati</label>
                                        <select multiple name="groups_id[]" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? foreach ($db->in_array("SELECT * FROM groups_list") as $group) { ?>
                                                <option value="<?=$group["id"]?>"><?=$group["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <button type="submit" class="btn btn-primary">Qo'shish</button>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->
    </div>
</div>
<!--**********************************
    Content body end
***********************************-->

<?
include "system/scripts.php";

// Script uchun

include "system/end.php";
?>