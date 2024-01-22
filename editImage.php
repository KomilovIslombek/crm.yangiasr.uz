<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_REQUEST["page"];
if (empty($page)) $page = 1;

$image_id = isset($_REQUEST["image_id"]) ? $_REQUEST["image_id"] : null;
if (!$image_id) {echo"error image_id not found";return;}

$image = $db->assoc("SELECT * FROM images WHERE id = ?", [$image_id]);
if (!$image["id"]) {echo"error (image not found)";exit;}

if ($_REQUEST["type"] == "deleteImage"){
    delete_image($image["id"]);

    header("Location: imagesList/?page=" . $page);
}
?>