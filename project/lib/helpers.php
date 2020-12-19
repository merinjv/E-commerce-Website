<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create
//I have provided two for you
function is_logged_in() {
    return isset($_SESSION["user"]);
}

function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] == $role) {
                return true;
            }
        }
    }
    return false;
}

function data($value)
{
    if(isset($_POST[$value]))
    {
        //flash("here");
        $data = $_POST[$value];
        $_SESSION[$value] = $data;
    }
    elseif(isset($_SESSION[$value]))
    {
        //flash("sess");
        $data = $_SESSION[$value];
    }
    else
    {
        //flash("gone");
        $data=null;
    }
    
    return $data;
}

function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}

function getQuantityPrice($quantity,$id){

    $db = getDB();
    $stmt = $db->prepare("SELECT price FROM Products WHERE id=:id");
    $r = $stmt->execute([
    ":id" => $id,
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
    
    $pr = $result["price"];
    
    $total = $pr*$quantity;
    return $total;

}

function deleteRow($id)
{	
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM Carts WHERE product_id=$id");
    $r = $stmt->execute();
    if($r)
      return true;
    else
      return false;
}
function clearCart($id)
{	
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM Carts WHERE user_id=$id");
    $r = $stmt->execute();
    if($r)
      return true;
    else
      return false;
}
function checkItemInProductTable($id, $cartQuant)
{
     $db = getDB();
     $stmt = $db->prepare("SELECT quantity FROM Products WHERE id=$id");
     $r = $stmt->execute();
     $results = $stmt->fetch(PDO::FETCH_ASSOC);
     
     $presQuantity = $results["quantity"];
     
     if($cartQuant<=$presQuantity)
         return true; 
     else
         return false;

}

function updateItemInProductTable($id, $cartQuant)
{
     $db = getDB();
     $stmt = $db->prepare("SELECT quantity FROM Products WHERE id=$id");
     $r = $stmt->execute();
     $results = $stmt->fetch(PDO::FETCH_ASSOC);
     
     $presQuantity = $results["quantity"];
     $visibility = 0;
     $leftQuant = $presQuantity-$cartQuant;
     if($leftQuant==0)
       $visibility = 1;
         $db = getDB();
         $stmt = $db->prepare("UPDATE Products set quantity=:quantity, visibility=:visibility WHERE id=:id");
         $r = $stmt->execute([
           ":id"=>$id,
	         ":quantity"=>$leftQuant,
          ":visibility"=>$visibility
         ]);
     return true;

}

function getItemInProductTable($arr)
{
     $db = getDB();
     $stmt = $db->prepare("SELECT name, quantity FROM Products WHERE id=$arr[0]");
     $r = $stmt->execute();
     $results = $stmt->fetch(PDO::FETCH_ASSOC);
     
     if($results["quantity"]>0){
       $statement = $results["name"]." has only a quantity of ".$results["quantity"];
     }
     else{
       $statement = $results["name"]." is out of stock";
     }
     
     if(count($arr)>1){
         for($i=1; $i<count($arr); $i++){
             $db = getDB();
             $stmt = $db->prepare("SELECT name, quantity FROM Products WHERE id=$arr[$i]");
             $r = $stmt->execute();
             $results = $stmt->fetch(PDO::FETCH_ASSOC);
             
             if($results["quantity"]>0){
               $statement = $statement." and ".$results["name"]." has only a quantity of ".$results["quantity"];
             }
             else
             {
               $statement = $statement." and ".$results["name"]." is out of stock";
             }
          }
      }
     
     return $statement;
}
function validateAddress($add)
{

    $check = '/^[a-zA-Z0-9-. ]+$/';
    return preg_match($check, $add);

}

?>
