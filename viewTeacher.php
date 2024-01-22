<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_GET['page'];
if (empty($page)) $page = 1;

$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : null;
if (!$id) {echo"error id not found";return;}

$teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $id ]);
if (empty($teacher["id"])) exit(http_response_code(404));

$region = $db->assoc("SELECT * FROM regions WHERE id = ?", [ $teacher["region_id"] ]);
$payment_method = $db->assoc("SELECT * FROM payment_methods WHERE id = ?", [ $teacher["payment_method"] ]);

include "system/head.php";

$breadcump_title_1 = "O'qituvchi:";
$breadcump_title_2 = "$teacher[first_name] $teacher[last_name]";

$image = fileArr($teacher["image_id"]);
if ($image["thumb_image_id"]) $image = image($image["thumb_image_id"]);

$passport_image = fileArr($teacher["passport_image_id"]);
if ($image["thumb_image_id"]) $image = image($image["thumb_image_id"]);

$diplom_image = fileArr($teacher["diplom_image_id"]);
if ($image["thumb_image_id"]) $image = image($image["thumb_image_id"]);

if ($_REQUEST["type"] == "changePrivilege"){
    if ($teacher["id"]) {
        $db->update("teachers", [
            "privilege_percent" => ($_POST["privilege_percent"] ? $_POST["privilege_percent"] : NULL),
            "privilege_note" => ($_POST["privilege_note"] ? $_POST["privilege_note"] : NULL)
        ], [
            "id" => $teacher["id"]
        ]);

        header("Location: /viewStudent/?id=" . $teacher["id"]);
        exit;
    }
}

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
                    <div class="col-xl-4">
                        <div  class="card" style="height:580px;">
                            <div class="card-header">
                                <img src="<?=$image["file_folder"]?>" class="w-100" height="380px">
                            </div>
                            <div class="card-body">
                                <div class="profile-blog mb-5">
                                    <!-- <img src="images/profile/1.jpg" alt="" class="img-fluid mt-4 mb-4 w-100 b-radius"> -->
                                    <h2 style="text-align:center;"><a href="post-details.html" class="text-black"><?=$teacher["first_name"] . " <br> " . $teacher["last_name"]?></a></h2>
                                </div>
                            </div>
                        </div>
                        <div style="height: 500px" class="change_in575 card">
                            <div style="height: 500px" class="change_in575 card-body ">
                                <div class="profile-personal-info">
                                    <h4 class="text-primary mb-4">O'qituvchi haqida ma'lumot</h4>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Id <span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$teacher["id"]?></span>
                                        </div>
                                    </div>      
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Tug'ilgan yili <span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$teacher["birth_date"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Jinsi <span class="pull-right d-none d-sm-block">:</span></h5>
                                        </div>
                                        <div class="col-7"><span><?=$teacher["sex"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Telefon raqami<span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$teacher["phone_1"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Lavozimi<span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$student["role"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Millati<span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$teacher["nation"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Viloyati<span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$region["name"]?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-5">
                                            <h5 class="f-w-500">Manzili<span class="pull-right d-none d-sm-block">:</span>
                                            </h5>
                                        </div>
                                        <div class="col-7"><span><?=$teacher["address"]?></span>
                                        </div>
                                    </div>
                                    
                                   
                                    <!-- <div class="row mb-4 d-flex align-items-center mb-sm-2">
                                        <div class="col-3">
                                            <h5 class="f-w-500">Year Experience <span class="pull-right d-none d-sm-block">:</span></h5>
                                        </div>
                                        <div class="col-sm-9"><span>07 Year Experiences</span>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                        <style>
                            @media (max-width: 575px){
                                .change_in575{
                                    height: 620px;
                                }
                                .mb-4 {
                                    margin-bottom: 0.70rem !important;
                                }
                            }
                        </style>
                    </div>
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="profile-tab">
                                    <div class="custom-tab-1">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li class="nav-item" role="presentation"><a href="#my-posts" data-bs-toggle="tab" class="nav-link active show" aria-selected="true" role="tab">Shaxsiy ma'lumot</a>
                                            </li>
                                            <li class="nav-item" role="presentation"><a href="#about-me" data-bs-toggle="tab" class="nav-link" aria-selected="false" tabindex="-1" role="tab">O'qituvchinig Guruhlari</a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <!-- Shaxsiy ma'lumot -->
                                            <div id="my-posts" class="tab-pane fade active show" role="tabpanel">
                                                <div class="my-post-content pt-3">
                                                <div class="col-lg-12">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h4 class="card-title"><i class="fa-solid fa-user-graduate"></i> O'qituvchi</h4>
                                                        </div>
                                                        <div class="card-body">
                                                            <div style="min-height: 300px;" class="table-responsive">
                                                                <table class="table table-striped table-hover table-bordered table-responsive-sm">
                                                                    <tbody>
                                                                        <tr>
                                                                            <th>Teacher id</th>
                                                                            <td> <?=$teacher["id"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>F.I.SH</th>
                                                                            <td><?=$teacher["last_name"]. " " . $teacher["first_name"]. " " . $teacher["father_first_name"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Fuqarolik</th>
                                                                            <td><span class="badge badge-success light"><?=$nation["name"]?></span></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Lavozimi</th>
                                                                            <td><?=$teacher["role"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Tug'ilgan sanasi</th>
                                                                            <td class="color-success"><?=$teacher["birth_date"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Jinsi</th>
                                                                            <td class="color-success"><?=$teacher["sex"]?> 
                                                                            <? if($teacher["sex"] == 'erkak') {?>
                                                                                <i class="fa-solid fa-person"></i>
                                                                            <?} else {?>
                                                                                <i class="fa-solid fa-person-dress"></i>
                                                                            <?}?>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            
                                                            <div class="table-responsive mt-5">
                                                                <table class="table table-striped table-hover table-bordered table-responsive-sm">
                                                                    <tbody>
                                                                        <tr>
                                                                            <th>Viloyati</th>
                                                                            <td> <?=$region["name"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Manzili</th>
                                                                            <td><?=$teacher["address"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Telefon raqami</th>
                                                                            <td><a href="tel: <?=$teacher["phone_1"]?>" class="badge badge-success light"><?=$teacher["phone_1"]?></a></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Shartnoma raqami</th>
                                                                            <td class="color-success"><?=$teacher["contract_id"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Shartnoma muddati</th>
                                                                            <td class="color-success"><?=$teacher["contract_expire_date"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Ilmiy unvoni</th>
                                                                            <td class="color-success"><?=$teacher["academic_title"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Diplom qaysi OTM dan ekanligi</th>
                                                                            <td class="color-success"><?=$teacher["diplom_otm"]?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Diplom yo'nalishi</th>
                                                                            <td class="color-success"><?=$teacher["diplom_direction"]?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-responsive-md">
                                                                <thead>
                                                                    <tr>
                                                                        <th><strong><i class="fa fa-file mb-2"></i> Fayl nomi</strong></th>
                                                                        <th><strong><i class="fa fa-eye mb-2"></i> Ko'rish</strong></th>
                                                                        <th><strong><i class="fa fa-download mb-2"></i> Skachat qilib olish</strong></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><strong>O'qituvchi 3x4 rasmi</strong></td>
                                                                        <td>
                                                                            <div class="col-auto mb-2">
                                                                                <a href="<?=$image["file_folder"]?>" class="btn btn-outline-primary btn-xs">O'qituvchini-rasmi.</a>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="col-auto mb-2">
                                                                                <a href="<?=$image["file_folder"]?>" download class="btn btn-outline-primary btn-xs">O'qituvchini-rasmi.</a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                            <!-- About me -->
                                            <div id="about-me" class="tab-pane fade" role="tabpanel">
                                                <div class="profile-about-me">
                                                    <div class="pt-4 border-bottom-1 pb-3">
                                                        <h4 class="text-primary"><i class="fa-solid fa-users-rectangle"></i> Guruhlari</h4>
                                                    </div>
                                                </div>
                                                <table style="text-align:center;" class="table table-hover table-responsive">
                                                    <thead class="border">
                                                        <tr>
                                                            <th scope="col">#id</th>
                                                            <th scope="col">Guruh id</th>
                                                            <th scope="col">Guruh nomi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <? 
                                                        $groups_teachers = $db->in_array("SELECT * FROM group_teachers WHERE teacher_id = ?", [ $teacher["id"] ]);
                                                                            
                                                        foreach( $groups_teachers as $groups_teacher) {

                                                        $group = $db->assoc("SELECT * FROM groups_list WHERE id = ?", [ $groups_teacher["group_id"] ]);
                                                        ?>
                                                            <tr class="hover-dark">
                                                                <td class="table-light" scope="row"><?=$teacher["id"]?></td>
                                                                <td class="table-light"><?=$group["id"]?></td>
                                                                <td class="table-light"><?=$group["name"]?></td>
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
                </div>
        
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