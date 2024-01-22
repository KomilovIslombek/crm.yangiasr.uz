<?
$is_config = true;
if (empty($load_defined)) include 'load.php';

if (isAuth() === false) {
    header("Location: /login");
    exit;
}

$page = (int)$_GET['page'];
if (empty($page)) $page = 1;

if($systemUser["role"] == "admin") {
    $groups = $db4->in_array("SELECT * FROM cohort");
}

if($systemUser["role"] == "teacher" && $systemUser["teacher_id"]) {
    $subjects = [];
    $teacher = $db->assoc("SELECT * FROM teachers WHERE id = ?", [ $systemUser["teacher_id"] ]);
    $moodle_teacher = $db4->assoc("SELECT * FROM user WHERE email = ?", [ $teacher["email"] ]);
    $enroments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $moodle_teacher["id"] ]);
    foreach ($enroments as $enroment) {
        $enrol = $db4->assoc("SELECT * FROM enrol WHERE id = ?", [ $enroment["enrolid"] ]);
        $subject = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $enrol["courseid"] ]);
        array_push($subjects, $subject);
    }
} 

if($systemUser["role"] == "student" && $systemUser["student_code"]){
    $subjects = [];
    $student = $db->assoc("SELECT * FROM students WHERE code = ?", [ $systemUser["student_code"] ]);
    $moodle_student = $db4->assoc("SELECT * FROM user WHERE username = ?", [ $student["code"] ]);
    $enroments = $db4->in_array("SELECT * FROM user_enrolments WHERE userid = ?", [ $moodle_student["id"] ]);
    foreach ($enroments as $enroment) {
        $enrol = $db4->assoc("SELECT * FROM enrol WHERE id = ?", [ $enroment["enrolid"] ]);
        $subject = $db4->assoc("SELECT * FROM course WHERE id = ?", [ $enrol["courseid"] ]);
        array_push($subjects, $subject);
    }
}

include "system/head.php";

$breadcump_title_1 = "Kalendar";
$breadcump_title_2 = "Kurslar ro'yxati";
?>

    <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">  
			<div class="container-fluid">
                <!-- start Filter -->
                <? if($systemUser["role"] != "student" && $systemUser["role"] != "teacher") { ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">

                                    <form action="/<?=$url[0]?>" method="GET" id="filter">
                                        <div class="basic-form row d-flex align-items-center">
                                            <!-- <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                                <label>Fanlar:</label>
                                                <select name="subject_id" id="subject_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                        <? 
                                                            foreach ($subjects as $subject) { 
                                                        ?>
                                                                <option
                                                                    value="<?=$subject["id"]?>"
                                                                    <?=($_REQUEST["subject_id"] == $subject["id"] ? 'selected=""' : '')?>
                                                                    data-subtext="<?=$subject["id"]?>"
                                                                > <?=$subject["fullname"]?></option>
                                                        <? } ?>
                                                </select>
                                            </div> -->
                                            <div class="form-group col-xl-4 col-lg-4 col-sm-6 col-12">
                                                <label>Guruhlar:</label>
                                                <select name="group_id" id="group_id" data-live-search="true" class="refresh mt-2 btn-sm form-control default-select form-control-lg no-overflow" data-actions-box="true">
                                                        <? 
                                                            foreach ($groups as $group) { 
                                                        ?>
                                                                <option
                                                                    value="<?=$group["id"]?>"
                                                                    <?=($_REQUEST["group_id"] == $group["id"] ? 'selected=""' : '')?>
                                                                > <?=$group["name"]?></option>
                                                        <? } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <? } ?>
                <!-- end Filter -->
				<div class="row">
					<div class="col-12">
						<div class="row">
							<div class="col-xl-12">
								<div class="card">
									<div class="card-body">
										<div id="calendar" class="app-fullcalendar dashboard-calendar"></div>
									</div>
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

        <style>
            .fc-h-event { /* allowed to be top-level */
                display: block;
                border: 1px solid #ef741f !important;
                border: 1px solid #ef741f !important;
                background-color: #ef741f !important ;
                background-color: #ef741f !important ;
            }
        </style>
        
