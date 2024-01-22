<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_REQUEST["page"];
if (empty($page)) $page = 1;

$setting = $db->assoc("SELECT * FROM settings");
if (!$setting["id"]) {echo"error (setting not found)";exit;}
// if (!empty($setting["from_date"]) && !empty($setting["to_date"]) && $setting["from_date"] <= date("Y-m-d H:i:s") && $setting["to_date"] >= date("Y-m-d H:i:s") && $systemUser["id"] != 1) {

$payment_id = isset($_REQUEST["payment_id"]) ? $_REQUEST["payment_id"] : null;
if (!$payment_id) {echo"error payment_id not found";return;}

$payment = $db->assoc("SELECT * FROM payments WHERE id = ?", [$payment_id]);
if (!$payment["id"]) {echo"error (payment not found)";exit;}

if (!empty($setting["from_date"]) && $payment["payment_date"] <= $setting["from_date"] && $systemUser["id"] != 1 && $payment["payment_date"] != "00-00-0000" && $payment["payment_date"] != "0000-00-00") {
    $ruxsat_berish = false;
} else {
    $ruxsat_berish = true;
}

if ($_REQUEST["type"] == $url[0] && $ruxsat_berish == true){
    validate(["id", "amount", "payment_method_id", "course_id"]);

    // include "modules/uploadImage.php";
    // $uploadedImage = uploadImageWithUpdate("image", "images/payments", ["jpg","jpeg","png"], false, false, $payment["image_id"]);
    
    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $student = $db->assoc("SELECT * FROM students WHERE id = ?", [ $_POST["id"] ]);

        if ($student["code"]) {
            $db->update("payments", [
                "student_id" => $student["id"],
                "code" => $student["code"],
                "amount" => str_replace(",", "", $_POST["amount"]),
                "payment_method_id" => $_POST["payment_method_id"],
                "payment_date" => $_POST["payment_date"],
                "course_id" => $_POST["course_id"],
                "direction_id" => $student["direction_id"],
                "learn_type_id" => $student["learn_type_id"],
                "privilege_percent" => $student["privilege_percent"],
                "privilege_note" => $student["privilege_note"]
            ], [
                "id" => $payment["id"]
            ]);

            header("Location: /paymentsList/?page=" . $page);
            exit;
        }
        
    }
}

// $systemUser["id"] = 2;

if ($_REQUEST["type"] == "deletePayment" && $ruxsat_berish == true) {
    $db->delete("payments", $payment["id"]);
    header("Location: /paymentsList/?page=" . $page);
    exit;
}
// $systemUser["id"] = 'islombek';
include "system/head.php";

// $systemUser["id"] = 2;

