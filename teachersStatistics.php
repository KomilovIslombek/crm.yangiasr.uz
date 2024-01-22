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

    $departments = $db->in_array("SELECT * FROM departments  ORDER BY id ASC LIMIT $page_start, $page_count");

include "system/head.php";

$breadcump_title_1 = "Statistika";
$breadcump_title_2 = "O'qituvchilar statistikasi";
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
                                        <th rowspan="2" style="vertical-align:middle;">kafedra</th>
                                        <th colspan="2">Jinsi</th>
                                        <th colspan="2">ilmiy unvoni</th>
                                    </tr>

                                    <tr>
                                        <th>Erkak</th>
                                        <th>Ayol</th>
                                        <th>Erkak</th>
                                        <th>Ayol</th>
                                    </tr>
                                </thead>
                                <tbody id="customers">

                                <? foreach ($departments as $department){ ?>
                                    <?
                                        $academic_titles = $db->in_array("SELECT DISTINCT(academic_title) AS name FROM teachers WHERE academic_title IS NOT NULL");

                                        $department_teachers = $db->in_array("SELECT * FROM department_teachers WHERE department_id = ?", [ $department["id"] ]);
                                        $mans = 0;
                                        $womans = 0;
                                        foreach($department_teachers as $department_teacher) {
                                            $teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $department_teacher["teacher_id"] ]);
                                            if($teacher["sex"] == "erkak") {
                                                $mans++;

                                                foreach ($academic_titles as $key => $academic_title) {
                                                    if ($academic_title["name"] == $teacher["academic_title"]) {
                                                        $academic_titles[$key]["man_teachers_count"] = 1;
                                                    }
                                                }
                                            } else if($teacher["sex"] == "ayol") {
                                                $womans++;

                                                foreach ($academic_titles as $key => $academic_title) {
                                                    if ($academic_title["name"] == $teacher["academic_title"]) {
                                                        $academic_titles[$key]["woman_teachers_count"] = 1;
                                                    }
                                                }
                                            }
                                        }
                                        ?>

                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2"><?=$department["name"]?></td>
                                            <td class="py-2"><?=$mans?></td>
                                            <td class="py-2"><?=$womans?></td>
                                            <td class="py-2">
                                                <?
                                                foreach ($academic_titles as $academic_title) {
                                                    echo "<div class='d-flex justify-content-between'>". "<span>". $academic_title["name"] . ":</span>". "<b>". ($academic_title["man_teachers_count"] ? $academic_title["man_teachers_count"] : 0) . "</b>". "</div>";    
                                                }
                                                ?>
                                            </td>
                                            <td class="py-2">
                                                <?
                                                foreach ($academic_titles as $academic_title) {
                                                    echo "<div class='d-flex justify-content-between'>". "<span>". $academic_title["name"] . ":</span>". "<b>". ($academic_title["woman_teachers_count"] ? $academic_title["woman_teachers_count"] : 0) . "</b>". "</div>";    
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <? } ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?
                        include "modules/pagination.php";
                        $count = (int)$db->assoc("SELECT COUNT(*) FROM departments")["COUNT(*)"];
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
        tableToExcel(
            $("#table").prop("innerHTML")
        );
    });
</script>

<?
include "system/end.php";
?>