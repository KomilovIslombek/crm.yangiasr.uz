<?
header("content-type: application/json");
$req = $_REQUEST;
$res = [];

function errorMessage($error) {
    $res_text = json_encode([
        "ok" => false,
        "error" => $error
    ]);

    apiLog($res_text);

    return exit(
        $res_text
    );
}

function getToken($token) {
    global $db;

    return $db->assoc("SELECT * FROM tokens WHERE token = ?", [
        $token
    ]);
}

function createToken($user_id, $callback = 0) {
    global $db, $env;

    if ($callback > 2) {
        exit("error!");
    }

    $token = bin2hex(openssl_random_pseudo_bytes("14")).uniqid();
    $tokenArr = getToken($token);

    if (!empty($tokenArr["token"])) {
        return createToken($user_id, $callback + 1);
    } else {
        $db->insert("tokens", [
            "user_id" => $user_id,
            "token" => $token,
            "ip" => $env->getIp(),
            "ip_via_proxy" => $env->getIpViaProxy(),
            "browser" => $env->getUserAgent()
        ]);

        $tokenArr = getToken($token);

        if ($tokenArr["token"]) {
            return $token;
        } else {
            return createToken($user_id, $callback + 1);
        }
    }
}

function validateForms($forms) {
    global $req;
    foreach ($forms as $form) {
        if (!isset($req[$form])) errorMessage("$form is empty");
    }
}

validateForms(["method"]);

if (!$user_id || $user_id == 0) {
    errorMessage("You are not authorized");
}

function apiLog($res) {
    global $db, $user_id, $req, $env;

    $db->insert("api_requests", [
        "user_id" => $user_id,
        "req" => json_encode($req, JSON_UNESCAPED_UNICODE),
        "res" => $res,
        "ip" => $env->getIp()
    ]);
}

switch ($req["method"]) {
    case "addToGroup":
        validateForms(["group_id", "student_code"]);

        if (!in_array("addGroup", $permissions) && !in_array("editGroup", $permissions)) {
            errorMessage("Sizda ushbu methodni amalga oshirish uchun huquq mavjud emas!");
        }

        $student = $db->assoc("SELECT * FROM students WHERE code = ?", [
            $req["student_code"]
        ]);

        if (!empty($student["code"])) {
            $group_user = $db->assoc("SELECT * FROM group_users WHERE student_code = ?", [ $student["code"] ]);
            if (!empty($group_user["id"])) {
                errorMessage("This user has in group_user");
                exit;
            }

            // foreach($db->in_array("SELECT * FROM group_users") as $group_user) {
            //     if($group_user['student_code'] == $student["code"]) {
            //         errorMessage("This user has in group_user");
            //         exit;
            //     } 
            // }
            $db->delete("group_users", $student["code"], "student_code");
            $group_user_id = $db->insert("group_users", [
                "creator_user_id" => $user_id,
                "group_id" => $req["group_id"],
                "student_code" => $student["code"]
            ]);

            if ($group_user_id > 0) {
                $res["ok"] = true;
                $res["group_user_id"] = $group_user_id;
            } else {
                errorMessage("student not inserted to group");
            }
        } else {
            errorMessage("student not found");
        }
    break;

    // removeInGroup group_users

    case "removeInGroup":
        validateForms([ "student_code"]);

            if (!in_array("addGroup", $permissions) && !in_array("editGroup", $permissions)) {
                errorMessage("Sizda ushbu methodni amalga oshirish uchun huquq mavjud emas!");
            }

            $db->delete("group_users", $req["student_code"], "student_code");
            $res["ok"] = true;

    break;


    // Teachers Api change
        
    case "addTeachers":
        validateForms(["group_id", "teacher_id"]);

        if (!in_array("addGroup", $permissions) && !in_array("editGroup", $permissions)) {
            errorMessage("Sizda ushbu methodni amalga oshirish uchun huquq mavjud emas!");
        }

        $teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [
            $req["teacher_id"]
        ]);

        if (!empty($teacher["id"])) {
            // foreach($db->in_array("SELECT * FROM group_teachers") as $group_teacher) {
            //     if($group_teacher['teacher_id'] == $teacher["id"]) {
            //         errorMessage("This teacher has in group");
            //         exit;
            //     } 
            // }
            $group_teacher_id = $db->insert("group_teachers", [
                "creator_user_id" => $user_id,
                "group_id" => $req["group_id"],
                "teacher_id" => $teacher["id"]
            ]);

            if ($group_teacher_id > 0) {
                $res["ok"] = true;
                $res["group_teacher_id"] = $group_teacher_id;
            } else {
                errorMessage("Teacher not inserted to group");
            }
        } else {
            errorMessage("Teacher not found");
        }
    break;

    // removeInGroup group_teachers

    case "removeInGroupTeachers":
        validateForms(["teacher_id", "group_id"]);
                 
        if (!in_array("addGroup", $permissions) && !in_array("editGroup", $permissions)) {
            errorMessage("Sizda ushbu methodni amalga oshirish uchun huquq mavjud emas!");
        }
            $group_teacher = $db->assoc("SELECT * FROM `group_teachers` WHERE group_id = ? AND teacher_id = ?", [ $req["group_id"], $req["teacher_id"] ]);

            $db->delete("group_teachers",  $group_teacher["id"]);
            $res["ok"] = true;
           

    break;


    // Sciences Api change
    
    case "addSciences":
        validateForms(["group_id", "science_id"]);

        if (!in_array("addGroup", $permissions) && !in_array("editGroup", $permissions)) {
            errorMessage("Sizda ushbu methodni amalga oshirish uchun huquq mavjud emas!");
        }

        $science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [
            $req["science_id"]
        ]);

        if (!empty($science["id"])) {
            // foreach($db->in_array("SELECT * FROM group_sciences") as $group_science) {
            //     if($group_science['science_id'] == $science["id"]) {
            //         errorMessage("This science has in group");
            //         exit;
            //     } 
            // }
            $group_science_id = $db->insert("group_sciences", [
                "creator_user_id" => $user_id,
                "group_id" => $req["group_id"],
                "science_id" => $science["id"]
            ]);

            if ($group_science_id >= 0) {
                $res["ok"] = true;
                $res["group_science_id"] = $group_science_id;
            } else {
                errorMessage("Sciences not inserted to group");
            }
        } else {
            errorMessage("Sciences not found");
        }
    break;

    // removeInGroup group_sciences

    case "removeInGroupSciences":
        validateForms([ "science_id", "group_id"]);

            if (!in_array("addGroup", $permissions) && !in_array("editGroup", $permissions)) {
                errorMessage("Sizda ushbu methodni amalga oshirish uchun huquq mavjud emas!");
            }

            $group_science = $db->assoc("SELECT * FROM `group_sciences` WHERE group_id = ? AND science_id = ?", [ $req["group_id"], $req["science_id"] ]);

            $db->delete("group_sciences", $group_science["id"]);
            $res["ok"] = true;

    break;

    default:
        errorMessage("this method not found!");
}


if ($res) {
    $res_text = json_encode($res, JSON_UNESCAPED_UNICODE);
    apiLog($res_text);
    exit($res_text);
}
?>