<?
include "system/scripts.php";
    if($systemUser["role"] != "student" && $systemUser["role"] != "teacher") {
?>
        <script>
            $("#group_id").change(function () {
                var group_id = $(this).val();

                $.ajax({
                    url: '/api',
                    type: "POST",
                    data: {
                        method: 'calendar',
                        group_id: group_id,
                        user_id: <?=$systemUser["id"]?>,
                    },
                    dataType: "json",
                    success: function(data) {
                        if (data.ok == true) {
                            // console.table(data.course);
                            // console.log(data.events);
                            // console.table(data.events);

                            $("#calendar").removeClass("d-none");
                            var today = new Date();
                            var dd = String(today.getDate()).padStart(2, '0');
                            var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                            var yyyy = today.getFullYear();

                            var todayDate = yyyy + '-'+ mm + '-'  + dd;
                            var calendarEl = document.getElementById('calendar');
                            var events = [];
                            data.events.forEach(item => {
                                events.push({
                                    title: item["name"],
                                    description: item["description"],
                                    start: item["start"],
                                });
                            });
                            console.log(data.events);
                            var calendar = new FullCalendar.Calendar(calendarEl, {
                            headerToolbar: {
                                left: 'prevYear,prev,next,nextYear today',
                                center: 'title',
                                right: 'dayGridMonth,dayGridWeek,dayGridDay'
                            },
                            initialDate: todayDate,
                            navLinks: true, // can click day/week names to navigate views
                            editable: true,
                            dayMaxEvents: true, // allow "more" link when too many events
                            events: events
                            });

                            calendar.render();

                            var today2 = document.querySelector('.fc-today-button');

                            
                            today2.style.display = 'none';   
                            // month.innerHTML = 'oy';   
                            
                            
                        } else {
                            $("#calendar").addClass("d-none");
                            $(".modal-title").text("Bu guruhga tegishli Fanlar mavjuda emas");
                            $(".modal").modal("show");
                        }
                    },
                    error: function() {
                        alert("Xatolik yuzaga keldi");
                    }
                })
            });

            $("#group_id").change();
        </script>
    <? } else {?>
        <script>
            $.ajax({
                url: '/api',
                type: "POST",
                data: {
                    method: 'calendar',
                    student_id: 1,
                    user_id: <?=$systemUser["id"]?>,
                },
                dataType: "json",
                success: function(data) {
                    if (data.ok == true) {
                        // console.log(data.events);
                        // console.table(data.events);
                        
                        $("#calendar").removeClass("d-none");
                        var today = new Date();
                        var dd = String(today.getDate()).padStart(2, '0');
                        var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                        var yyyy = today.getFullYear();

                        var todayDate = yyyy + '-'+ mm + '-'  + dd;
                        var calendarEl = document.getElementById('calendar');
                        var events = [];
                        data.events.forEach(item => {
                            events.push({
                                title: item["name"],
                                description: item["description"],
                                start: item["start"],
                            });
                        });
                        console.log(data.events);
                        var calendar = new FullCalendar.Calendar(calendarEl, {
                        headerToolbar: {
                            left: 'prevYear,prev,next,nextYear today',
                            center: 'title',
                            right: 'dayGridMonth,dayGridWeek,dayGridDay'
                        },
                        initialDate: todayDate,
                        navLinks: true, // can click day/week names to navigate views
                        editable: true,
                        dayMaxEvents: true, // allow "more" link when too many events
                        events: events
                        });

                        calendar.render();

                        var today2 = document.querySelector('.fc-today-button');

                        
                        today2.style.display = 'none';   
                        // month.innerHTML = 'oy';   
                        
                        
                    } else {
                        $("#calendar").addClass("d-none");
                        $(".modal-title").text("Sizga tegishli Fanlar mavjuda emas");
                        $(".modal").modal("show");
                    }
                },
                error: function() {
                    alert("Xatolik yuzaga keldi");
                }
            })
        </script>
    <? } ?>
<?
include "system/end.php";
?>