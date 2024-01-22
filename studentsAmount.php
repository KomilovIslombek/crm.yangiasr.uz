<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$directions = $db->in_array("SELECT * FROM directions");

$learn_types = [];
$learn_types_arr = $db->in_array("SELECT * FROM learn_types");
foreach ($learn_types_arr as $learn_type_arr) {
    $learn_types[$learn_type_arr["id"]] = $learn_type_arr;
}

include "system/head.php";

$breadcump_title_1 = "Statistika";
$breadcump_title_2 = "Talabalar soni";
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
                            <table class="table table-responsive-md mb-0 table-bordered text-center" id="table">
                                <thead>
                                    <tr>
                                        <th rowspan="2" style="vertical-align:middle;">yo'nalish</th>
                                        <th colspan="2">Jinsi</th>
                                        <!-- <th rowspan="2" style="vertical-align:middle;">Qarzdorlar soni</th> -->
                                        <th rowspan="2" style="vertical-align:middle;">Jami</th>
                                    </tr>

                                    <tr>
                                        <th>Erkak</th>
                                        <th>Ayol</th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <?
                                    $jami_erkaklar_soni = 0;
                                    $jami_ayollar_soni = 0;
                                    // $jami_qarzdorlar_soni = 0;
                                    $jami_talabalar_soni = 0;
                                    ?>
                                    <? foreach ($directions as $direction){ ?>
                                            <?
                                            $mans_count = 0;
                                            $womans_count = 0;
                                            $students_count = 0;
                                            // $qarzdorlar_soni = 0;

                                            $students = $db->in_array("SELECT * FROM students WHERE direction_id = ? AND status = 1", [ $direction["id"] ]);

                                            foreach ($students as $student) {
                                                if ($student["sex"] == "erkak") {
                                                    $mans_count++;
                                                    $jami_erkaklar_soni++;
                                                }
                                                if ($student["sex"] == "ayol") {
                                                    $womans_count++;
                                                    $jami_ayollar_soni++;
                                                }
                                                $students_count++;
                                                $jami_talabalar_soni++;

                                                // $learn_type = $learn_types[$student["learn_type_id"]];

                                                // if ($learn_type["name"] == "Kunduzgi") {
                                                //     $kontrakt_summasi = $direction["kunduzgi_narx"];
                                                // } else if ($learn_type["name"] == "Kechki") {
                                                //     $kontrakt_summasi = $direction["kechki_narx"];
                                                // } else if ($learn_type["name"] == "Sirtqi") {
                                                //     $kontrakt_summasi = $direction["sirtqi_narx"];
                                                // }

                                                // $privilege_percent = $student["privilege_percent"];

                                                // $tolangan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE code = ?", [
                                                //     $student["code"]
                                                // ])["SUM(amount)"];
                                                
                                                // $imtiyoz_summasi = $privilege_percent * ($kontrakt_summasi / 100);

                                                // $qarzdorlik = $kontrakt_summasi - $tolangan_summa - $imtiyoz_summasi;

                                                // if ($qarzdorlik > 0) {
                                                //     $qarzdorlar_soni++;
                                                //     $jami_qarzdorlar_soni++;
                                                // }
                                            }
                                            ?>

                                            <tr class="btn-reveal-trigger">
                                                <td class="py-2"><?=$direction["name"]?></td>
                                                <td class="py-2"><?=$mans_count?></td>
                                                <td class="py-2"><?=$womans_count?></td>
                                                <!-- <td class="py-2"><?=$qarzdorlar_soni?></td> -->
                                                <td class="py-2"><?=$students_count?></td>
                                            </tr>
                                        <? } ?>

                                        <tr class="btn-reveal-trigger">
                                            <th class="py-2">Jami</th>
                                            <th class="py-2"><?=$jami_erkaklar_soni?></th>
                                            <th class="py-2"><?=$jami_ayollar_soni?></th>
                                            <!-- <td class="py-2"><?=$jami_qarzdorlar_soni?></td> -->
                                            <th class="py-2"><?=$jami_talabalar_soni?></th>
                                        </tr>
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
        tableToExcel(
            $("#table").prop("innerHTML")
        );
    });
</script>

<?
include "system/end.php";
?>