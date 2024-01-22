<!--**********************************
    Nav header start
***********************************-->
<div class="nav-header">
    <a href="/<?=$permissions[0]?>/?page=1" class="brand-logo">
        <div class="logo-abbr">
            <img src="../theme/vora/images/yangi-asr-logo.png" alt="logo icon" width="50px" height="52px">
        </div>
        <img class="logo-compact" src="theme/vora/images/logo-text-dark.png" alt="log text">
        <div class="brand-title">
            <img src="../theme/vora/images/logo-text-dark.png" width="150px" alt="">
        </div>
    </a>

    <div class="nav-control">
        <div class="hamburger">
            <span class="line"></span><span class="line"></span><span class="line"></span>
        </div>
    </div>
</div>
<!--**********************************
    Nav header end
***********************************-->

<!--**********************************
    Header start
***********************************-->
<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left">
                    <div class="dashboard_bar">
                        Boshqaruv paneli
                    </div>
                </div>
                <ul class="navbar-nav header-right">
                    <li class="nav-item">
                        <div class="input-group search-area d-lg-inline-flex d-none">
                            <div class="input-group-append">
                                <span class="input-group-text"><a href="javascript:void(0)"><i class="flaticon-381-search-2"></i></a></span>
                            </div>
                            <input type="text" class="form-control" placeholder="Qidirish..." id="search-input">
                        </div>
                    </li>
                    <li class="nav-item dropdown header-profile">
                        <a class="nav-link" href="javascript:void(0)" role="button" data-bs-toggle="dropdown">
                                <? if($systemUser["role"] == "admin") {?>
                                    <img src="https://niuedu.uz/profileimg.php?n=<?=mb_substr(mb_strtoupper($systemUser["last_name"]), 0, 1).mb_substr(mb_strtoupper($systemUser["first_name"]), 0, 1)?>&c=orange" width="20" alt=""/>
                                <?} else if($systemUser["role"] == "teacher" && $systemUser["teacher_id"] > 0) {?>
                                    <? 
                                        $systemTeacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $systemUser["teacher_id"] ]); 
                                        $teacher_image = fileArr($systemTeacher["image_id"]);
                                    ?>
                                    <img style="object-fit:cover;" src="<?=$teacher_image['file_folder']?>" width="20" alt=""/>
                                <? }  else if($systemUser["role"] == "student" && $systemUser["student_code"] > 0) {?>
                                    <? 
                                        $systemStudent = $db->assoc("SELECT * FROM students WHERE code = ?", [ $systemUser["student_code"] ]); 
                                        $student_image = fileArr($systemStudent["image_id"]);
                                    ?>
                                    <img style="object-fit:cover;" src="<?=$student_image['file_folder']?>" width="20" alt=""/>
                                <? } ?>
                            <div class="header-info">
                                <? if($systemUser["role"] == "admin") {?>
                                    <span class="text-black"><?=$systemUser["first_name"] . " " . $systemUser["last_name"]?></span>
                                <?} else if($systemUser["role"] == "teacher" && $systemUser["teacher_id"] > 0) {?>
                                    <? $systemTeacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $systemUser["teacher_id"] ]); ?>
                                    <span class="text-black"><?=$systemTeacher["first_name"] . " " . $systemTeacher["last_name"]?></span>
                                <? }  else if($systemUser["role"] == "student" && $systemUser["student_code"] > 0) {?>
                                    <? $systemStudent = $db->assoc("SELECT * FROM students WHERE code = ?", [ $systemUser["student_code"] ]); ?>
                                    <span class="text-black"><?=$systemStudent["first_name"] . " " . $systemStudent["last_name"]?></span>
                                <? } ?>
                                <p class="fs-12 mb-0"><?=$systemUser["role"]?></p>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <!-- <a href="/profile" class="dropdown-item ai-icon">
                                <svg id="icon-user1" xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                <span class="ms-2">Akkaunt </span>
                            </a> -->

                            <a href="/exit" class="dropdown-item ai-icon">
                                <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                <span class="ms-2">Akkauntdan chiqish </span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
<!--**********************************
    Header end ti-comment-alt
***********************************-->

<!--**********************************
    Sidebar start
