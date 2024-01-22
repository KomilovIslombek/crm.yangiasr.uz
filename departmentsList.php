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

$page_count = 20;
$page_end = $page * $page_count;
$page_start = $page_end - $page_count;

    $departments = $db->in_array("SELECT * FROM departments ORDER BY id ASC LIMIT $page_start, $page_count");
    // $payments = $db->in_array("SELECT * FROM payments ORDER BY id ASC LIMIT $page_start, $page_count");
    // $student_count = $db->assoc("SELECT COUNT(*) from students");

include "system/head.php";

$breadcump_title_1 = "Kafedralar";
$breadcump_title_2 = "Kafedralar ro'yxati";
?>

<!--**********************************
    Content body start
***********************************-->

<div class="content-body">
    <div class="container-fluid">
        <!-- Add Order -->
        <div class="page-titles d-flex justify-content-between align-items-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)"><?=$breadcump_title_1?></a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?=$breadcump_title_2?></a></li>
            </ol>
            <a href="javascript:void(0)" class="btn btn-primary rounded me-3 mb-sm-0 mb-2" id="exportToExcel">
                <i class="fa fa-upload me-3 scale5" aria-hidden="true"></i>Export
            </a>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div style="min-height: 300px;" class="table-responsive">
                            <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>#id</th>
                                        <th>Kafedra nomi</th>
                                        <th>Kafedra Fanlar soni</th>
                                        <th>Kafedra O'qituvchilar soni</th>
                                        <th>Kafedra Guruhlar soni</th>
                                        <th>Qo'shilgan sana</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($departments as $department){ ?>
                                        <?
                                            // $group_students = $db->in_array("SELECT * FROM group_users WHERE group_id = ?", [ $group["id"] ]);
                                            $department_sciences = $db->in_array("SELECT * FROM department_sciences WHERE department_id = ?", [ $department["id"] ]);
                                            $department_teachers = $db->in_array("SELECT * FROM department_teachers WHERE department_id = ?", [ $department["id"] ]);
                                            $department_groups = $db->in_array("SELECT * FROM department_groups WHERE department_id = ?", [ $department["id"] ]);
                                        ?>
                               
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2"><?=$department["id"]?></td>
                                            <td class="py-2"><?=$department["name"]?></td>
                                            <td class="py-2 px-3">
                                                <?=count($department_sciences)?>
                                            </td>
                                            <td class="py-2 px-3">
                                                <?=count($department_teachers)?>
                                            </td>
                                            <td class="py-2 px-3">
                                                <?=count($department_groups)?>
                                            </td>
                                            <td class="py-2"><?=$department["created_date"]?></td>
                                            <td class="py-2 text-end">
                                                <div class="dropdown">  
                                                    <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                        <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right border py-0">
                                                        <div class="py-2">
                                                            <a class="dropdown-item"  href="/editDepartment/?id=<?=$department["id"]?>">Tahrirlash</a>
                                                            <a class="dropdown-item text-danger" href="/editDepartment/?id=<?=$department["id"]?>&type=deletedepartment">O'chirish</a>
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
                            $count = (int)$db->assoc("SELECT COUNT(*) FROM departments")["COUNT(*)"];
                        // $count = (int)$db->assoc("SELECT COUNT(*) FROM users WHERE role = 'department'")["COUNT(*)"];
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
</script>

<?
include "system/end.php";
?>