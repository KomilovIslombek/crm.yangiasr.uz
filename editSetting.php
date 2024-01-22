<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : null;
if (!$id) {echo"error id not found";return;}

$setting = $db->assoc("SELECT * FROM settings WHERE id = ?", [$id]);
if (!$setting["id"]) {echo"error (setting not found)";exit;}

if ($_REQUEST["type"] == $url[0]){
    validate(["from_date"]);

    $year_privilege_arr = explode("-", $_POST["year_privilege"]);
    $year_amount_arr = explode("-", $_POST["year_amount"]);
    
    if (!$errors["forms"] || count($errors["forms"]) == 0) {
        $db->update("settings", [
            "from_date" => $_POST["from_date"],
            "from_year_privilege" => $year_privilege_arr[0] ? trim($year_privilege_arr[0]) : null,
            "to_year_privilege" => $year_privilege_arr[1] ? trim($year_privilege_arr[1]) : null,
            "from_year_amount" => $year_amount_arr[0] ? trim($year_amount_arr[0]) : null,
            "to_year_amount" => $year_amount_arr[1] ? trim($year_amount_arr[1]) : null,
            "min_date" => $_POST["min_date"] ? $_POST["min_date"] : null,
            // "to_date" => $_POST["to_date"],
        ], [
            "id" => $setting["id"]
        ]);
        
        header("Location: /editSetting?id=".$setting["id"]);
        exit;
    } else {
        // header("Content-type: text/plain");
        // print_r($errors);
        // exit;
    }
}


if ($_REQUEST["type"] == "deleteSetting") {
    $db->delete("settings", $setting["id"], "id");

    header("Location: /paymentsList");
    exit;
}

include "system/head.php";

$breadcump_title_1 = "Sozlama";
$breadcump_title_2 = "Sozlamani tahrirlash";
$form_title = date("Y-m-d", strtotime($setting["from_date"]))." gacha to'lovlarni faqat siz o'chirishingiz va tahrirlashingiz mumkin";
$form_title_privilege = $setting["from_year_privilege"].'-'.$setting["to_year_privilege"] . " o‘quv yili gacha imtiyozlar tahrirlash bloklangan";
$form_title_amount = $setting["from_year_amount"].'-'.$setting["to_year_amount"] . " o‘quv yili gacha shartnoma summasini tahrirlash bloklangan";
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
                    <div class="card-header d-grid">
                        <h4 class="card-title" style="text-transform:none;"><?=$form_title?></h4>
                        <h4 class="card-title my-3" style="text-transform:none;"><?=$form_title_privilege?></h4>
                        <h4 class="card-title" style="text-transform:none;"><?=$form_title_amount?></h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form action="/<?=$url[0]?>/?id=<?=$setting["id"]?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?=$setting["id"]?>">
                                <input type="hidden" name="type" value="<?=$url[0]?>">

                                <div class="form-row">
                                    <?=getError("from_date")?>
                                    <div class="form-group col-12">
                                        <label>Dan (sana)</label>
                                        <input type="date" name="from_date" class="form-control" value="<?=$setting["from_date"]?>">
                                    </div>
                                    
                                    <!-- <?=getError("from_year_privilege")?>
                                    <div class="form-group col-12">
                                        <label>Imtiyoz Dan (yil)</label>
                                        <input type="number" name="from_year_privilege" placeholder="YYYY" min="2021" class="form-control" value="<?=$setting["from_year_privilege"]?>">
                                    </div>
                                    
                                    <?=getError("to_year_privilege")?>
                                    <div class="form-group col-12">
                                        <label>Imtiyoz Gacha (yil)</label>
                                        <input type="number" name="to_year_privilege" placeholder="YYYY" min="2021" class="form-control" value="<?=$setting["to_year_privilege"]?>">
                                    </div> -->
                                    
                                    <div class="form-group col-12">
                                        <label>Imtiyoz summasini bloklash (gacha yil)</label>
                                        <select name="year_privilege" class="form-control default-select form-control-lg">
                                            <option value="">o'chirish</option>
                                            <? foreach ($years as $year) { 
                                                if(mb_strlen($year) > 3) {
                                            ?>
                                                <option value="<?=$year. '-'. ($year + 1)?>" <?=($year == $setting["from_year_privilege"] && ($year + 1) == $setting["to_year_privilege"] ? 'selected=""' : '')?> ><?=$year. ' - '. ($year + 1)?></option>
                                            <? } 
                                            } 
                                            ?>
                                        </select>
                                    </div>

                                    <hr>

                                    <?=getError("min_date")?>
                                    <div class="form-group col-12">
                                        <label>Minimal sana (shu sanagacha qaysi talaba kontark to'lovini 50% ni amalga oshirmasa, turniket tekshiruvidan o'ta olmaydi)</label>
                                        <input type="date" name="min_date" class="form-control" value="<?=$setting["min_date"]?>">
                                    </div>

                                    <hr>

                                    <div class="form-group col-12">
                                        <label>shartnoma summasini bloklash (gacha yil)</label>
                                        <select name="year_amount" class="form-control default-select form-control-lg">
                                            <option value="">o'chirish</option>
                                            <? foreach ($years as $year) { 
                                                if(mb_strlen($year) > 3) {
                                            ?>
                                                <option value="<?=$year. '-'. ($year + 1)?>" <?=($year == $setting["from_year_amount"] && ($year + 1) == $setting["to_year_amount"] ? 'selected=""' : '')?> ><?=$year. ' - '. ($year + 1)?></option>
                                            <? } 
                                            } 
                                            ?>
                                        </select>
                                    </div>
                                    
                                </div>

                                <div class="toolbar toolbar-bottom" role="toolbar" style="text-align: right;">
                                    <button type="submit" class="btn btn-primary">Saqlash</button>
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


<?
include "system/end.php";
?>