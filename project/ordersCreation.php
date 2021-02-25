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
<?php
if(isset($_POST["save"]) && validateAddress($_POST["address"]))
{

      $address = $_POST["address"];
      $payment = $_POST["payment"];
      $pr = $_POST["totalPrice"];
    	$db = getDB();
    	$stmt = $db->prepare("INSERT INTO Orders(user_id, address, payment_method, total_price) VALUES(:user_id, :address, :payment_method, :total_price)");
    	$r = $stmt->execute([
          ":user_id"=>$id,
          ":address"=>$address,
          ":payment_method"=>$payment,
          ":total_price"=>$pr
      ]);
      
      //$order_id = mysqli_insert_id($db);
      
      $db = getDB();
    	$stmt = $db->prepare("SELECT * FROM Orders WHERE user_id=$id ORDER BY ID DESC LIMIT 1");
    	$r = $stmt->execute();
      $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
     
      $order_id = $orders[0]["id"];

      $db = getDB();
      $stmt = $db->prepare("SELECT product_id, quantity FROM Carts WHERE user_id=$id");
      $r = $stmt->execute();
      $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      foreach($products as $res)
      {
          $check = updateItemInProductTable($res["product_id"], $res["quantity"]);
      }
      
      $idP = 0;

      foreach($products as $res){
          $idP=$res["product_id"];
          $quan = $res["quantity"];
          
          $db = getDB();
          $stmt = $db->prepare("SELECT price FROM Products WHERE id=$idP");
          $r = $stmt->execute();
          $prod = $stmt->fetch(PDO::FETCH_ASSOC);
          
          $unit = $prod["price"];
          
          $db = getDB();
        	$stmt = $db->prepare("INSERT INTO OrderItems(order_id, product_id, quantity, unit_price) VALUES($order_id, $idP, $quan, $unit)");
        	$r = $stmt->execute();
      }
      header("Location: orderConfirmation.php?id=$id");
      /*$result = clearCart($id);
      if($result)
          header("Location: orderConfirmation.php?id=$id");
      else
          flash("error");*/
}
elseif(isset($_POST["save"]) && !validateAddress($_POST["address"]))
  flash("Enter a valid address");
?>

<h3>Order Info</h3>
    <form method="POST">
        <label>Payment Method</label>
        <select name="payment">
            <option> <value="-1">Name</option>
            <option> <value="Visa">Visa</option>
            <option> <value="MasterCard">MasterCard</option>
            <option> <value="Amex">Amex</option>
        </select></br>
        <label>Address</label>
        <input type="varchar" name="address"/></br>
        <label>Total Price</label>
        <input type="number" name="totalPrice" value = "<?php safer_echo($totalPrice); ?>"/></br>
        <input type="submit" name="save" value="Order"/>
    </form>

<?php require(__DIR__ . "/partials/flash.php");