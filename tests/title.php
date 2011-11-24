<?php
require '../class_IMDb.php';
$imdb = new IMDb(true);

$q = trim(stripslashes($_GET['q']));
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
<input type="text" name="q" value="<?=$q?>" /> <input type="submit" value="Search" />
</form>
<br />

<?php
if(!empty($q)){
	print '<h2>Results</h2>';
	
	$movies = $imdb->find_by_title($q);

	print '<pre>';
	print_r($movies);
	print '</pre>';
}
?>

</body>
</html>