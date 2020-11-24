<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    ////Is not logged in
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Carts where product_id = $id");
    $r = $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<?php
//saving
if (isset($_POST["save"])) {
    $name = $_POST["name"];
    $product_id = substr($_POST["product_id"],-2);
    $quantity = $_POST["quantity"];
    if($quantity==0){
      $check = deleteRow($product_id);
      if($check)
        die(header("Location: cartView.php"));
      else
        flash("error deleting product");
    }
    $price = getQuantityPrice($quantity, $product_id);
    if ($product_id <= 0) {
        $product_id = null;
    }
   // $price = $_POST["price"];
    $quantity = $_POST["quantity"];
    $user = get_user_id();
    $db = getDB();
    if (isset($product_id)) {
        $stmt = $db->prepare("UPDATE Carts set name=:name, quantity=:quantity, price=:price where product_id=:product_id");
        $r = $stmt->execute([
            ":name" => $name,
            ":product_id" => $product_id,
            ":quantity" => $quantity,
            ":price" => $price
        ]);
        if ($r) {
            flash("Updated successfully with id: " . $product_id);
            flash("Updated total price for ".$quantity." ".$name." is: $".$price);
        }
        else {
            $e = $stmt->errorInfo();
            flash("Error updating: " . var_export($e, true));
        }
    }
    else {
        flash("ID isn't set, we need an ID in order to update");
    }
}
?>
<?php
//fetching

//$result = [];
/*if (isset($id)) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Carts where id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}*/
/*
$db = getDB();
$stmt = $db->prepare("SELECT id,name from Products LIMIT 10");
$r = $stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);*/
?>
    <h3>Edit Cart</h3>
    <form method="POST">
        <label>Name</label>
        <input type="text" name="name" value="<?php echo $result["name"]; ?>"/>
        <label>Product ID</label>
        <input type="number" name="product_id" value="<?php echo $result["product_id"]; ?>"/>
        <label>Quantity</label>
        <input type="number" min="0" name="quantity" value="<?php echo $result["quantity"]; ?>"/>
        <input type="submit" name="save" value="Update"/>
    </form>


<?php require(__DIR__ . "/partials/flash.php");
