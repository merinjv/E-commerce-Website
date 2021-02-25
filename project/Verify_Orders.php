<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php 
    if (!is_logged_in()) {
        //Is not logged in
        flash("You don't have permission to access this page");
        die(header("Location: login.php"));
    }
?>
<?php
    if (isset($_GET["id"])) {
        $userID = $_GET["id"];

        $db = getDB();
        $stmt = $db->prepare("SELECT product_id, quantity FROM Carts WHERE user_id=$userID");
        $r = $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $doubleCheck = true;
        $errProd = array();
        foreach($results as $res)
        {
            $check = checkItemInProductTable($res["product_id"], $res["quantity"]);
            if(!$check)
            {
                $errProd[] = $res["product_id"]; 
            }
        }
        
        if(count($errProd)==0)
        {
              header("Location: ordersCreation.php?id=$userID");
       
        }
        else
        {
            $statement = "Order Error: ".getItemInProductTable($errProd)." .We apologize for the inconvenience, please update your cart and try again.";
            flash($statement);
            die(header("Location: cartView.php"));
        }
    
    
    }

?>
