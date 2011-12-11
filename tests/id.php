<?php
require '../class_IMDb.php';
$imdb = new IMDb(true);

$q = trim(stripslashes($_GET['q']));
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Find by ID - IMDb-PHP-API</title>
</head>
<body>

<p>Enter a movie's IMDb ID to return its data. Example: <em>tt0068646</em></p>
<form action="" method="get">
<input type="text" name="q" maxlength="9" value="<?=$q?>" /> <input type="submit" value="Find" /><br />
<input id="nosummary" type="checkbox" name="nosummary"<? if($_GET['nosummary']=="on") print ' checked="checked"'?> /> <label for="nosummary">Do NOT summarise?</label>
</form>
<br />

<?php
if(!empty($q)){
	print '<h2>Results</h2>';
	if($_GET['nosummary']=="on") $imdb->summary=false;
	$movie = $imdb->find_by_id($q);

	print '<pre>';
	print_r($movie);
	print '</pre>';
}
?>

</body>
</html>