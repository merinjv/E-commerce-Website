<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //Only admin/shopowner
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
else{
$visibility = 0;
}
?>

<form method="POST">
	<label>Name</label>
	<input name="name" placeholder="Name"/></br>
	<label>Quantity</label>
	<input type="number" min="0" name="quantity"/></br>
	<label>Price</label>
	<input type="number" min="0.00" name="price"/></br>
	<label>Description</label>
	<input type="text" name="description"/></br>
  <label>Category</label>
	<input type="text" name="category"/></br>
  <label>Visibility (Yes:0 No:1)</label>
  <select name="visibility">
     <option> <value="-1">None</option>
     <option> <value="0">0</option>
     <option> <value="1">1</option>
  </select></br>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$name = $_POST["name"];
	$quantity = $_POST["quantity"];
	$price = $_POST["price"];
	$desc = $_POST["description"];
  $category = $_POST["category"];
  $visibility = $_POST["visibility"];
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Products(name, quantity, price, description, category, user_id, visibility) VALUES(:name, :quantity, :price, :desc, :category, :user, :visibility)");
	$r = $stmt->execute([
		":name"=>$name,
		":quantity"=>$quantity,
		":price"=>$price,
		":desc"=>$desc,
    ":category"=>$category,
		":user"=>$user,
    ":visibility" =>$visibility
	]);
	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");