***********************************-->
<div class="dlabnav">
    <div class="dlabnav-scroll">
        <ul class="metismenu" id="menu">
            <?php
            addMenu("flaticon-381-calendar-5", "Jurnal", [
                [
                    "name" => "Jurnal",
                    "page" => "journalList",
                    "href" => "/journalList"
                ],
                [
                    "name" => "Davomat",
                    "page" => "journalAttendance",
                    "href" => "/journalAttendance"
                ],
                [
                    "name" => "Nazorat ishlari",
                    "page" => "journalEvaluate",
                    "href" => "/journalEvaluate"
                ],
            ]);
             
            // addMenu("flaticon-381-bookmark-1", "Nazorat ishlar", [
            //     [
            //         "name" => "Nazorat ishlar ro'yxati",
            //         "page" => "controlWorksList",
            //         "href" => "/controlWorksList/?page=1"
            //     ],
            //     [
            //         "name" => "yangi nazorat ishi qo'shish",
            //         "page" => "addControlWork",
            //         "href" => "/addControlWork"
            //     ],
            // ]);
            
            addMenu("flaticon-381-user", "Shaxsiy kabinet", [
                [
                    "name" => "kabinet",
                    "page" => "personalArea",
                    "href" => "/personalArea"
                ]
            ]);
            
            // addMenu("flaticon-381-bookmark", "Topshiriqlar", [
            //     [
            //         "name" => "Topshiriqlar ro'yxati",
            //         "page" => "assignmentsList",
            //         "href" => "/assignmentsList/?page=1"
            //     ],
            //     [
            //         "name" => "yangi topshiriq qo'shish",
            //         "page" => "addAssignment",
            //         "href" => "/addAssignment"
            //     ],
            //     [
            //         "name" => "Topshiriqa berilgan javoblar",
            //         "page" => "answerAssignmentsList",
            //         "href" => "/answerAssignmentsList/?page=1"
            //     ],
            // ]);
            $setting = $db->assoc("SELECT * FROM `settings` ORDER BY id ASC");

            addMenu("flaticon-381-list", "To'lovlar", [
                [
                    "name" => "To'lovlar ro'yxati",
                    "page" => "paymentsList",
                    "href" => "/paymentsList/?page=1"
                ],
                [
                    "name" => "yangi to'lov qo'shish",
                    "page" => "addPayment",
                    "href" => "/addPayment"
                ],
                [
                    "name" => "To'lovlar ro'yxati sanalari",
                    "page" => "paymentsListDates",
                    "href" => "/paymentsListDates/?page=1"
                ],
                [
                    "name" => "Sozlamalar",
                    "page" => "editSetting",
                    "href" => "/editSetting?id=$setting[id]"
                ],
            ]);

            addMenu("flaticon-381-controls-9", "Statistika", [
                [
                    "name" => "Talabalar soni",
                    "page" => "studentsAmount",
                    "href" => "/studentsAmount/?page=1"
                ],
                [
                    "name" => "O'qituvchilar statistikasi",
                    "page" => "teachersStatistics",
                    "href" => "/teachersStatistics/?page=1"
                ],
                [
                    "name" => "Qarzdorlik ro'yxati",
                    "page" => "debtorsList",
                    "href" => "/debtorsList/?page=1"
                ],
                [
                    "name" => "Talabalar to'lovlari<br>statisikasi",
                    "page" => "studentsDebtorsList",
                    "href" => "/studentsDebtorsList/?page=1"
                ],
                [
                    "name" => "Qarzdorlik bo'yicha umumiy hisobot",
                    "page" => "generalDebtReport",
                    "href" => "/generalDebtReport/"
                ],
                [
                    "name" => "Talabalar to'lovlari davri",
                    "page" => "studentsPaymentsPeriod",
                    "href" => "/studentsPaymentsPeriod/"
                ]
            ]);

            
            addMenu("flaticon-381-calendar-6", "Kalendar", [
                [
                    "name" => "Kalendar",
                    "page" => "calendar",
                    "href" => "/calendar"
                ]
            ]);
            // addMenu("flaticon-381-picture", "Rasmlar", [
            //     [
            //         "name" => "rasmlar ro'yxati",
            //         "page" => "imagesList",
            //         "href" => "/imagesList/?page=1"
            //     ]
            // ]);

            addMenu("flaticon-381-user-9", "Talabalar", [
                [
                    "name" => "Talabalar ro'yxati",
                    "page" => "studentsList",
                    "href" => "/studentsList/?page=1"
                ],
                [
                    "name" => "yangi talaba qo'shish",
                    "page" => "addStudent",
                    "href" => "/addStudent"
                ],
                [
                    "name" => "Talabani import qilish (1-bosqich)",
                    "page" => "importStudent",
                    "href" => "/importStudent/1"
                ],
                [
                    "name" => "Talabani import qilish (2-bosqich)",
                    "page" => "importStudent",
                    "href" => "/importStudent/2"
                ],
                [
                    "name" => "Talabani import qilish (3-bosqich)",
                    "page" => "importStudent",
                    "href" => "/importStudent/3"
                ],
                [
                    "name" => "Arxiv talabalar ro'yxati",
                    "page" => "archiveStudentsList",
                    "href" => "/archiveStudentsList/?page=1"
                ],
                // [
                //     "name" => "Moodle dan topilmaganlar",
                //     "page" => "noFoundStudents",
                //     "href" => "/noFoundStudents/?page=1"
                // ],
            ]);

            addMenu("flaticon-381-layer-1", "Guruhlar", [
                [
                    "name" => "Guruhlar ro'yxati",
                    "page" => "groupsList",
                    "href" => "/groupsList/?page=1"
                ],
                [
                    "name" => "yangi guruh qo'shish",
                    "page" => "addGroup",
                    "href" => "/addGroup"
                ],
            ]);

            // addMenu("flaticon-381-layer", "Kurslar", [
            //     [
            //         "name" => "Kurslar ro'yxati",
            //         "page" => "coursesList",
            //         "href" => "/coursesList/?page=1"
            //     ],
            // ]);

            addMenu("flaticon-381-user-8", "O'qituvchilar", [
                [
                    "name" => "O'qituvchilar ro'yxati",
                    "page" => "teachersList",
                    "href" => "/teachersList/?page=1"
                ],
                [
                    "name" => "yangi o'qituvchi qo'shish",
                    "page" => "addTeacher",
                    "href" => "/addTeacher"
                ],
            ]);

            addMenu("flaticon-381-notepad-2", "Fanlar", [
                [
                    "name" => "Fanlar ro'yxati",
                    "page" => "sciencesList",
                    "href" => "/sciencesList/?page=1"
                ],
                [
                    "name" => "yangi fan qo'shish",
                    "page" => "addScience",
                    "href" => "/addScience"
                ],
            ]);

            addMenu("flaticon-381-notepad-2", "Turlar", [
                [
                    "name" => "Turlar ro'yxati",
                    "page" => "typesList",
                    "href" => "/typesList/?page=1"
                ],
                [
                    "name" => "yangi tur qo'shish",
                    "page" => "addType",
                    "href" => "/addType"
                ],
            ]);

            // addMenu("flaticon-381-notepad", "Mavzular", [
            //     [
            //         "name" => "Mavzular ro'yxati",
            //         "page" => "subjectsList",
            //         "href" => "/subjectsList/?page=1"
            //     ],
            //     [
            //         "name" => "yangi mavzu qo'shish",
            //         "page" => "addSubject",
            //         "href" => "/addSubject"
            //     ]
            // ]);
            
            addMenu("flaticon-381-notepad", "Kafedralar", [
                [
                    "name" => "Kafedralar ro'yxati",
                    "page" => "departmentsList",
                    "href" => "/departmentsList/?page=1"
                ],
                [
                    "name" => "yangi kafedra qo'shish",
                    "page" => "addDepartment",
                    "href" => "/addDepartment"
                ],
            ]);

            addMenu("flaticon-381-user-2", "Xodimlar", [
                [
                    "name" => "Xodimlar ro'yxati",
                    "page" => "employeesList",
                    "href" => "/employeesList/?page=1"
                ],
                [
                    "name" => "yangi xodim qo'shish",
                    "page" => "addEmployee",
                    "href" => "/addEmployee"
                ],
            ]);
            ?>
        </ul>
    </div>
</div>
<!--**********************************
    Sidebar end
***********************************-->