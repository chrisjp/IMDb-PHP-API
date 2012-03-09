<?php
require '../class_IMDb.php';
$imdb = new IMDb(true);

$chart = trim(stripslashes($_GET['chart']));
$charts = array(
				"top" => "Top 250",
				"bottom" => "Bottom 100",
				"boxoffice" => "Box Office (US)"
          );
foreach($charts as $cid => $cname){
	if($chart==$cid) $s=' selected="selected"';
	$options .= '<option value="'.$cid.'"'.$s.'>'.$cname.'</option>'."\n";
	unset($s);
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Charts - IMDb-PHP-API</title>
</head>
<body>

<p>Various charts from IMDb.</p>
<form action="" method="get">
<select name="chart">
<?=$options?>
</select>
<input type="submit" value="Show" />
</form>

<?php
print '<h2>Results</h2>';
if($chart=="top") $movies = $imdb->chart_top();
else if($chart=="bottom") $movies = $imdb->chart_bottom();
else if($chart=="boxoffice") $movies = $imdb->boxoffice();

print '<pre>';
if($movies) print_r($movies);
print '</pre>';
?>

</body>
</html>