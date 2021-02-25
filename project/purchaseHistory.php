<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //Is not logged in
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$categories = [];
$db = getDB();
$stmt = $db->prepare("SELECT id,name,quantity,price,description,category,visibility, user_id from Products WHERE 1=1");
$re = $stmt->execute();
if ($re) {
 $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
 $x = 0;
 foreach($res as $p){
       if(!in_array($p["category"],$categories)){
            $categories[$x] = $p["category"];
       }
       $x++;
     }
 }
?>
<form method="POST">
  <?php if(has_role("Admin")): ?>
  <br>
  <div>Filter Results:</div>
    <label for="start">Start date:</label>
    <input type="date" id="start" name="start"
       value=""
       min="2020-01-01" max="2020-12-31">
    <label for="end">End date:</label>
    <input type="date" id="end" name="end"
       value=""
       min="2020-01-01" max="2020-12-31">
    <label>Category:</label>
    <select name="category">
            <option> <value="None">None</option>
            <?php foreach ($categories as $product): ?>
            <option> <value="$product"><?php safer_echo($product); ?></option>
        <?php endforeach; ?>
    </select>
    <input type="submit" value="Filter" name="Filter"/>
    <?php endif; ?>
  </br>
</form>
<?php
$username="";
$order_id = array();
$usernamearr = array();
$time = array();
$total_price = array();
$unit_price = array();
$productids = array();
$quantity = array();
$payment = array();
$address = array();
$items = 0;
$val=false;
$names = array();
$page = 1;
$per_page = 10;
$total_pages = 0;
$prds = array();
$calculate = 0;

if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}
if (!has_role("Admin")) {
    $user = get_user_id();
    
    $db = getDB();
      	$stmt = $db->prepare("SELECT username FROM Users WHERE id=$user");
      	$r = $stmt->execute();
        $usernames = $stmt->fetch(PDO::FETCH_ASSOC);
        $username = $usernames["username"];
        
    $db = getDB();
      	$stmt = $db->prepare("SELECT * FROM Orders WHERE user_id=$user ORDER BY ID DESC");
      	$r = $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        //flash($orde["id"]);
       $total = 0;
       $offset = 0;
       $calculate = 0;
       $in = 0;
       $orderid="";
       foreach($orders as $order){
             //flash($order["id"]);
             if($in==0){
              $orderid ="".$order["id"];
              $in++;
              }
              else
                $orderid .=" OR order_id=".$order["id"];
             
            
            $db = getDB();
          	$stmt = $db->prepare("SELECT count(*) as total from OrderItems WHERE order_id=$orderid");
          	$r = $stmt->execute();
            $products = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($products){
                $total += (int)$products["total"];
            }
            
            $total_pages = ceil($total / $per_page);
            $offset = ($page-1) * $per_page;
       }

            $stmt = $db->prepare("SELECT product_id,unit_price,quantity,created FROM OrderItems WHERE order_id=$orderid LIMIT :offset, :count");
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            
            $stmt->execute();
            $e = $stmt->errorInfo();
            if($e[0] != "00000"){
                flash(var_export($e, true), "alert");
            }
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            foreach($products as $product)
            {

                $calculate += $product["quantity"]*$product["unit_price"];
                $time[] = $product["created"];
                $product_id = $product["product_id"];
                $unit_price[] = $product["unit_price"];
                $quantity[] = $product["quantity"];
                $db = getDB();
              	$stmt = $db->prepare("SELECT name FROM Products WHERE id=$product_id");
              	$r = $stmt->execute();
                $productName = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $names[] = $productName["name"];
                $productids[] = $product_id;
                $items++;
                
                //if($items==10){
                //  $val=true;
                 // break;
                 // }
            }
            //if($val)
              //break;
      
}
elseif(has_role("Admin")){
$db = getDB();
$q1 = "SELECT count(*) as total from OrderItems";
$q2 = "SELECT product_id,unit_price,quantity,created FROM OrderItems";
$stmt = $db->prepare("SELECT * FROM Orders ORDER BY ID DESC");
$r = $stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!empty($_POST["start"]) && !empty($_POST["end"]) && !empty($_POST["category"]) && $_POST["category"]!="None" && isset($_POST["Filter"])){
        $start = $_POST["start"];
        $end = $_POST["end"];
        $category = $_POST["category"];
        $cts = "";
        
        //flash($category);
        
        $stmt = $db->prepare("SELECT id FROM Products WHERE category=:category");
   	    $r = $stmt->execute([":category"=>$category]);
        $ca = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //$cts = "category = ".$ca[$ind]["id"];
        //flash(count($ca));
        $in = 0;
        foreach($ca as $cas)
        {
            if($in==0)
            {
                $cts = "".$cas["id"]." ";
            }
            else
            {
                $cts .= "OR product_id = ".$cas["id"]." ";
            }
            $in++;
        }
        //flash($cts);
        $q1 = "SELECT count(*) as total from `OrderItems` WHERE product_id=".$cts."AND DATE(created) between '$start' and '$end'";
        $q2="SELECT * FROM `OrderItems` WHERE product_id=".$cts."AND DATE(created) between '$start' and '$end' ORDER BY `created` DESC";
    }
    elseif(!empty($_POST["category"]) && $_POST["category"]!="None" &&  isset($_POST["Filter"])){
        $category = $_POST["category"];
        $cts = "";
        
        //flash($category);
        
        $stmt = $db->prepare("SELECT id FROM Products WHERE category=:category");
   	    $r = $stmt->execute([":category"=>$category]);
        $ca = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //$cts = "category = ".$ca[$ind]["id"];
        //flash(count($ca));
        $in = 0;
        foreach($ca as $cas)
        {
            if($in==0)
            {
                $cts = "".$cas["id"]." ";
            }
            else
            {
                $cts .= "OR product_id = ".$cas["id"]." ";
            }
            $in++;
        }
        $q1= "SELECT count(*) as total from OrderItems WHERE product_id=".$cts;
        $q2="SELECT * FROM OrderItems WHERE product_id=".$cts;

    }
    elseif(!empty($_POST["start"]) && !empty($_POST["end"]) && isset($_POST["Filter"])){
        $start = $_POST["start"];
        $end = $_POST["end"];
        
        $q1= "SELECT count(*) as total from OrderItems WHERE DATE(created) between '$start' and '$end'";
        $q2="SELECT * FROM OrderItems WHERE DATE(created) between '$start' and '$end' ORDER BY created DESC";

    }
    elseif(isset($_GET["page"])){
        $q1 = "SELECT count(*) as total from OrderItems";
        $q2 = "SELECT product_id,unit_price,quantity,created FROM OrderItems";
    }
        
        $order_id = array();
        $time = array();
        $calculate = 0;
        
        $unit_price = array();
        $quantity = array();
        $items = 0;
        $val=false;
        $names = array();
        $usernamearr = array();
        //$products=array();
        $total = 0;
        $offset = 0;
        

            //$orderid = $order["id"];
            $db = getDB();
          	$stmt = $db->prepare($q1);
          	$r = $stmt->execute();
            $prds = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($prds){
                $total += (int)$prds["total"];
            }
            
            $total_pages = ceil($total / $per_page);
            $offset = ($page-1) * $per_page;
        
        $q2.=" LIMIT :offset, :count";
        $stmt = $db->prepare($q2);
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            
            $stmt->execute();
            $e = $stmt->errorInfo();
            if($e[0] != "00000"){
                flash(var_export($e, true), "alert");
            } 
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
      foreach($products as $product)
            {
                $calculate += $product["quantity"]*$product["unit_price"];
            }

       foreach($orders as $order){
            $order_id[] = $order["id"];
            $orderid = $order["id"];
            $payment[] = $order["payment_method"];
            $address[] = $order["address"];
            $user = $order["user_id"];
            $db = getDB();
          	$stmt = $db->prepare("SELECT username FROM Users WHERE id=$user");
          	$r = $stmt->execute();
            $usernames = $stmt->fetch(PDO::FETCH_ASSOC);
            $usernamearr[] = $usernames["username"];
            
            foreach($products as $product)
            {
                $time[] = $product["created"];
                //flash($product["product_id"]);
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
               // if($items==10){
                 // $val=true;
                  //break;
                  //}
            }
           // if($val)
             // break;
        }
       
  //flash($calculate);
  }
