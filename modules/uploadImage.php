<?php
/* 
    <[Funksiyadan foydalanish bo'yicha qo'llanma]>

    $file = $_FILES["image] * Required
    $imageFolder = "images/news" * Required
    allowedFileTypes = ["jpg","jpeg","png"] * Required
    $additionalImageSize = [ * Optional
        "width" => 512, * Required
        "height" => 512 * Required
    ]
*/

function uploadImage($fileKey, $imageFolder, $allowedFileTypes, $additionalImageSize = false, $required = true) {
    global $errors, $db, $user_id;

    $file = $_FILES[$fileKey];

    $res = [];
    $errorsArr = [];

    if ($file["size"] != 0){
        if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$imageFolder)) {
            mkdir($_SERVER["DOCUMENT_ROOT"]."/".$imageFolder, 0777, true);
        }
    
        $file = $file;
        $random_name = date("Y-m-d-H-i-s").'_'.md5(time().rand(0, 10000000000000000));
        $file_type = basename($file["type"]);
        $file_folder = $imageFolder . "/" . $random_name . ".$file_type";
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
            array_push($errorsArr, "Kechirasiz, faqat ".implode(" ", $allowedFileTypes)." fayllarni yuklashga ruxsat berilgan.");
            $uploadOk = 0;
        }
        if ($uploadOk == 0) {
            // array_push($errorsArr, "Kechirasiz, sizning faylingiz yuklanmadi.");
        } else {
            if (move_uploaded_file($file["tmp_name"], $file_path)) {
                $size = filesize($file_path);
                list($width, $height) = getimagesize($file_path);
    
                $image_id = $db->insert("images", [
                    "creator_user_id" => $user_id,
                    "width" => $width,
                    "height" => $height,
                    "size" => $size,
                    "file_folder" => $file_folder,
                ]);
    
                if ($image_id > 0) {
                    $res["image_id"] = $image_id;

                    if ($additionalImageSize) {
                        $small_file_folder = $imageFolder . $random_name . "2" . ".$file_type";
                        $pic = new Imagick($_SERVER['DOCUMENT_ROOT'] . "/$file_folder");
                        $pic->resizeImage(
                            $additionalImageSize["width"],
                            $additionalImageSize["height"],
                            Imagick::FILTER_LANCZOS,
                            1
                        );
                        $pic->writeImage($_SERVER['DOCUMENT_ROOT'] . "/$small_file_folder");
                        $pic->destroy();
                        $small_size = filesize($_SERVER['DOCUMENT_ROOT'] . "/$small_file_folder");
                        
                        $additional_image_id = $db->insert("images", [
                            "creator_user_id" => $user_id,
                            "width" => $additionalImageSize["width"],
                            "height" => $additionalImageSize["height"],
                            "size" => $small_size,
                            "file_folder" => $small_file_folder,
                        ]);

                        if ($additional_image_id > 0) {
                            $res["additional_image_id"] = $additional_image_id;
                        } else {
                            array_push(
                                $errorsArr,
                                "Faylni kichraytirish jarayonida xatolik yuzaga keldi!"
                            );
                        }
                    }
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

function uploadImageWithUpdate($fileKey, $imageFolder, $allowedFileTypes, $additionalImageSize = false, $required = true, $image_id_for_change) {
    $uploadedImage = uploadImage($fileKey, $imageFolder, $allowedFileTypes, $additionalImageSize, $required);

    $image_id = $image_id_for_change;
    $new_image_id = $uploadedImage["image_id"];

    if ($new_image_id > 0) {
        delete_image($image_id);
        $image_id = $new_image_id;
        $res["image_id"] = $image_id;
    }

    return $res;
}
?>