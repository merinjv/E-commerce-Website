<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
//Search result gathering
$query = "";
$results = [];
$category = "";
$page = 1;
$per_page = 10;
$total_pages = 0;
$ratingsavg = [];
$q = 0;
$prices = 0;
$rates = "";
if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
        $query = data("query");
        $category = data("category");
        $search = data("search");
        $prices = data("priceCheck");
        $rates = data("ratingCheck");
        //flash($rates."ok");
        
    }
    catch(Exception $e){

    }
}
//set initial values
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
elseif(isset($_POST["category"])){
    $category = $_POST["category"];
}
elseif(isset($_POST["quantity"])){
    $q = $_POST["quantity"];
}
//database selection
if((isset($_POST["quantity"]) || !empty($q)) && has_role("Admin")){
//out of stock check and quantity search
$total = 0;
    $offset = 0;
    $db = getDB();
    $qy = "SELECT count(*) as total from Products WHERE quantity=$q ";
    $stmt = $db->prepare($qy);
    $r = $stmt->execute([]);
    if ($r) {
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if($res){
                $total += (int)$res["total"];
            }
            
            $total_pages = ceil($total / $per_page);
            $offset = ($page-1) * $per_page;
    }
    $qy = "SELECT * from Products WHERE quantity=$q ";
    
    $qy .="LIMIT :offset, :count";   
    
    $stmt = $db->prepare($qy);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $e = $stmt->errorInfo();
    if($e[0] != "00000"){
         //flash(var_export($e, true), "alert");
    } 
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
elseif((isset($_POST["search"]) || !empty($search)) && !empty($query)) {
    $total = 0;
    $offset = 0;
    $db = getDB();
    $qy = "SELECT count(*) as total from Products WHERE name like :q or category like :q ";
     if(!has_role("Admin"))
    {
        //visibility check
        $qy = "SELECT count(*) as total from Products WHERE (name like :q or category like :q) AND visibility=0 ";
    }
    $stmt = $db->prepare($qy);
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r) {
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if($res){
                $total += (int)$res["total"];
            }
            
            $total_pages = ceil($total / $per_page);
            $offset = ($page-1) * $per_page;
    }
    
    $qy = "SELECT * from Products WHERE name like :q or category like :q ";
    if(!has_role("Admin"))
    {
        //visibility check
        $qy = "SELECT * from Products WHERE (name like :q or category like :q) AND visibility=0 ";
    }
    if(isset($_POST["priceCheck"]) || !empty($prices))
    {

         $qy .="ORDER BY price ASC LIMIT :offset, :count ";  
    }
    elseif(isset($_POST["ratingCheck"]) || !empty($rates))
    {
        $qy .="ORDER BY rating DESC LIMIT :offset, :count ";
    }
    else{
        $qy .="LIMIT :offset, :count";   
    }
    $stmt = $db->prepare($qy);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt->bindValue(":q", "%$query%", PDO::PARAM_STR);
    $stmt->execute();
    $e = $stmt->errorInfo();
    if($e[0] != "00000"){
         //flash(var_export($e, true), "alert");
    } 
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
elseif(isset($_POST["categorize"]) || !empty($category)) {

    $total = 0;
    $offset = 0;
    $db = getDB();
    $qy = "SELECT count(*) as total from Products WHERE category=:category ";
     if(!has_role("Admin"))
    {
        //visibility check
        $qy = "SELECT count(*) as total from Products WHERE category=:category AND visibility=0 ";
    }
    $stmt = $db->prepare($qy);
    $r = $stmt->execute([":category" => "$category"]);
    if ($r) {
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if($res){
                $total += (int)$res["total"];
            }
            
            $total_pages = ceil($total / $per_page);
            $offset = ($page-1) * $per_page;
    }
    $db = getDB();
    $qy = "SELECT * from Products WHERE category=:category ";
    if(!has_role("Admin"))
    {
        //visibility check
        $qy="SELECT * from Products WHERE category=:category AND visibility=0 ";
    }
    if(isset($_POST["priceCheck"]) || !empty($prices))
    {
         $qy .="ORDER BY price ASC LIMIT :offset, :count";   
    }
    elseif(isset($_POST["ratingCheck"]) || !empty($rates))
    {
        $qy .="ORDER BY rating DESC LIMIT :offset, :count ";
    }
    else{
        $qy .="LIMIT :offset, :count";   
    }
    $stmt = $db->prepare($qy);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt->bindValue(":category", "$category", PDO::PARAM_STR);
    $stmt->execute();
    $e = $stmt->errorInfo();
    if($e[0] != "00000"){
         //flash(var_export($e, true), "alert");
    } 
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$rated = [];
    foreach($results as $r)
    {
      if($r["rating"]==0)
      {
          $rated[] = "no ratings";
      }
      else{
          $rated[] = "".$r["rating"];
      }
    }
$index=0;
?>
<form method="POST">
  <br>
    Category<input type="radio" name="sort" value="category"/>
    Search<input type="radio" name="sort" value="search"/>
    <div class=sort>
    Sort By:<select name="sorting">
            <option> <value="-1">NONE</option>
            <option> <value="price">price</option>
            <option> <value="rating">rating</option>
    </select>
    </div> 
  </br>
    <input type="submit" value="Go" name="go"/>
    <?php if(has_role("Admin")): ?>
    Search By Quantity:<input type="number" name="quantity"/>
    <input type="submit" value="View" name="View"/>
    <?php endif; ?>
</form>
<?php 
    $search = false;
    $rateds = [];
    $quant = 0;
    $indexs = 0;
    $categories = [];
    $res = [];
    $pCheck = false;
    $rCheck = false;
    $qCheck = false;
    $cateCheck = false;
    if(isset($_POST["go"])){
        $which = $_POST["sort"];
        if($which=="category"){
            $cateCheck = true;
            $db = getDB();
            $que="";
            if(has_role("Admin")){
              $que = "SELECT id,name,quantity,price,description,category,visibility, user_id from Products WHERE 1=1";
            }
            else{
              $que = "SELECT id,name,quantity,price,description,category,visibility, user_id from Products WHERE 1=1 AND visibility=0";
            }
            $stmt = $db->prepare($que);
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
        }
        elseif($which=="search"){
            $search = true;
        }
        if(isset($_POST["sorting"]) && $_POST["sorting"]=="price"){
            $pCheck=true;
        }
        elseif(isset($_POST["sorting"]) && $_POST["sorting"]=="rating"){
            $rCheck=true;
        }
    }
    elseif(isset($_POST["View"])){
        if(isset($_POST["quantity"])){
            
            $quant = $_POST["quantity"];
        }
    
    }

?>  
<form method="POST">
    <?php if($search): ?>    
        <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
        <?php if($pCheck): ?>  
          <input type="hidden" value = "pCheck" name = "priceCheck"/>
        <?php elseif($rCheck): ?>  
          <input type="hidden" value = "rCheck" name = "ratingCheck"/>
        <?php endif; ?>
        <input type="submit" value="Search" name="search"/>
    <?php elseif($cateCheck): ?>
        <label>Select Category</label>
            <select name="category">
                <option> <value="-1">None</option>
                <?php foreach ($categories as $product): ?>
                    <option> <value="$product"><?php safer_echo($product); ?></option>
                <?php endforeach; ?>
            </select>
            <?php if($pCheck): ?>  
              <input type="hidden" value = "pCheck" name = "priceCheck"/>
            <?php elseif($rCheck): ?>  
              <input type="hidden" value = "rCheck" name = "ratingCheck"/>
            <?php elseif($quant>0): ?>
            <input type="number" name="quantity" value=$quant/>
            <?php endif; ?>
            <input type="submit" value="Search" name="categorize"/>
    <?php endif; ?>
</form>
<div class="results">
    <?php if (count($results) > 0 && !$qCheck): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <div>
                        <div>Name: <?php safer_echo($r["name"]); ?></div>
                    </div>
                    <div>
                        <div>Quantity: <?php safer_echo($r["quantity"]); ?></div>
                    </div>
                    <div>
                        <div>Price: <?php safer_echo($r["price"]); ?></div>
                    </div>
                    <div>
                        <div>Rating: <?php safer_echo($rated[$index++]); ?></div>
                    </div>
                    <div>
                        <?php if(has_role("Admin")): ?>
                            <a type="button" href="productEdit.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                        <?php endif; ?>
                        <a type="button" href="productView.php?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
</div>
        <nav aria-label="Orders History">
            <ul class="pagination justify-content-flex-start">
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