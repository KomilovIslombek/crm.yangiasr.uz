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

// if($systemUser["role"] == "teacher") {
//     $group_teacher = $db->assoc("SELECT * FROM group_teachers WHERE teacher_id = ?", [ $systemUser["teacher_id"] ]);
//     if(!$group_teacher["group_id"]) {
//         $query .= " AND group_id = " . 0;
//     }
// }


$courseCategories = $db4->in_array("SELECT * FROM course_categories ORDER BY id ASC LIMIT $page_start, $page_count");

include "system/head.php";

$breadcump_title_1 = "Topshiriqlar";
$breadcump_title_2 = "Topshiriqlar ro'yxati";
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
            <!-- Column starts -->
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-block">
                        <h4 class="card-title">Kurslar</h4>
                    </div>
                    <div class="card-body p-0">
                        <? foreach ($courseCategories as $courseCategorie) {
                        ?>
                            <div class="accordion" id="accordionPanelsStayOpenExample">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="<?=$courseCategorie["id"]?>">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                                        <?=$courseCategorie["name"]. "  ". $courseCategorie["id"]?>
                                    </button>
                                    </h2>
                                    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="<?=$courseCategorie["id"]?>">
                                    <div class="accordion-body p-0">
                                        
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="panelsStayOpen-headingTwo">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                                                Accordion Item #2
                                            </button>
                                            </h2>
                                            <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingTwo">
                                            <div class="accordion-body">
                                                <strong>This is the second item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                                            </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                                                Accordion Item #3
                                            </button>
                                            </h2>
                                            <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingThree">
                                            <div class="accordion-body">
                                                <strong>This is the third item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                                            </div>
                                            </div>
                                        </div>
                                            
                                    </div>
                                    </div>
                                </div>
                            </div>
                        <? } ?>
                    </div>
                </div>
            </div>
            <!-- Column ends -->
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