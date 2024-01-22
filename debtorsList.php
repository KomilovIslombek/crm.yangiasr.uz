<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_GET['page'];
$code = (int)$_REQUEST['code'];
if (empty($page)) $page = 1;

$query = "";
$queryCourse = "";

if (!empty($_GET["regtype"])) {
    $query .= " AND reg_type = '" . $_GET["regtype"] . "'";
}

if (!empty($_GET["year_id"])) {
    $query .= " AND year_of_admission = '" . $_GET["year_id"] . "'";
}

if (!empty($_GET["season"])) {
    $query .= " AND season = '" . $_GET["season"] . "'";
}

if (!empty($_GET["course_id"])) {
    $query .= " AND NOT course_id < " . (int)$_GET["course_id"] . "";

    // $queryCourse .= " AND course_id = '" . $_GET["course_id"] . "'";
}
// $courseId = $_GET["course_id"];
// if($courseId == 1) $courseId = '';
$courseId = $_GET["payment_course_id"];
if($courseId == 1) $courseId = ''; // imtiyoz uchun

if (empty($_GET["payment_course_id"])) {
    $_GET["payment_course_id"] = '1';
}

$queryCourse .= " AND course_id = '" . $_GET["payment_course_id"] . "'"; // payments

$page_count = 100;
$page_end = $page * $page_count;
$page_start = $page_end - $page_count;

$direction_learn_types = $db->in_array("SELECT * FROM direction_learn_types ORDER BY direction_id");

$learn_types = $db->in_array("SELECT * FROM learn_types");
$directions = $db->in_array("SELECT * FROM directions");

include "system/head.php";

