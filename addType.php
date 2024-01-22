<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

if ($_REQUEST["type"] == $url[0]){
    validate(["name",]);
    
    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $science_id = $db->insert("types", [
            "creator_user_id" => $user_id,
            "name" => $_POST["name"],
        ]);
        
        header("Location: typesList/?page=1");
    } else {
        // header("Content-type: text/plain");
        // print_r($errors);
        // exit;
    }
}

include "system/head.php";

$breadcump_title_1 = "Turlar";
$breadcump_title_2 = "yangi tur qo'shish";
$form_title = "Yangi tur qo'shish";
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
                                        <label>Nomi:</label>
                                        <input type="text" name="name" class="form-control" placeholder="Nomi" value="<?=$_POST["name"]?>">
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