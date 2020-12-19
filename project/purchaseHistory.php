<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //Is not logged in
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<?php
$username="";
$order_id = array();
$usernamearr = array();
$time = array();
$total_price = array();
$unit_price = array();
$quantity = array();
$payment = array();
$address = array();
$items = 0;
$val=false;
$names = array();
if (!has_role("Admin")) {
    $user = get_user_id();
    
    $db = getDB();
      	$stmt = $db->prepare("SELECT username FROM Users WHERE id=$user");
      	$r = $stmt->execute();
        $usernames = $stmt->fetch(PDO::FETCH_ASSOC);
        $username = $usernames["username"];
        
    $db = getDB();
      	$stmt = $db->prepare("SELECT * FROM Orders WHERE user_id=$user ORDER BY ID DESC LIMIT 10");
      	$r = $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
        //flash($orde["id"]);
       
       foreach($orders as $order){
             //flash($order["id"]);
            $order_id[] = $order["id"];
            $orderid = $order["id"];
            $total_price[] = $order["total_price"];
            $payment[] = $order["payment_method"];
            $address[] = $order["address"];
            $time[] = $order["created"];
            
            $db = getDB();
          	$stmt = $db->prepare("SELECT product_id,unit_price,quantity FROM OrderItems WHERE order_id=$orderid LIMIT 10");
          	$r = $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
    
            foreach($products as $product)
            {
                $product_id = $product["product_id"];
                $unit_price[] = $product["unit_price"];
                $quantity[] = $product["quantity"];
                $db = getDB();
              	$stmt = $db->prepare("SELECT name FROM Products WHERE id=$product_id");
              	$r = $stmt->execute();
                $productName = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $names[] = $productName["name"];
                $items++;
                
                if($items==10){
                  $val=true;
                  break;
                  }
            }
            if($val)
              break;
              
            //flash($names[0]);
      }
}
elseif(has_role("Admin")){
     
    $db = getDB();
      	$stmt = $db->prepare("SELECT * FROM Orders ORDER BY ID DESC LIMIT 10");
      	$r = $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        //flash(count($orders));
        
        $order_id = array();
        $time = array();
        $total_price = array();
        $unit_price = array();
        $quantity = array();
        $payment = array();
        $address = array();
        $items = 0;
        $val=false;
        $names = array();
        $usernamearr = array();
       
       foreach($orders as $order){
            $order_id[] = $order["id"];
            $orderid = $order["id"];
            $total_price[] = $order["total_price"];
            $payment[] = $order["payment_method"];
            $address[] = $order["address"];
            $time[] = $order["created"];
            $user = $order["user_id"];
            
            $db = getDB();
          	$stmt = $db->prepare("SELECT username FROM Users WHERE id=$user");
          	$r = $stmt->execute();
            $usernames = $stmt->fetch(PDO::FETCH_ASSOC);
            $usernamearr[] = $usernames["username"];
            
            $db = getDB();
          	$stmt = $db->prepare("SELECT product_id,unit_price,quantity FROM OrderItems WHERE order_id=$orderid LIMIT 10");
          	$r = $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach($products as $product)
            {
                $product_id = $product["product_id"];
                $unit_price[] = $product["unit_price"];
                $quantity[] = $product["quantity"];
                $db = getDB();
              	$stmt = $db->prepare("SELECT name FROM Products WHERE id=$product_id");
              	$r = $stmt->execute();
                $productName = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $names[] = $productName["name"];
                $items++;
                //flash($names[0]);
                if($items==10){
                  $val=true;
                  break;
                  }
            }
            if($val)
              break;
        }
  }
?>

<div class="results">
    <?php if ($items > 0 && count($usernamearr)>0): ?>
        <div class="list-group">
            <?php for ($ord=0; $ord<$items; $ord++): ?>
                <div>
                    <div>Username: <?php safer_echo($usernamearr[$ord]); ?></div>
                </div>
                <div class="list-group-item">
                    <div>
                        <div>Name: <?php safer_echo($names[$ord]); ?></div>
                    </div>
                    <div>
                        <div>Quantity: <?php safer_echo($quantity[$ord]); ?></div>
                    </div>
                    <div>
                        <div>Price: <?php safer_echo($unit_price[$ord]); ?></div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    <?php elseif ($items > 0 && count($usernamearr)==0): ?>
        <div class="list-group">
            <div>
                <div>Username: <?php safer_echo($username); ?></div>
            </div>
            <?php for ($ord=0; $ord<$items; $ord++): ?>
                <div class="list-group-item">
                    <div>
                        <div>Name: <?php safer_echo($names[$ord]); ?></div>
                    </div>
                    <div>
                        <div>Quantity: <?php safer_echo($quantity[$ord]); ?></div>
                    </div>
                    <div>
                        <div>Price: <?php safer_echo($unit_price[$ord]); ?></div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <div>
        <div>Username: <?php safer_echo($username); ?></div>
        </div>
        <p>You have not yet made a purchase</p>
    <?php endif; ?>
</div>

<?php require(__DIR__ . "/partials/flash.php");