$uord = 0;
  $nord = 0;
  $qord = 0;
  $unord = 0;
  $ord = 0;
  $tord = 0;
  $pord = 0;

?>
<div class="results">
    <br>
    <div class="card-subtitle">Total Price: $<?php safer_echo($calculate); ?></div>
    </br>
    <?php if ($items>0 && count($usernamearr)>0): ?>
        <div class="list-group">
            <?php foreach ($products as $product): ?>
                <div>
                    <div>Username: <?php safer_echo($usernamearr[$uord++]); ?></div>
                </div>
                <div class="list-group-item">
                    <div>
                        <div>Name: <?php safer_echo($names[$nord++]); ?></div>
                    </div>
                    <div>
                        <div>Quantity: <?php safer_echo($quantity[$qord++]); ?></div>
                    </div>
                    <div>
                        <div>Price: <?php safer_echo($unit_price[$unord++]); ?></div>
                    </div>
                    <div>
                        <div>Order Time: <?php safer_echo($time[$tord++]); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($items > 0 && count($usernamearr)==0): ?>
        <div class="list-group">
            <div>
                <div>Username: <?php safer_echo($username); ?></div>
            </div>
            <?php foreach ($products as $product): ?>
                <div class="list-group-item">
                    <div>
                        <div>Name: <?php safer_echo($names[$nord++]); ?></div>
                    </div>
                    <div>
                        <div>Quantity: <?php safer_echo($quantity[$qord++]); ?></div>
                    </div>
                    <div>
                        <div>Price: <?php safer_echo($unit_price[$unord++]); ?></div>
                    </div>
                    <div>
                        <div>Order Time: <?php safer_echo($time[$tord++]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="productView.php?id=<?php safer_echo($productids[$ord++]); ?>">Rate</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div>
        <div>Username: <?php safer_echo($username); ?></div>
        </div>
        <p>No Purchases</p>
    <?php endif; ?>
</div>
</div>
        <nav aria-label="Orders History">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                    <a class="page-link" href="?page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
                </li>
                <?php for($i = 0; $i < $total_pages; $i++):?>
                <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a></li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                    <a class="page-link" href="?page=<?php echo $page+1;?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>

<?php require(__DIR__ . "/partials/flash.php");