<?php

    function lessonAttendance($group) {
        $res = [];

        $all_lessons_count = $group["all_lessons_count"];
        $lesson_start_date = $group["lesson_start_date"];
        $allowed_days = [
            "ya" => $group["ya"],
            "du" => $group["du"],
            "se" => $group["se"],
            "cho" => $group["cho"],
            "pa" => $group["pa"],
            "ju" => $group["ju"],
            "sha" => $group["sha"]
        ];
        $allowed_days_filtered = array_filter($allowed_days, function($val){
            if ($val == 1) return $val;
        });

        $week_days = [
            "Yakshanba",
            "Dushanba",
            "Seshanba",
            "Chorchanba",
            "Payshanba",
            "Juma",
            "Shanba"
        ];
            
        $week_days_eng = [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday"
        ];
        
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

        function getNextDayKey($lesson_start_date, $day_key, $allowed_days, $week_days_eng) {
            // print_r([ $lesson_start_date, $day_key ]);
            // global $allowed_days, $week_days_eng;
            
            $day_founded = false;
            $next_day_key = false;
            $num = 0;
            $first_allowed_day = false;
            
            foreach ($allowed_days as $week_name => $val) {
                if ($val == 1 && !$first_allowed_day) $first_allowed_day = $week_days_eng[$num];
            
                if ($day_key == $num) {
                $day_founded = true;
                }
                
                if ($val == 1 && $day_founded && $day_key != $num) {
                // print_r([
                //   "val" => $val,
                //   "day_founded" => $day_founded,
                //   "day_key" => $day_key,
                //   "num" => $num
                // ]);
            
                $next_day_key = $num;
                $day_founded = false;
            
                // echo "next $week_days_eng[$next_day_key] $lesson_start_date\n";
                return date("Y-m-d", strtotime("next $week_days_eng[$next_day_key] $lesson_start_date"));
                }
                $num++;
            }
            
            return date("Y-m-d", strtotime("next $first_allowed_day $lesson_start_date"));
        }

        $days = [];
        $learning_months = [];

        $umumiy_arr = [];
        $ishchi_arr = [];
        

        foreach (range(1, $all_lessons_count) as $lesson) {
            
            $lesson_month = date("m", strtotime($lesson_start_date));
            // print( "oylar". $lesson_month . "<br>");
            if (!in_array($lesson_month, $learning_months)) {
                array_push($learning_months, $lesson_month);
            }
            
            $day_key = date("w", strtotime($lesson_start_date));
            // print( "kunlar". $day_key . "<br>");

            array_push($ishchi_arr, date("Y-m-d", strtotime($lesson_start_date)));
        
            if (count($ishchi_arr) == $group["one_monthly_lessons_count"]) {
                array_push($umumiy_arr, $ishchi_arr);
                $ishchi_arr = [];
            }

            array_push($days, date("Y-m-d", strtotime($lesson_start_date)));
            // print(date("Y-m-d", strtotime($lesson_start_date)));
            // echo "$lesson-dars) " . date("Y-m-d", strtotime($lesson_start_date)) . " " . $week_days[$day_key] . "\n";
            
            $next_date = getNextDayKey($lesson_start_date, $day_key, $allowed_days, $week_days_eng);
            if (!$next_date) $next_date = getNextDayKey($lesson_start_date, $day_key, $allowed_days, $week_days_eng);
            if ($next_date) $lesson_start_date = $next_date;
        }

        $res["learning_months"] = $learning_months;
        $res["months"] = $months;
        $res["days"] = $days;
        $res["week_days"] = $week_days;

        return $res;
    }
    
?>