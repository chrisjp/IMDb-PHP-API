<?php
require '../class_IMDb.php';
$imdb = new IMDb(true);

$q = trim(stripslashes($_GET['q']));
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Person by ID - IMDb-PHP-API</title>
</head>
<body>

<p>Enter a person's IMDb ID to return their data. Example: <em>nm0000288</em></p>
<form action="" method="get">
<input type="text" name="q" maxlength="9" value="<?=$q?>" /> <input type="submit" value="Find" /><br />
<input id="nosummary" type="checkbox" name="nosummary"<? if($_GET['nosummary']=="on") print ' checked="checked"'?> /> <label for="nosummary">Do NOT summarise?</label>
</form>
<br />

<?php
if(!empty($q)){
	print '<h2>Results</h2>';
	if($_GET['nosummary']=="on") $imdb->summary=false;
	$person = $imdb->person_by_id($q);

	print '<pre>';
	print_r($person);
	print '</pre>';
}
?>

</body>
</html>