<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //login redirection
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$db = getDB();
$stmt = $db->prepare("SELECT Carts.product_id,Carts.name,Carts.price, Carts.quantity, Carts.user_id, Users.username FROM Carts JOIN Users on Carts.user_id = Users.id LEFT JOIN Products on Products.id = Carts.product_id");
$r = $stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total=0;

foreach($results as $p){

 $total+=$p["price"];

}
?>

    <h3>View Cart</h3>
    <div class="card">
      <?php foreach ($results as $p): ?>
        
        <div class="card-title"><br>
           <a type="button" href="productView.php?id=<?php safer_echo($p['product_id']); ?>"><?php safer_echo($p["name"]); ?></a>
        </div>
        <div class="card-body">
            <div>
                <div>Quantity: <?php safer_echo($p["quantity"]); ?></div>
                <div>Subtotal: <?php safer_echo($p["price"]); ?></div>
                <div>Product ID: <?php safer_echo($p["product_id"]); ?></div>
                <div>Owned by: <?php safer_echo($p["username"]); ?></div>
                <div>
                     <a type="button" name="edit" href="cartEdit.php?id=<?php safer_echo($p['product_id']); ?>">Edit</a>
                     <a type="button" name="delete" href="delete_product_cart.php?id=<?php safer_echo($p['product_id']); ?>">Delete</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="total"><br>Total: $<?php safer_echo($total); ?></div>
        <div class="card-title">
                <div>
                     <a type="button" name="delete" href="delete_cart.php?id=<?php safer_echo($p['user_id']); ?>">Clear Cart</a>
                </div>
        </div>
    </div>
<?php require(__DIR__ . "/partials/flash.php");
