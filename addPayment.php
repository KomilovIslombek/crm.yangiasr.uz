<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

if ($_REQUEST["type"] == $url[0]){
    validate(["id", "amount", "payment_method_id", "course_id"]);
    
    // include "modules/uploadImage.php";
    // $uploadedImage = uploadImage("image", "images/payments", ["jpg","jpeg","png"]);?

    // header("Content-type: text/plain");
    // print_r($errors);
    // exit;

    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $student = $db->assoc("SELECT * FROM students WHERE id = ?", [ $_POST["id"] ]);

        if ($student["code"]) {
            $payment_id = $db->insert("payments", [
                "creator_user_id" => $user_id,
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
                // "price" => str_replace(",", "", $_POST["price"])
                // "image_id" => $uploadedImage["image_id"],
            ]);
            
            if ($payment_id > 0) {
                header("Location: paymentsList/?page=1");
                exit;
            }
        }
    } else {
        // header("Content-type: text/plain");
        // print_r($errors);
        // exit;
    }
}

include "system/head.php";

$breadcump_title_1 = "To'lovlar";
$breadcump_title_2 = "yangi to'lov qo'shish";
$form_title = "Yangi to'lov qo'shish";
?>
<link href="theme/vora/vendor/sweetalert2/dist/sweetalert2.min.css?v=1" rel="stylesheet">

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
                                <div class="form-row">
                                    <?=getError("id")?>
                                    <div class="form-group col-12">
                                        <label>Talaba (ID)</label>
                                        <select name="id" data-debtor="true" id="single-select">
                                            <? foreach ($db->in_array("SELECT * FROM students ORDER BY last_name ASC") as $student) { ?>
                                                <option value="<?=$student["id"]?>" <?=($student["id"] == $_REQUEST["id"] ? 'selected=""' : '')?>><?=$student["last_name"] . " " . $student["first_name"]?> <?=$student["father_first_name"]?> (<?=$student["code"]?>)</option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("course_id")?>
                                    <div class="form-group col-12">
                                        <label>To'lov kursi</label>
                                        <select name="course_id" id="course_id" data-debtor="true" class="form-control default-select form-control-lg">
                                            <? foreach ($coursesArr as $course_id => $value) { ?>
                                                <option value="<?=$course_id?>" <?=($course_id == $_GET["course_id"] ? 'selected=""' : '')?> ><?=$value?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("amount")?>
                                    <div class="form-group col-12">
                                        <label>To'lov miqdori: <span class="debtorStudent"></span></label>
                                        <input type="text" name="amount" class="form-control" placeholder="To'lov miqdori" value="<?=$_POST["amount"]?>" id="price-input">
                                    </div>

                                    <?=getError("payment_method_id")?>
                                    <div class="form-group col-12">
                                        <label>To'lov uslubi</label>
                                        <select name="payment_method_id" class="form-control default-select form-control-lg">
                                            <? foreach ($db->in_array("SELECT * FROM payment_methods") as $payment_method) { ?>
                                                <option value="<?=$payment_method["id"]?>"><?=$payment_method["name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                    <?=getError("payment_date")?>
                                    <div class="form-group col-12">
                                        <div class="form-group col-12">
                                            <label>To'lov sanasi</label>
                                            <input type="date" name="payment_date" class="form-control" placeholder="To'lov sanasi" value="<?=$_POST["payment_date"]?>" id="price-input" <?=(!empty($setting["from_date"]) ? 'min="'.$setting["from_date"].'"' : '')?> required>
                                        </div>
                                    </div>
                                
                                    <!-- 
                                    <div class="form-group col-12">
                                        <label for="formFile" class="form-label">Rasm yuklash (jpg, jpeg, png) (300x300)</label>
                                        <input class="form-control" type="file" name="image" id="formFile" accept="image/*">
                                    </div> -->
                                </div>

                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <button type="submit" class="btn btn-primary">Qo'shish</button>
                                </div>
                                
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

<script src="theme/vora/vendor/sweetalert2/dist/sweetalert2.min.js"></script>

<script>
    var courses = <?=json_encode($coursesArr, true)?>;
    
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
        var whichCourse = 1;

        getDebtorStudent(id, courseId, whichCourse)
    });

    $("[name=course_id]").change(function () {
        var id = $("[name=id]").val();
        var courseId = $("[name=course_id]").val();

        getDebtorStudent(id, courseId)
    })

    $("[name=id]").change();

    function getDebtorStudentJson(id, courseId, whichCourse = null) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'filterDebtorStudent',
                    id: id,
                    course_id: courseId,
                    which_course: whichCourse,
                },
                dataType: "json",
                success: function(data) {
                    resolve(data);
                },
                error: function() {
                    reject();
                }
            });
        });
    }

    function getDebtorStudent(id, courseId, whichCourse = null) {
        getDebtorStudentJson(id, courseId, whichCourse).then(function(data) {
            if (data.ok == true) {
                $("*[type='submit']").removeAttr("disabled");
                
                if (data.course_id) {
                    $("#course_id").find("option").removeAttr("selected");
                    $("#course_id").find("option[value='"+data.course_id+"']").attr("selected", "");
                    $("#course_id").selectpicker("refresh");
                    return getDebtorStudent(id, data.course_id);
                }

                if (courseId > 1) {
                    getDebtorStudentJson(id, courseId - 1).then(function(data2) {
                        if (parseInt(data2.qarzdorlik.replaceAll(",", "")) > 0) {
                            <? if ($user_id != 1) { ?>
                                $("*[type='submit']").attr("disabled", "true");
                            <? } ?>
                            sweetAlert(courseId + "-kursga to'lov qilib bo'lmaydi", "Chunki "+(courseId - 1)+"-kursdan "+(data2.qarzdorlik)+" so\'m qarzodrligini yopish kerak", "error");
                        } else {
                            $("*[type='submit']").removeAttr("disabled");
                        }
                    });
                }

                if (parseInt(data.qarzdorlik.replaceAll(",", "")) > 0) {
                    data.qarzdorlik = '<span class="text-danger">(qarzdorligi: '+data.qarzdorlik +' so\'m)</span>'
                } else {
                    data.qarzdorlik = '('+courseId+'-kursdan qarzdor emas)';
                }
                $(".debtorStudent").html(data.qarzdorlik);
            } else {
                console.log(data.errorCourse);
                $(".modal-title").text(`Bu Talabada xatolik yuz berdi`);
                $(".modal_err").modal("show");
            }
        });
    }
</script>

<?
include "system/end.php";
?>