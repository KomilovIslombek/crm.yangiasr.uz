<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_REQUEST["page"];
if (empty($page)) $page = 1;

$group_id = isset($_REQUEST["groups_id"]) ? $_REQUEST["groups_id"] : null;
// if (!$group_id) {echo"error groups_id not found";return;}

$group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [$group_id]);
if (!$group["id"]) {echo"error (groups not found)";exit;}

$students = $db->in_array("SELECT code, first_name, last_name, father_first_name FROM students ORDER BY last_name");
$teachers = $db->in_array("SELECT id, first_name, last_name FROM teachers");
$sciences = $db->in_array("SELECT * FROM sciences");
$group_name = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $group_id ]);

if ($_REQUEST["type"] == $url[0]){
    validate(["group_name"]);

    // include "modules/uploadImage.php";
    // $uploadedImage = uploadImageWithUpdate("image", "images/groups", ["jpg","jpeg","png"], false, false, $group["image_id"]);
    
    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $db->update("groups_list", [
            "name" => $_POST['group_name']
        ], [
            "id" => $group["id"]
        ]);
        
        header("Location: /groupsList/?page=" . $page);
        exit;
    }
}

if ($_REQUEST["type"] == "deletegroup") {   
    $db->delete("groups_list", $group["id"]);
    // $db->delete("group_users", $group["id"], "group_id");
    $db->delete("group_teachers", $group["id"], "group_id");
    $db->delete("group_sciences", $group["id"], "group_id");
    $db->delete("department_groups", $group["id"], "group_id");
    header("Location: /groupsList/?page=" . $page);
    exit;
}


$breadcump_title_1 = "Guruhlar";
$breadcump_title_2 = "Guruhni tahrirlash";
$form_title = "Guruhni tahrirlash";

$group_students = $db->in_array("SELECT * FROM students WHERE group_id = ?", [ $group["id"] ]);
$group_teachers = $db->in_array("SELECT * FROM group_teachers WHERE group_id = ?", [ $group["id"] ]);
$group_sciences = $db->in_array("SELECT * FROM group_sciences WHERE group_id = ?", [ $group["id"] ]);

// teachers

$teachersArr = [];
foreach($teachers as $teacher_key => $teacher) {
    $group_teacher = $db->assoc("SELECT * FROM group_teachers WHERE teacher_id = ? AND group_id = ?", [ $teacher["id"], $group["id"] ]);

    if (!$group_teacher["id"]) {
        array_push($teachersArr, $teacher);
    }
}

// sciences

