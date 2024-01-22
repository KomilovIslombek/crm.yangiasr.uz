<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

if ($_REQUEST["type"] == "restore") {
    $id = $_REQUEST["id"];
    $deleted = $db->assoc("SELECT * FROM db_deleted WHERE id = ?", [ $id ]);
    
    if (!empty($deleted["id"])) {
        $table_name = $deleted["table_name"];
        $table_datas = json_decode($deleted["table_data"], true);
        
        if (count($table_datas) > 0) {
            foreach ($table_datas as $table_data) {
                $db->insert($table_name, $table_data);
            }

            $db->delete("db_deleted", $deleted["id"]);
            // $db->update("db_deleted", [
            //     "restored" => 1
            // ], [
            //     "id" => $deleted["id"]
            // ]);

            header("Location: /archiveStudentsList/?page=$page");
        } else {
            exit("Ma'lumotlar yo'q");
        }
    } else {
        exit("Bunday ma'lumot topilmadi tiklangan");
    }
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

$sql = "SELECT * FROM db_deleted WHERE table_name = 'students' ORDER BY id ASC";

$count = $db->assoc("SELECT COUNT(*) FROM db_deleted WHERE table_name = 'students'")["COUNT(*)"];

$sql .= " LIMIT $page_start, $page_count";
$students = $db->in_array($sql);

include "system/head.php";

$breadcump_title_1 = "Talabalar";
$breadcump_title_2 = "Talabalar ro'yxati ($count ta)";

$directions_arr = $db->in_array("SELECT * FROM directions");
$directions = [];
foreach ($directions_arr as $direction_arr) {
    $directions[$direction_arr["id"]] = $direction_arr;
}

$learn_types_arr = $db->in_array("SELECT * FROM learn_types");
$learn_types = [];
foreach ($learn_types_arr as $learn_type_arr) {
    $learn_types[$learn_type_arr["id"]] = $learn_type_arr;
}

$regions_arr = $db->in_array("SELECT * FROM regions");
$regions = [];
foreach ($regions_arr as $region_arr) {
    $regions[$region_arr["id"]] = $region_arr;
}

$payment_methods = [
    "monthly" => "oyma oy",
    "half" => "yarim",
    "full" => "to'liq"
];

$groups = [];
$groups_list = $db->in_array("SELECT DISTINCT(group_id) FROM students WHERE group_id IS NOT NULL$g_query AND status = 1");
foreach ($groups_list as $group_list) {
    $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $group_list["group_id"] ]);
    if (!empty($group["id"])) {
        array_push($groups, $group);
    }
}

$_REQUEST["export"] = "excel";
?>

<style>
    .ms-num {
        mso-number-format:General;
    }
    .ms-text{
        mso-number-format:"\@";/*force text*/
    }
