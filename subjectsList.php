<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_GET['page'];
if (empty($page)) $page = 1;

if (!empty($_GET["page_count"])) {
    $page_count = $_GET["page_count"];
} else {
    $page_count = 20;
}

$page_end = $page * $page_count;
$page_start = $page_end - $page_count;


if (!empty($_GET["training_type"])) {
    // $subject = $db->assoc("SELECT * FROM subjects WHERE `name` LIKE '%".$_GET["training_type"]."%'");
    $query .= " AND training_type = " . $_GET["training_type"];
}

if (!empty($_GET["science_id"])) {
    $query .= " AND science_id = " . (int)$_GET["science_id"];
}

$count = (int)$db->assoc("SELECT COUNT(*) FROM `subjects` WHERE 1=1$query")["COUNT(*)"];

$subjects = $db->in_array("SELECT * FROM `subjects` WHERE 1=1$query ORDER BY id ASC LIMIT $page_start, $page_count");

include "system/head.php";

$breadcump_title_1 = "Mavzular";
$breadcump_title_2 = "Mavzular ro'yxati ($count)";
?>

<!--**********************************
    Content body start
***********************************-->

<div class="content-body">
    <div class="container-fluid">
        <!-- Add Order -->
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)"><?=$breadcump_title_1?></a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?=$breadcump_title_2?></a></li>
            </ol>
        </div>


        <!-- start Filter -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="/<?=$url[0]?>" method="GET" id="filter">
                            <div class="basic-form row d-flex align-items-center">
                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Fanlar:</label>
                                    <select name="science_id" id="science_id" class="form-control form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($db->in_array("SELECT * FROM sciences") as $science) { ?>
                                            <option
                                                value="<?=$science["id"]?>"
                                                <?=($_GET["science_id"] == $science["id"] ? 'selected=""' : '')?>
                                            ><?=$science["name"]?></option>
                                        <? } ?>
                                    </select>
                                </div>

                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Mash'gulot turlari:</label>
                                    <select name="training_type" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        <option value="">Barchasi</option>
                                        <option value="1" <?=($_GET["training_type"] == 1 ? 'selected=""' : '')?>>Amaliy</option>
                                        <option value="2" <?=($_GET["training_type"] == 2 ? 'selected=""' : '')?>>Nazariy</option>
                                        <option value="3" <?=($_GET["training_type"] == 3 ? 'selected=""' : '')?>>Labaratoriya</option>
                                    </select>
                                </div>
                                
                                <div class="col-xl-3 col-lg-3 col-sm-6 col-12" style="display:none;">
                                    <div class="form-group search-area d-lg-inline-flex col-12">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><a href="javascript:void(0)"><i class="flaticon-381-search-2"></i></a></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Qidirish...">
                                    </div>
                                </div>

                                <!-- <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <a href="export.php" class="btn btn-sm btn-success" id="submit-date" style="margin-top: 17px;padding: 0.9rem 1.5rem;"><i class="icon-file5"></i> Barcha tabalarni olish (EXCEL)</a>
                                </div> -->
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end Filter -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div style="min-height: 300px;" class="table-responsive">
                            <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>Mavzu nomi</th>
                                        <th>Mavzu fani</th>
                                        <th>Mash'gulot turi</th>
                                        <th>Qo'shilgan sana</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($subjects as $subject){ ?>
                                        <? $science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [ $subject["science_id"] ]); ?>
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2"><?=$subject["name"]?></td>
                                            <td class="py-2"><?=$science["name"]?></td>
                                            <? if($subject["training_type"] == 1) {?>
                                                <td class="py-2">Amaliy</td>
                                            <? } else if($subject["training_type"] == 2) { ?>
                                                <td class="py-2">Nazariy</td>
                                            <? } else if($subject["training_type"] == 3) { ?>
                                                <td class="py-2">Labaratoriya</td>
                                            <? } ?>
                                            <td class="py-2"><?=$subject["created_date"]?></td>
                                            <td class="py-2 text-end">
                                                <div class="dropdown">  
                                                    <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                        <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right border py-0">
                                                        <div class="py-2">
                                                            <a class="dropdown-item"  href="/editSubject/?id=<?=$subject["id"]?>&page=<?=$page?>">Tahrirlash</a>
                                                            <a class="dropdown-item text-danger" href="/editSubject/?id=<?=$subject["id"]?>&type=deletesubject">O'chirish</a>
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