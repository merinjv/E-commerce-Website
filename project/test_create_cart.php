<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$db = getDB();
$stmt = $db->prepare("SELECT id,name from Products LIMIT 10");
$r = $stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <h3>Cart</h3>
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
        <input type="number" min="0" max="10" name="quantity"/>
        <input type="submit" name="save" value="Created"/>
    </form>

<?php
if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $name = $_POST["name"];
    $quantity = $_POST["quantity"];
    $product_id = substr($_POST["product_id"],-2);
    $price = getQuantityPrice($quantity, $product_id);
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Carts (name, product_id, price, quantity, user_id) VALUES(:name, :product_id, :price, :quantity,:user)");
    $r = $stmt->execute([
        ":name" => $name,
        ":product_id" => $product_id,
        ":price" => $price,
        ":quantity" => $quantity,
        ":user" => $user
    ]);
    if ($r) {
        flash("Created successfully with id: " . $db->lastInsertId());
        flash("Total price for ".$quantity." ".$name." is: $".$price);
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>
<?php require(__DIR__ . "/partials/flash.php");