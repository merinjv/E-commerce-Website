<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //Is not logged in
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
    $totalPrice = 0;
    if(isset($_GET["id"])){
        $id = $_GET["id"];
        $db = getDB();
        $stmt = $db->prepare("SELECT id,price from Carts WHERE user_id = :user_id");
        $r = $stmt->execute([":user_id"=>$id]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($products as $product)
        {
            $totalPrice+=$product["price"];
        }
    }
?>

<h3>Order Info</h3>
    <form method="POST">
        <label>Payment Method</label>
        <select name="name">
            <option> <value="-1">Name</option>
            <option> <value="Visa">Visa</option>
            <option> <value="MasterCard">MasterCard</option>
            <option> <value="Amex">Amex</option>
        </select></br>
        <label>Address</label>
        <input type="varchar" name="address"/></br>
        <label>Total Price</label>
        <input type="number" name="totalPrice" value = "<?php safer_echo($totalPrice); ?>"/></br>
        <input type="submit" name="save" value="Ordered"/>
    </form>

<?php require(__DIR__ . "/partials/flash.php");