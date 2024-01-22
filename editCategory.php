<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_REQUEST["page"];
if (empty($page)) $page = 1;

$category_id = isset($_REQUEST["category_id"]) ? $_REQUEST["category_id"] : null;
if (!$category_id) {echo"error category_id not found";return;}

$category = $db->assoc("SELECT * FROM categories WHERE id = ?", [$category_id]);
if (!$category["id"]) {echo"error (category not found)";exit;}

if ($_REQUEST["type"] == $url[0]){
    validate(["name"]);

    include "modules/uploadImage.php";
    $uploadedImage = uploadImageWithUpdate("image", "images/categories", ["jpg","jpeg","png"], false, false, $category["image_id"]);
    
    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $db->update("categories", [
            "name" => $_POST["name"],
            "image_id" => $uploadedImage["image_id"]
        ], [
            "id" => $category["id"]
        ]);
        
        header("Location: /categoriesList/?page=" . $page);
        exit;
    }
}

if ($_REQUEST["type"] == "deleteCategory") {
    $db->delete("categories", $category["id"]);
    if ($category["image_id"] > 0) delete_image($category["image_id"]);
    header("Location: /categoriesList/?page=" . $page);
    exit;
}

include "system/head.php";

$breadcump_title_1 = "Bo'lim";
$breadcump_title_2 = "bo'limni tahrirlash";
$form_title = "bo'limni tahrirlash";
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
                                <input type="hidden" name="category_id" value="<?=$category["id"]?>">

                                <div class="form-row">
                                    <?=getError("name")?>
                                    <div class="form-group col-12">
                                        <label>Nomi</label>
                                        <input type="text" name="name" class="form-control" placeholder="Nomi" value="<?=$category["name"]?>">
                                    </div>

                                    <?
                                    if ($category["image_id"] > 0) {
                                        $image = image($category["image_id"]);
                                        if ($image["file_folder"]) {
                                            echo '<image src="'.$image["file_folder"].'" width="400px">';
                                        }
                                    }
                                    ?>

                                    <?=getError("image")?>
                                    <div class="form-group col-12">
                                        <label for="formFile" class="form-label">Rasm yuklash (jpg, jpeg, png) (300x300)</label>
                                        <input class="form-control" type="file" name="image" id="formFile" accept="image/*">
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
?>

<script>
    $("#price-input").on("input", function(){
        var val = $(this).val().replaceAll(",", "").replaceAll(" ", "");
        console.log(val);

        if (val.length > 0) {    
            $(this).val(
                String(val).replace(/(.)(?=(\d{3})+$)/g,'$1,')
            );
        }
    });
</script>

<?
include "system/end.php";
?>