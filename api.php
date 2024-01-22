<?
header("content-type: application/json");

foreach ($_REQUEST as $key => $val) {
    $_REQUEST[$key] = htmlspecialchars(trim($val));
}

$req = $_REQUEST;
$res = [];

$headers = getallheaders();
$token = trim(substr($headers['Authorization'], 6));
$user = explode(":", base64_decode($token));

// Our username and password
$user_name = "YangiAsr";
$user_pass = "2A6qvybiaLHUsQ6";

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

if ($req["method"] == "checkStudent" || $req["method"] == "checkStudents") {
    if ($user[0] != $user_name || $user[1] != $user_pass) {
        errorMessage("You are not authorized!");
    }
} else if (!$user_id || $user_id == 0) {
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

function getDebtorStudent($id, $course_id, $which_course = false)  {
    global $db;

    $student = $db->assoc("SELECT * FROM students WHERE id = ?", [ $id ]);
    $courseId = $course_id;
    $courseId2 = $courseId;
    if($courseId == 1) $courseId = '';
    
    // if (!empty($courseId)) {
    //     $queryCourse = "AND course_id = '" . $courseId . "'";
    // }

    // Tolangan summasi
    $tolangan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1 AND course_id = ? AND amount > 0 AND code = ?", [
        $courseId2, $student["code"]
    ])["SUM(amount)"];
    $student["tolangan_summa"] = $tolangan_summa;

    $qaytarilgan_summa = $db->assoc("SELECT SUM(amount) FROM payments WHERE 1=1 AND course_id = ? AND amount < 0 AND code = ?", [
        $courseId2, $student["code"]
    ])["SUM(amount)"];
    $student["qaytarilgan_summa"] = $qaytarilgan_summa;


    // Directions and Learn_types arr 
    $learn_types_arr = $db->in_array("SELECT * FROM learn_types");
    $learn_types = [];
    foreach ($learn_types_arr as $learn_type) {
        $learn_types[$learn_type["id"]] = $learn_type;
    }

    $directions_arr = $db->in_array("SELECT * FROM directions");
    $directions = [];
    foreach ($directions_arr as $direction) {
        $directions[$direction["id"]] = $direction;
    }

    $direction = $directions[$student["direction_id"]];
    $learn_type = $learn_types[$student["learn_type_id"]];
    
    $talaba_soni = 1;
    
    if ($learn_type["name"] == "Kunduzgi") {
        $kontrakt_summasi = $direction["kunduzgi_narx"];
    } else if ($learn_type["name"] == "Kechki") {
        $kontrakt_summasi = $direction["kechki_narx"];
    } else if ($learn_type["name"] == "Sirtqi") {
        $kontrakt_summasi = $direction["sirtqi_narx"];
    }

    if ($student["annual_contract_amount$courseId"] > 0) {
        $kontrakt_summasi = $student["annual_contract_amount$courseId"];
    }

    if ($course_id > $student["course_id"]) {
        $kontrakt_summasi = 0;
    }

    $tolov_miqdori = $talaba_soni * $kontrakt_summasi;

    $imtiyoz_summasi = 0;
    $privilege_percent = $student["privilege_percent$courseId"];
    
    $imtiyoz_summasi = $privilege_percent * ($kontrakt_summasi / 100);

    $tolangan_summa = $student["tolangan_summa"];

    $qaytarilgan_summa = abs($student["qaytarilgan_summa"]);
    
    if($tolangan_summa == $qaytarilgan_summa && $student["status"] == 0) {
        $qarzdorlik = 0;
        $kontrakt_summasi = 0;
    } else {
        
        $qarzdorlik = $kontrakt_summasi - $imtiyoz_summasi - ($tolangan_summa - $qaytarilgan_summa);
        if ($qarzdorlik == $kontrakt_summasi && $student["status"] == 0) {
            $qarzdorlik = 0;
            $kontrakt_summasi = 0;
        }
    }

    if ($qarzdorlik < 0 && $student["status"] == 0) {
        $qarzdorlik = 0;
        $kontrakt_summasi = 0;
        $tolangan_summa = 0;
        $qaytarilgan_summa = 0;
        $imtiyoz_summasi = 0;
    }
    if ($which_course) {
        $res["course_id"] = $student["course_id"];
        $res["courses"] = $coursesArr;
    }

    return [
        "qarzdorlik" => $qarzdorlik,
        "tolangan_summa" => ($student["tolangan_summa"] ? $student["tolangan_summa"] : 0),
        "kontrakt_summasi" => $kontrakt_summasi,
        "course_id" => $student["course_id"],
    ];
}

