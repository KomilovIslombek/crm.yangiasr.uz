<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$student = $db->assoc("SELECT * FROM students WHERE code = ? AND status = 1", [ $systemUser["login"] ] );
$learn_type = $db->in_array("SELECT * FROM learn_types WHERE id = ?", [ $student["learn_type_id"] ]);
$direction = $db->in_array("SELECT * FROM directions WHERE id = ?", [ $student["direction_id"] ]);
$moodle_student = $db4->assoc("SELECT * FROM user WHERE username = ?", [ $student["code"] ]);

$lesson_visits = $db->in_array("SELECT * FROM lessons_visits WHERE student_id = ? AND status = ?", [ $moodle_student["id"], "kelmadi" ]);
$count = 0;
$tolangan_summa_umumiy = 0;
$imtiyoz_summasi = 0;

foreach ($db->in_array("SELECT * FROM payments WHERE code = ?", [ $systemUser["login"] ]) as $payment_piece) {
    $tolangan_summa_umumiy += $payment_piece["amount"];
}
            
    if ($learn_type["name"] == "Kunduzgi") {
        $kontrakt_summasi = $direction["kunduzgi_narx"];
    } else if ($learn_type["name"] == "Kechki") {
        $kontrakt_summasi = $direction["kechki_narx"];
    } else if ($learn_type["name"] == "Sirtqi") {
        $kontrakt_summasi = $direction["sirtqi_narx"];
    }

    if ($student["annual_contract_amount"] > 0) {
        $kontrakt_summasi = $student["annual_contract_amount"];
    }

    $privilege_percent = $student["privilege_percent"];

    $imtiyoz_summasi = $privilege_percent * ($kontrakt_summasi / 100);
    
    $tolangan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE amount > 0 AND code = ?", [
        $student["code"]
    ])["SUM(amount)"];
    $student["tolangan_summa"] = $tolangan_summa;

    $qaytarilgan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE amount < 0 AND code = ?", [
        $student["code"]
    ])["SUM(amount)"];
    $student["qaytarilgan_summa"] = $qaytarilgan_summa;

    $tolangan_summa = $student["tolangan_summa"];

    $qaytarilgan_summa = abs($student["qaytarilgan_summa"]);

    $qolgan_summa = $tolangan_summa - $qaytarilgan_summa;

    $qarzdorlik = $kontrakt_summasi - $imtiyoz_summasi - $tolangan_summa;
    
    $first_character = mb_substr($qarzdorlik, 0, 1);
    if ($first_character == '-') {
        $qarzdorlik = 0;
    } 
    
    // $qarzdorlik = $kontrakt_summasi - $imtiyoz_summasi - ($tolangan_summa - $qaytarilgan_summa);
    if ($qarzdorlik == $kontrakt_summasi && $student["status"] == 0) $qarzdorlik = 0;


include "system/head.php";

$breadcump_title_1 = "Shaxsiy kabinet";
$breadcump_title_2 = "kabinet";
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
        <div class="row">
            <div class="col-xl-3 float-right">
                <div class="card fun">
                    <div class="card-body">
                        <div class="media align-items-center">
                            <div class="media-body me-3">
                                <h4 style="font-size: 30px;" class="num-text text-black font-w600"><?=number_format($tolangan_summa_umumiy)?></h4>
                                <span class="fs-14 text-muted">Umumiy to'langan summa</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 float-right">
                <div class="card fun">
                    <div class="card-body">
                        <div class="media align-items-center">
                            <div class="media-body me-3">
                                <h4 style="font-size: 30px;" class="num-text text-black font-w600"><?=number_format($qarzdorlik)?></h4>
                                <span class="fs-14 text-muted">Qarzdorlik summasi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 float-right">
                <div class="card fun">
                    <div class="card-body">
                        <div class="media align-items-center">
                            <div class="media-body me-3">
                                <h4 style="font-size: 30px;" class="num-text text-black font-w600"><?=number_format($imtiyoz_summasi)?></h4>
                                <span class="fs-14 text-muted">imtiyoz summasi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 float-right">
                <div class="card fun">
                    <div class="card-body">
                        <div class="media align-items-center">
                            <div class="media-body me-3">
                                <h4 style="font-size: 30px;" class="num-text text-black font-w600"><?=count($lesson_visits)?></h4>
                                <span class="fs-14 text-muted">Qoldirilgan darslar so'ni </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
        <!-- Student  -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-responsive-md mb-0 table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sanasi</th>
                                        <th>Miqdori</th>
                                        <th>To'lov uslubi</th>
                                        <th>Ta'lim yo'nalish</th>
                                        <th>Ta'lim shakli</th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($db->in_array("SELECT * FROM payments WHERE code = ?", [ $systemUser["login"] ]) as $payment){ ?>
                                        <?
                                            $payment_method = $db->assoc("SELECT * FROM payment_methods WHERE id = ?", [ $payment["payment_method_id"] ]);
                                            $direction = $db->assoc("SELECT * FROM directions WHERE id = ?", [ $payment["direction_id"] ]);
                                            $learn_type = $db->assoc("SELECT * FROM learn_types WHERE id = ?", [ $payment["learn_type_id"] ]);
                                        ?>
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2"><?=$payment["payment_date"]?></td>
                                            <td class="py-2"><?=number_format($payment["amount"]). ' so\'m'?></td>
                                            <td class="py-2"><?=$payment_method["name"]?></td>
                                            <td class="py-2"><?=$direction["name"]?></td>
                                            <td class="py-2"><?=$learn_type["name"]?></td>
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

include "system/end.php";
?>