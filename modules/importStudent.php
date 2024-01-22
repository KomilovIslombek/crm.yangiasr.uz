<?php
include "db3.php";

function imageThumb($file_path, $file_folder) {
    global $db3, $db, $user_id;

    copy($file_path, $_SERVER["DOCUMENT_ROOT"] . "/" . $file_folder);

    // 1280px 720px
    $size = filesize($file_path);
    list($width, $height) = getimagesize($file_path);

    $image_id = $db->insert("images", [
        "creator_user_id" => $user_id,
        "width" => $width,
        "height" => $height,
        "size" => $size,
        "file_folder" => $file_folder,
    ]);
    return $image_id;
}

function importStudent($studentArr, $step = 2) {
    global $db3, $db, $user_id;

    // if (!empty($studentArr["student_id"])) {
    //     $student = $db3->assoc("SELECT * FROM requests WHERE id = ?", [ $studentArr["student_id"] ]);
    //     $student["year_of_admission"] = "2023";
    // } else if (!empty($studentArr["code"])) {
    //     $student = $db3->assoc("SELECT * FROM requests WHERE code = ?", [ $studentArr["code"] ]);
    //     $student["year_of_admission"] = "2023";
    // }

    if (!empty($studentArr["student_id"])) {
        if ($step == 1) {
            $student = $db3->assoc("SELECT * FROM requests_1 WHERE id = ?", [ $studentArr["student_id"] ]);
        } else if ($step == 2) {
            $student = $db3->assoc("SELECT * FROM requests_2 WHERE id = ?", [ $studentArr["student_id"] ]);
        } else if ($step == 3) {
            $student = $db3->assoc("SELECT * FROM requests WHERE id = ?", [ $studentArr["student_id"] ]);
        }
    } else if (!empty($studentArr["code"])) {
        if ($step == 1) {
            $student = $db3->assoc("SELECT * FROM requests_1 WHERE code = ?", [ $studentArr["code"] ]);
        } else if ($step == 2) {
            $student = $db3->assoc("SELECT * FROM requests_2 WHERE code = ?", [ $studentArr["code"] ]);
        } else if ($step == 3) {
            $student = $db3->assoc("SELECT * FROM requests WHERE code = ?", [ $studentArr["code"] ]);
        }
    }

    if ($step == 1) {
        $student["year_of_admission"] = "2022";
    } else if ($step == 2) {
        $student["year_of_admission"] = "2022";
    } else if ($step == 3) {
        $student["year_of_admission"] = "2023";
    }

    if (!empty($student["id"])) {
        $regions = [
            ["region_id" => 14, "name" => "Toshkent shahri"],
            ["region_id" => 11, "name" => "Toshkent viloyati"],
            ["region_id" => 2, "name" => "Andijon viloyati"],
            ["region_id" => 12, "name" => "Farg‘ona viloyati"],
            ["region_id" => 7, "name" => "Namangan viloyati"],
            ["region_id" => 3, "name" => "Buxoro viloyati"],
            ["region_id" => 4, "name" => "Jizzax viloyati"],
            ["region_id" => 5, "name" => "Qashqadaryo viloyati"],
            ["region_id" => 6, "name" => "Navoiy viloyati"],
            ["region_id" => 8, "name" => "Samarqand viloyati"],
            ["region_id" => 9, "name" => "Surxondaryo viloyati"],
            ["region_id" => 10, "name" => "Sirdaryo viloyati"],
            ["region_id" => 13, "name" => "Xorazm viloyati"],
            ["region_id" => 1, "name" => "Qoraqalpog'iston Respublikasi"]
        ];
        
        $region_id = NULL;
        foreach ($regions as $region) {
            if ($region["name"] == $student["region"]) $region_id = $region["region_id"];
        }
        
        $learn_type_id = NULL;
        $learn_type = $db3->assoc("SELECT id, name FROM learn_types WHERE name = ?", [ $student["learn_type"] ]);
        if (!empty($learn_type["id"])) $learn_type_id = $learn_type["id"];
        
        if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . "files/upload/3x4/")) {
            mkdir($_SERVER["DOCUMENT_ROOT"] . "/" . "files/upload/3x4/", 0777, true);
        }
        if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . "files/upload/passport/")) {
            mkdir($_SERVER["DOCUMENT_ROOT"] . "/" . "files/upload/passport/", 0777, true);
        }
        if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . "files/upload/diplom/")) {
            mkdir($_SERVER["DOCUMENT_ROOT"] . "/" . "files/upload/diplom/", 0777, true);
        }
        
        $image_id = $student["file_id_1"];
        $passport_image_id = $student["file_id_2"];
        $diplom_image_id = $student["file_id_3"];
        
        $image_file = $db3->assoc("SELECT * FROM files WHERE id = ?", [ $image_id ]);
        $passport_file = $db3->assoc("SELECT * FROM files WHERE id = ?", [ $passport_image_id ]);
        $diplom_file = $db3->assoc("SELECT * FROM files WHERE id = ?", [ $diplom_image_id ]);
        
        if ($image_file["id"]) {
            $image_file_path = "../yangiasr.uz/" . $image_file["file_folder"];
        
            if (file_exists($image_file_path)) {
                copy($image_file_path, $_SERVER["DOCUMENT_ROOT"] . "/" . $image_file["file_folder"]);
        
                if (file_exists($image_file_path . "_thumb.jpg")) {
                    $thumb_image_id1 = imageThumb($image_file_path . "_thumb.jpg", $image_file["file_folder"]  . "_thumb.jpg");
                    $image_file["thumb_image_id"] = $thumb_image_id1;
                }
        
                unset($image_file["id"]);
                $image_id = $db->insert("files", $image_file);
            } else {
                exit("image mavjud emas");
            }
        }
        
        if ($passport_file["id"]) {
            $passport_file_path = "../yangiasr.uz/" . $passport_file["file_folder"];
        
            if (file_exists($passport_file_path)) {
                copy($passport_file_path, $_SERVER["DOCUMENT_ROOT"] . "/" . $passport_file["file_folder"]);
        
                if (file_exists($passport_file_path . "_thumb.jpg")) {
                    $thumb_image_id2 = imageThumb($passport_file_path . "_thumb.jpg", $passport_file["file_folder"]  . "_thumb.jpg");
                    $passport_file["thumb_image_id"] = $thumb_image_id2;
                }
        
                unset($passport_file["id"]);
                $passport_image_id = $db->insert("files", $passport_file);
            }
        }
        
        if ($diplom_file["id"]) {
            $diplom_file_path = "../yangiasr.uz/" . $diplom_file["file_folder"];
            if (file_exists($diplom_file_path)) {
                copy($diplom_file_path, $_SERVER["DOCUMENT_ROOT"] . "/" . $diplom_file["file_folder"]);
        
                if (file_exists($diplom_file_path . "_thumb.jpg")) {
                    $thumb_image_id3 = imageThumb($diplom_file_path . "_thumb.jpg", $diplom_file["file_folder"]  . "_thumb.jpg");
                    $diplom_file["thumb_image_id"] = $thumb_image_id3;
                }
        
                unset($diplom_file["id"]);
                $diplom_image_id = $db->insert("files", $diplom_file);
            }
        }

        $learn_type = $db->assoc("SELECT * FROM learn_types WHERE id = ?", [ $learn_type_id ]);
        $direction = $db->assoc("SELECT * FROM directions WHERE id = ?", [ $student["direction_id"] ]);

        if ($learn_type["name"] == "Kunduzgi") {
            $annual_contract_amount = $direction["kunduzgi_narx"];
        } else if ($learn_type["name"] == "Kechki") {
            $annual_contract_amount = $direction["kechki_narx"];
        } else if ($learn_type["name"] == "Sirtqi") {
            $annual_contract_amount = $direction["sirtqi_narx"];
        } else {
            $annual_contract_amount = NULL;
        }

        $student_id = $db->insert("students", [
            "reg_type" => ($student["reg_type"] ? $student["reg_type"] : "oddiy"),
            "code" => $student["code"],
            "creator_user_id" => $user_id,
            "first_name" => $student["first_name"],
            "last_name" => $student["last_name"],
            "image_id" => $image_id,
            "father_first_name" => $student["father_first_name"],
            "birth_date" => $student["birth_date"],
            "sex" => $student["sex"],
            "direction_id" => $student["direction_id"],
            "learn_type_id" => $learn_type_id,
            "phone_1" => $student["phone_1"],
            "phone_2" => $student["phone_2"],
            "region_id" => $region_id,
            "address" => $student["region"],
            "nation" => $_POST["nation"],
            "course_id" => 1,
            "year_of_admission" => $student["year_of_admission"],
            "season" => $studentArr["season"],
            "contract_id" => $_POST["contract_id"],
            "annual_contract_amount" => $annual_contract_amount,
            "payment_method" => $_POST["payment_method"],
            "passport_serial_number" => $student["passport_serial_number"],
            "pinfl" => $_POST["pinfl"],
            "teacher_id" => $_POST["teacher_id"],
            "passport_image_id" => $passport_image_id,
            "diplom_image_id" => $diplom_image_id
        ]);

        if ($student_id > 0) {
            $student = $db->assoc("SELECT * FROM students WHERE id = ?", [ $student_id ]);
            return $student["id"] ? $student : false;
        } else {
            exit("Xatolik!");
        }
    } else {
        return false;
    }
}


// print_r([
//     "image_file" => $image_file,
//     "passport_file" => $passport_file,
//     "diplom_file" => $diplom_file
// ]);
// exit;
?>