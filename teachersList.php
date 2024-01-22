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

// Filterlar
if (!empty($_GET["q"])) {
    $_GET["q"] = str_replace(" ", "", $_GET["q"]);
    $q = mb_strtolower(trim($_GET["q"]));
    $q = str_replace("'", "\\"."'"."\\", $q);

    $pq = "";
    $pq .= "REPLACE(phone_1, '+', ''), ";
    $pq .= "REPLACE(phone_1, '-', ''), ";
    $pq .= "REPLACE(REPLACE(phone_1, '+', ''), '-', ''), ";

    $pq .= "REPLACE(phone_2, '+', ''), ";
    $pq .= "REPLACE(phone_2, '-', ''), ";
    $pq .= "REPLACE(REPLACE(phone_2, '+', ''), '-', '')";
    // AhmadjonMadgaziyevAbdulbosit
    
    $query .= " AND (id LIKE '%".$q."%' OR CONCAT(first_name,last_name,father_first_name) LIKE '%".$q."%' OR CONCAT(father_first_name,last_name,first_name) LIKE '%".$q."%' OR REPLACE(REPLACE(phone_1, '+', ''), '-', '') LIKE '%".str_replace("-", "", $q)."%' OR REPLACE(REPLACE(phone_2, '+', ''), '-', '') LIKE '%".str_replace("-", "", $q)."%')";
}


$sql = "SELECT * FROM teachers WHERE 1=1$query ORDER BY id ASC";

$count = $db->assoc("SELECT COUNT(*) FROM teachers WHERE 1=1$query")["COUNT(*)"];
// $teachers = $db->in_array("SELECT * FROM teachers WHERE role = 'teacher' ORDER BY id ASC LIMIT $page_start, $page_count");
$sql .= " LIMIT $page_start, $page_count";

$teachers = $db->in_array($sql);

if (!empty($_GET["department_id"])) {
    $query2 .= " AND department_id = " . (int)$_GET["department_id"];
    $teachers = [];
    $count = $db->assoc("SELECT COUNT(*) FROM department_teachers WHERE 1=1$query2")["COUNT(*)"];
    $department_teachers = $db->in_array("SELECT * FROM department_teachers WHERE 1=1$query2 LIMIT $page_start, $page_count");

    foreach ($department_teachers as $department_teacher) {
        $teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $department_teacher["teacher_id"] ]);
        array_push($teachers, $teacher);
    }
}

// $teachers = $db->in_array('SELECT * FROM teachers');
// $teacher_permissions = [ "journalList", "personalArea", "calendar"];
// $teacher_permissions = [ "journalList", "pdf", "calendar", "sciencesList"];

// foreach ($teachers as $teacher) {
//     $db->update("users", [
//         "permissions" => ($teacher_permissions ? json_encode($teacher_permissions) : NULL)
//     ], [
//         "teacher_id" => $teacher["id"]
//     ]);
// }

include "system/head.php";

$breadcump_title_1 = "O'qituvchilar";
$breadcump_title_2 = "O'qituvchilar ro'yxati ($count)";
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

        <!-- Start Filter -->
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="/<?=$url[0]?>" method="GET" id="filter">
                            <div class="basic-form row d-flex align-items-center">
                                <div class="form-group  col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Kafedralar:</label>
                                    <select name="department_id" class="form-control form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($db->in_array("SELECT * FROM departments") as $department) { ?>
                                            <option 
                                            value="<?=$department["id"]?>"
                                            <?=($_GET["department_id"] == $department["id"] ? 'selected=""' : '')?>
                                            ><?=$department["name"]?></option>
                                        <? } ?>
                                    </select>
                                </div>
    
                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Qidirish:</label>
                                    <input type="text" name="q" class="form-control form-control" placeholder="Qidirish..." id="input-search">
                                </div>
                                
                                <!-- <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <button style="margin-top: 17px" class="btn btn-sm btn-primary" id="send-sms"><i class="text-white flaticon-381-send-2"></i> Barchaga sms yuborish</button>
                                </div>
                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <a style="margin-top: 17px" href="export.php" class="btn btn-sm btn-success" id="submit-date"><i class="icon-file5"></i> Barcha arizalarni olish (EXCEL)</a>
                                </div> -->
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- End Filter -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>#id</th>
                                        <th id="table_image">O'qituvchinig rasmi</th>
                                        <th>F.I.SH</th>
                                        <th>Telefon raqami</th>
                                        <th>Kafedrasi</th>
                                        <? if ($_GET["export"] == "excel") { ?>
                                            <th>login</th>
                                            <th>parol</th>
                                        <? } ?>
                                        <th>Qo'shilgan sana</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($teachers as $teacher){ ?>
                                        <?
                                        $image = fileArr($teacher["image_id"]);
                                        if ($image["thumb_image_id"]) $image = image($image["thumb_image_id"]);
                                        $group_teacher = $db->assoc("SELECT * FROM group_teachers WHERE teacher_id = ?", [ $teacher["id"] ]);
                                        $science = $db->assoc("SELECT * FROM group_sciences WHERE group_id = ?", [ $group_teacher["group_id"] ]);
                                        $department_teachers = $db->in_array("SELECT * FROM department_teachers WHERE teacher_id = ?", [ $teacher["id"] ]);
                                        ?>
                                        
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2"><?=$teacher["id"]?></td>
                                            <td id="teacher_image" class="py-2"><img src="<?=$image["file_folder"]?>" width="125px"></td>
                                            <td class="py-2">
                                                <a href="/viewTeacher/?id=<?=$teacher["id"]?>" ><?=($teacher["last_name"] . " " . $teacher["first_name"] . " " . $teacher["father_first_name"])?></a>
                                            </td>
                                            <td id="phone_number" class="py-2"><?=$teacher["phone_1"]?></td>
                                            <td class="py-2">
                                                <? 
                                                    foreach($department_teachers as $department_teacher) { 
                                                        $department_teachers_name = $db->assoc("SELECT * FROM departments WHERE id = ?", [ $department_teacher["department_id"] ]);
                                                        echo $department_teachers_name["name"] . " ";   
                                                    } 
                                                ?>
                                            </td>
                                            <? if ($_GET["export"] == "excel") { ?>
                                                <td class="py-2" id="phone_number"><?=$teacher["login"]?></td>
                                                <td class="py-2"><?=decode($teacher["password_encrypted"])?></td>
                                            <? } ?>
                                            <td class="py-2"><?=$teacher["created_date"]?></td>
                                            <td class="py-2 text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                        <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right border py-0">
                                                        <div class="py-2">
                                                            <a class="dropdown-item"  href="/viewTeacher/?id=<?=$teacher["id"]?>">O'qituvchini malumotlarini ko'rish</a>
                                                            <a class="dropdown-item"  href="/editTeacher/?id=<?=$teacher["id"]?>&page=<?=$page?>">Tahrirlash</a>
                                                            <!-- <a class="dropdown-item"  href="/addPayment/?id=<?=$teacher["id"]?>">To'lov qo'shish</a> -->
                                                            <a class="dropdown-item text-danger" href="/editTeacher/?id=<?=$teacher["id"]?>&type=deleteteacher">O'chirish</a>
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

<script>

    $("#exportToExcel").on("click", function(){
        var q = $( "#filter" ).serialize();
        var url = '/<?=$url[0]?>?' + q + "&page_count=1000000&export=excel";

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
                $(this).find("#teacher_image").remove();
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