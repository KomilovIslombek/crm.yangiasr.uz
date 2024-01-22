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

$type = $db->assoc("SELECT * FROM types WHERE id = ?", [$id]);
if (!$type["id"]) {echo"error (type not found)";exit;}

if ($_REQUEST["type"] == $url[0]){
    validate(["name",]);
    
    include "modules/uploadFile.php";

    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $db->update("types", [
            "name" => $_POST["name"],
        ], [
            "id" => $type["id"]
        ]);

        header("Location: typesList/?page=" . $page);
        exit;
    } else {
        // header("Content-type: text/plain");
        // print_r($errors);
        // exit;
    }
}

if ($_REQUEST["type"] == "deletetype") {
    $db->delete("types", $type["id"], "id");

    header("Location: /typesList/?page=" . $page);
    exit;
}

include "system/head.php";

$breadcump_title_1 = "Turlar";
$breadcump_title_2 = "Turni tahrirlash";
$form_title = "Turni tahrirlash";
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
                                <input type="hidden" name="id" value="<?=$type["id"]?>">
                                <input type="hidden" name="type" value="<?=$url[0]?>">

                                <div class="form-row">

                                    <?=getError("name")?>
                                    <div class="form-group col-12">
                                        <label>Fan nomi:</label>
                                        <input type="text" name="name" class="form-control" placeholder="Fan nomi" value="<?=$type["name"]?>">
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