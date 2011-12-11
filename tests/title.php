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
  <title>Find by Title - IMDb-PHP-API</title>
</head>
<body>

<p>Enter a search term to return data for matching movie titles. Example: <em>The Godfather</em></p>
<form action="" method="get">
<input type="text" name="q" value="<?=$q?>" /> <input type="submit" value="Search" /><br />
<input type="text" name="year" value="<?=$year?>" maxlength="4" /> Specify year (<em>Optional</em>)<br />
<input id="nosummary" type="checkbox" name="nosummary"<? if($_GET['nosummary']=="on") print ' checked="checked"'?> /> <label for="nosummary">Do NOT summarise?</label><br />
<input type="text" name="limit" value="<?=intval($_GET['limit'])?>" maxlength="2" /> Max. titles returned
</form>
<br />

<?php
if(!empty($q)){
	print '<h2>Results</h2>';
	if($_GET['nosummary']=="on") $imdb->summary=false;
	if(intval($_GET['limit'])>0) $imdb->titlesLimit = intval($_GET['limit']);
	$movies = $imdb->find_by_title($q, $year);

	print '<pre>';
	print_r($movies);
	print '</pre>';
}
?>

</body>
</html>