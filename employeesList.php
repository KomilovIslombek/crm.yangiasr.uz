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


// $page_count = 20;
$page_end = $page * $page_count;
$page_start = $page_end - $page_count;

// $employees = $db->in_array("SELECT * FROM employees WHERE role = 'employee' ORDER BY id ASC LIMIT $page_start, $page_count");
$employees = $db->in_array("SELECT * FROM users WHERE role != 'teacher' AND role != 'student' ORDER BY id ASC LIMIT $page_start, $page_count");

include "system/head.php";

$breadcump_title_1 = "Xodimlar";
$breadcump_title_2 = "Xodimlar ro'yxati";
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
                        <div class="table-responsive">
                            <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>#id</th>
                                        <th>Lavozimi</th>
                                        <th>F.I.SH</th>
                                        <th>Login</th>
                                        <th>Password</th>
                                        <th>Qo'shilgan sana</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($employees as $employee){ ?>
                                        <?
                                        $image = fileArr($employee["image_id"]);
                                        if ($image["thumb_image_id"]) $image = image($image["thumb_image_id"]);

                                        // $passport_image = fileArr($employee["passport_image_id"]);
                                        // $diplom_image = fileArr($employee["diplom_image_id"]);
                                        // $get_kafedra_employee = $db->assoc("SELECT * FROM kafedra_employees WHERE employee_id = ?", [ $employee["id"] ]);
                                        // $kafedra_id = $db->assoc("SELECT * FROM kafedras WHERE id = ?", $get_kafedra_employee["kafedra_id"])
                                        // $department_employees = $db->in_array("SELECT * FROM department_users WHERE employee_id = ?", [ $employee["id"] ]);
                                        // $get_learn_type = $db->assoc("SELECT * FROM learn_types WHERE id = ?", [ $employee["learn_type_id"] ]);
                                        // $get_employee_region = $db->assoc("SELECT * FROM regions WHERE id = ?", [ $employee["region_id"] ]);
                                        // $get_employee = $db->assoc("SELECT * FROM employees WHERE id = ?", [ $employee["employee_id"] ]);
                                        ?>
                                        
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2"><?=$employee["id"]?></td>
                                            <td class="py-2"><?=$employee["role"]?></td>
                                            <td class="py-2">
                                                <a href="/editEmployee/?id=<?=$employee["id"]?>" ><?=($employee["last_name"] . " " . $employee["first_name"])?></a>
                                            </td>
                                            <td class="py-2"><?=$employee["login"]?></td>
                                            <td class="py-2"><?=decode($employee["password_encrypted"])?></td>
                                            <td class="py-2"><?=$employee["created_date"]?></td>
                                            <td class="py-2 text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                        <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right border py-0">
                                                        <div class="py-2">
                                                            <a class="dropdown-item"  href="/editEmployee/?id=<?=$employee["id"]?>">Tahrirlash</a>
                                                            <a class="dropdown-item text-danger" href="/editEmployee/?id=<?=$employee["id"]?>&type=deleteemployee">O'chirish</a>
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
                        $count = (int)$db->assoc("SELECT COUNT(*) FROM users WHERE role != 'teacher' AND role != 'student' ORDER BY id ASC")["COUNT(*)"];
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
            $(table).find("#table_image").remove();
            $(table).find("#phone_number").each(function() {
               $(this).text(
                $(this).text().replaceAll("+", "")
               ) 
            });
            $(table).find("tbody").find("tr").each(function(){
                $(this).find("td").last().remove();
                $(this).find("#employee_image").remove();
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