$sciencesArr = [];
foreach($sciences as $science_key => $science) {
    $group_science = $db->assoc("SELECT * FROM group_sciences WHERE science_id = ? AND group_id = ?", [ $science["id"], $group["id"] ]);

    if (!$group_science["id"]) {
        array_push($sciencesArr, $science);
    }
}
include "system/head.php";
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
                            <div>
                                <input type="hidden" name="type" value="<?=$url[0]?>">
                                <input type="hidden" name="page" value="<?=$page?>">

                                <div id="wrap_contents" class="d-flex align-items-center">
                                    <div class="group_option w-50">
                                        <h3>(<?=$group["name"]?>) Guruhidagi talabalar</h3>
                                        <p>Ayni vaqtda guruhdagi talabalar soni <b id="group-students-count"><?=count($group_students)?></b> ta</p>
                                        
                                        <div style="height: 400px; padding: 1.100rem 1.785rem;" class="wrap_users bg-white radial border">
                                            <select multiple name="myselect2[]" id="myselect2" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-live-search="true" data-actions-box="true">
                                                <? foreach($group_students as $group_student) { ?>
                                                    <option value="<?=$group_student["code"]?>" data-subtext="<?=$group_student["code"]?>"><?=$group_student["last_name"]. " " . $group_student["first_name"]. " " . $group_student["father_first_name"]?></option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <span class="mx-3 buttons d-flex flex-column">
                                        <span id="addStudent" class="btn btn-outline-dark">Qo'shish</span>
                                        <span id="removeStudent" class="btn btn-outline-danger my-2">O'chirish</span>
                                    </span>

                                    <div class="group_users mt-5 w-50">
                                        <p>Ayni vaqtdagi talabalar soni <b id="students-count"><?=count($students)?></b> ta</p>
                                        <div style="height: 400px; padding: 1.100rem 1.785rem;" class="wrap_users bg-white radial border">
                                            <select multiple name="myselect" id="myselect" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                <? foreach($students as $student) { ?>
                                                    <option value="<?=$student["code"]?>" data-subtext="<?=$student["code"]?>"><?=$student["last_name"]. " " . $student["first_name"]. " " . $student["father_first_name"]?></option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guruhdagi Ustozlarni tahrirlash -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform:none;">Guruhdagi Ustozlarni tahrirlash</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <div>
                                <!-- Guruhdagi Ustozlar -->

                                <div id="wrap_contents" class="d-flex align-items-center flex-wrap-wrap">
                                    <div class="group_option w-50">
                                        <h3>(<?=$group["name"]?>) Guruhidagi Ustozlar</h3>
                                        <p>Ayni vaqtda guruhdagi ustozlar soni <b id="group-teachers-count"><?=count($group_teachers)?></b> ta</p>
                                        
                                        <div style="height: 400px; padding: 1.100rem 1.785rem;" class="wrap_users bg-white radial border">
                                            <select multiple name="myselectTeachers2[]" id="myselectTeachers2" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-live-search="true" data-actions-box="true">
                                                <? foreach($group_teachers as $group_teacher) { ?>
                                                    <?
                                                    $group_teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $group_teacher["teacher_id"] ]);
                                                    ?>
                                                    <option value="<?=$group_teacher["id"]?>" data-subtext="<?=$group_teacher["id"]?>"><?=$group_teacher["first_name"]. " " . $group_teacher['last_name']?></option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <span class="mx-3 buttons d-flex flex-column">
                                        <span id="addTeacher" class="btn btn-outline-dark">Qo'shish</span>
                                        <span id="removeTeacher" class="btn btn-outline-danger my-2">O'chirish</span>
                                    </span>

                                    <div class="group_users mt-5 w-50">
                                        <p>Ayni vaqtdagi ustozlar soni <b id="teachers-count"><?=count($teachersArr)?></b> ta</p>
                                        <div style="height: 400px; padding: 1.100rem 1.785rem;" class="wrap_users bg-white radial border">
                                            <select multiple name="myselectTeachers[]" id="myselectTeachers" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                <? foreach($teachersArr as $teacher) { ?>
                                                    <option value="<?=$teacher["id"]?>" data-subtext="<?=$teacher["id"]?>"><?=$teacher["first_name"]. " " . $teacher['last_name']?></option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <style>
                                    @media (max-width: 1000px) {
                                        #wrap_contents{
                                            flex-direction: column;                                            
                                        }
                                        .group_option,.group_users{
                                            width: 100% !important;
                                        }
                                        .buttons{
                                            margin-top: 30px;
                                        }
                                    }
                                </style>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guruhdagi fanlarni tahrirlash -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform:none;">Guruhdagi Fanlarni tahrirlash</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <div>
                                <!-- Guruhdagi fanlar -->

                                <div id="wrap_contents" class="d-flex align-items-center flex-wrap-wrap">
                                    <div class="group_option w-50">
                                        <h3>(<?=$group["name"]?>) Guruhidagi Fanlar</h3>
                                        <p>Ayni vaqtda guruhdagi Fanlar soni <b id="group-sciences-count"><?=count($group_sciences)?></b> ta</p>
                                        
                                        <div style="height: 400px; padding: 1.100rem 1.785rem;" class="wrap_users bg-white radial border">
                                            <select multiple name="myselectSciences2[]" id="myselectSciences2" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-live-search="true" data-actions-box="true">
                                                <? foreach($group_sciences as $group_science) { ?>
                                                    <?
                                                    $group_science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [ $group_science["science_id"] ]);
                                                    ?>
                                                    <option value="<?=$group_science["id"]?>" data-subtext="<?=$group_science["id"]?>"><?=$group_science["name"]?></option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <span class="mx-3 buttons d-flex flex-column">
                                        <span id="addScience" class="btn btn-outline-dark">Qo'shish</span>
                                        <span id="removeScience" class="btn btn-outline-danger my-2">O'chirish</span>
                                    </span>

                                    <div class="group_users mt-5 w-50">
                                        <p>Ayni vaqtdagi fanlar soni <b id="sciences-count"><?=count($sciencesArr)?></b> ta</p>
                                        <div style="height: 400px; padding: 1.100rem 1.785rem;" class="wrap_users bg-white radial border">
                                            <select multiple name="myselectSciences[]" id="myselectSciences" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                <? foreach($sciencesArr as $science) { ?>
                                                    <option value="<?=$science["id"]?>" data-subtext="<?=$science["id"]?>"><?=$science["name"]?></option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <style>
                                    @media (max-width: 1000px) {
                                        #wrap_contents{
                                            flex-direction: column;                                            
                                        }
                                        .group_option,.group_users{
                                            width: 100% !important;
                                        }
                                        .buttons{
                                            margin-top: 30px;
                                        }
                                    }
                                </style>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guruhni nomini taxrirlash -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform:none;">Guruh nomini tahrirlash</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form action="/<?=$url[0]?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="type" value="<?=$url[0]?>">
                                <input type="hidden" name="groups_id" value="<?=$group["id"]?>">

                                <div class="form-row">
                                    <?=getError("group_name")?>
                                    <div class="form-group col-12">
                                        <label>Guruh nomi</label>
                                        <input type="text" name="group_name" class="form-control" placeholder="Guruh nomi" value="<?=$group_name["name"]?>">
                                    </div>
                                </div>

                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <button id="addToGroup_teachers" type="submit" class="btn btn-primary">Saqlash</button>
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

    $("#addStudent").click(function() {
        var selectedOptions = $('#myselect').find(":selected");//selected option value
        console.log(selectedOptions);
        
        $(selectedOptions).each(function(){
            var student_code = $(this).val();
            var group_id = <?=$group["id"]?>;
            var option = $(this);
            var option_html = $(this).prop('outerHTML');

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'addToGroup',
                    student_code: student_code,
                    group_id: group_id
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
                        // console.log(option);
                        $(option).remove();

                        var group_student_count = parseInt($("#group-students-count").text());
                        $("#group-students-count").text(group_student_count + 1); // +

                        var student_count = parseInt($("#students-count").text());
                        $("#students-count").text(student_count - 1); // -

                        // $("#users_list").append(option)
                        $('#myselect2').append(option_html);
                        $('#myselect').selectpicker('refresh');
                        $('#myselect2').selectpicker('refresh');
                    } else {
                        alert("Bunday foydalanuvchi guruhda mavjud");
                        // console.error(data);
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        })
    })


    $("#removeStudent").click(function() {
        var selectedOptions2 = $('#myselect2').find(":selected");//selected option value
        console.log(selectedOptions2);
        
        $(selectedOptions2).each(function(){
            var student_code = $(this).val();
            var group_id = <?=$group["id"]?>;
            var option = $(this);
            var option_html = $(this).prop('outerHTML');

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'removeInGroup',
                    student_code: student_code,
                    group_id: group_id
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
                        // console.log(option);
                        $(option).remove();

                        var group_student_count = parseInt($("#group-students-count").text());
                        $("#group-students-count").text(group_student_count - 1); // -

                        var student_count = parseInt($("#students-count").text());
                        $("#students-count").text(student_count + 1); // +

                        // $("#users_list").append(option)
                        $('#myselect').append(option_html);
                        $('#myselect').selectpicker('refresh');
                        $('#myselect2').selectpicker('refresh');
                        // $select.("refresh", );
                    } else {
                        alert("Bunday foydalanuvchi guruhda mavjud");
                        // console.error(data);
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        })
    });

    $('#myselect').selectpicker({
        placeholder: "Talabani tanlang",
        selectAllText: 'Barchasini tanlash',
        deselectAllText: 'Tanlaganlarni o\'chirish'
    });

    $('#myselect2').selectpicker({
        placeholder: "Talabani tanlang",
        selectAllText: 'Barchasini tanlash',
        deselectAllText: 'Tanlaganlarni o\'chirish'
    });

    // Teachers API change

    $("#addTeacher").click(function() {
        var myselectTeachers = $('#myselectTeachers').find(":selected");//selected option value
        console.log(myselectTeachers);
        
        $(myselectTeachers).each(function(){
            var teacher_id = $(this).val();
            var group_id = <?=$group["id"]?>;
            var option = $(this);
            var option_html = $(this).prop('outerHTML');

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'addTeachers',
                    teacher_id: teacher_id,
                    group_id: group_id
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
                        // console.log(option);
                        $(option).remove();

                        var group_teachers_count = parseInt($("#group-teachers-count").text());
                        $("#group-teachers-count").text(group_teachers_count + 1); // +

                        var teachers_count = parseInt($("#teachers-count").text());
                        $("#teachers-count").text(teachers_count - 1); // -

                        // $("#users_list").append(option)
                        $('#myselectTeachers2').append(option_html);
                        $('#myselectTeachers').selectpicker('refresh');
                        $('#myselectTeachers2').selectpicker('refresh');
                    } else {
                        alert("Bunday O'qituvchi guruhda mavjud");
                        // console.error(data);
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        })
    })


    $("#removeTeacher").click(function() {
        var myselectTeachers2 = $('#myselectTeachers2').find(":selected");//selected option value
        console.log(myselectTeachers2);
        
        $(myselectTeachers2).each(function(){
            var teacher_id = $(this).val();
            var group_id = <?=$group["id"]?>;
            var option = $(this);
            var option_html = $(this).prop('outerHTML');

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'removeInGroupTeachers',
                    teacher_id: teacher_id,
                    group_id: group_id
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
                        // console.log(option);
                        $(option).remove();

                        var group_teachers_count = parseInt($("#group-teachers-count").text());
                        $("#group-teachers-count").text(group_teachers_count - 1); // -

                        var teachers_count = parseInt($("#teachers-count").text());
                        $("#teachers-count").text(teachers_count + 1); // +

                        // $("#users_list").append(option)
                        $('#myselectTeachers').append(option_html);
                        $('#myselectTeachers').selectpicker('refresh');
                        $('#myselectTeachers2').selectpicker('refresh');
                        // $select.("refresh", );
                    } else {
                        alert("Bunday foydalanuvchi guruhda mavjud");
                        // console.error(data);
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        })
    });

    $('#myselectTeachers').selectpicker({
        placeholder: "Ustozni tanlang",
        selectAllText: 'Barchasini tanlash',
        deselectAllText: 'Tanlaganlarni o\'chirish'
    });

    $('#myselectTeachers2').selectpicker({
        placeholder: "Ustozni tanlang",
        selectAllText: 'Barchasini tanlash',
        deselectAllText: 'Tanlaganlarni o\'chirish'
    });

    // Sciences API change

    $("#addScience").click(function() {
        var myselectSciences = $('#myselectSciences').find(":selected");//selected option value
        console.log(myselectSciences);
        
        $(myselectSciences).each(function(){
            var science_id = $(this).val();
            var group_id = <?=$group["id"]?>;
            var option = $(this);
            var option_html = $(this).prop('outerHTML');

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'addSciences',
                    science_id: science_id,
                    group_id: group_id
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
                        // console.log(option);
                        $(option).remove();

                        var group_sciences_count = parseInt($("#group-sciences-count").text());
                        $("#group-sciences-count").text(group_sciences_count + 1); // +

                        var sciences_count = parseInt($("#sciences-count").text());
                        $("#sciences-count").text(sciences_count - 1); // -

                        // $("#users_list").append(option)
                        $('#myselectSciences2').append(option_html);
                        $('#myselectSciences').selectpicker('refresh');
                        $('#myselectSciences2').selectpicker('refresh');
                    } else {
                        alert("Bunday Fan guruhda mavjud");
                        // console.error(data);
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        })
    })


    $("#removeScience").click(function() {
        var myselectSciences2 = $('#myselectSciences2').find(":selected");//selected option value
        console.log(myselectSciences2);
        
        $(myselectSciences2).each(function(){
            var science_id = $(this).val();
            var group_id = <?=$group["id"]?>;
            var option = $(this);
            var option_html = $(this).prop('outerHTML');

            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'removeInGroupSciences',
                    science_id: science_id,
                    group_id: group_id
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
                        // console.log(option);
                        $(option).remove();

                        var group_sciences_count = parseInt($("#group-sciences-count").text());
                        $("#group-sciences-count").text(group_sciences_count - 1); // -

                        var sciences_count = parseInt($("#sciences-count").text());
                        $("#sciences-count").text(sciences_count + 1); // +

                        // $("#users_list").append(option)
                        $('#myselectSciences').append(option_html);
                        $('#myselectSciences').selectpicker('refresh');
                        $('#myselectSciences2').selectpicker('refresh');
                        // $select.("refresh", );
                    } else {
                        alert("Bunday fan guruhda mavjud");
                        // console.error(data);
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        })
    });

    $('#myselectSciences').selectpicker({
        placeholder: "Fandi tanlang",
        selectAllText: 'Barchasini tanlash',
        deselectAllText: 'Tanlaganlarni o\'chirish'
    });

    $('#myselectSciences2').selectpicker({
        placeholder: "Fandi tanlang",
        selectAllText: 'Barchasini tanlash',
        deselectAllText: 'Tanlaganlarni o\'chirish'
    });
</script>

<?
include "system/end.php";
?>