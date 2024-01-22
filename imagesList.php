<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_GET['page'];
if (empty($page)) $page = 1;

$page_count = 20;
$page_end = $page * $page_count;
$page_start = $page_end - $page_count;

$images = $db->in_array("SELECT * FROM images ORDER BY id ASC LIMIT $page_start, $page_count");

include "system/head.php";
?>

<!--**********************************
    Content body start
***********************************-->
<div class="content-body">
    <div class="container-fluid">
        <!-- Add Order -->
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Rasmlar</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Rasmlar ro'yxati</a></li>
            </ol>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div style="min-height: 300px;" class="table-responsive">
                            <table class="table table-bordered table-responsive-md mb-0">
                                <thead>
                                    <tr>
                                        <th>#id</th>
                                        <th>Rasmi</th>
                                        <th>Qo'shilgan sana</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($images as $image){ ?>
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2"><?=$image["id"]?></td>
                                            <td>
                                                <a href="<?=$image["file_folder"]?>">
                                                    <img src="<?=$image["file_folder"]?>" alt="<?=$image["file_folder"]?>" width="100px">
                                                </a>
                                            </td>
                                            <td class="py-2"><?=$image["created_date"]?></td>
                                            <td class="py-2 text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                        <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right border py-0">
                                                        <div class="py-2">
                                                            <a class="dropdown-item"  href="/editImage/?image_id=<?=$image["id"]?>">Tahrirlash</a>
                                                            <a class="dropdown-item text-danger" href="/editImage/?image_id=<?=$image["id"]?>&type=deleteImage">O'chirish</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <? } ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?
                        include "modules/pagination.php";

                        // count
                        $count = (int)$db->assoc("SELECT COUNT(*) FROM images")["COUNT(*)"];
                        echo pagination($count, $url[0]."/", $page_count); 
                        ?>
                        <!-- End Pagination -->
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