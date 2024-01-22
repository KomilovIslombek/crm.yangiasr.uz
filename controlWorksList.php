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

if (!empty($_GET["subject"])) {
    $subject = $db->assoc("SELECT * FROM subjects WHERE `name` LIKE '%".$_GET["subject"]."%'");
    $query .= " AND subject_id = " . $subject["id"];
}

if (!empty($_GET["group_id"])) {
    $query .= " AND group_id = " . (int)$_GET["group_id"];
} else if($systemUser["role"] == "teacher") {
    $group_teacher = $db->assoc("SELECT * FROM group_teachers WHERE teacher_id = ?", [ $systemUser["teacher_id"] ]);
    if(!empty($group_teacher["group_id"])) {
        $query .= " AND group_id = " . $group_teacher["group_id"];
    } else {
        $query .= " AND group_id = " . 0;
    }
}

if (!empty($_GET["science_id"])) {
    $query .= " AND science_id = " . (int)$_GET["science_id"];
}

$sql = "SELECT * FROM science_subjects WHERE 1=1$query ORDER BY id ASC";

$count = (int)$db->assoc("SELECT COUNT(*) FROM `science_subjects` WHERE 1=1$query")["COUNT(*)"];

$sql .= " LIMIT $page_start, $page_count";

$controlWorks = $db->in_array($sql);

    
include "system/head.php";

$breadcump_title_1 = "Mavzular";
$breadcump_title_2 = "Mavzular ro'yxati";
?>

<!--**********************************
    Content body start
***********************************-->

<div class="content-body">
    <div class="container-fluid">
        <!-- Add Order -->
        <div class="page-titles d-flex justify-content-between">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)"><?=$breadcump_title_1?></a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?=$breadcump_title_2?></a></li>
            </ol>
        </div>

        <!-- start Filter -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="/<?=$url[0]?>" method="GET" id="filter">
                            <div class="basic-form row d-flex align-items-center">
                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Guruhlar:</label>
                                    <select name="group_id" id="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        <? if($systemUser["role"] == "teacher") {
                                                $teahcer_groups = $db->in_array("SELECT * FROM group_teachers WHERE teacher_id = ?", [ $systemUser["teacher_id"] ] );
                                                foreach ($teahcer_groups as $teahcer_group) { 
                                                    $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $teahcer_group["group_id"] ]); 
                                        ?> 
                                                <option
                                                    value="<?=$group["id"]?>"
                                                    <?=($_GET["group_id"] == $group["id"] ? 'selected=""' : '')?>
                                                ><?=($group["id"])?>  <?=$group["name"]?></option>
                                            <? } ?>
                                        <?  } else if($systemUser["role"] == "admin") {?>
                                            <option value="">Barchasi</option>
                                            <? foreach ($db->in_array("SELECT * FROM groups_list") as $group) { ?>
                                                <option
                                                    value="<?=$group["id"]?>"
                                                    <?=($_GET["group_id"] == $group["id"] ? 'selected=""' : '')?>
                                                ><?=$group["name"]?></option>
                                            <? } ?>
                                        <? } ?>
                                    </select>
                                </div>

                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Guruh fanlari:</label>
                                    <select name="science_id" id="science_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        
                                        
                                    </select>
                                </div>

                                <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                    <label>Nazorat ishlari:</label>
                                    <select name="subject" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                        <option value="">Barchasi</option>
                                        <option
                                            value="JN"
                                            <?=($_GET["subject"] == "JN" ? 'selected=""' : '')?>
                                        >JN</option>
                                        <option
                                            value="ON"
                                            <?=($_GET["subject"] == "ON" ? 'selected=""' : '')?>
                                        >ON</option>
                                        <option
                                            value="YN"
                                            <?=($_GET["subject"] == "YN" ? 'selected=""' : '')?>
                                        >YN</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end Filter -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div style="min-height: 300px;" class="table-responsive">
                            <table class="table table-responsive-md mb-0 table-bordered" id="table">
                                <thead>
                                    <tr>
                                        <th>#id</th>
                                        <th>Guruh nomi</th>
                                        <th>Fan nomi</th>
                                        <th>Sana</th>
                                        <th>Mavzu</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="customers">
                                    <? foreach ($controlWorks as $controlWork){ ?>
                                        <?
                                            $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $controlWork["group_id"] ]);
                                            $science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [ $controlWork["science_id"] ]);
                                            $subject = $db->assoc("SELECT * FROM subjects WHERE id = ?", [ $controlWork["subject_id"] ]);
                                        ?>
                                        
                                        <tr class="btn-reveal-trigger">
                                            <td class="py-2"><?=$controlWork["id"]?></td>
                                            <td class="py-2"><?=$group["name"]?></td>
                                            <td class="py-2"><?=$science["name"]?></td>
                                            <td class="py-2"><?=$controlWork["subject_date"]?></td>
                                            <td class="py-2"><?=$subject["name"]?></td>
                                            <td class="py-2 text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                        <span class="fs--1"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right border py-0">
                                                        <div class="py-2">
                                                            <a class="dropdown-item"  href="/editControlWork/?id=<?=$controlWork["id"]?>&page=<?=$page?>">Tahrirlash</a>
                                                            <a class="dropdown-item text-danger" href="/editControlWork/?id=<?=$controlWork["id"]?>&type=deletecontrolwork">O'chirish</a>
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
                            // $count = (int)$db->assoc("SELECT COUNT(*) FROM science_subjects")["COUNT(*)"];
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
    // $('#group_id').change(function() {
    //     var group_id = $(this).val();
    //     updateTable();

    //     $.ajax({
    //         url: '/api',
    //         type: "POST",
    //         data: {
    //             method: 'filterGroup',
    //             group_id: group_id,
    //         },
    //         dataType: "json",
    //         success: function(data) {
    //                 if (data.ok == true) {

    //                 $("#science_id").html('');
    //                 data.sciences.forEach(science => {
    //                     $("#science_id").append(`<option value="${science.id}"> ${science.name}</option>`)
    //                 });
    //                 $('#science_id').change();
    //                 $("#form").removeClass('d-none');
    //                 $('#science_id').selectpicker('refresh');
    //             } else {
    //                 $("#form").addClass('d-none');
    //                 alert("Bu guruhga tegishli fanlar mavjud emas");
    //                 // console.error(data);
    //             }
    //         },
    //         error: function() {
    //             alert("Xatolik yuzaga keldi");
    //         }
    //     })
    // });

    $('#group_id').change(function() {
        var group_id = $(this).val();
        console.log(group_id);
        
        if(!group_id) {
            $("#science_id").html('');
            $('#science_id').selectpicker('refresh');
            $("#subject_id").html('');
            $('#subject_id').selectpicker('refresh');
            updateTable();
        } else {
            updateTable();
            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'filterGroup',
                    group_id: group_id,
                },
                dataType: "json",
                success: function(data) {
                        if (data.ok == true) {
    
                        $("#science_id").html('');
                        data.sciences.forEach(science => {
                            $("#science_id").append(`<option value="${science.id}"> ${science.name}</option>`)
                        });
                        $('#science_id').change();
                        $('#science_id').selectpicker('refresh');
                    } else {
                        $("#science_id").html('');
                        $('#science_id').selectpicker('refresh');
                        $(".modal-title").text("Bu guruhga tegishli fanlar mavjud emas");
                        $(".modal").modal("show");
                        // console.error(data);
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        }
    });

    $("#group_id").change();
</script>

<?
include "system/end.php";
?>