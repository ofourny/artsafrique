<pre>

<?php 
var_dump('lol3');

$dataString = explode("|",$_GET['Data']);
$dataArray = [];
foreach ($dataString as $key => $value) {
	$rep = explode("=",$value);
	$dataArray[$rep[0]] = $rep[1];
}

var_dump($dataArray);
var_dump('lol4') 

?>

</pre>