<?php
    $is_config = true;
    if (empty($load_defined)) include 'load.php';

    if (isAuth() === false) {
        header("Location: /login");
        exit;
    }
    
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

    function convertNumberToWord($num = false) {
        $num = str_replace(array(',', ' '), '' , trim($num));
        if(! $num) {
            return false;
        }
        $num = (int) $num;
        $words = array();
        $list1 = array('', 'bir', 'ikki', 'uch', 'to\'rt', 'besh', 'olti', 'yetti', 'sakkiz', 'to\'qqiz', 'o\'n', 'o\'n bir',
            'o\'n ikki', 'o\'n uch', 'o\'n to\'rt', 'o\'n besh', 'o\'n olti', 'o\'n etti', 'o\'n sakkiz', 'o\'n to\'qqiz'
        );
        $list2 = array('', 'o\'n', 'yigirma', 'o\'ttiz', 'qirq', 'ellik', 'oltmish', 'yetmish', 'sakson', 'to\'qson', 'yuz');
        $list3 = array('', 'ming', 'million', 'milliard', 'trillion', 'kvadrillion', 'kvintillion', 'sekstilion', 'septillion',
            'oktilion', 'nonillion', 'decillion', 'undecillion', 'duodilion', 'tredesilion', 'kvattuordesilion',
            'kvindesilyon', 'sexdecillion', 'septendesilion', 'oktodesilyon', 'novemdecillion', 'vigintilion'
        );
        $num_length = strlen($num);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $num = substr('00' . $num, -$max_length);
        $num_levels = str_split($num, 3);
        for ($i = 0; $i < count($num_levels); $i++) {
            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';
            if ( $tens < 20 ) {
                $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
            } else {
                $tens = (int)($tens / 10);
                $tens = ' ' . $list2[$tens] . ' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }
            $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
        } //end for loop
        $commas = count($words);
        if ($commas > 1) {
            $commas = $commas - 1;
        }
        return implode(' ', $words);
    }

    $file = "shartnoma_2024.docx";
    $file2 = "shartnoma2_2024.docx";
    $dir = getcwd();

    if(!is_dir("temp")){
        mkdir("temp");
    } else {
        recursive_remove_directory("temp", true);
    }

    $edir = escapeshellarg($dir);
    
    shell_exec("unzip $edir/$file -d $edir/temp");

    $c = file_get_contents("temp/word/document.xml");
    // $c = str_replace("YANGI", "PRIVET1224", $c);
    $f = new NumberFormatter("uz", NumberFormatter::SPELLOUT);
    echo $f->format(19000000);
    $c = strtr($c, [ 
        "#SH_NUM" => 192,
        "#ST_CODE" => 777,
        "#DAY" => date("d"),
        "#MONTH" => $months[(9 - 1)],
        "#YEAR" => date("Y"),
        "#B_D" => 18,
        "#B_M" => $months[(11 - 1)],
        "#B_YEAR" => 2004,
        "#L_NAME" => "Komilov",
        "#F_NAME" => "Islombek",
        "#FATHER" => "Nodirbek ogli",
        "#ADDRESS" => "Andijon viloyati andijon tumani",
        "#P_DAY" => 18,
        "#P_MONTH" => $months[(date("m") - 1)],
        "#P_YEAR" => 2021,
        "#P_WHO" => "Andijon tumani",
        "#P_CODE" => "AB",
        "#P_NUMBER" => "95063",
        "#DIRECTION" => "FILOLOGIYA VA TILLARNI OQITIK",
        "#C_DURATION" => 4,
        "#EDUCATION_TYPE" => "bakalavr",
        "#LEARN_TYPE" => "Kunduzgi",
        "#PRICE" => number_format(19000000),
        "#PRICE_WORD" => convertNumberToWord(19000000),
        "#FIRST_N" => "I",
        "#PHONE" => "90-202-83-88",
    ]);
    
    unlink("temp/word/document.xml");
    file_put_contents("temp/word/document.xml", $c);

    // rezip everything 
    if(is_file($file2)) unlink($file2);

    $toZip = array(
        "_rels",
        "docProps",
        "word",
        "[Content_Types].xml"
    );

    $cmd = "cd $edir/temp && zip -r ../$file2 ".implode(" ", $toZip);
    echo shell_exec($cmd);

    // special function: clean a directory
    function recursive_remove_directory($directory, $empty=FALSE) {
        if(substr($directory,-1) == '/') {
            $directory = substr($directory, 0, -1);
        }
        if(!file_exists($directory) || !is_dir($directory)) {
            return FALSE;
        } elseif(is_readable($directory)) {
            $handle = opendir($directory);
            while (FALSE !== ($item = readdir($handle))) {
                if($item != '.' && $item != '..') {
                    $path = $directory.'/'.$item;
                    if(is_dir($path)) {
                        recursive_remove_directory($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            closedir($handle);
            if($empty == FALSE){
                if(!rmdir($directory)) {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }
?>