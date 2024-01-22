<?php
/* 
    <[Funksiyadan foydalanish bo'yicha qo'llanma]>

    $file = $_FILES["file] * Required
    $fileFolder = "files/news" * Required
    allowedFileTypes = ["jpg","jpeg","png"] * Required
    $additionalFileSize = [ * Optional
        "width" => 512, * Required
        "height" => 512 * Required
    ]
*/

function uploadFile($fileKey, $fileFolder, $allowedFileTypes, $additionalFileSize = false, $required = true) {
    global $errors, $db, $user_id;

    $file = $_FILES[$fileKey];

    $res = [];
    $errorsArr = [];

    if ($file["size"] != 0){
        if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$fileFolder)) {
            mkdir($_SERVER["DOCUMENT_ROOT"]."/".$fileFolder, 0777, true);
        }
    
        $file = $file;
        $random_name = date("Y-m-d-H-i-s").'_'.md5(time().rand(0, 10000000000000000));
        $file["type"] = strtr($file["type"], [
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => "docx",
            "application/msword" => "doc",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "xlsx",
            "application/vnd.openxmlformats-officedocument.presentationml.presentation" => "pptx",
            "text/plain" => "txt",
        ]);
        $file_type = basename($file["type"]);
        $file_folder = $fileFolder . "/" . $random_name . ".$file_type";
        $file_path = $_SERVER["DOCUMENT_ROOT"] . "/" . $file_folder;
        $uploadOk = 1;
        $file_type = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
        if (file_exists($file_path)) {
            array_push($errorsArr, "Kechirasiz, fayl allaqachon mavjud.");
            $uploadOk = 0;
        }
        if ($file["size"] > 5000000) {
            array_push($errorsArr, "Kechirasiz, sizning faylingiz juda katta.");
            $uploadOk = 0;
        }
        if (!in_array($file_type, $allowedFileTypes)) {
            array_push($errorsArr, "Siz yuklamoqchi bo'lgan format '$file[type]' Kechirasiz, faqat ".implode(" ", $allowedFileTypes)." fayllarni yuklashga ruxsat berilgan.");
            $uploadOk = 0;
        }
        if ($uploadOk == 0) {
            // array_push($errorsArr, "Kechirasiz, sizning faylingiz yuklanmadi.");
        } else {
            if (move_uploaded_file($file["tmp_name"], $file_path)) {
                $size = filesize($file_path);
                // list($width, $height) = getfilesize($file_path);
    
                $file_id = $db->insert("files", [
                    "creator_user_id" => $user_id,
                    "type" => $file_type,
                    "size" => $size,
                    "file_folder" => $file_folder,
                ]);
    
                if ($file_id > 0) {
                    $res["file_id"] = $file_id;
                } else {
                    array_push(
                        $errorsArr,
                        "Rasmni bazaga yozishda xatolik yuzaga keldi"
                    );
                    return;
                }
            } else {
                array_push($errorsArr, "Kechirasiz, faylingizni yuklashda xatolik yuz berdi.");
            }
        }
    } else {
        if ($required) array_push($errorsArr, "faylni tanlashni unutdingiz!");
    }

    if (count($errorsArr) > 0) $errors["forms"][$fileKey] = $errorsArr;
    return $res;
}

function uploadFileWithUpdate($fileKey, $fileFolder, $allowedFileTypes, $additionalFileSize = false, $required = true, $file_id_for_change) {
    $uploadedFile = uploadFile($fileKey, $fileFolder, $allowedFileTypes, $additionalFileSize, $required);

    $file_id = $file_id_for_change;
    $new_file_id = $uploadedFile["file_id"];

    $res["file_id"] = $file_id_for_change;

    if ($new_file_id > 0) {
        delete_file($file_id);
        $file_id = $new_file_id;
        $res["file_id"] = $file_id;
    }

    return $res;
}
?>