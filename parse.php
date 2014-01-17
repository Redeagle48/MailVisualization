<?php
global $days_array;	
global $months_array;
global $years_array;
	
function parseLocationFile($path) {
	$file_handle = fopen($path, "r") or exit("Unable to open file!");
	global $days_array;
	global $months_array;
	global $years_array;
	$days_array = array();	
	$months_array = array();
	$years_array = array();	
	$dateCounter = -1;
	$lineCounter = -1;
	$table = null;
	
	while(!feof($file_handle)){
		$line = fgets($file_handle);
		$isADateLine = count($date = explode("--",$line)) == 2;
		$isALocationLine = count($line = explode(":",$line)) == 2;
		if($isADateLine) {
			$tmp = explode("_",$date[1]);
			$years_array[] = $tmp[0];
			$months_array[] = $tmp[1];
			$days_array[] = $tmp[2];
			$dateCounter++;
			$lineCounter = -1;
			continue;
		} elseif($isALocationLine) {
			$lineCounter++;
			$loc = $line[1];
			$fullHours = explode("-",$line[0]);
			$hBegin = $fullHours[0][0].$fullHours[0][1];
			$hEnd = $fullHours[1][0].$fullHours[1][1];
			$mBegin = $fullHours[0][2].$fullHours[0][3];
			$mEnd = $fullHours[1][2].$fullHours[1][3];
			$table[$dateCounter][$lineCounter]["hBegin"] = $hBegin;
			$table[$dateCounter][$lineCounter]["mBegin"] = $mBegin;
			$table[$dateCounter][$lineCounter]["hEnd"] = $hEnd;
			$table[$dateCounter][$lineCounter]["mEnd"] = $mEnd;
			$table[$dateCounter][$lineCounter]["location"] = $loc;
		}
	}
	fclose($file_handle);
    return $table;
}

?>