$breadcump_title_1 = "To'lovlar";
$breadcump_title_2 = "To'lovni tahrirlash";
$form_title = "To'lovni tahrirlash";
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
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform:none;"><?=$form_title?></h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form action="/<?=$url[0]?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="type" value="<?=$url[0]?>">
                                <input type="hidden" name="page" value="<?=$page?>">
                                <input type="hidden" name="payment_id" value="<?=$payment["id"]?>">

                                <div class="form-row">
                                    <?=getError("code")?>
                                    <div class="form-group col-12">
                                        <label>Talaba (ID)</label>
                                        
                                        <? if (!$ruxsat_berish) { ?>
                                            <select disabled="true" name="id" id="single-select">
                                                <? foreach ($db->in_array("SELECT * FROM students ORDER BY last_name ASC") as $student) { ?>
                                                    <option value="<?=$student["id"]?>" <?=($payment["student_id"] == $student["id"] ? 'selected=""' : "")?>><?=$student["last_name"] . " " . $student["first_name"]?> <?=$student["father_first_name"]?> (<?=$student["code"]?>)</option>
                                                <? } ?>
                                            </select>
                                        <? } else { ?>
                                            <select name="id" id="single-select">
                                                <? foreach ($db->in_array("SELECT * FROM students ORDER BY last_name ASC") as $student) { ?>
                                                    <option value="<?=$student["id"]?>" <?=($payment["student_id"] == $student["id"] ? 'selected=""' : "")?>><?=$student["last_name"] . " " . $student["first_name"]?> <?=$student["father_first_name"]?> (<?=$student["code"]?>)</option>
                                                <? } ?>
                                            </select>
                                        <? } ?>
                                    </div>

                                    <?=getError("course_id")?>
                                    <div class="form-group col-12">
                                        <label>To'lov kursi</label>

                                        <? if (!$ruxsat_berish) { ?>
                                            <select disabled="true" name="course_id" class="form-control default-select form-control-lg">
                                                <? foreach ($coursesArr as $course_id => $value) { ?>
                                                    <option value="<?=$course_id?>" <?=($course_id == $payment["course_id"] ? 'selected=""' : "")?>><?=$value?></option>
                                                <? } ?>
                                            </select>
                                        <? } else {?>
                                            <select name="course_id" class="form-control default-select form-control-lg">
                                                <? foreach ($coursesArr as $course_id => $value) { ?>
                                                    <option value="<?=$course_id?>" <?=($course_id == $payment["course_id"] ? 'selected=""' : "")?>><?=$value?></option>
                                                <? } ?>
                                            </select>
                                        <? } ?>
                                    </div>

                                    <?=getError("amount")?>
                                        <div class="form-group col-12">
                                            <label>To'lov miqdori <span class="debtorStudent"></span></label>

                                            <? if (!$ruxsat_berish) { ?>
                                                <input type="text" readonly="true" name="amount" class="form-control" placeholder="To'lov miqdori" value="<?=number_format($payment["amount"])?>" id="price-input">
                                            <? } else {?>
                                                <input type="text" name="amount" class="form-control" placeholder="To'lov miqdori" value="<?=number_format($payment["amount"])?>" id="price-input">
                                            <? } ?>
                                        </div>

                                    <?=getError("payment_method_id")?>
                                    <div class="form-group col-12">
                                        <label>To'lov uslubi</label>

                                        <? if (!$ruxsat_berish) { ?>
                                            <select disabled="true" name="payment_method_id" class="form-control default-select form-control-lg">
                                                <? foreach ($db->in_array("SELECT * FROM payment_methods") as $payment_method) { ?>
                                                    <option value="<?=$payment_method["id"]?>" <?=($payment_method["id"] == $payment["payment_method_id"] ? 'selected=""' : '')?>><?=$payment_method["name"]?></option>
                                                <? } ?>
                                            </select>
                                        <? } else {?>
                                            <select name="payment_method_id" class="form-control default-select form-control-lg">
                                                <? foreach ($db->in_array("SELECT * FROM payment_methods") as $payment_method) { ?>
                                                    <option value="<?=$payment_method["id"]?>" <?=($payment_method["id"] == $payment["payment_method_id"] ? 'selected=""' : '')?>><?=$payment_method["name"]?></option>
                                                <? } ?>
                                            </select>
                                        <? } ?>
                                    </div>

                                    <?=getError("payment_date")?>
                                    <div class="form-group col-12">
                                        <div class="form-group col-12">
                                            <label>To'lov sanasi</label>

                                            <? if (!$ruxsat_berish) { ?>
                                                <input disabled="true" type="date" name="payment_date" class="form-control" placeholder="To'lov sanasi" value="<?=$payment["payment_date"]?>" id="price-input">
                                            <? } else {?>
                                                <input type="date" name="payment_date" class="form-control" placeholder="To'lov sanasi" value="<?=$payment["payment_date"]?>" id="price-input">
                                            <? } ?>

                                        </div>
                                    </div>
                                    
                                <? if (!$ruxsat_berish) { ?>
                                <? } else {?>
                                    <div class="toolbar toolbar-bottom" role="toolbar" style="margin-left: auto; ">
                                        <button type="submit" class="btn btn-primary">Saqlash</button>
                                    </div>
                                <? } ?>
                            </form>
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
    $("#price-input").on("input", function(){
        var val = $(this).val().replaceAll(",", "").replaceAll(" ", "");
        console.log(val);

        if (val.length > 0) {    
            $(this).val(
                String(val).replace(/(.)(?=(\d{3})+$)/g,'$1,')
            );
        }
    });

    $("[name=id]").change(function () {
        var id = $("[name=id]").val();
        var courseId = $("[name=course_id]").val();

        getDebtorStudent(id, courseId)
    });

    $("[name=course_id]").change(function () {
        var id = $("[name=id]").val();
        var courseId = $("[name=course_id]").val();

        getDebtorStudent(id, courseId)
    })

    $("[name=id]").change();

    function getDebtorStudent(id, courseId) {
        $.ajax({
            url: '/api',
            type: "POST",
            data: {
                method: 'filterDebtorStudent',
                id: id,
                course_id: courseId,
            },
            dataType: "json",
            success: function(data) {
                if (data.ok == true) {

                    // console.table(data);

                    if(parseInt(data.qarzdorlik) > 0) {
                        data.qarzdorlik = data.qarzdorlik +' so\'m'
                    } 
                    $(".debtorStudent").html('qarzdorligi: '+data.qarzdorlik);
                } else {
                    console.log(data.errorCourse);
                    $(".modal-title").text(`Bu Talabada xatolik yuz berdi`);
                    $(".modal_err").modal("show");
                }
            },
            error: function() {
                alert("Xatolik yuzaga keldi");
            }
        })
    }
</script>

<?
include "system/end.php";
?>