<?php
include 'graph2.php';

$chart = new graph(400,240);
$chart->parameter['path_to_fonts'] = 'fonts/'; // path to where fonts are stored
$chart->parameter['title'] = 'Line and Point Chart';
$chart->parameter['x_label'] = 'Day of the Week';
$chart->parameter['y_label_left'] = 'Some Data';

$chart->x_data          = array('Friday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');

srand ((double) microtime() * 1000000);
$a = 25;
$b = 10;
$c = 18;
$d = 4;
$e = 9;
$f = 12;
foreach (array('alpha','beta','theta') as $i) {
	$a += rand(-4, 2);
	$b += rand(-2, 4);
	$c += rand(-4, 2);
	$d += rand(-2, 4);
	$e += rand(-4, 2);
	$f += rand(-2, 4);
	if ($a < 0) $a = 0;
	if ($b < 0) $b = 0;
	if ($c < 0) $c = 0;
	if ($d < 0) $d = 0;
	if ($e < 0) $e = 0;
	if ($f < 0) $f = 0;
	$chart->y_data[$i] = array($a, $b, $c, $d, $e, $f);
	//$chart->y_data[$i] = array(-189, -222, -546, -223, -451, -567);

}

$chart->parameter['point_size'] = 6;

// format for each data set
$chart->y_format['alpha'] =
  array('colour' => 'blue', 'line' => 'line', 'point' => 'square-open');

$chart->y_format['beta'] =
  array('colour' => 'red', 'line' => 'line', 'point' => 'square');

$chart->y_format['theta'] =
  array('colour' => 'green', 'line' => 'line', 'point' => 'circle');

// order in which to draw data sets.
$chart->y_order = array('alpha', 'beta', 'theta');
//$chart->y_order = array('alpha');

//$chart->parameter['y_max_left']= -600;
//$chart->parameter['yMinLeft']= -10;

$chart->parameter['y_resolution_left']= 1;
$chart->parameter['y_decimal_left']= 0;

//$chart->init();
//expand_pre($chart);
//exit;

// draw it.
$chart->draw();
?>