<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

include "modules/importStudent.php";

if ($_REQUEST["type"] == "importStudent") {
    
    $student = importStudent([
        "student_id" => $_REQUEST["student_id"],
        "season" => $_REQUEST["season"]
    ], $url[1]);

    if ($student != false && $student["id"]) {
        header("Location: /editStudent/?id=" . $student["id"]);
        exit;
    }
}

include "system/head.php";

$breadcump_title_1 = "Talabalar";
$breadcump_title_2 = "talabani import qilish";
$form_title = "Talabani import qilish";

if ($url[1] == 1) {
    $requests = $db3->in_array("SELECT * FROM requests_1 ORDER BY last_name ASC");
} else if ($url[1] == 2) {
    $requests = $db3->in_array("SELECT * FROM requests_2 ORDER BY last_name ASC");
} else if ($url[1] == 3) {
    $requests = $db3->in_array("SELECT * FROM requests ORDER BY last_name ASC");
}
?>
<!--**********************************
    Content body start
***********************************-->
<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)"><?=$breadcump_title_1?></a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?=$breadcump_title_2?></a></li>
            </ol>
        </div>

        <!-- row -->
        <div class="row">
            <div class="col-lg-8 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform:none;">Talabani import qilish (yangiasr.uz dan)</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form action="/<?=$url[0]?>/<?=$url[1]?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="type" value="importStudent">
                                <div class="form-row">
                                    <?=getError("student_id")?>
                                    <div class="form-group col-12">
                                        <label>Talaba:</label>
                                        <select name="student_id" id="single-select">
                                            <? foreach ($requests as $request) { ?>
                                                <?
                                                $localStudent = $db->assoc("SELECT * FROM students WHERE passport_serial_number = ?", [ $request["passport_serial_number"] ]);

                                                // if (!empty($request["reg_type"])) {
                                                //     $db->update("students", [
                                                //         "reg_type" => $request["reg_type"]
                                                //     ], [
                                                //         "passport_serial_number" => $localStudent["passport_serial_number"]
                                                //     ]);
                                                // }

                                                // if ($localStudent["code"]) continue;
                                                ?>  
                                                
                                                <option value="<?=$request["id"]?>" <?=($localStudent["code"] ? 'disabled="" title="talaba bazada mavjud"' : '')?>><?=$request["last_name"] . " " . $request["first_name"]?> <?=$request["father_first_name"]?> (<?=$request["code"]?>)</option>
                                            <? } ?>
                                        </select>
                                    </div>
                                    <?=getError("season")?>
                                    <div class="form-group col-12">
                                        <label>Mavsumi:</label>
                                        <select name="season" class="form-control default-select form-control-lg">
                                            <option value="yozgi">Yozgi</option>
                                            <option value="qishki">Qishki</option>
                                        </select>
                                    </div>
                                </div>

                                

                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <button type="submit" class="btn btn-primary">Import qilish</button>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->
    </div>
</div>
<!--**********************************
    Content body end
***********************************-->
<?
include "system/scripts.php";
?>

<?
include "system/end.php";
?>