</style>

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
        <div class="modal fade" id="exportModal" style="display: none;" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <input type="checkbox" class="form-check-input" id="select_all" required="">
                            <label class="form-check-label" for="select_all">Barchasini tanlash</label>
                        </h5>
                        <button type="button" class="close" data-bs-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="col-xl-4 col-xxl-6 col-6">
                                <div class="form-check custom-checkbox mb-3">
                                    <input type="checkbox" class="form-check-input" id="check_code" required="">
                                    <label class="form-check-label" for="check_code">code</label>
                                </div>
                            </div>
                            <div class="w-100">
                                <div class="form-check custom-checkbox mb-3">
                                    <input type="checkbox" class="form-check-input" id="check_fish" required="">
                                    <label class="form-check-label" for="check_fish">F.I.SH</label>
                                </div>
                            </div>
                            <div class="w-100">
                                <div class="form-check custom-checkbox mb-3">
                                    <input type="checkbox" class="form-check-input" id="check_phone" required="">
                                    <label class="form-check-label" for="check_phone">Telefon raqami</label>
                                </div>
                            </div>
                            <div class="w-100">
                                <div class="form-check custom-checkbox mb-3">
                                    <input type="checkbox" class="form-check-input" id="check_payments" required="">
                                    <label class="form-check-label" for="check_payments">To'lovlari</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-primary">Keyingi Modal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div style="min-height: 300px;" class="table-responsive">
                            <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>code</th>
                                        <th>Tiklash</th>
                                        <? if ($_REQUEST["export"] == "excel") { ?>
                                            <th>Mavsumi</th>
                                            <th>Ism</th>
                                            <th>Familiya</th>
                                            <th>Otasining ismi</th>
                                            <th>Tug'ilgan sanasi</th>
                                            <th>Jinsi</th>
                                            <th>Ta'lim yo'nalishi</th>
                                            <th>Ta'lim shakli</th>
                                            <th>Telefon raqami</th>
                                            <th>Qo'shimcha telefon raqami</th>
                                            <th>Viloyati</th>
                                            <th>Manzili</th>
                                            <th>Fuqaroligi</th>
                                            <th>Statusi</th>
                                            <th>Shartnoma raqami</th>
                                            <th>Shartnoma summasi (1-yil uchun)</th>
                                            <th>To'lov uslubi</th>
                                            <th>Passport raqamlari</th>
                                            <th>PINFL</th>
                                            <th>Ustozi</th>
                                            <th>Guruhi</th>
                                            <th>O'qish turi</th>
                                            <th></th>
                                        <? } else { ?>
                                            <th>F.I.SH</th>
                                            <th>Mavsumi</th>
                                            <th>Telefon raqami</th>
                                            <th>To'lovlari</th>
                                            <th>Tug'ilgan sanasi</th>
                                            <th>Guruhi</th>
                                            <!-- <th></th> -->
                                        <? } ?>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($students as $student){ ?>
                                        <?
                                        $studentArr = json_decode($student["table_data"], true);
                                        $student = $studentArr[0];

                                        $teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $student["teacher_id"] ]);

                                        $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $student["group_id"] ]);
                                        ?>
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2 bg-<?=$student["status"] == 0 ? 'danger' : 'success'?>"><?=$student["code"]?></td>
                                            <td class="py-2 text-end">
                                                <a class="btn btn-success text-white btn-sm" href="/admin/archiveStudentsList/?id=<?=$student["id"]?>&type=restore&page=<?=$page?>">Tiklash</a>
                                            </td>
                                            
                                            <? if ($_REQUEST["export"] == "excel") { ?>
                                                <td class="py-2"><?=$student["season"]?></td>
                                                <td class="py-2"><?=$student["first_name"]?></td>
                                                <td class="py-2"><?=$student["last_name"]?></td>
                                                <td class="py-2"><?=$student["father_first_name"]?></td>
                                                <td class="py-2"><?=$student["birth_date"]?></td>
                                                <td class="py-2"><?=$student["sex"]?></td>
                                                <td class="py-2"><?=$directions[$student["direction_id"]]["name"]?></td>
                                                <td class="py-2"><?=$learn_types[$student["learn_type_id"]]["name"]?></td>
                                                <td class="py-2"><?=str_replace("+998", "(998)", $student["phone_1"])?></td>
                                                <td class="py-2"><?=str_replace("+998", "(998)", $student["phone_2"])?></td>
                                                <td class="py-2"><?=$regions[$student["region_id"]]["name"]?></td>
                                                <td class="py-2"><?=$student["address"]?></td>
                                                <td class="py-2"><?=$student["nation"]?></td>
                                                <td class="py-2"><?=($student["status"] == 1 ? "O'qiyapti" : "O'qimayapti")?></td>
                                                <td class="py-2"><?=$student["contract_id"]?></td>
                                                <td class="py-2"><?=number_format($student["annual_contract_amount"])?></td>
                                                <td class="py-2"><?=$payment_methods[$student["payment_method"]]?></td>
                                                <td class="py-2"><?=$student["passport_serial_number"]?></td>
                                                <td class="py-2 ms-text">
                                                    PINFIL: <?=$student["pinfl"]?>
                                                </td>
                                                <td class="py-2"><?=$teacher["first_name"] . " " . $teacher["last_name"]?></td>
                                                <td class="py-2"><?=(!empty($group["id"]) ? $group["name"] : " biriktirilmagan")?></td>
                                                <td class="py-2"><?=$student["reg_type"]?></td>
                                                <td></td>
                                            <? } else { ?>
                                                <td class="py-2">
                                                    <a href="/viewStudent/?id=<?=$student["id"]?>">
                                                        <?=($student["last_name"] . " " . $student["first_name"] . " " . $student["father_first_name"])?>
                                                    </a>
                                                </td>
                                                <td class="py-2"><?=$student["season"]?></td>
                                                <td class="py-2"><?=$student["phone_1"]?></td>
                                                <td class="py-2"><a href="/paymentsList?code=<?=$student["code"]?>&page=1" class="btn btn-sm btn-primary">To'lovlari </a></td>
                                                <td class="py-2"><?=$student["birth_date"]?></td>
                                                <td class="py-2"><?=(!empty($group["id"]) ? $group["name"] : " biriktirilmagan")?></td>
                                            <? } ?>
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
            $(table).find("tbody").find("tr").each(function(){
                $(this).find("td").last().remove();
            });

            tableToExcel(
              $(table).prop("innerHTML")  
            );
        });
    });

    // $("#filter").find("select").on("change", function(){
    //     updateTable();
    // });

    // function updateTable() {
    //     var q = $( "#filter" ).serialize();
    //     var url = '/<?=$url[0]?>?' + q;
    //     $.ajax({
    //         url: url,
    //         type: "GET",
    //         dataType: "html",
    //         success: function(data) {
    //             window.history.pushState($(data).find("title").text(), "Title", url);
    //             // console.log(data);
    //             $("#table").html($(data).find("#table").html());
    //             $("#pagination-wrapper").html($(data).find("#pagination-wrapper").html());
    //         }
    //     })
    // }

    // $("#input-search").on("input", function(){
    //     updateTable();
    // });

    // $("#select_all").on("click", function(){
    //     $("#exportModal").find(".modal-body").find("*[type='checkbox']").prop("checked", $(this).prop("checked"));
    // });


    // exportModal
</script>

<?
include "system/end.php";
?>