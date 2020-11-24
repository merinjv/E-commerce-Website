<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
//Search result gathering
$query = "";
$results = [];
$category = "";
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
elseif(isset($_POST["category"])){
    $category = $_POST["category"];
}
if (isset($_POST["search"]) && !empty($query)) {
    $db = getDB();
    $qy = "SELECT id,name,quantity,price,description,visibility, user_id from Products WHERE name like :q ";
    if(!has_role("Admin"))
    {
        //visibility check
        $qy = "SELECT id,name,quantity,price,description,visibility, user_id from Products WHERE name like :q AND visibility=0 ";
    }
    if(isset($_POST["priceCheck"]))
    {
         $qy .="ORDER BY price ASC LIMIT 10";   
    }
    else{
        $qy .="LIMIT 10";   
    }
    $stmt = $db->prepare($qy);
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}
elseif(isset($_POST["categorize"])) {

    $db = getDB();
    $qy = "SELECT id,name,quantity,price,description,visibility, user_id from Products WHERE category=:category ";
    if(!has_role("Admin"))
    {
        $qy="SELECT id,name,quantity,price,description,visibility, user_id from Products WHERE category=:category AND visibility=0 ";
    }
    if(isset($_POST["priceCheck"]))
    {
         $qy .="ORDER BY price ASC LIMIT 10";   
    }
    else{
        $qy .="LIMIT 10";   
    }
    $stmt = $db->prepare($qy);
    $r = $stmt->execute([":category"=>$category]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }

}
?>
<form method="POST">
  <br>
    Category<input type="radio" name="sort" value="category"/>
    Search<input type="radio" name="sort" value="search"/>
    Price Sort<input type="checkbox" name="price" value="price"/> 
  </br>
    <input type="submit" value="Go" name="go"/>
</form>
<?php 

    $search = false;
    $categories = [];
    $res = [];
    $pCheck = false;
    $cateCheck = false;
    if(isset($_POST["go"])){
        $which = $_POST["sort"];
        if($which=="category"){
            $cateCheck = true;
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
        }
        elseif($which=="search"){
            $search = true;
        }
        if(isset($_POST["price"])){
            $pCheck=true;
        }
    }

?>  
<form method="POST">
    <?php if($search): ?>    
        <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
        <?php if($pCheck): ?>  
          <input type="hidden" value = "pCheck" name = "priceCheck"/>
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
            <?php endif; ?>
            <input type="submit" value="Search" name="categorize"/>
    <?php endif; ?>
</form>
<div class="results">
    <?php if (count($results) > 0): ?>
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
                        <div>Owner: <?php safer_echo($r["user_id"]); ?></div>
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
