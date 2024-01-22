<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

include "system/head.php";

$breadcump_title_1 = "Statistika";
$breadcump_title_2 = "Qarzdorlik bo'yicha umumiy hisobot";
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
                                        <th>QABUL YILI</th>
                                        <th>KURS</th>
                                        <th>Talaba soni</th>
                                        <th>Kontrakt summasi</th>
                                        <th>Imtiyoz Summasi</th>
                                        <th>To'landi</th>
                                        <th>Qaytarilgan summa</th>
                                        <th>Qarzdorlik</th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <?
                                    $final_talaba_soni = 0;
                                    $final_talaba_soni_years_arr = [];
                                    $final_kontrakt_summasi = 0;
                                    $final_imtiyoz_summasi = 0;
                                    $final_tolangan_summasi = 0;
                                    $final_qaytarilgan_summasi = 0;
                                    $final_qarzdorlik_summasi = 0;

                                    foreach ($years as $year) {
                                        $max_course = $db->assoc("SELECT DISTINCT(course_id) FROM students WHERE year_of_admission = 2022 ORDER BY course_id DESC LIMIT 1")["course_id"];

                                        $coursesArr = range(1, $max_course);

                                        foreach ($coursesArr as $course_id) {
                                            $_GET["year_id"] = $year;
                                            $_GET["payment_course_id"] = $course_id;
                                            
                                            $query = "";
                                            $query2 = "";
                                            $g_query = "";
                                            $queryCourse = "";
                                            
                                            $studentsCodes = [];
                                            $codee = $_GET["code"];
                                            // Kontraktga to'lov qilgan va qilmaganlarni filterlash
                                            $learn_types_arr = $db->in_array("SELECT * FROM learn_types");
                                            $learn_types = [];
                                            foreach ($learn_types_arr as $learn_type) {
                                                $learn_types[$learn_type["id"]] = $learn_type;
                                            }
                                            
                                            $directions_arr = $db->in_array("SELECT * FROM directions");
                                            $directions = [];
                                            foreach ($directions_arr as $direction) {
                                                $directions[$direction["id"]] = $direction;
                                            }
                                            
                                            if (!empty($_GET["regtype"])) {
                                                $query .= " AND reg_type = '" . $_GET["regtype"] . "'";
                                                $g_query .= " AND reg_type = '" . $_GET["regtype"] . "'";
                                                $query2 .= " AND reg_type = '" . $_GET["regtype"] . "'";
                                            }
                                            
                                            if (!empty($_GET["direction_id"])) {
                                                $query .= " AND direction_id = " . $_GET["direction_id"];
                                                $g_query .= " AND direction_id = " . $_GET["direction_id"];
                                                $query2 .= " AND direction_id = " . $_GET["direction_id"];
                                            }
                                            
                                            if (!empty($_GET["learn_type_id"])) {
                                                $query .= " AND learn_type_id = '" . $_GET["learn_type_id"] . "'";
                                                $g_query .= " AND learn_type_id = '" . $_GET["learn_type_id"] . "'";
                                                $query2 .= " AND learn_type_id = '" . $_GET["learn_type_id"] . "'";
                                            }
                                            
                                            if (!empty($_GET["year_id"])) {
                                                $query .= " AND year_of_admission = '" . $_GET["year_id"] . "'";
                                                $g_query .= " AND year_of_admission = '" . $_GET["year_id"] . "'";
                                                $query2 .= " AND year_of_admission = '" . $_GET["year_id"] . "'";
                                            }
                                            
                                            if (!empty($_GET["season"])) {
                                                $query .= " AND season = '" . $_GET["season"] . "'";
                                                $g_query .= " AND season = '" . $_GET["season"] . "'";
                                                $query2 .= " AND season = '" . $_GET["season"] . "'";
                                            }
                                            
                                            if (!empty($_GET["code"])) {
                                                $query .= " AND code = '" . $_GET["code"] . "'";
                                            }
                                            
                                            if (!empty($_GET["group_id"])) {
                                                $query .= " AND group_id = " . $_GET["group_id"] . "";
                                                $query2 .= " AND group_id = " . $_GET["group_id"] . "";
                                            }
                                            
                                            if (!empty($_GET["course_id"])) {
                                                $query .= " AND course_id = " . (int)$_GET["course_id"] . ""; // students uchun
                                                $g_query .= " AND course_id = " . (int)$_GET["course_id"] . ""; // students uchun
                                                $query2 .= " AND course_id = " . (int)$_GET["course_id"] . ""; // students select uchun
                                            
                                                // if( $_GET["course_id"] > $student["course_id"]) continue;
                                                
                                                // $queryCourse .= " AND course_id = '" . $_GET["course_id"] . "'"; // payments
                                            }
                                            
                                            if (empty($_GET["payment_course_id"])) {
                                                $_GET["payment_course_id"] = '1';
                                            }
                                            
                                            $queryCourse .= " AND course_id = '" . $_GET["payment_course_id"] . "'"; // payments
                                            
                                            $courseId = $_GET["payment_course_id"];
                                            if($courseId == 1) $courseId = ''; // imtiyoz uchun
                                            // $startYear = "2022";
                                            // $futureDate = date('Y', strtotime('+1 year', strtotime($startYear)) );
                                            
                                            $sql = "SELECT * FROM students WHERE 1=1$query ORDER BY code ASC";
                                            // $sqlForSelect = "SELECT * FROM students WHERE 1=1$query2 ORDER BY last_name ASC";
                                            
                                            // $count = $db->assoc("SELECT COUNT(*) FROM students WHERE 1=1$query")["COUNT(*)"];
                                            
                                            $students = $db->in_array($sql);
                                            
                                            // $students3 = $db->in_array("SELECT * FROM students WHERE 1=1 ");
                                            
                                            // $all_groups = [];
                                            // $all_groups_arr = $db->in_array("SELECT * FROM groups_list");
                                            // foreach ($all_groups_arr as $all_group) {
                                            //     $all_groups[$all_group["id"]] = $all_group;
                                            // }
                                            
                                            // $groups = [];
                                            // $groups_list = $db->in_array("SELECT DISTINCT(group_id) FROM students WHERE group_id IS NOT NULL$g_query AND status = 1");
                                            // foreach ($groups_list as $group_list) {
                                            //     $group = $all_groups[$group_list["group_id"]];
                                            
                                            //     if (!empty($group["id"])) {
                                            //         array_push($groups, $group);
                                            //     }
                                            // }
                                            
                                            foreach ($students as $student_key => $student) {
                                                $tolangan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1$queryCourse AND amount > 0 AND code = ?", [
                                                    $student["code"]
                                                ])["SUM(amount)"];
                                                $student["tolangan_summa"] = $tolangan_summa;
                                                // if($student["tolangan_summa"]) $have_been = 'yes';
                                            
                                                $qaytarilgan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1$queryCourse AND amount < 0 AND code = ?", [
                                                    $student["code"]
                                                ])["SUM(amount)"];
                                                $student["qaytarilgan_summa"] = $qaytarilgan_summa;
                                            
                                                $students[$student_key] = $student;
                                            }
                                    

                                            // 

                                            $talaba_soni_umumiy = 0;
                                            $kontrakt_summasi_umumiy = 0;
                                            $tolov_miqdori_umumiy = 0;
                                            $imtiyoz_summasi_umumiy = 0;
                                            $tolangan_summa_umumiy = 0;
                                            $qarzdorlik_umumiy = 0;
                                            $qaytarilgan_summa_umumiy = 0;
                                            
                                            foreach ($students as $student) {
                                                $direction = $directions[$student["direction_id"]];
                                                $learn_type = $learn_types[$student["learn_type_id"]];
                                                
                                                // $count += 1;
                                                
                                                $talaba_soni = 1;
                                                
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
                                                    // echo "Kurs to'g'ri kelmadi kontrakt summasi 0 ga tenglandi ($courseId-kurs uchun): kontrakt_summasi: $kontrakt_summasi<hr>";
                                                    $kontrakt_summasi = 0;
                                                }

                                                $kontrakt_summasi2 = $kontrakt_summasi;
                                                $tolov_miqdori = $talaba_soni * $kontrakt_summasi;

                                                $imtiyoz_summasi = 0;
                                                $imtiyoz_summasi = $student["privilege_amount$courseId"];
                                                // if ($imtiyoz_summasi < 0) {
                                                //     echo "Imtiyoz summasi 0 dan kichik ($courseId-kurs uchun): $imtiyoz_summasi<hr>";
                                                // }
                                                

                                                $talaba_soni_umumiy += $talaba_soni;

                                                $tolangan_summa = $student["tolangan_summa"];
                                                // if ($tolangan_summa < 0) {
                                                //     echo "To'langan summa 0 dan kichik: $tolangan_summa<hr>";
                                                // }

                                                $qaytarilgan_summa = abs($student["qaytarilgan_summa"]);
                                                // if ($qaytarilgan_summa < 0) {
                                                //     echo "Qaytarilgan summa 0 dan kichik: $qaytarilgan_summa<hr>";
                                                // }
                                                
                                                if ($tolangan_summa == $qaytarilgan_summa && $student["status"] == 0) {
                                                    // if ($qarzdorlik != 0 || $kontrakt_summasi != 0) {
                                                    //     echo "Qarzdorlik va Kontakt summasi 0 ga teng qilindi<br>Qarzdorlik: $qarzdorlik<br>Kontakt summasi: $kontrakt_summasi<hr>";
                                                    // }
                                                    $qarzdorlik = 0;
                                                    $kontrakt_summasi = 0;
                                                } else {
                                                    $qarzdorlik = $kontrakt_summasi - $imtiyoz_summasi - ($tolangan_summa - $qaytarilgan_summa);
                                                    if ($qarzdorlik == $kontrakt_summasi && $student["status"] == 0) {
                                                        // if ($qarzdorlik != 0 || $kontrakt_summasi != 0) { // bu bo'yicha hech qanday holat yo'q
                                                        //     echo "qarzdorlik va Kontakt summasi 0 ga teng qilindi<br>Qarzdorlik: $qarzdorlik<br>Kontakt summasi: $kontrakt_summasi<hr>";
                                                        // }
                                                        $qarzdorlik = 0;
                                                        $kontrakt_summasi = 0;
                                                    }
                                                }

                                                // if ($qarzdorlik < 0 && $student["status"] == 0) {
                                                //     $qarzdorlik = 0;
                                                // }

                                                $tolangan_summa2 = $tolangan_summa;
                                                $imtiyoz_summasi2 = $imtiyoz_summasi;
                                                $qarzdorlik2 = $qarzdorlik;
                                                $qaytarilgan_summa2 = $qaytarilgan_summa;
                                                // if ($qarzdorlik < 0 && $student["status"] == 0) {
                                                //     $qarzdorlik = 0;
                                                //     $kontrakt_summasi = 0;
                                                    // $tolangan_summa = 0;
                                                    // $qaytarilgan_summa = 0;
                                                    // $imtiyoz_summasi = 0;
                                                // }
                                                // if ($qarzdorlik < 0) {
                                                //     echo("Bizni qarzdorlik ($qarzdorlik)<br>");
                                                // }
                                                
                                                

                                                $tolov_miqdori_umumiy += $tolov_miqdori;
                                                $imtiyoz_summasi_umumiy += $imtiyoz_summasi;
                                                $tolangan_summa_umumiy += $tolangan_summa;
                                                $qaytarilgan_summa_umumiy += $qaytarilgan_summa;
                                                $kontrakt_summasi_umumiy += $kontrakt_summasi;
                                                $qarzdorlik_umumiy += $qarzdorlik;
                                                

                                                if ($tolangan_summa2 == $qaytarilgan_summa && $student["status"] == 0 || $qarzdorlik2 < 0 && $student["status"] == 0) {
                                                    $no_edited = true;
                                                } else {
                                                    $no_edited = false;
                                                }
                                            }

                                            if ($tolangan_summa_umumiy == 0 && $imtiyoz_summasi_umumiy == 0 && (abs($qaytarilgan_summa_umumiy)) == 0) continue;

                                            if (!$final_talaba_soni_years_arr[$year]) {
                                                $final_talaba_soni += $talaba_soni_umumiy;
                                            }
                                            $final_talaba_soni_years_arr[$year] = true;
                                            $final_kontrakt_summasi += $kontrakt_summasi_umumiy;
                                            $final_imtiyoz_summasi += $imtiyoz_summasi_umumiy;
                                            $final_tolangan_summasi += $tolangan_summa_umumiy;
                                            $final_qaytarilgan_summasi += (abs($qaytarilgan_summa_umumiy));
                                            $final_qarzdorlik_summasi += $qarzdorlik_umumiy;

                                            ?>
                                            
                                            <tr class="btn-reveal-trigger">
                                                <td class="py-2"><?=($year. ' - '. ($year + 1))?></td>
                                                <td class="py-2"><?=$course_id?></td>
                                                <td class="p-2"><?=$talaba_soni_umumiy?></td>
                                                <td class="p-2"><?=($_GET["export"] == "excel" ? ($kontrakt_summasi_umumiy) : number_format($kontrakt_summasi_umumiy))?></td>
                                                <td class="py-2"><?=($_GET["export"] == "excel" ? ($imtiyoz_summasi_umumiy) : number_format($imtiyoz_summasi_umumiy))?></td>
                                                <td class="py-2"><?=($_GET["export"] == "excel" ? ($tolangan_summa_umumiy) : number_format($tolangan_summa_umumiy))?></td>
                                                <td class="py-2"><?=($_GET["export"] == "excel" ? (abs($qaytarilgan_summa_umumiy)) : number_format(abs($qaytarilgan_summa_umumiy)))?></td>
                                                <td class="py-2" title="<?=number_format($kontrakt_summasi_umumiy - $imtiyoz_summasi_umumiy - $tolangan_summa_umumiy - $qaytarilgan_summa_umumiy)?>"><?=($_GET["export"] == "excel" ? ($qarzdorlik_umumiy) : number_format($qarzdorlik_umumiy))?></td>
                                            </tr>
                                        <? } ?>
                                    <? } ?>

                                    <tr>
                                        <th>JAMI</th>
                                        <th></th>
                                        <th><?=number_format($final_talaba_soni)?></th>
                                        <th><?=number_format($final_kontrakt_summasi)?></th>
                                        <th><?=number_format($final_imtiyoz_summasi)?></th>
                                        <th><?=number_format($final_tolangan_summasi)?></th>
                                        <th><?=number_format($final_qaytarilgan_summasi)?></th>
                                        <th><?=number_format($final_qarzdorlik_summasi)?></th>
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