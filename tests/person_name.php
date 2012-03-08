<?php
require '../class_IMDb.php';
$imdb = new IMDb(true);

$q = trim(stripslashes($_GET['q']));
$year = $_GET['year'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Person by Name - IMDb-PHP-API</title>
</head>
<body>

<p>Enter a search term to return data for matching names. Example: <em>Christian Bale</em></p>
<form action="" method="get">
<input type="text" name="q" value="<?=$q?>" /> <input type="submit" value="Search" /><br />
<input id="nosummary" type="checkbox" name="nosummary"<? if($_GET['nosummary']=="on") print ' checked="checked"'?> /> <label for="nosummary">Do NOT summarise?</label><br />
<input type="text" name="limit" value="<?=intval($_GET['limit'])?>" maxlength="2" /> Max. people returned
</form>
<br />

<?php
if(!empty($q)){
	print '<h2>Results</h2>';
	if($_GET['nosummary']=="on") $imdb->summary=false;
	if(intval($_GET['limit'])>0) $imdb->titlesLimit = intval($_GET['limit']);
	$people = $imdb->person_by_name($q);

	print '<pre>';
	print_r($people);
	print '</pre>';
}
?>

</body>
</html>