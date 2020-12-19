<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//fetch
$result = [];
$canRate = false;
$ratingbox = false;
$allratings = array();
$page = 1;
$per_page = 10;
$total_pages = 0;
$userPriv = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Products.id,name,quantity,price,description, user_id, Users.username FROM Products as Products JOIN Users on Products.user_id = Users.id where Products.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
    
    if(is_logged_in() && !has_role("Admin")){
    $curr_user_id = get_user_id();
    $canRate = false;
    
     $db = getDB();
   	 $stmt = $db->prepare("SELECT id FROM Orders WHERE user_id=$curr_user_id");
   	 $r = $stmt->execute();
     $order = $stmt->fetchAll(PDO::FETCH_ASSOC);
     
     if(!empty($order)){
         foreach($order as $ord){
             $ord_id = $ord['id'];
             $db = getDB();
       	     $stmt = $db->prepare("SELECT product_id FROM OrderItems WHERE order_id=$ord_id");
       	     $r = $stmt->execute();
             $prod = $stmt->fetchAll(PDO::FETCH_ASSOC);
             foreach($prod as $p){
               if($id == $p['product_id']){
                   $canRate = true;
                   break;
                   
               }
             }
             if($canRate)
               break;
         }
     
     }
     
     if(isset($_POST["rate"]))
     {
       $ratingbox = true;
       $canRate = false;
     }
     if(isset($_POST["rated"]))
     {
       $rating = $_POST["rate"];
       $comment = $_POST["comment"];
       $db = getDB();
 	     $stmt = $db->prepare("INSERT INTO Ratings(product_id, user_id, rating, comment) VALUES(:product_id, :user_id, :rating, :comment)");
 	     $r = $stmt->execute([
        ":product_id"=>$id,
        ":user_id"=>$curr_user_id,
        ":rating"=>$rating,
        ":comment"=>$comment
        ]);
       $ratingbox = false;
     }
     }
    $total = 0;
    $offset = 0;
    
    $db = getDB();
   	$stmt = $db->prepare("SELECT count(*) as total from Ratings");
   	$r = $stmt->execute();
    $prds = $stmt->fetch(PDO::FETCH_ASSOC);
            
    if($prds){
        $total += (int)$prds["total"];
    }
            
    $total_pages = ceil($total / $per_page);
    $offset = ($page-1) * $per_page;
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Ratings WHERE product_id=:product_id ORDER BY id DESC LIMIT :offset, :count");
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt->bindValue(":product_id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $e = $stmt->errorInfo();
    if($e[0] != "00000"){
        flash(var_export($e, true), "alert");
    } 
    $allratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($allratings as $onerating){
         if($onerating["user_id"]==$curr_user_id){
             $canRate = false;
             break;
         }
    }
    
     foreach($allratings as $onerating){
         $rater = $onerating["user_id"];
         $db = getDB();
       	 $stmt = $db->prepare("SELECT privacy, username FROM Users WHERE id=$rater");
       	 $r = $stmt->execute();
         $user = $stmt->fetch(PDO::FETCH_ASSOC);
         
         if($user["privacy"]==2){
           $userPriv[]="Anonymous";
         }
         else{
           $userPriv[]=$user["username"];
         }
     }
}
$db = getDB();
    $stmt = $db->prepare("SELECT TRUNCATE(AVG(rating),1) as average from Ratings WHERE product_id=$id");
    $re = $stmt->execute();
    $rating = $stmt->fetch(PDO::FETCH_ASSOC);
    $ratingsavg = $rating["average"];
       
    $db = getDB();
    $stmt = $db->prepare("UPDATE Products set rating=$ratingsavg where id=$id");
    $r = $stmt->execute();
$index = 0;
?>

<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <?php safer_echo($result["name"]); ?>
        </div>
        <div class="card-body">
            <div>
                <div class="card-subtitle">Product Description</div>
                <div>Quantity: <?php safer_echo($result["quantity"]); ?></div>
                <div>Price: <?php safer_echo($result["price"]); ?></div>
                <div>Description: <?php safer_echo($result["description"]); ?></div>
            </div>
            <?php if ($canRate): ?>
            <form method = "POST">
                <input type="submit" name="rate" value="Rate"/>
            </form>
            <?php endif; ?>
            <?php if ($ratingbox): ?>
            <form method = "POST">
                <div class = "card-subbody">
                <label>Rate:</label>
                <select name="rate">
                   <option> <value="1">1</option>
                   <option> <value="1">1</option>
                   <option> <value="2">2</option>
                   <option> <value="3">3</option>
                   <option> <value="4">4</option>
                   <option> <value="5">5</option>
                </select></br>
                <label>Comment: </label>
	              <input type="text" name="comment"/></br>
                <input type="submit" name="rated" value="Rate"/>
            </form>
            <?php endif; ?>    
        <br>
        <?php if (isset($allratings) &&!empty($allratings)): ?>
            <div class = "card-rating">
                    <div class="card-subtitle">Product Ratings</div>
                    <?php foreach($allratings as $onerating): ?>
                        <div>User: <?php safer_echo($userPriv[$index++]); ?></div>
                        <div>Rating: <?php safer_echo($onerating["rating"]); ?></div>
                        <div>Comment: <?php safer_echo($onerating["comment"]); ?></div>
                        <br></br>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?php safer_echo("No ratings"); ?>
        <?php endif; ?>
        </br>
        </div>
        <nav aria-label="Review Orders">
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
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>

<?php require(__DIR__ . "/partials/flash.php");
