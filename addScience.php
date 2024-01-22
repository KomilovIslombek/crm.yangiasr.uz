<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

if ($_REQUEST["type"] == $url[0]){
    validate(["name", "lecture_hour", "practica_hour", "seminar_hour"]);
    
    include "modules/uploadFile.php";

    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $science_id = $db->insert("sciences", [
            "creator_user_id" => $user_id,
            "name" => $_POST["name"],
            "code" => $_POST["code"],
            "lecture_hour" => $_POST["lecture_hour"],
            "practica_hour" => $_POST["practica_hour"],
            "seminar_hour" => $_POST["seminar_hour"],
            "theoretical_hour" => $_POST["theoretical_hour"],
            "labaratory_hour" => $_POST["labaratory_hour"],
            "nation_education" => $_POST["nation_education"],
            "science_hour" => $_POST["lecture_hour"] + $_POST["practica_hour"] + $_POST["seminar_hour"] + $_POST["labaratory_hour"]
        ]);

        if($science_id > 0) {
            foreach($_POST["department"] as $department_id) {
                $departmen_science_id = $db->insert("department_sciences", [
                    "creator_user_id" => $user_id,
                    "department_id" => $department_id,
                    "science_id" => $science_id,
                ]);
            }
        }
        
        header("Location: sciencesList/?page=1");
    } else {
        header("Content-type: text/plain");
        print_r($errors);
        exit;
    }
}

include "system/head.php";

$breadcump_title_1 = "Fanlar";
$breadcump_title_2 = "yangi fan qo'shish";
$form_title = "Yangi fan qo'shish";
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
                                    <?=getError("department")?>
                                    <div class="form-group col-12">
                                        <label>Kafedra nomi:</label>
                                        <select multiple name="department[]" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? foreach ($db->in_array("SELECT * FROM departments") as $department) { ?>
                                                <option value="<?=$department["id"]?>"><?=$department["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("name")?>
                                    <div class="form-group col-12">
                                        <label>Fan nomi:</label>
                                        <input type="text" name="name" class="form-control" placeholder="Fan nomi" value="<?=$_POST["name"]?>">
                                    </div>
                                    
                                    <?=getError("code")?>
                                    <div class="form-group col-12">
                                        <label>Fan kodi:</label>
                                        <input type="text" name="code" class="form-control" placeholder="Fan kodi" value="<?=$_POST["code"]?>">
                                    </div>

                                    <?=getError("lecture_hour")?>
                                    <div class="form-group col-12">
                                        <label>Necha soat maruza ekanligi:</label>
                                        <input type="number" step="0.01" name="lecture_hour" class="form-control" placeholder="Necha soat maruza ekanligi" value="<?=$_POST["lecture_hour"]?>">
                                    </div>

                                    <?=getError("practica_hour")?>
                                    <div class="form-group col-12">
                                        <label>Necha soat amaliyot ekanligi:</label>
                                        <input type="number" step="0.01" name="practica_hour" class="form-control" placeholder="Necha soat amaliyot ekanligi" value="<?=$_POST["practica_hour"]?>">
                                    </div>

                                    <?=getError("seminar_hour")?>
                                    <div class="form-group col-12">
                                        <label>Necha soat seminar ekanligi:</label>
                                        <input type="number" step="0.01" name="seminar_hour" class="form-control" placeholder="Necha soat seminar ekanligi" value="<?=$_POST["seminar_hour"]?>">
                                    </div>

                                    <?=getError("theoretical_hour")?>
                                    <div class="form-group col-12">
                                        <label>Necha soat nazariy ekanligi:</label>
                                        <input type="number" step="0.01" name="theoretical_hour" class="form-control" placeholder="Necha soat nazariy ekanligi" value="<?=$_POST["theoretical_hour"]?>">
                                    </div>

                                    <?=getError("labaratory_hour")?>
                                    <div class="form-group col-12">
                                        <label>Necha soat labaratoriya ekanligi:</label>
                                        <input type="number" step="0.01" name="labaratory_hour" class="form-control" placeholder="Necha soat labaratoriya ekanligi" value="<?=$_POST["labaratory_hour"]?>">
                                    </div>
                                    
                                    <?=getError("nation_education")?>
                                    <div class="form-group col-12">
                                        <label>Mustaqil ta'lim:</label>
                                        <input type="number" step="0.01" name="nation_education" class="form-control" placeholder="Mustaqil ta'lim" value="<?=$_POST["nation_education"]?>">
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