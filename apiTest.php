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

if ($req["token"]) {
    $tokenArr = getToken($req["token"]);

    if ($tokenArr["token"]) {
        $tokenUser = $db->assoc("SELECT id, first_name, last_name, phone, created_date, role FROM users WHERE id = ?", [
            $tokenArr["user_id"]
        ]);

        if ($tokenUser["id"]) {
            $systemUser = $tokenUser;
            $user_id = (int)$systemUser["id"];
        } else {
            errorMessage("User not found");
        }
    } else {
        errorMessage("Token not found");
    }
}

validateForms(["method"]);

if (!$user_id || $user_id == 0) {
    if ($req["method"] != "auth") {
        errorMessage("You are not authorized");
    }
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
    case "auth":
        validateForms(["login", "password"]);

        $login = trim(htmlspecialchars($req["login"], ENT_QUOTES));
        $password = trim(htmlspecialchars($req["password"], ENT_QUOTES));

        $user = $db->assoc("SELECT * FROM users WHERE login = ? AND password = ?", [
            $login,
            md5(md5(encode($password)))
        ]);

        if ($login && $password && empty($user["id"])) {
            errorMessage("Login or password incorrect!");
        } else {
            $db->update("users", [
                "failed_login" => 0,
                "sestime" => time()
            ], [
                "id" => $user['id']
            ]);

            $token = createToken($user["id"]);

            $res["ok"] = true;
            $res["token"] = $token;
        }
    break;

    case "checkAuth":
        validateForms(["token"]);

        $res["ok"] = true;
        $res["user"] = $systemUser;
    break;

    case "getCategories":
        $categories = $db->in_array("SELECT * FROM categories");

        foreach ($categories as $key => $category) {
            $image = image($category["image_id"]);
            $categories[$key]["image"] = $image;
        }

        $res["ok"] = true;
        $res["categories"] = $categories;
    break;

    case "getCategory":
        validateForms(["category_id"]);

        $category = $db->assoc("SELECT * FROM categories WHERE id = ?", [
            $req["category_id"]
        ]);

        if (empty($category["id"])) {
            $res["ok"] = false;
        } else {
            $res["ok"] = true;
            $image = image($category["image_id"]);
            $category["image"] = $image;
            $res["category"] = $category;
        }
    break;

    case "getProducts":
        validateForms(["category_id"]);

        $products = $db->in_array("SELECT * FROM products WHERE category_id = ?", [ $req["category_id"] ]);

        foreach ($products as $key => $product) {
            $productType = $db->assoc("SELECT * FROM product_types WHERE id = ?", [
                $product["product_type_id"]
            ]);

            $products[$key]["productType"] = [
                "name" => $productType["name"]
            ];
            $products[$key]["image"] = $db->assoc("SELECT * FROM images WHERE id = ?", [ $product["image_id"] ]);
        }

        $res["ok"] = true;
        $res["products"] = $products;
    break;

    case "getProduct":
        validateForms(["product_id"]);

        $product = $db->assoc("SELECT * FROM products WHERE id = ?", [
            $req["product_id"]
        ]);

        if (empty($product["id"])) {
            errorMessage("product not found");
        } else {
            $res["ok"] = true;

            $productType = $db->assoc("SELECT * FROM product_types WHERE id = ?", [
                $product["product_type_id"]
            ]);
            $product["productType"] = [
                "name" => $productType["name"]
            ];

            $image = image($product["image_id"]);
            $product["image"] = $image;

            $res["product"] = $product;
        }
    break;

    case "addToCart":
        validateForms(["product_id", "product_count"]);

        $product = $db->assoc("SELECT * FROM products WHERE id = ?", [
            $req["product_id"]
        ]);

        if (!empty($product["id"])) {
            $cart = $db->assoc("SELECT * FROM carts WHERE user_id = ? AND product_id = ?", [
                $user_id,
                $req["product_id"]
            ]);

            if (!empty($cart["id"])) {
                $db->update("carts", [
                    "product_count" => $req["product_count"]
                ], [
                    "id" => $cart["id"]
                ]);

                $res["ok"] = true;
                $res["message"] = "updated";
            } else {
                $cart_id = $db->insert("carts", [
                    "user_id" => $user_id,
                    "product_id" => $req["product_id"],
                    "product_count" => $req["product_count"]
                ]);
        
                if ($cart_id > 0) {
                    $res["ok"] = true;
                    $res["cart_id"] = $cart_id;
                } else {
                    errorMessage("product not added to cart");
                }
            }
        } else {
            errorMessage("product not found");
        }
    break;

    case "removeFromCart":
        validateForms(["cart_id"]);

        $cart = $db->assoc("SELECT * FROM carts WHERE id = ?", [ $req["cart_id"] ]);

        if (!empty($cart["id"]) && $cart["user_id"] == $user_id) {
            $db->delete("carts", $cart["id"]);
            $res["ok"] = true;
        } else {
            errorMessage("cart not found");
        }
    break;

    case "getMyCarts":
        $carts = $db->in_array("SELECT * FROM carts WHERE user_id = ?", [ $user_id ]);

        $res["ok"] = true;
        $res["carts"] = $carts;
    break;

    case "clearMyCarts":
        $carts = $db->in_array("SELECT * FROM carts WHERE user_id = ?", [ $user_id ]);

        foreach ($carts as $cart) {
            $db->delete("carts", $cart["id"]);
        }
        $res["ok"] = true;
    break;

    case "createOrder":
        $carts = $db->in_array("SELECT * FROM carts WHERE user_id = ?", [ $user_id ]);
        
        if (count($carts) > 0) {
            $products = [];

            foreach ($carts as $cart) {
                array_push($products, [
                    "product_id" => $cart["product_id"],
                    "product_count" => $cart["product_count"]
                ]);
                $db->delete("carts", $cart["id"]);
            }

            $order_id = $db->insert("orders", [
                "user_id" => $user_id,
                "products" => json_encode($products, JSON_UNESCAPED_UNICODE),
                "products_count" => count($products)
            ]);

            if ($order_id > 0) {
                $res["order_id"] = $order_id;
                $res["ok"] = true;
            } else {
                errorMessage("unexpected error");
            }
        } else {
            errorMessage("There are no products in your cart");
        }
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