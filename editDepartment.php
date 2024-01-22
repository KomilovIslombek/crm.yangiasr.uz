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

$department = $db->assoc("SELECT * FROM departments WHERE id = ?", [$id]);
if (!$department["id"]) {echo"error (department not found)";exit;}

// echo "<pre>";
// print_r($department_sciences);
// exit;

if ($_REQUEST["type"] == $url[0]){
    validate(["name"]);
   
    include "modules/uploadFile.php";

    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $db->delete("department_sciences", $department["id"], "department_id");
        $db->delete("department_teachers", $department["id"], "department_id");
        $db->delete("department_groups", $department["id"], "department_id");
        
        $db->update("departments", [
            // "creator_user_id" => $user_id,
            "name" => $_POST["name"],
        ], [
            "id" => $department["id"]
        ]);
        foreach($_POST["sciences_id"] as $sciences_id) {
            $db->insert("department_sciences", [
                "creator_user_id" => $user_id,
                "department_id" => $department["id"],
                "science_id" => $sciences_id,
            ], [
                "department_id" => $department["id"]
            ]);
        }

        foreach($_POST["teachers_id"] as $teachers_id) {
            $db->insert("department_teachers", [
                "creator_user_id" => $user_id,
                "department_id" => $department["id"],
                "teacher_id" => $teachers_id,
            ], [
                "department_id" => $department["id"]
            ]);
        }

        foreach($_POST["groups_id"] as $groups_id) {
            $db->insert("department_groups", [
                "creator_user_id" => $user_id,
                "department_id" => $department["id"],
                "group_id" => $groups_id,
            ], [
                "department_id" => $department["id"]
            ]);
        }

        header("Location: departmentsList/?page=" . $page);
        exit;
    } else {
        header("Content-type: text/plain");
        print_r($errors);
        exit;
    }
}

if ($_REQUEST["type"] == "deletedepartment") {
    $db->delete("departments", $department["id"], "id");
    $db->delete("department_sciences", $department["id"], "department_id");
    $db->delete("department_teachers", $department["id"], "department_id");
    $db->delete("department_groups", $department["id"], "department_id");

    header("Location: /departmentsList/?page=" . $page);
    exit;
}

include "system/head.php";

$breadcump_title_1 = "Kafedralar";
$breadcump_title_2 = "Kafedrani tahrirlash";
$form_title = "Kafedrani tahrirlash";
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
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform:none;"><?=$form_title?></h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form action="/<?=$url[0]?>" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="type" value="<?=$url[0]?>">
                                <input type="hidden" name="page" value="<?=$page?>">
                                <input type="hidden" name="id" value="<?=$department["id"]?>">
                                <input type="hidden" name="type" value="<?=$url[0]?>">

                                <div class="form-row">
                                    <?=getError("name")?>
                                    <div class="form-group col-12">
                                        <label>Kafedrani nomi:</label>
                                        <input type="text" name="name" class="form-control" placeholder="kafedrani nomi" value="<?=$department["name"]?>">
                                    </div>

                                    <?=getError("sciences_id")?>
                                    <div class="form-group col-12">
                                        <label>Fanlar royxati</label>
                                        <select multiple name="sciences_id[]" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? foreach ($db->in_array("SELECT * FROM sciences") as $science) { ?>
                                                <?
                                                    $department_science = $db->assoc("SELECT * FROM department_sciences WHERE department_id = ? AND science_id = ?", [ $department["id"], $science["id"] ]);
                                                ?>
                                                <option value="<?=$science["id"]?>" <?=($science["id"] == $department_science["science_id"]  ? 'selected=""' : '')?>><?=$science["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("teachers_id")?>
                                    <div class="form-group col-12">
                                        <label>Ustozlar royxati</label>
                                        <select multiple name="teachers_id[]" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" id="myselect" data-actions-box="true">
                                            <? foreach ($db->in_array("SELECT * FROM teachers") as $teacher) { ?>
                                                <? 
                                                    $department_teacher = $db->assoc("SELECT * FROM department_teachers WHERE department_id = ? AND teacher_id = ?", [$department["id"], $teacher["id"]]);
                                                ?>
                                                <option value="<?=$teacher["id"]?>" <?=($teacher["id"] == $department_teacher["teacher_id"] ? 'selected=""' : '')?>><?=$teacher["first_name"]. " " . $teacher["last_name"]?></option>
                                            <? } ?>
                                        </select>
                                        </select>
                                    </div>
                                    
                                    <?=getError("groups_id")?>
                                    <div class="form-group col-12">
                                        <label>Guruhlar royxati</label>
                                        <select multiple name="groups_id[]" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? foreach ($db->in_array("SELECT * FROM groups_list") as $group_list) { ?>
                                                <?
                                                    $department_group = $db->assoc("SELECT * FROM department_groups WHERE department_id = ? AND group_id = ?", [$department["id"], $group_list["id"]]);
                                                ?>
                                                <option value="<?=$group_list["id"]?>" <?=($group_list["id"] == $department_group["group_id"] ? 'selected=""' : '')?>><?=$group_list["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>
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

// bu yerga script yoziladi

include "system/end.php";
?>