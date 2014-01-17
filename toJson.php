<?php
///////////////////////////////////////////
//Emails recebidos
//////////////////////////////////////////

$txt_file    = file_get_contents("./" . $username[0]);
$headers      = explode("_*H*_", $txt_file);
array_shift($headers);

$arr = array();

$resultWithResponse = array(); //mails recebidos com resposta

$finalResults = array(); //min, max, average
define('IS_RESPONSE', 24 * 60 * 60 * 3); //Mail dentro de 3 dias considerado resposta */

foreach($headers as $row => $data)
{
    //get row data
    $row_data = explode('_*F*_', $data);

    $info[$row]['from'] = $row_data[0];
    $info[$row]['date'] = $row_data[1];
    $info[$row]['answeredFlag'] = $row_data[2];
    $info[$row]['to'] = $row_data[3];
    
    
        if(isset($arr[$info[$row]['from']]) | array_key_exists($arr[$info[$row]['from']], $arr)) {
            $arr[$info[$row]['from']]["size"] = $arr[$info[$row]['from']]["size"]+1;
            //$arr[$info[$row]['from']]["date"] = $info[$row]['date'] ;
        } else {
            //echo 'Entrei 2 </br>';
            $name = explode(" <", $info[$row]['from'])[0];
            $arr[$info[$row]['from']] = array(
                                            "name"=>$name,//explode("\"", $name)[1],
                                            "size"=>1
                    );
        }
       
        if(strpos($info[$row]['answeredFlag'], "A") !== false) {
            $resultWithResponse[$info[$row]['date']] = array("From" => $info[$row]['from'],
                                                 "Answered" => $info[$row]['answeredFlag'], 
                                                 "Date" => $info[$row]['date'],
                                                 "Match" => 0); 
        }    

}

//echo 'No toJson.php </br></br>';
//print_r($resultWithResponse);


