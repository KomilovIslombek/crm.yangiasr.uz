<?
date_default_timezone_set("Asia/Tashkent");

session_start();
// unset cookies
if (isset($_SERVER['HTTP_COOKIE'])) {
  $cookies = explode(';', $_SERVER['HTTP_COOKIE']);

  if (!empty($_COOKIE["DEVICE"])) {
    $cookie_device = $_COOKIE["DEVICE"];
  }
  foreach($cookies as $cookie) {
    $parts = explode('=', $cookie);
    $name = trim($parts[0]);
    setcookie($name, '', time()-100000);
    setcookie($name, '', time()-100000, '/');
    unset($_COOKIE[$name]);
  }

  if (!empty($cookie_device)) {
    addCookie("DEVICE", $cookie_device);
  }
}
session_destroy();

// if ($user_id > 0) {
//   $db->update("users", ["password" => md5(time())], ["id" => $user_id]);
// }

header('Location: /');