switch ($req["method"]) {
    case "checkStudents":
        $setting = $db->assoc("SELECT * FROM settings");

        $students = [];
        $students_arr = $db->in_array("SELECT code, first_name, last_name, father_first_name, course_id, card_number, image_id FROM students WHERE card_number IS NOT NULL AND NOT card_number = ''");

        foreach ($students_arr as $student) {
            $data = getDebtorStudent($student["code"], $student["course_id"], $req["which_course"]);
            $arr = [];

            if (strtotime($setting["min_date"]) < strtotime(date("Y-m-d"))) {
                // muddat o'tib ketgan
                $kontrakt_50 = ((int)$data["kontrakt_summasi"] / 100) * 50;

                if ($data["qarzdorlik"] > 0 && $kontrakt_50 >= $data["tolangan_summa"]) {
                    $arr["permission"] = false;
                } else {
                    $arr["permission"] = true;
                }
            } else {
                // muddat o'tib ketgani yo'q
            }

            // $arr["min_date"] = $setting["min_date"];

            $image = $db->assoc("SELECT * FROM files WHERE id = ?", [ $student["image_id"] ]);

            $arr["course_id"] = $data["course_id"];
            // $arr["qarzdorlik"] = $data["qarzdorlik"];
            // $arr["tolangan_summa"] = (int)$data["tolangan_summa"];
            // $arr["kontrakt_summasi"] = (int)$data["kontrakt_summasi"];
            // $arr["kontrakt_summasi_yarmi"] = (int)$kontrakt_50;
            $arr["student"] = [
                "code" => $student["code"],
                "first_name" => $student["first_name"],
                "last_name" => $student["last_name"],
                "father_first_name" => $student["father_first_name"],
                "card_number" => $student["card_number"],
                "student_image" => "https://crm.yangiasr.uz/".$image["file_folder"],
                "student_image_id" => $image["id"],
            ];

            array_push($students, $arr);
        }

        $res["data"] = $students;
        $res["ok"] = true;
    break;

    case "checkStudent":
        if (empty($req["student_code"])) validate([ "card_number" ]);
        if (empty($req["card_number"])) validate([ "code" ]);

        $setting = $db->assoc("SELECT * FROM settings");

        if (!empty($req["card_number"])) {
            $student = $db->assoc("SELECT * FROM students WHERE card_number = ?", [ $req["card_number"] ]);
        } else if (!empty($req["student_code"])) {
            $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $req["student_code"] ]);
        }

        if (!empty($student["code"])) {
            $data = getDebtorStudent($student["code"], $student["course_id"], $req["which_course"]);

            if (strtotime($setting["min_date"]) < strtotime(date("Y-m-d"))) {
                // muddat o'tib ketgan
                $kontrakt_50 = ((int)$data["kontrakt_summasi"] / 100) * 50;

                if ($data["qarzdorlik"] > 0 && $kontrakt_50 >= $data["tolangan_summa"]) {
                    $res["data"]["permission"] = false;
                } else {
                    $res["data"]["permission"] = true;
                }
            } else {
                // muddat o'tib ketgani yo'q
            }


            $db->insert("student_visits", [
                "student_code" => $student["code"],
                "student_card_number" => $student["card_number"],
                "type" => $req["type"],
                "visit_date" => $req["visit_date"],
                "permission" => ($res["data"]["permission"] ? "ok" : "false")
            ]);

            // $res["data"]["min_date"] = $setting["min_date"];

            $image = $db->assoc("SELECT * FROM files WHERE id = ?", [ $student["image_id"] ]);

            $res["data"]["course_id"] = $data["course_id"];
            // $res["data"]["qarzdorlik"] = $data["qarzdorlik"];
            // $res["data"]["tolangan_summa"] = (int)$data["tolangan_summa"];
            // $res["data"]["kontrakt_summasi"] = (int)$data["kontrakt_summasi"];
            // $res["data"]["kontrakt_summasi_yarmi"] = (int)$kontrakt_50;
            $res["data"]["student"] = [
                "code" => $student["code"],
                "first_name" => $student["first_name"],
                "last_name" => $student["last_name"],
                "father_first_name" => $student["father_first_name"],
                "card_number" => $student["card_number"],
                "student_image" => "https://crm.yangiasr.uz/".$image["file_folder"],
                "student_image_id" => $image["id"],
            ];

            $res["ok"] = true;
        } else {
            errorMessage("student not found!");
        }
    break;

    case "addToGroup":
        validateForms(["group_id", "student_code"]);

        if (!in_array("addGroup", $permissions) && !in_array("editGroup", $permissions)) {
            errorMessage("Sizda ushbu methodni amalga oshirish uchun huquq mavjud emas!");
        }

        $student = $db->assoc("SELECT * FROM students WHERE code = ?", [
            $req["student_code"]
        ]);

        if (!empty($student["code"])) {
            $db->update("students", [
                "group_id" => $req["group_id"]
            ], [
                "code" => $req["student_code"]
            ]);

            $res["ok"] = true;
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

            $db->update("students", [
                "group_id" => NULL
            ], [
                "code" => $req["student_code"]
            ]);
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
    //  Moodle filters Start

    case "addJoriy": 
        validate([ "course_id", "direction_id" ]);

        if($req["course_id"] && $req["direction_id"]) {
            $grade_item = $db4->insert("grade_items", [
                "courseid" => $req["course_id"],
                "categoryid" => $req["direction_id"],
                "itemname" => "Joriy nazorat",
                "itemtype" => "mod",
                "sortorder" => 10,
                "timecreated" => strtotime(date("Y-m-d h:i:s")),
            ]);

            if($grade_item) {
                $res["text"] = "Yang joriy nazorat qo'shildi";
                $res["ok"] = true;
            }
        } else {
            errorMessage("Kursni yoki yo'nalish mavjud emas");
        }
    break;

    case "filterSubjTeach":
        validateForms(["subject_id"]);
        $course = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $req["subject_id"] ]);
        
        $semester = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $course["category"] ]);
        $whereCourse = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $semester["parent"] ]);
        $learn_type = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $whereCourse["parent"] ]);
        $direction = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $learn_type["parent"] ]);

        if($course && $semester && $whereCourse ) {
            $res["semester"] = $semester;
            $res["whereCourse"] = $whereCourse;
            $res["learn_type"] = $learn_type;
            $res["direction"] = $direction;
            $res["ok"] = true;
        } else {
            errorMessage("Ta'lim shakli topilmadi");
        }
    break;

    case "filterGroup1":
        validateForms(["group_id"]);

        $cohort_users = $db4->in_array("SELECT * FROM cohort_members WHERE cohortid = ?", [ $req["group_id"] ]);
            
        $OriginalCohort_user;
        foreach ($cohort_users as $cohort_user) {
            $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $cohort_user["userid"] ]);
            $role_student = $db4->assoc("SELECT * FROM role_assignments WHERE userid = ?", [ $student["id"] ]);
            if($student["id"] && $role_student["roleid"] == 5) $OriginalCohort_user = $cohort_user;
        }

        $user_enrolments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $OriginalCohort_user["userid"] ]);

        $subjects = [];

        foreach ($user_enrolments as $user_enrolment) {
            $enrol_course = $db4->assoc("SELECT * FROM enrol WHERE id = ?", [ $user_enrolment["enrolid"] ] );
            $course = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $enrol_course["courseid"] ] );
            $semester = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $course["category"] ]);
            $whereCourse = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $semester["parent"] ]);

            $getCourse = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $req["course_id"] ]);
            $getSemester = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $req["semester_id"] ]);
            
            if($whereCourse["name"] == $getCourse["name"] && $semester["name"] == $getSemester["name"]) {
                array_push($subjects, $course);
            }
        }

        // foreach ($user_enrolments as $user_enrolment) {
        //     $enrol = $db4->assoc("SELECT * FROM enrol WHERE id = ?", [ $user_enrolment["enrolid"] ]);
        //     $enrol_course = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $enrol["courseid"] ]); // prosto tak
        //     // $course = $enrol_course;
        //     // break;
        //     $course_users = $db4->in_array("SELECT * FROM user_enrolments WHERE enrolid = ?", [ $enrol["id"] ]);

            
        //     // $userid = 0;
        //     foreach ($course_users as $course_user) {
        //         $role_student = $db4->assoc("SELECT * FROM role_assignments WHERE userid = ?", [ $course_user["userid"] ]);

        //         if($role_student["roleid"] == 5) {
        //             $userid = $course_user["userid"];    
        //             $cohort_member = $db4->assoc("SELECT * FROM cohort_members WHERE userid = ?", [ $course_user["userid"] ] );
            
        //             if($cohort_member["cohortid"] == $req["group_id"]) {
        //                 $course = $enrol_course;
        //                 break;
        //             } 
        //         }
                
        //     }

            
        // }
        
        if($subjects) {
            $res["subjects"] = $subjects;
            // $res["whereCourse"] = $whereCourse;
            // $res["learn_type"] = $learn_type;
            // $res["direction"] = $direction;
            $res["ok"] = true;
        } else {
            // $res["errorCourse"] = $course["id"];
            $res["ok"] = false;
            // errorMessage("Ta'lim shakli topilmadi");
        }
        // if($course["id"]) {
        //     $semester = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $course["category"] ]);
        //     $whereCourse = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $semester["parent"] ]);
        //     $learn_type = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $whereCourse["parent"] ]);
        //     $direction = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $learn_type["parent"] ]);
    
        //     $random_category = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $course["category"] ]);
        //     if($random_category["name"] == 'Kunduzgi' || $random_category["name"] == 'Kechki' || $random_category["name"] == 'Sirtqi') {
        //         $direction = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $random_category["parent"] ]);
        //         $learn_type = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $course["category"] ]);
        //         $whereCourse = $db4->assoc("SELECT * FROM course_categories WHERE parent = ?", [ $learn_type["id"] ]);
        //         $semester = $db4->assoc("SELECT * FROM course_categories WHERE parent = ?", [ $whereCourse["id"] ]);
        //     }
                
        //     if($direction["id"]) {
        //         $res["semester"] = $semester;
        //         $res["whereCourse"] = $whereCourse;
        //         $res["learn_type"] = $learn_type;
        //         $res["direction"] = $direction;
        //         $res["ok"] = true;
        //     } else {
        //         $res["errorCourse"] = $course["id"];
        //         $res["ok"] = false;
        //         // errorMessage("Ta'lim shakli topilmadi");
        //     }
        // }
    break;

    case "filterDirection":
        validateForms(["direction_id"]);

        $learn_types = $db4->in_array("SELECT id, name FROM course_categories WHERE parent = ? ORDER BY sortorder ASC", [ $req["direction_id"] ]);


        if($learn_types) {
            $res["learn_types"] = $learn_types;
            $res["ok"] = true;
        } else {
            errorMessage("Ta'lim shakli topilmadi");
        }
    break;
    
    case "filterLearnType":
        validateForms(["learn_type_id"]);

        $courses = $db4->in_array("SELECT id, name FROM course_categories WHERE parent = ? ORDER BY sortorder ASC", [ $req["learn_type_id"] ]);

        if($courses) {
            $res["courses"] = $courses;
            $res["ok"] = true;
        } else {
            errorMessage("Kurslar shakli topilmadi");
        }
    break;
    
    case "filterCourse":
        validateForms(["course_id"]);

        $semesters = $db4->in_array("SELECT id, name FROM course_categories WHERE parent = ? ORDER BY sortorder ASC", [ $req["course_id"] ]);

        if($semesters) {
            $res["semesters"] = $semesters;
            $res["ok"] = true;
        } else {
            errorMessage("Semester topilmadi");
        }
    break;

    case "filterSemester":
        // validateForms(["semester_id"]);
        
        $oldSubjects = $db4->in_array("SELECT id, shortname, fullname, startdate FROM course WHERE category = ? ORDER BY sortorder ASC", [ $req["semester_id"] ]);

        if($oldSubjects) {
            $subjects = [];
            foreach( $oldSubjects as $oldSubject ) {
                $date = date("Y-m-d", $oldSubject["startdate"]);
                $oldSubject["startdate"] = $date;
                array_push($subjects, $oldSubject);
            }
            $res["subjects"] = $subjects;
            $res["ok"] = true;
        } else {
            errorMessage("mavzu topilmadi");
        }
    break;

    case "filterSemester2":
        // validateForms(["semester_id"]);
        
         // $oldSubjects = $db4->in_array("SELECT id, shortname, fullname, startdate FROM course WHERE category = ? ORDER BY sortorder ASC", [ $req["semester_id"] ]);

         if($systemUser["role"] == "admin") {

             $cohort_users = $db4->in_array("SELECT * FROM cohort_members WHERE cohortid = ?", [ $req["group_id"] ]);
                
             $OriginalCohort_user;
             foreach ($cohort_users as $cohort_user) {
                 $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $cohort_user["userid"] ]);
                 $role_student = $db4->assoc("SELECT * FROM role_assignments WHERE userid = ?", [ $student["id"] ]);
                 if($student["id"] && $role_student["roleid"] == 5) $OriginalCohort_user = $cohort_user;
             }
     
             $user_enrolments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $OriginalCohort_user["userid"] ]);
     
             $oldSubjects = [];
     
             foreach ($user_enrolments as $user_enrolment) {
                 $enrol_course = $db4->assoc("SELECT * FROM enrol WHERE id = ?", [ $user_enrolment["enrolid"] ] );
                 $course = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $enrol_course["courseid"] ] );
                 $semester = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $course["category"] ]);
                 $whereCourse = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $semester["parent"] ]);
     
                 $getCourse = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $req["course_id"] ]);
                 $getSemester = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $req["semester_id"] ]);
                 
                 $noCourse = $db4->assoc("SELECT id, name FROM course_categories WHERE parent = ? ORDER BY sortorder ASC", [ $semester["id"] ]);
                 $noSemester = $db4->assoc("SELECT id, name FROM course_categories WHERE parent = ? ORDER BY sortorder ASC", [ $noCourse["id"] ]);
         
    
                 if($whereCourse["name"] == $getCourse["name"] && $semester["name"] == $getSemester["name"] || $noCourse["name"] == $getCourse["name"] && $noSemester["name"] == $getSemester["name"]) {
                     array_push($oldSubjects, $course);
                 }
             }
     
             if($oldSubjects) {
                 $subjects = [];
                 foreach( $oldSubjects as $oldSubject ) {
                     $date = date("Y-m-d", $oldSubject["startdate"]);
                     $oldSubject["startdate"] = $date;
                     array_push($subjects, $oldSubject);
                 }
                 $res["subjects"] = $subjects;
                 $res["ok"] = true;
             } else {
                 errorMessage("Fan topilmadi");
             }
         } else {
            if($systemUser["role"] == "teacher" && $systemUser["teacher_id"]) {
                $teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $systemUser["teacher_id"] ]);
                $moodle_teacher = $db4->assoc("SELECT * FROM user WHERE email = ?", [ $teacher["email"] ]);
                $user_enrolments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $moodle_teacher["id"] ]);
            } 

            if($systemUser["role"] == "student" && $systemUser["student_code"]){
                $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $systemUser["student_code"] ]);
                $moodle_student = $db4->assoc("SELECT * FROM user WHERE username = ?", [ $student["code"] ]);
                $user_enrolments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $moodle_student["id"] ]);
            }

            $oldSubjects = [];

            foreach ($user_enrolments as $user_enrolment) {
                if($systemUser["role"] == "teacher") {
                    $enrolment_user = $db4->assoc("SELECT * FROM user_enrolments WHERE enrolid = ? AND userid != ?", [ $user_enrolment["enrolid"], $user_enrolment["userid"] ]);
                    $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $enrolment_user["userid"] ]);
                } else {
                    $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $user_enrolment["userid"] ]);
                }
                $role_student = $db4->assoc("SELECT * FROM role_assignments WHERE userid = ?", [ $student["id"] ]);
                if($student["id"] && $role_student["roleid"] == 5) {
                    $cohort_member = $db4->assoc("SELECT * FROM cohort_members WHERE userid = ?", [ $student['id'] ]);
                    $cohort = $db4->assoc("SELECT * FROM cohort WHERE id = ?", [ $cohort_member["cohortid"] ]);
                    
                    $enrol_course = $db4->assoc("SELECT * FROM enrol WHERE id = ?", [ $user_enrolment["enrolid"] ] );
                    $course = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $enrol_course["courseid"] ] );
                    $semester = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $course["category"] ]);
                    $whereCourse = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $semester["parent"] ]);
        
                    $getCourse = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $req["course_id"] ]);
                    $getSemester = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $req["semester_id"] ]);
                    
                    $noCourse = $db4->assoc("SELECT id, name FROM course_categories WHERE parent = ? ORDER BY sortorder ASC", [ $semester["id"] ]);
                    $noSemester = $db4->assoc("SELECT id, name FROM course_categories WHERE parent = ? ORDER BY sortorder ASC", [ $noCourse["id"] ]);
            
    
                    if($whereCourse["name"] == $getCourse["name"] && $semester["name"] == $getSemester["name"] && $cohort["id"] == $req["group_id"] || $noCourse["name"] == $getCourse["name"] && $noSemester["name"] == $getSemester["name"] && $cohort["id"] == $req["group_id"]) {
                        array_push($oldSubjects, $course);
                    }
                }

            }

            if($oldSubjects) {
                $subjects = [];
                foreach( $oldSubjects as $oldSubject ) {
                    $date = date("Y-m-d", $oldSubject["startdate"]);
                    $oldSubject["startdate"] = $date;
                    array_push($subjects, $oldSubject);
                }
                $res["subjects"] = $subjects;
                $res["ok"] = true;
            } else {
                errorMessage("Fan topilmadi");
            }
         }
         
    break;

    // Moodle filters End

    case "filterGroup":
        validateForms(["group_id"]);

        $group_sciences = $db->in_array("SELECT * FROM group_sciences WHERE group_id = ?", [
            $req["group_id"]
        ]);

        if($group_sciences) {
            $sciences = [];
            foreach( $group_sciences as $group_science ) {
                $science = $db->assoc("SELECT * FROM sciences WHERE id = ?", [ $group_science["science_id"] ]);
                array_push($sciences, $science);
            }
            $res["sciences"] = $sciences;
            $res["ok"] = true;
        } else {
            errorMessage("Guruh topilmadi");
        }
    break;
    
    case "filterScience":
        validateForms(["science_id"]);
        
        if($systemUser["role"] == "admin" || $req["page"]) {
            $science_subjects = $db->in_array("SELECT * FROM science_subjects WHERE group_id = ? AND science_id = ? ORDER BY subject_date ASC", [
                $req["group_id"], $req["science_id"]
            ]);
        } else if($systemUser["role"] == "teacher" && !$req["page"]) {
            // $date = date("Y-m-d");
            // $science_subjects = $db->in_array("SELECT * FROM science_subjects WHERE group_id = ? AND science_id = ? AND subject_date = ?", [
            //     $req["group_id"], $req["science_id"], $date
            // ]);
            $science_subjects = $db->in_array("SELECT * FROM science_subjects WHERE group_id = ? AND science_id = ? ORDER BY subject_date ASC", [
                $req["group_id"], $req["science_id"]
            ]);
            // if(!$science_subjects)  {
            //     $science_subjects2 = $db->in_array("SELECT * FROM science_subjects WHERE group_id = ? AND science_id = ? AND subject_date != ?", [ 
            //         $req["group_id"], $req["science_id"], date("Y-m-d")
            //      ]); 
            // }
        } else if($systemUser["role"] == "student") {
            $date = date("Y-m-d");
            $science_subjects = $db->in_array("SELECT * FROM science_subjects WHERE group_id = ? AND science_id = ? AND subject_date <= ?", [
                $req["group_id"], $req["science_id"], $date
            ]);
        }

        if($science_subjects) {
                $subjects = [];
                foreach( $science_subjects as $science_subject ) {
                    $subject = $db->assoc("SELECT * FROM subjects WHERE id = ?", [ $science_subject["subject_id"] ]);
                    $subject["date"] = date("Y-m-d", strtotime($science_subject["subject_date"]));
                    array_push($subjects, $subject);
                }
                
                $res["subjects"] = $subjects;
                $res["ok"] = true;
        } else {
            // if($date && !$science_subjects) {
            //     if(!empty($science_subjects2)) {
            //         $res["text"] = "Bu fandi bugunga tegishli mavzu mavjud emas";
            //         $res["ok"] = false;
            //     } else {
                    errorMessage("Fan topilmadi");
            //     }
            // } else {
            //     errorMessage("Fan topilmadi");
            // }
        }
    break;

    // filterScience2

    case "filterScience2":
        validateForms(["science_id"]);
        
        $science_subjects = $db->in_array("SELECT * FROM science_subjects WHERE group_id = ? AND science_id = ?", [
            $req["group_id"], $req["science_id"]
        ]);

        if($science_subjects) {
            $subjects = [];
            foreach( $science_subjects as $science_subject ) {
                $subject = $db->assoc("SELECT * FROM subjects WHERE id = ?", [ $science_subject["subject_id"] ]);
                $subject["date"] = date("Y-m-d", strtotime($science_subject["subject_date"]));
                array_push($subjects, $subject);
            }
            $res["subjects"] = $subjects;
            $res["ok"] = true;
        } else {
            if(!empty($science_subjects)) {
                $res["text"] = "Bu fandi bugunga tegishli mavzu mavjud emas";
                $res["ok"] = false;
            } else {
                errorMessage("Fan topilmadi");
            }
        }
    break;

    // Filter science from subjects table 

    case "filterScienceFromSub":
        validate(["science_id"]);

        $subjects = $db->in_array("SELECT * FROM subjects WHERE science_id = ?", [ $req["science_id"] ]);

        if(!empty($subjects)) {
            $res["subjects"] = $subjects;
            $res["filtSubject_id"] = $req["subject_id"];
            $res["ok"] = true;
        } else {
            $res["ok"] = false;
        }
    break;
    
    case "filterSubject":
        validate(["science_id",]);

        $subject = $db->assoc("SELECT * FROM subjects WHERE id = ? AND science_id = ?", [ $req["subject_id"], $req["science_id"] ]);

        if(!empty($subject)) {
            $res["subject"] = $subject;
            $res["ok"] = true;
        } else {
            $res["ok"] = false;
        }
    break;
    
    case "calendar":
        if(empty($req["student_id"]) && $systemUser["role"] != "student" && $systemUser["role"] != "teacher" ) {

            $cohort_users = $db4->in_array("SELECT * FROM cohort_members WHERE cohortid = ?", [ $req["group_id"] ]);
            
            $OriginalCohort_user;
            foreach ($cohort_users as $cohort_user) {
                $student = $db4->assoc("SELECT * FROM user WHERE id = ? AND username != 'admin' ORDER BY id DESC", [ $cohort_user["userid"] ]);
                $role_student = $db4->assoc("SELECT * FROM role_assignments WHERE userid = ?", [ $student["id"] ]);
                if($student["id"] && $role_student["roleid"] == 5) $OriginalCohort_user = $cohort_user;
            }

            $user_enrolments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $OriginalCohort_user["userid"] ]);
            $our_events = [];
    
            foreach ($user_enrolments as $user_enrolment) {
                $enrol_course = $db4->assoc("SELECT * FROM enrol WHERE id = ?", [ $user_enrolment["enrolid"] ] );
                $course = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $enrol_course["courseid"] ] );
                $semester = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $course["category"] ]);
                $whereCourse = $db4->assoc("SELECT * FROM course_categories WHERE id = ?", [ $semester["parent"] ]);
    
                if($course["id"] && $whereCourse["name"] != '1-kurs') {
                $events = $db4->in_array("SELECT * FROM course_sections WHERE course = ? ORDER BY date ASC", [ $course["id"] ]);
                foreach ($events as $event) {
                    // $event["start"] = date("Y-m-d", $event["timestart"]);
                    if($event["name"] != '' || $event["name"] != null){
                        $event["start"] = $event["date"];
                        array_push($our_events, $event);
                    }
                }
                

            }
            }
            
            if($our_events) {
                $res["events"] = $our_events;
                $res["course"] = $course;
                $res["ok"] = true;   
            } else {
                $res["ok"] = false;
            }

        } else {
            if($systemUser["role"] == "student" && $systemUser["role"] != "teacher") {
                $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $systemUser["student_code"] ]);
                $moodle_student = $db4->assoc("SELECT * FROM user WHERE username = ?", [ $student["code"] ]);
                $enroments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $moodle_student["id"] ]);
            }  
            if($systemUser["role"] == "teacher" && $systemUser["role"] != "student"){
                $teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $systemUser["teacher_id"] ]);
                $moodle_teacher = $db4->assoc("SELECT * FROM user WHERE email = ?", [ $teacher["email"] ]);
                $enroments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $moodle_teacher["id"] ]);
            }

            // $subjects = [];
            $subject_id = 0;
            $our_events = [];
            foreach ($enroments as $enroment) { 
                $enrol = $db4->assoc("SELECT * FROM enrol WHERE id = ?", [ $enroment["enrolid"] ]);
                $subject = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $enrol["courseid"] ]);
                // $subject["id"] ? $subject_id = $subject["id"] : '';
                if($subject["id"]) {
                    $events = $db4->in_array("SELECT * FROM course_sections WHERE course = ? ORDER BY date ASC", [ $subject["id"] ]);
                    foreach ($events as $event) {
                        // $event["start"] = date("Y-m-d", $event["timestart"]);
                        if($event["name"] != '' || $event["name"] != null){
                            $event["start"] = $event["date"];
                            array_push($our_events, $event);
                        }
                    }
                    

                }
                // $subject["id"] ? array_push($subjects, $subject) : '';
            }
            
            if($our_events) {
                $res["events"] = $our_events;
                $res["course"] = $course;
                $res["ok"] = true;   
            } else {
                $res["ok"] = false;
            }
        }
    break;

    // addLessonVisit Api change
    
    case "addLessonVisit":
        validateForms(["student_id", "subject_date",  "status"]);

        // if (!in_array("addLessonVisits", $permissions) && !in_array("editLessonVisits", $permissions)) {
        //     errorMessage("Sizda ushbu methodni amalga oshirish uchun huquq mavjud emas!");
        // }
        
        $lesson_visit = $db->assoc("SELECT * FROM `lessons_visits` WHERE student_id = ? AND science_id = ? AND type_id = ? AND subject_id = ?", [ $req["student_id"], $req["science_id"], $req["type_id"], $req["subject_id"] ]);
        
        if(!$lesson_visit["id"]) {
            if($systemUser["role"] != "teacher") {
                $lesson_visit_id = $db->insert("lessons_visits", [
                    "creator_user_id" => $user_id,
                    "student_id" => $req["student_id"],
                    "science_id" => $req["science_id"],
                    "type_id" => $req["type_id"],
                    "subject_id" => $req["subject_id"],
                    "subject_date" => $req["subject_date"],
                    "status" => $req["status"],
                ]);
            } else if($req["subject_date"] && $systemUser["role"] == "teacher" && $req["subject_date"] == date("Y-m-d")) {
                $lesson_visit_id = $db->insert("lessons_visits", [
                    "creator_user_id" => $user_id,
                    "student_id" => $req["student_id"],
                    "science_id" => $req["science_id"],
                    "type_id" => $req["type_id"],
                    "subject_id" => $req["subject_id"],
                    "subject_date" => $req["subject_date"],
                    "status" => $req["status"],
                ]);
            } else if($systemUser["role"] != "student" && $req["subject_date"] == date("Y-m-d")) { 
                $lesson_visit_id = $db->insert("lessons_visits", [
                    "creator_user_id" => $user_id,
                    "student_id" => $req["student_id"],
                    "science_id" => $req["science_id"],
                    "type_id" => $req["type_id"],
                    "subject_id" => $req["subject_id"],
                    "subject_date" => $req["subject_date"],
                    "status" => $req["status"],
                ]);
            }
            if ($lesson_visit_id > 0) {
                $res["id"] = $req["element_id"];
                // $res["method"] = 'removeLessonVisit';
                if($req["status"] == "keldi") {
                    $res["simvol"] = '+';
                } else if($req["status"] == "kelmadi") {
                    $res["simvol"] = '-';
                } else if($req["status"] == "sababli") {
                    $res["simvol"] = '*';
                }
                $res["ok"] = true;
            } else {
                $res["ok"] = false;
            }
        } else {
            if($systemUser["role"] != "teacher") {
                $db->update("lessons_visits", [
                    "student_id" => $req["student_id"],
                    "science_id" => $req["science_id"],
                    "type_id" => $req["type_id"],
                    "subject_id" => $req["subject_id"],
                    "subject_date" => $req["subject_date"],
                    "status" => $req["status"],
                ], [
                    "id" => $lesson_visit["id"]
                ]);
            } else if($req["subject_date"] && $systemUser["role"] == "teacher" && $req["subject_date"] == date("Y-m-d")) {
                $db->update("lessons_visits", [
                    "student_id" => $req["student_id"],
                    "science_id" => $req["science_id"],
                    "type_id" => $req["type_id"],
                    "subject_id" => $req["subject_id"],
                    "subject_date" => $req["subject_date"],
                    "status" => $req["status"],
                ], [
                    "id" => $lesson_visit["id"]
                ]);
            } else if($systemUser["role"] != "student" && $req["subject_date"] == date("Y-m-d")) { 
                $db->update("lessons_visits", [
                    "student_id" => $req["student_id"],
                    "science_id" => $req["science_id"],
                    "type_id" => $req["type_id"],
                    "subject_id" => $req["subject_id"],
                    "subject_date" => $req["subject_date"],
                    "status" => $req["status"],
                ], [
                    "id" => $lesson_visit["id"]
                ]);
            }
            if($req["status"] == "keldi") {
                $res["simvol"] = '+';
            } else if($req["status"] == "kelmadi") {
                $res["simvol"] = '-';
            } else if($req["status"] == "sababli") {
                $res["simvol"] = '*';
            }
            $res["ok"] = true;
        }
    break;

    case "addRequestInCrm": 
      
        $db5->update("requests", [
            "code" => $req["code"]
        ], [
            "id" => $req["request_id"]
        ]);

        $res["ok"] = true;

    break;

    case "filterDebtorStudent":
        validateForms(["course_id", "id"]);

        $data = getDebtorStudent($req["id"], $req["course_id"], $req["which_course"]);

        // $res["course_id"] = $data["course_id"];

        if ($req["which_course"]) {
            $res["course_id"] = $data["course_id"];
        }
        $res["qarzdorlik"] = number_format($data["qarzdorlik"]);
        $res["tolangan_summa"] = number_format($data["tolangan_summa"]);
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