<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //Is not logged in
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
    if(isset($_GET["id"])){
        $userid = $_GET["id"];
        $db = getDB();
      	$stmt = $db->prepare("SELECT * FROM Orders WHERE user_id=$userid ORDER BY ID DESC LIMIT 1");
      	$r = $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
        $order_id = $orders[0]["id"];
        $total_price = $orders[0]["total_price"];
        $payment = $orders[0]["payment_method"];
        $address = $orders[0]["address"];
        
        $db = getDB();
      	$stmt = $db->prepare("SELECT username FROM Users WHERE id=$userid");
      	$r = $stmt->execute();
        $usernames = $stmt->fetch(PDO::FETCH_ASSOC);
        $username = $usernames["username"];
        
        $db = getDB();
      	$stmt = $db->prepare("SELECT product_id,unit_price,quantity FROM OrderItems WHERE order_id=$order_id");
      	$r = $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $names = array();

        foreach($products as $product)
        {
            $product_id = $product["product_id"];
            $db = getDB();
          	$stmt = $db->prepare("SELECT name FROM Products WHERE id=$product_id");
          	$r = $stmt->execute();
            $productName = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $names[] = $productName["name"];
        }
        $i=0;
    }
?>
<?php
if(isset($_POST["confirm"]))
{
    $result = clearCart($userid);
      if($result){
          flash("Your order was successful! Thank you and please come back again!");
          die(header("Location: home.php?id=$userid"));
      }
      else
          flash("error");
}
elseif(isset($_POST["cancel"]))
{
    
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM OrderItems WHERE order_id=$order_id");
    $r = $stmt->execute();
    
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM Orders WHERE id=$order_id");
    $r = $stmt->execute();
    if($r){
    
      flash("Successful Cancelation");
      die(header("Location: cartView.php?id=$userid"));
      
    }
    else
      flash("Error");
}
?>
<div class="results">
    <div>
        <div>Username: <?php safer_echo($username); ?></div>
    </div>
    <?php if (count($products) > 0): ?>
        <div class="list-group">
            <?php foreach ($products as $r): ?>
                <div class="list-group-item">
                    <div>
                        <div>Name: <?php safer_echo($names[$i++]); ?></div>
                    </div>
                    <div>
                        <div>Quantity: <?php safer_echo($r["quantity"]); ?></div>
                    </div>
                    <div>
                        <div>Price: <?php safer_echo($r["unit_price"]); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="list-group-item">
                <div>
                    <div>Payment: <?php safer_echo($payment); ?></div>
                </div>
                <div>
                    <div>Address: <?php safer_echo($address); ?></div>
                </div>
                <div>
                    <div>Total: <?php safer_echo($total_price); ?></div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<form method="POST">
    <input type="submit" name="confirm" value="Confirm Order"/>
    <input type="submit" name="cancel" value="Cancel"/>
</form>
<?php require(__DIR__ . "/partials/flash.php");