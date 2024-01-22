<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : null;
if (!$id) {echo"error id not found";return;}

$science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [$id]);
if (!$science["id"]) {echo"error (science not found)";exit;}

$department_science = $db->assoc("SELECT * FROM department_sciences WHERE science_id = ?", [ $science["id"] ]);
$department = $db->assoc("SELECT * FROM departments WHERE id = ?", [ $department_science["department_id"] ]);

$academic_hour = $science["lecture_hour"] + $science["practica_hour"] + $science["nation_education"];
$ects = $academic_hour / 30;

// html to pdf module
include $_SERVER["DOCUMENT_ROOT"]."/modules/wkhtmltopdf/vendor/autoload.php";
use mikehaertl\wkhtmlto\Pdf;

$months = [
    "Yanvar",
    "Fevral",
    "Mart",
    "Aprel",
    "May",
    "Iyun",
    "Iyul",
    "Avgust",
    "Sentabr",
    "Oktabr",
    "Noyabr",
    "Dekabr",
];

$html = file_get_contents("doc.html");

$html = strtr($html, [ 
    "#day" => date("d"),
    "#month" => $months[(date("m") - 1)],
    "#year" => date("Y"),
    "#scienceCode" => $science["code"],
    "#scienceName1" => strtoupper($science["name"]),
    "#scienceName" => $science["name"],
    "#4ects" => number_format($ects, 2, ".", ""),
    "#5allAcademicHour" => $academic_hour,
    "#6lecture_hour" => $science["lecture_hour"],
    "#7practica_hour" => $science["practica_hour"],
    "#8nation_eduaction" => $science["nation_education"],
    "#9eduaction_direction" => $department["name"],
    "#10semester" => 1,
    "#12creator" => $systemUser["first_name"]. " " .$systemUser["last_name"]. " " . $systemUser["father_first_name"],
]);
// echo $html;

$pdf = new Pdf($html);

if (!$pdf->send()) {
    $error = $pdf->getError();
}

?>