$breadcump_title_1 = "Statistika";
$breadcump_title_2 = "Qarzdorlik ro'yxati";
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

        <!-- Filter -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="/<?=$url[0]?>" method="GET" id="filter">
                            <div class="basic-form row d-flex align-items-center">
                                
                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Qabul yili</label>
                                    <select name="year_id" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($years as $year) { 
                                            if(mb_strlen($year) > 3) {
                                        ?>
                                            <option value="<?=$year?>" <?=($year == $_GET["year_id"] ? 'selected=""' : '')?> ><?=$year. ' - '. ($year + 1)?></option>
                                        <? } 
                                          } 
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Kurslar</label>
                                    <select name="course_id" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($coursesArr as $course_id => $value) { ?>
                                            <option value="<?=$course_id?>" <?=($course_id == $_GET["course_id"] ? 'selected=""' : '')?> ><?=$value?></option>
                                        <? } ?>
                                    </select>
                                </div>

                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Kursga to'lov</label>

                                    <select name="payment_course_id" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach ($coursesArr as $payment_course_id => $value) { ?>
                                            <option value="<?=$payment_course_id?>" <?=($payment_course_id == $_GET["payment_course_id"] ? 'selected=""' : '')?> ><?=$value?></option>
                                        <? } ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>Fasllar</label>
                                    <select name="season" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>
                                        <? foreach (["yozgi", "qishki"] as $season) { ?>
                                            <option value="<?=$season?>" <?=($season == $_GET["season"] ? 'selected=""' : '')?> ><?=$season?></option>
                                        <? } ?>
                                    </select>
                                </div>

                                <div class="form-group col-xl-3 col-lg-3 col-sm-6 col-12">
                                    <label>O'qish turi</label>
                                    <select name="regtype" class="form-control default-select form-control-lg">
                                        <option value="">Barchasi</option>

                                        <option value="oddiy" <?=$_GET["regtype"] == "oddiy" ? 'selected=""' : ''?>>Oddiy</option>
                                        <option value="oqishni-kochirish" <?=$_GET["regtype"] == "oqishni-kochirish" ? 'selected=""' : ''?>>O'qishni kochirish</option>
                                        <option value="ikkinchi-mutaxassislik" <?=$_GET["regtype"] == "ikkinchi-mutaxassislik" ? 'selected=""' : ''?>>Ikkinchi mutaxassislik</option>
                                    </select>
                                </div>
                                
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
                        <div style="min-height: 300px;" class="table-responsive">
                            <table class="table mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Ta'lim Yonalishi</th>
                                        <th>Ta'lim Shakli</th>
                                        <th>Talabar soni</th>
                                        <th>Kontrakt summasi</th>
                                        <th>To'lov Miqdori</th>
                                        <th>Imtiyoz Summasi</th>
                                        <th>To'landi</th>
                                        <th>Qaytarilgan summa</th>
                                        <th>Qarzdorlik</th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <?
                                    $count = 0;
                                    $talaba_soni_umumiy = 0;
                                    $kontrakt_summasi_umumiy = 0;
                                    $tolov_miqdori_umumiy = 0;
                                    $imtiyoz_summasi_umumiy = 0;
                                    $tolangan_summa_umumiy = 0;
                                    $qarzdorlik_umumiy = 0;
                                    $qaytarilgan_summa_umumiy = 0;
                                    $qolgan_summa_umumiy = 0;

                                    foreach ($directions as $direction) {
                                        foreach ($learn_types as $learn_type) {
                                            
                                            // $students = $db->in_array("SELECT * FROM students WHERE direction_id = ? AND learn_type_id = ? AND status = 1", [
                                            $students = $db->in_array("SELECT * FROM students WHERE 1=1$query AND direction_id = ? AND learn_type_id = ?", [
                                                $direction["id"],
                                                $learn_type["id"],
                                            ]);
                                            if (count($students) == 0) continue;
                                            
                                            $count += 1;
                                            $talaba_soni_learn_type = 0;
                                            $imtiyoz_summasi_learn_type = 0;
                                            $tolangan_summa_learn_type = 0;
                                            $qaytarilgan_summa_learn_type = 0;
                                            $qarzdorlik_learn_type = 0;
                                            $tolov_miqdori_learn_type = 0;

                                            foreach ($students as $student) {
                                                // $have_been = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1$queryCourse AND amount > 0 AND code = ?", [
                                                //     $student["code"]
                                                // ])["SUM(amount)"];
                                                
                                                // if(!$have_been) continue;

                                                // if(!empty($_GET["course_id"]) && !$have_been) continue;
                                                $student["tolangan_summa"] = $tolangan_summa;
                                                $talaba_soni = 1;
                                                $talaba_soni_umumiy += $talaba_soni;
                                                $talaba_soni_learn_type += $talaba_soni;
                                                
                                                if ($learn_type["name"] == "Kunduzgi") {
                                                    $kontrakt_summasi = $direction["kunduzgi_narx"];
                                                } else if ($learn_type["name"] == "Kechki") {
                                                    $kontrakt_summasi = $direction["kechki_narx"];
                                                } else if ($learn_type["name"] == "Sirtqi") {
                                                    $kontrakt_summasi = $direction["sirtqi_narx"];
                                                }

                                                if ($student["annual_contract_amount$courseId"] > 0) {
                                                    $kontrakt_summasi = $student["annual_contract_amount$courseId"];
                                                }

                                                if ($courseId > $student["course_id"]) {
                                                    $kontrakt_summasi = 0;
                                                }
                                                
                                                $kontrakt_summasi2 = $kontrakt_summasi;


                                                $imtiyoz_summasi = 0;
                                                // $privilege_percent = $student["privilege_percent$courseId"];
                                                // $imtiyoz_summasi = $privilege_percent * ($kontrakt_summasi / 100);
                                                $imtiyoz_summasi = $student["privilege_amount$courseId"];
                                                
                                                
                                                $tolangan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1$queryCourse AND amount > 0 AND code = ?", [
                                                    $student["code"]
                                                ])["SUM(amount)"];
                                                $student["tolangan_summa"] = $tolangan_summa;
                                            
                                                $qaytarilgan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1$queryCourse AND amount < 0 AND code = ?", [
                                                    $student["code"]
                                                ])["SUM(amount)"];
                                                $qaytarilgan_summa = abs($qaytarilgan_summa);

                                                $tolangan_summa = $student["tolangan_summa"];
                                                $qolgan_summa = $tolangan_summa - $qaytarilgan_summa;
                                                

                                                if ($tolangan_summa == $qaytarilgan_summa && $student["status"] == 0) {
                                                    $qarzdorlik = 0;
                                                    $kontrakt_summasi = 0;
                                                } else {
                                                    $qarzdorlik = $kontrakt_summasi - $imtiyoz_summasi - ($tolangan_summa - $qaytarilgan_summa);
                                                    if ($qarzdorlik == $kontrakt_summasi && $student["status"] == 0) {
                                                        $qarzdorlik = 0;
                                                        $kontrakt_summasi = 0;
                                                    }
                                                }

                                                $tolangan_summa2 = $tolangan_summa;
                                                $imtiyoz_summasi2 = $imtiyoz_summasi;
                                                $qaytarilgan_summa2 = $qaytarilgan_summa;
                                                $qarzdorlik2 = $qarzdorlik;
                                                if ($qarzdorlik < 0 && $student["status"] == 0) {
                                                    $qarzdorlik = 0;
                                                    $kontrakt_summasi = 0;
                                                    $tolangan_summa = 0;
                                                    $qaytarilgan_summa = 0;
                                                    $imtiyoz_summasi = 0;
                                                }
                                                $tolov_miqdori = $talaba_soni * $kontrakt_summasi;
                                                $tolov_miqdori_learn_type += $kontrakt_summasi;
                                                
                                                // umumiylar start
                                                
                                                $kontrakt_summasi_umumiy += $kontrakt_summasi;
                                                $imtiyoz_summasi_umumiy += $imtiyoz_summasi;
                                                $imtiyoz_summasi_learn_type += $imtiyoz_summasi;

                                                $tolangan_summa_umumiy += $tolangan_summa;
                                                $tolangan_summa_learn_type += $tolangan_summa;

                                                $qaytarilgan_summa_umumiy += $qaytarilgan_summa;
                                                $qaytarilgan_summa_learn_type += $qaytarilgan_summa;
                                                
                                                $qolgan_summa_umumiy += $qolgan_summa;
                                                // umumiylar end

                                                $qarzdorlik_umumiy += $qarzdorlik;
                                                $qarzdorlik_learn_type += $qarzdorlik;
                                                // $testi += $qarzdorlik;
                                                $qaytarilgan_summa2 = abs($qaytarilgan_summa2);
                                                // $qarzdorlik = $talaba_soni * $kontrakt_summasi - $imtiyoz_summasi - ($tolangan_summa - $qaytarilgan_summa);
                                                // if ($qarzdorlik == $kontrakt_summasi && $student["status"] == 0) $qarzdorlik = 0;
                                            }
                                            if($tolov_miqdori_learn_type > 0) $tolov_miqdori_umumiy += $tolov_miqdori_learn_type;
                                            // $testi += $qarzdorlik_learn_type;
                                            // $testi2 += $qaytarilgan_summa_learn_type;
                                            // $testi3 += $tolangan_summa_learn_type;
                                            // $testi4 += $imtiyoz_summasi_learn_type;
                                            ?>
                                            <tr class="btn-reveal-trigger">
                                                <td class="py-2"><?=$count?></td>
                                                <td class="py-2">
                                                    <?=$direction["id"]?>) <?=$direction["short_name"]?>
                                                </td>
                                                <td class="py-2">
                                                    <?=$learn_type["name"]?>
                                                </td>
                                                <td class="p-2"><?=$talaba_soni_learn_type?></td>
                                                <td class="p-2"><?=($_GET["export"] == "excel" ? ($kontrakt_summasi2) : number_format($kontrakt_summasi2))?></td>
                                                <td class="py-2"><?=($_GET["export"] == "excel" ? ($tolov_miqdori_learn_type) : number_format($tolov_miqdori_learn_type))?></td>
                                                <td class="py-2"><?=($_GET["export"] == "excel" ? ($imtiyoz_summasi_learn_type) : number_format($imtiyoz_summasi_learn_type))?></td>
                                                <td class="py-2"><?=($_GET["export"] == "excel" ? ($tolangan_summa_learn_type) : number_format($tolangan_summa_learn_type) )?></td>
                                                <td class="py-2"><?=($_GET["export"] == "excel" ? abs($qaytarilgan_summa_learn_type) : number_format(abs($qaytarilgan_summa_learn_type)))?></td>
                                                <td class="py-2"><?=($_GET["export"] == "excel" ? ($qarzdorlik_learn_type) : number_format($qarzdorlik_learn_type))?></td>
                                            </tr>
                                            <?
                                        }
                                    }
                                    ?>

                                    <tr class="btn-reveal-trigger">
                                        <th class="py-2">Jami:</th>
                                        <th class="py-2"></th>
                                        <th class="py-2"></th>
                                        <th class="p-2"><?=$talaba_soni_umumiy?></th>
                                        <th class="p-2">0</th>
                                        <th class="py-2"><?=($_GET["export"] == "excel" ? ($tolov_miqdori_umumiy) : number_format($tolov_miqdori_umumiy))?></th>
                                        <th class="py-2"><?=($_GET["export"] == "excel" ? ($imtiyoz_summasi_umumiy) :  number_format($imtiyoz_summasi_umumiy))?></th>
                                        <th class="py-2"><?=($_GET["export"] == "excel" ? ($tolangan_summa_umumiy) :  number_format($tolangan_summa_umumiy))?></th>
                                        <th class="py-2"><?=($_GET["export"] == "excel" ? abs($qaytarilgan_summa_umumiy) :  number_format(abs($qaytarilgan_summa_umumiy)))?></th>
                                        <th class="py-2"><?=($_GET["export"] == "excel" ? ($qarzdorlik_umumiy) :  number_format($qarzdorlik_umumiy))?></th>
                                    </tr>

                                    <tr>
                                        <th>№</th>
                                        <th>Ta'lim Yonalishi</th>
                                        <th>Ta'lim Shakli</th>
                                        <th>Talabar soni</th>
                                        <th>Kontrakt summasi</th>
                                        <th>To'lov Miqdori</th>
                                        <th>Imtiyoz Summasi</th>
                                        <th>To'landi</th>
                                        <th>Qaytarilgan summa</th>
                                        <th>Qarzdorlik</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?
                        include "modules/pagination.php";

                        $count = (int)$db->assoc("SELECT COUNT(*) FROM direction_learn_types")["COUNT(*)"];

                        // $count = (int)$db->assoc("SELECT COUNT(*) FROM users WHERE role = 'student'")["COUNT(*)"];
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