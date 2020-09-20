<?php
	function newline()
	{
		echo "<br>\n";
	}
	$arr1 = array(1,2,3,4,5,6,7,8);
	foreach($arr1 as $a)
	{
		echo "$a ";
	}
	newline();
	foreach($arr1 as $a)
	{
		if($a%2==0)
		{
			echo "$a ";
		}
	}
	
?>
