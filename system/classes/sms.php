<?php
function bot($method, $callback_datas=[]){
    define("key", "");
    $url = "https://api.telegram.org/bot".key."/".$method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $callback_datas);
    $res = curl_exec($ch);

    if (curl_error($ch)) {
      var_dump(curl_error($ch));
    } else {
      $res_arr = json_decode($res, true);
      return $res_arr;
    }
}

function sendMessage($phone, $message) {
    bot("sendMessage", [
        "chat_id" => 166975358,
        "text" => $message,
        "parse_mode" => "html"
    ]);
}
?>