<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

header("Location: /$permissions[0]");
exit;

include "system/head.php";
?>



<?
include "system/scripts.php";
?>

<?
include "system/end.php";
?>