function aasort (&$array, $key) {
    $sorter=array(); $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    arsort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

aasort($arr,"size");

$arrReceived = array_slice($arr, 0, 30);
$out = array_values($arrReceived);

$data1 = array(

    "name" => "info",
    "children" => [array(
                        "name" => "vis",
                        "children" => $out)]

);


//$dataOut = array_values($data1);
//print_r(json_encode($out));
//print_r($out);

$fp = fopen('received_results.json', 'w+');
fwrite($fp, json_encode($data1));
fclose($fp);

///////////////////////////////////////////
//Emails enviados
//////////////////////////////////////////

$txt_fileSent    = file_get_contents("./" . 'sent-' . $username[0]);
$headersSent      = explode("_*H*_", $txt_fileSent);
array_shift($headersSent);

$arrSent = array();
$total = array();

foreach($headersSent as $rowSent => $dataSent)
{
    //get row data
    $row_dataSent = explode('_*F*_', $dataSent);

    $infoSent[$rowSent]['from'] = $row_dataSent[0];
    $infoSent[$rowSent]['date'] = $row_dataSent[1];
    $infoSent[$rowSent]['answeredFlag'] = $row_dataSent[2];
    $infoSent[$rowSent]['to'] = $row_dataSent[3];
    
    
        if(isset($arrSent[$infoSent[$rowSent]['to']]) | array_key_exists($arr[$infoSent[$rowSent]['to']], $arrSent)) {
            $arrSent[$infoSent[$rowSent]['to']]["size"] = $arrSent[$infoSent[$rowSent]['to']]["size"]+1;
            //$arr[$info[$row]['from']]["date"] = $info[$row]['date'] ;
        } else {
            //echo 'Entrei 2 </br>';
            $sentName = explode(" <", $infoSent[$rowSent]['to'])[0];
            $arrSent[$infoSent[$rowSent]['to']] = array(
                                            "name"=>$sentName,
                                            "size"=>1
                    );
        }
        
        foreach($resultWithResponse as $date => $values) {
            
            if($values['Match'] === 0 && $values['From'] == $infoSent[$rowSent]['to']){
                if($date < $infoSent[$rowSent]['date'] &&  $date > $infoSent[$rowSent]['date'] - IS_RESPONSE) {
                    $resultWithResponse[$date]['Match'] = 1;
                    
                    $gap = $infoSent[$rowSent]['date']-$date;
                   
                    if(array_key_exists($infoSent[$rowSent]['to'], $finalResults) == false) {
                        $finalResults[$infoSent[$rowSent]['to']]['count'] = 1;
                        $finalResults[$infoSent[$rowSent]['to']]['min'] = $gap;
                        $finalResults[$infoSent[$rowSent]['to']]['max'] = $gap;
                        $finalResults[$infoSent[$rowSent]['to']]['average'] = $gap;
                        
                    } else {
                        
                        if($finalResults[$infoSent[$rowSent]['to']]['min'] > $gap) {
                            $finalResults[$infoSent[$rowSent]['to']]['min'] = $gap;
                        } else if($finalResults[$infoSent[$rowSent]['to']]['max'] < $gap) {
                            $finalResults[$infoSent[$rowSent]['to']]['max'] = $gap;
                        }

                        $finalResults[$infoSent[$rowSent]['to']]['count'] += 1;
                        
                        // incremental average
                        $remainder = (($gap - $finalResults[$infoSent[$rowSent]['to']]['average'])%$finalResults[$infoSent[$rowSent]['to']]['count']);
                        $quotient = (($gap - $finalResults[$infoSent[$rowSent]['to']]['average'])/$finalResults[$infoSent[$rowSent]['to']]['count']);
                        $division = $quotient + $remainder;
                        $newAverage = $finalResults[$infoSent[$rowSent]['to']]['average'] + $division;
                        $finalResults[$infoSent[$rowSent]['to']]['average'] = $newAverage;
                    }
                }
            }
        }
}

$firstRun = 1;
foreach ($finalResults as $key => $values) {
    
    if($firstRun == 1){
        $total['tmin'] = $values['min'];
        $total['tmax'] = $values['max'];
        $total['tavg'] = $values['average'];
        $firstRun = 0;
    } else {
        
        if($total['tmin'] > $values['min']) {
            $total['tmin'] = $values['min'];
        }
        else if($total['tmax'] < $values['max']) {
            $total['tmax'] = $values['max'];
        }
    }
} 

foreach ($finalResults as $person => $value) {
    
    $goodPerson = explode(" <", $person)[0];
    $dataPerson = array(
    
            array("subtitle" => "", "ranges" => [],"markers" => []),
            array("subtitle" => "", "ranges" => [],"markers" => []),
            array("title" => $goodPerson."'s Response Times", "subtitle" => "(in minutes)",
                "locationMin" => "", "locationMax" => "", "ranges" => [ceil($value['min']/60),ceil($value['max']/60)], 
                "markers" => [ceil($value['average']/60)]));

    
    
    $fpPerson = fopen("files/" . $goodPerson . ".json", 'w+');
    fwrite($fpPerson, json_encode($dataPerson));
    fclose($fpPerson);
}

$sumAvg = 0;
$count = 1;
foreach ($finalResults as $person => $value) {
    
    $remainderT = (($value['average']-$sumAvg)%$count);
    $quotientT = (($value['average']-$sumAvg)+$count);
    $divisionT = $quotientT + $remainderT;
    $sumAvg = $sumAvg + $divisionT;    
    $count++;
    
}
$total['tavg'] = $sumAvg;

$dataTotal = array(
    
            array("subtitle" => "", "ranges" => [],"markers" => []),
            array("subtitle" => "", "ranges" => [],"markers" => []),
            array("title" => "Average Response Times", "subtitle" => "(in minutes)",
                "locationMin" => "", "locationMax" => "", "ranges" => [ceil($total['tmin']/60),ceil($total['tmax']/60)], 
                "markers" => [ceil($total['tavg']/60)]));

    
    
    $fpTotal = fopen("files/total.json", 'w+');
    fwrite($fpTotal, json_encode($dataTotal));
    fclose($fpTotal);

aasort($arrSent,"size");

$arrSents2 = array_slice($arrSent, 0, 30);
$outSent = array_values($arrSents2);

$data2 = array(

    "name" => "info",
    "children" => [array(
                        "name" => "vis",
                        "children" => $outSent)]

);

$fpSent = fopen('sent_results.json', 'w+');
fwrite($fpSent, json_encode($data2));
fclose($fpSent);

include 'vis.html';

?>