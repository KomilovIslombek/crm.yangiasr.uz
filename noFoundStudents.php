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

$crm_students = $db->in_array("SELECT * FROM students");
$edu_students = [];
$students = [];

$crm_students = $db->in_array("SELECT * FROM students WHERE email_setted = 0");


// foreach ($crm_students as $crm_student) {
//     $edu_student = $db4->assoc("SELECT * FROM user WHERE username = ?", [ $crm_student["code"] ]);
//     if($edu_student["id"]) {
//         array_push($edu_students, $edu_student);

//         $db->update("students", [
//             "email" => $edu_student["email"],
//             "email_setted" => 1
//         ], [
//             "code" => $crm_student["code"],
//             "email_setted" => 0
//         ]);
//     } else {
//         array_push($students, $crm_student);
//     }
// }


// foreach ($crm_students as $crm_student) {
//     $edu_student = $db4->assoc("SELECT * FROM user WHERE firstname = ? AND lastname = ?", [ $crm_student["first_name"], $crm_student["last_name"] ]);
//     if($edu_student["id"]) {
//         array_push($edu_students, $edu_student);
//         echo $edu_student["id"] . ") " . $edu_student["firstname"] . " " . $edu_student["lastname"] . "<br>";
//         echo $crm_student["code"] . ") " . $crm_student["first_name"] . " " . $crm_student["last_name"] . "<br><hr>";

//         $db->update("students", [
//             "email" => $edu_student["email"],
//             "email_setted" => 1
//         ], [
//             "code" => $crm_student["code"],
//             "email_setted" => 0
//         ]);
//     } else {
//         array_push($students, $crm_student);
//     }
// }

// foreach ($crm_students as $crm_student) {
//     $edu_student = $db4->assoc("SELECT * FROM user WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone1, '(', ''), ')', ''), '-', ''), ' ', ''), '+', '') = ? OR REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone1, '(', ''), ')', ''), '-', ''), ' ', ''), '+', '') = ? OR REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone2, '(', ''), ')', ''), '-', ''), ' ', ''), '+', '') = ? OR REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone2, '(', ''), ')', ''), '-', ''), ' ', ''), '+', '') = ?", [
//         strtr($crm_student["phone_1"], [
//             "+" => "",
//             "-" => ""
//         ]),
//         strtr($crm_student["phone_2"], [
//             "+" => "",
//             "-" => ""
//         ]),
//         strtr($crm_student["phone_1"], [
//             "+" => "",
//             "-" => ""
//         ]),
//         strtr($crm_student["phone_2"], [
//             "+" => "",
//             "-" => ""
//         ])
//     ]);
//     if($edu_student["id"]) {
//         array_push($edu_students, $edu_student);
//         echo $edu_student["id"] . ") " . $edu_student["firstname"] . " " . $edu_student["lastname"] . "<br>";
//         echo $crm_student["code"] . ") " . $crm_student["first_name"] . " " . $crm_student["last_name"] . "<br><hr>";

//         $db->update("students", [
//             "email" => $edu_student["email"],
//             "email_setted" => 1
//         ], [
//             "code" => $crm_student["code"],
//             "email_setted" => 0
//         ]);
//     } else {
//         array_push($students, $crm_student);
//     }
// }
exit;

include "system/head.php";

$breadcump_title_1 = "Talabalar";
$breadcump_title_2 = "Moodledan topilmagan talabalar ro'yxati (". count($students)." ta)";

$payment_methods = [
    "monthly" => "oyma oy",
    "half" => "yarim",
    "full" => "to'liq"
];
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

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div style="min-height: 300px;" class="table-responsive">
                            <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>code</th>
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
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($students as $student){ ?>
                                        <?
                                        $teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $student["teacher_id"] ]);

                                        $group_user = $db->assoc("SELECT * FROM group_users WHERE student_code = ?", [ $student["code"] ]);
                                        if (!empty($group_user["id"])) {
                                            $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $group_user["group_id"] ]);
                                        }
                                        ?>
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2 "><?=$student["code"]?></td>
                                            
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
                                                <td class="py-2"><?=(!empty($group["id"]) ? $group["name"] : $group_user["group_id"] . " o'chirilgan")?></td>
                                                <td></td>
                                        </tr>
                                    <? } ?>
                                </tbody>
                            </table>
                        </div>

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

</script>

<?
include "system/end.php";
?>