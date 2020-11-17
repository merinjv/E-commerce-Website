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
$stmt = $db->prepare("SELECT Carts.product_id,Carts.name,Carts.price, Carts.quantity, Users.username FROM Carts JOIN Users on Carts.user_id = Users.id LEFT JOIN Products on Products.id = Carts.product_id");
$r = $stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <h3>View Cart</h3>
    <div class="card">
      <?php foreach ($results as $p): ?>
        
        <div class="card-title"><br>
           <a type="button" href="test_view_products.php?id=<?php safer_echo($p['product_id']); ?>"><?php safer_echo($p["name"]); ?></a>
        </div>
        <div class="card-body">
            <div>
                <div>Rate: <?php safer_echo($p["price"]); ?></div>
                <div>Quantity: <?php safer_echo($p["quantity"]); ?></div>
                <div>Product ID: <?php safer_echo($p["product_id"]); ?></div>
                <div>Owned by: <?php safer_echo($p["username"]); ?></div>
            </div>
        </div>
        <div></div>
        <?php endforeach; ?>
    </div>
<?php require(__DIR__ . "/partials/flash.php");
