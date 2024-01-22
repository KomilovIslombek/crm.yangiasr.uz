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

$query = "";

if (!empty($_GET["group_id"])) {
    $query .= " AND group_id = " . (int)$_GET["group_id"];
}

// $page_count = 20;
$page_end = $page * $page_count;
$page_start = $page_end - $page_count;

$sciences = $db->in_array("SELECT * FROM sciences ORDER BY id ASC LIMIT $page_start, $page_count");

if($systemUser["role"] == "teacher" && $systemUser["teacher_id"]) {
    $teacher_groups = $db->in_array("SELECT * FROM group_teachers WHERE teacher_id = ?", [ $systemUser["teacher_id"] ]);
    $group_sciences = $db->in_array("SELECT * FROM group_sciences WHERE 1=1$query");
}


include "system/head.php";

$breadcump_title_1 = "Fanlar";
$breadcump_title_2 = "Fanlar ro'yxati";
?>

<!--**********************************
    Content body start
***********************************-->

<div class="content-body">
    <div class="container-fluid">
        <!-- Add Order -->
        <div class="page-titles d-flex justify-content-between">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)"><?=$breadcump_title_1?></a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?=$breadcump_title_2?></a></li>
            </ol>
            <a href="javascript:void(0)" class="btn btn-primary rounded me-3 mb-sm-0 mb-2" id="exportToExcel">
                <i class="fa fa-upload me-3 scale5" aria-hidden="true"></i>Export
            </a>
        </div>
        
        <!-- start Filter -->
        <? if($systemUser["role"] == "teacher" && $systemUser["teacher_id"]) {?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="/<?=$url[0]?>" method="GET" id="filter">
                                <div class="basic-form row d-flex align-items-center">

                                    <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                        <label>Guruhlar ro'yxati:</label>
                                        <select name="group_id" id="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            
                                            <? foreach ($teacher_groups as $teacher_group) {
                                                $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $teacher_group["group_id"] ]);
                                            ?>
                                                <option
                                                    value="<?=$group["id"]?>"
                                                    <?=($_GET["group_id"] == $group["id"] ? 'selected=""' : '')?>
                                                > <?=$group["name"]?></option>
                                            <? } ?>
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

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <? } ?>
        <!-- end Filter -->
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div style="min-height: 300px;" class="table-responsive">
                            <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <? if ($_REQUEST["export"] == "word") { ?>
                                            <th>#id</th>
                                            <th>Fan nomi</th>
                                            <th>Fan kodi</th>
                                            <th>Akademik daraja</th>
                                            <th>Kredit hajmi (1 kredit = 30 soat)</th>
                                            <th>Ajratilgan akademik soat hajmi	120	Talabalarni erkin qabul qilish kuni	</th>
                                            <th>Ta’lim yo‘nalishi</th>
                                            <th>Semester</th>
                                            <th>Modulning davomiyligi</th>
                                            <th>Tayyorladi</th>
                                            <th>Talabalarni erkin qabul qilish kuni</th>
                                            <th></th>
                                        <? } else { ?>
                                            <th>#id</th>
                                            <th>Kafedrasi</th>
                                            <th>Fan nomi</th>
                                            <th>Necha soat Ma'ruza ekanligi </th>
                                            <th>Necha soat amaliyot ekanligi</th>
                                            <th>Necha soat seminar ekanligi</th>
                                            <th>Necha soat labaratoriya ekanligi</th>
                                            <th>fanlar vaqtini yig'indisi</th>
                                            <th>Mustaqil ta'lim</th>
                                            <th>Qo'shilgan sana</th>
                                            <th></th>
                                        <? } ?>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? if($systemUser["role"] != "teacher") { ?>
                                        <? foreach ($sciences as $science){ ?>
                                            <?
                                            // $image = fileArr($science["image_id"]);
                                            // if ($image["thumb_image_id"]) $image = image($image["thumb_image_id"]);

                                            $department_sciences = $db->in_array("SELECT * FROM department_sciences WHERE science_id = ?", [ $science["id"] ]);
                                            // $get_direction = $db->assoc("SELECT * FROM directions WHERE id = ?", [ $science["direction_id"] ]);
                                            // $get_learn_type = $db->assoc("SELECT * FROM learn_types WHERE id = ?", [ $science["learn_type_id"] ]);
                                            // $get_science_region = $db->assoc("SELECT * FROM regions WHERE id = ?", [ $science["region_id"] ]);
                                            // $get_science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [ $science["science_id"] ]);
                                            ?>
                                            
                                            <tr class="btn-reveal-trigger">
                                                <td class="py-2"><?=$science["id"]?></td>
                                                <td class="py-2">
                                                    <?
                                                        foreach($department_sciences as $department_science) {
                                                            $department_science_name = $db->assoc("SELECT * FROM departments WHERE id = ?", [ $department_science["department_id"] ]);
                                                            echo $department_science_name["name"] . " ";
                                                        }
                                                    ?>
                                                </td>
                                                <td class="py-2"><a class="dropdown-item"  href="/addSubject/?id=<?=$science["id"]?>"><?=$science["name"]?></a></td>
                                                <td class="py-2"><?=$science["lecture_hour"]?></td>
                                                <td class="py-2"><?=$science["practica_hour"]?></td>
                                                <td class="py-2"><?=$science["seminar_hour"]?></td>
                                                <td class="py-2"><?=$science["labaratory_hour"]?></td>
                                                <td class="py-2"><?=$science["science_hour"]?></td>
                                                <td class="py-2"><?=$science["nation_education"]?></td>
                                                <td class="py-2"><?=$science["created_date"]?></td>
                                                <td class="py-2 text-end">
                                                    <div class="dropdown">
                                                        <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                            <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right border py-0">
                                                            <div class="py-2">
                                                                <a class="dropdown-item" target="_blank"  href="/pdf/?id=<?=$science["id"]?>">Sillabus olish</a>
                                                                <!-- <a class="dropdown-item"  href="/addSubject/?id=<?=$science["id"]?>">Fanga mavzu qo'shish</a> -->
                                                                <a class="dropdown-item"  href="/editScience/?id=<?=$science["id"]?>">Tahrirlash</a>
                                                                <a class="dropdown-item text-danger" href="/editScience/?id=<?=$science["id"]?>&type=deletescience">O'chirish</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <? } ?>
                                    <? } else if($_GET["group_id"]) { ?>
                                        <? foreach ($group_sciences as $group_science){ ?>
                                            <?
                                            $science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [ $group_science["science_id"] ]); 
                                            $department_sciences = $db->in_array("SELECT * FROM department_sciences WHERE science_id = ?", [ $science["id"] ]);
                                            ?>
                                            
                                            <tr class="btn-reveal-trigger">
                                                <td class="py-2"><?=$science["id"]?></td>
                                                <td class="py-2">
                                                    <?
                                                        foreach($department_sciences as $department_science) {
                                                            $department_science_name = $db->assoc("SELECT * FROM departments WHERE id = ?", [ $department_science["department_id"] ]);
                                                            echo $department_science_name["name"] . " ";
                                                        }
                                                    ?>
                                                </td>
                                                <td class="py-2"><a class="dropdown-item"  href="/addSubject/?id=<?=$science["id"]?>"><?=$science["name"]?></a></td>
                                                <td class="py-2"><?=$science["lecture_hour"]?></td>
                                                <td class="py-2"><?=$science["practica_hour"]?></td>
                                                <td class="py-2"><?=$science["seminar_hour"]?></td>
                                                <td class="py-2"><?=$science["labaratory_hour"]?></td>
                                                <td class="py-2"><?=$science["science_hour"]?></td>
                                                <td class="py-2"><?=$science["created_date"]?></td>
                                                <td class="py-2 text-end">
                                                    <div class="dropdown">
                                                        <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                            <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right border py-0">
                                                            <div class="py-2">
                                                                <a class="dropdown-item" target="_blank"  href="/pdf/?id=<?=$science["id"]?>">Sillabus olish</a>
                                                                <!-- <a class="dropdown-item"  href="/addSubject/?id=<?=$science["id"]?>">Fanga mavzu qo'shish</a> -->
                                                                <a class="dropdown-item"  href="/editScience/?id=<?=$science["id"]?>">Tahrirlash</a>
                                                                <a class="dropdown-item text-danger" href="/editScience/?id=<?=$science["id"]?>&type=deletescience">O'chirish</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <? } ?>
                                    <? } ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?
                        include "modules/pagination.php";

                        // count
                            $count = (int)$db->assoc("SELECT COUNT(*) FROM sciences")["COUNT(*)"];
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

<script>
        $("#exportToExcel").on("click", function(){
            var q = $( "#filter" ).serialize();
            var url = '/<?=$url[0]?>?' + q + "&page_count=1000000";

            $.get(url, function(data){
                var table = $(data).find("#table");
                $(table).find("thead").find("th").last().remove();
                $(table).find("tbody").find("tr").each(function(){
                    $(this).find("td").last().remove();
                });

                tableToExcel(
                $(table).prop("innerHTML")  
                );
            });
        });

        $("#group_id").change();
</script>

<?
include "system/end.php";
?>