<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_GET['page'];
if (empty($page)) $page = 1;

$html = file_get_contents("sh2024.doc");
// echo "test";
// echo $html;
$html = strtr($html, [ 
    "SHARTNOMA" => "HELLO",
]);

echo "<pre>";
print_r($html);
echo "</pre>";
exit;
include "system/head.php";

?>

<?
include "system/scripts.php";

include "system/end.php";
?>