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

if (!empty($_GET["q"])) {
    $_GET["q"] = str_replace(" ", "", $_GET["q"]);
    $q = mb_strtolower(trim($_GET["q"]));
    $q = str_replace("'", "\\"."'"."\\", $q);
    
    $query .= " AND name LIKE '%".$q."%'";
}

$groups_list = $db->in_array("SELECT * FROM `groups_list` WHERE 1=1$query ORDER BY id ASC LIMIT $page_start, $page_count");
// $payments = $db->in_array("SELECT * FROM payments ORDER BY id ASC LIMIT $page_start, $page_count");
$count = $db->assoc("SELECT COUNT(*) FROM groups_list WHERE 1=1$query")["COUNT(*)"];

include "system/head.php";

$breadcump_title_1 = "Guruhlar";
$breadcump_title_2 = "Guruhlar ro'yxati ($count ta)";
?>

<!--**********************************
    Content body start
***********************************-->

<div class="content-body">
    <div class="container-fluid">
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
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="/<?=$url[0]?>" method="GET" id="filter">
                            <div class="basic-form row d-flex align-items-center">
                                <div class="form-group col-xl-12 col-lg-12 col-sm-6 col-12">
                                    <label>Qidirish:</label>
                                    <input type="text" name="q" class="form-control form-control-lg" placeholder="Qidirish..." id="input-search" value="<?=htmlspecialchars($_GET["q"])?>">
                                </div>
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
                                        <th>#id</th>
                                        <th>Guruh nomi</th>
                                        <th>Ustozlar</th>
                                        <th>Talabalar soni</th>
                                        <th>Qo'shilgan sana</th>
                                        <? if (empty($_GET["export"])) { ?>
                                            <th></th>
                                        <? } ?>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($groups_list as $group){ ?>
                                        <?
                                            $students_count = $db->assoc("SELECT COUNT(*) FROM students WHERE group_id = ?", [ $group["id"] ])["COUNT(*)"];

                                            $group_teachers = $db->in_array("SELECT * FROM group_teachers WHERE group_id = ?", [ $group["id"] ]);
                                        ?>
                               
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2"><?=$group["id"]?></td>
                                            <td class="py-2"><?=$group["name"]?></td>
                                            <td class="py-2">
                                                <?
                                                foreach($group_teachers as $group_teacher) {
                                                    $teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $group_teacher["teacher_id"] ]);
                                                    echo $teacher["first_name"]. " " . $teacher["last_name"] . "<br>";
                                                }   
                                                ?>
                                            </td>
                                            <td>
                                                <a href="/studentsList/?group_id=<?=$group["id"]?>&page=1" class="badge badge-success"><?=$students_count?> ta</a>
                                            </td>
                                            <td class="py-2"><?=$group["created_date"]?></td>
                                            <? if (empty($_GET["export"])) { ?>
                                                <td class="py-2 text-end">
                                                    <div class="dropdown">  
                                                        <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                            <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right border py-0">
                                                            <div class="py-2">
                                                                <a class="dropdown-item"  href="/editGroup/?groups_id=<?=$group["id"]?>">Tahrirlash</a>
                                                                <a class="dropdown-item text-danger" href="/editGroup/?groups_id=<?=$group["id"]?>&type=deletegroup">O'chirish</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            <? } ?>
                                        </tr>
                                    <? } ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?
                        include "modules/pagination.php";

                        // count
                            $count = (int)$db->assoc("SELECT COUNT(*) FROM groups_list")["COUNT(*)"];
                        // $count = (int)$db->assoc("SELECT COUNT(*) FROM users WHERE role = 'group'")["COUNT(*)"];
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
        var url = '/<?=$url[0]?>?' + q + "&export=excel&page_count=1000000";

        $.get(url, function(data){
            var table = $(data).find("#table");
            tableToExcel(
                $(table).prop("innerHTML")
            );
        });
    });
</script>

<?
include "system/end.php";
?>