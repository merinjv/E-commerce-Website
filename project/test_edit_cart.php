<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//saving
if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $name = $_POST["name"];
    $product_id = substr($_POST["product_id"],-2);
    $quantity = $_POST["quantity"];
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
$result = [];
if (isset($id)) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Carts where id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}

$db = getDB();
$stmt = $db->prepare("SELECT id,name from Products LIMIT 10");
$r = $stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <h3>Edit Cart</h3>
    <form method="POST">
        <label>Name</label>
        <select name="name">
            <option> <value="-1">Name</option>
            <?php foreach ($products as $product): ?>
                <option> <value="<?php safer_echo($product["name"]); ?>"><?php safer_echo($product["name"]); ?></option>
            <?php endforeach; ?>
        </select>
        <label>Product ID</label>
        <select name="product_id">
            <option> <value="-1">None</option>
            <?php foreach ($products as $product): ?>
                <option> <value="$product["id"]"><?php safer_echo($product["name"]); ?>:  <?php safer_echo($product["id"]); ?></option>
            <?php endforeach; ?>
        </select>
        <label>Quantity</label>
        <input type="number" min="1" name="quantity" value="<?php echo $result["quantity"]; ?>"/>
        <input type="submit" name="save" value="Update"/>
    </form>


<?php require(__DIR__ . "/partials/flash.php");
