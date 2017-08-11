<?php
// Get data only from this time from this city...
$city_date_filter = array(
		'44'	=> array('from' => '2017-08-01'),	// Bangalore
		'6'		=> array('from' => '2017-08-01'),	// Vellore
		'7'		=> array('from' => '2017-08-01'),	// Vizag
		'12'	=> array('from' => '2017-08-01'),	// Bhopal
		'17'	=> array('from' => '2017-08-01'),	// Hyd
		'19'	=> array('from' => '2017-08-01'),	// Coimbatore
		'9'		=> array('from' => '2017-08-01'),	// Mumbai
		'3'		=> array('from' => '2017-08-01'),	// Cochin
		'8'		=> array('from' => '2017-08-01'),	// Nagpur
		'20'	=> array('from' => '2017-08-01'),	// Delhi
		'11'	=> array('from' => '2017-08-01'),	// Kolkatta
		'13'	=> array('from' => '2017-08-01'),	// Ahmd
		'18'	=> array('from' => '2017-08-01'),	// Guntur
		'16'	=> array('from' => '2017-08-01'),	// Wada
		'15'	=> array('from' => '2017-08-01'),	// Tvm
		'22'	=> array('from' => '2017-08-01'),	// Lucknow
		'23'	=> array('from' => '2017-08-01'),	// Gwalior
		'14'	=> array('from' => '2017-08-01'),	// Chennai
		'5'		=> array('from' => '2017-08-01'),	// Mysore
		'10'	=> array('from' => '2017-08-01'),	// Pune
		'4'		=> array('from' => '2017-08-01'),	// Mlore
		'21'	=> array('from' => '2017-08-01'),	// Chandigarh
		'24'	=> array('from' => '2017-08-01'),	// Dun
		'25'	=> array('from' => '2017-08-01'),	// National
	);
$filter_array = array();
foreach ($city_date_filter as $this_city_id => $dates) {
	$filter_array[] = "(users.city_id=$this_city_id AND D.created_at >= '$dates[from] 00:00:00')";
}
$exclude_people = "(users.id != 29 AND users.id != 146 AND users.id != 27 AND users.id != 150)";
$city_checks = "(" . implode(" \nOR ", $filter_array) . ") \nAND $exclude_people";