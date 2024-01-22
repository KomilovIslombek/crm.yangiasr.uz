<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

if ($_REQUEST["type"] == $url[0]){
    validate(["name"]);
  
    include "modules/uploadImage.php";



    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $added_group_id = $db->insert("groups_list", [
            "creator_user_id" => $user_id,
            "name" => $_POST["name"],
        ]);
        
        if ($added_group_id > 0) {
            foreach($_POST["teachers"] as $teacher) {
                $added_group_teachers = $db->insert("group_teachers", [
                    "creator_user_id" => $user_id,
                    "group_id" => $added_group_id,
                    "teacher_id" => $teacher,
                ]);
            }
            header("Location: groupsList/?page=1");
            exit;
        }
    } else {
        header("Content-type: text/plain");
        print_r($errors);
        exit;
    }
}

include "system/head.php";

$breadcump_title_1 = "Guruhlar";
$breadcump_title_2 = "yangi guruh qo'shish";
$form_title = "Yangi guruh qo'shish";
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
            <div class="col-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform:none;"><?=$form_title?></h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form action="/<?=$url[0]?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="type" value="<?=$url[0]?>">
                                <div class="form-row">
                                    <?=getError("name")?>
                                    <div class="form-group col-12">
                                        <label>Guruh nomi</label>
                                        <input type="text" name="name" class="form-control" placeholder="Guruh nomi" value="<?=$_POST["name"]?>">
                                    </div>

                                    <?=getError("teachers")?>
                                    <div class="form-group col-12">
                                        <label>Ustozlar:</label>
                                        <select multiple name="teachers[]" id="myselect" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                            <? foreach ($db->in_array("SELECT * FROM teachers") as $teacher) { ?>
                                                <option value="<?=$teacher["id"]?>"><?=$teacher["first_name"] . " " . $teacher["last_name"]?></option>
                                            <? } ?>
                                        </select>
                                    </div>

                                </div>

                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <button id="addToGroup_teachers" type="click" class="btn btn-primary">Qo'shish</button>
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
    $("#phone-mask").on("input keyup", function(e){
        var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,2})(\d{0,3})(\d{0,2})(\d{0,2})/);
        // console.log(x);
        e.target.value = !x[2] ? '+' + (x[1].length == 3 ? x[1] : '998') : '+' + x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
    });

    $("#phone-mask").keyup();
   
    $("#phone-mask2").on("input keyup", function(e){
        var x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,2})(\d{0,3})(\d{0,2})(\d{0,2})/);
        // console.log(x);
        e.target.value = !x[2] ? '+' + (x[1].length == 3 ? x[1] : '998') : '+' + x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
    });

    $("#phone-mask").keyup();
        
    // Input mask

    $("#price-input").on("input", function(){
        var val = $(this).val().replaceAll(",", "").replaceAll(" ", "");
        console.log(val);

        if (val.length > 0) {    
            $(this).val(
                String(val).replace(/(.)(?=(\d{3})+$)/g,'$1,')
            );
        }
    });


</script>

<?
include "system/end.php";
?>