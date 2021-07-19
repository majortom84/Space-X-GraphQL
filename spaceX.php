<?php

// for testing
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


// variables
$curl = curl_init();
$fileNameCSV = 'common/spacex.csv';
$fileNmeYMAL = 'spacex.yml';
$url = 'https://api.spacex.land/graphql/';
$finalArray = array();

// get data from curl
$response = getData( $curl, $url );


// JSON to file
$fp = fopen('common/spacex.json', 'w');
fwrite($fp, $response);
fclose($fp);

$json = file_get_contents('common/spacex.json');

// echo response test
//echo $json;
//echo "<pre>";
//print_r(json_decode($json, true));

// Convert to flat array with useful header names for file output
$finalArray = array_flatten (json_decode($json, true));

// test final array
//print_r($finalArray);

// call functions to produce output
produceCSV($fileNameCSV, $finalArray);
//yaml_emit_file($fileNmeYMAL, yaml_emit(json_decode($json, true)));





//////////// Functions //////////////////////

function produceCSV($file_name, $data) {

  // Generate CSV data from array
  $fh = fopen($file_name, 'w'); # don't create a file, attempt
  // to use memory instead

  // write out the headers
  fputcsv($fh, array_keys(current($data)));

  // write out the data
  foreach ( $data as $row ) {
  fputcsv($fh, $row);
  }
  
  fclose($fh);

}// end function


// flatten array function
function array_flatten ($jArray) {

  for ($x = 0; $x <= count($jArray['data']['launches']) -1; $x++) {

    foreach ($jArray['data']['launches'][$x] as $key => $value) {
      
      if(gettype($value) != "array"){
        $finalArray[$x][$key] = $value;
      }
  
      if(gettype($value) == "array"){
        echo "\n".$key." ".gettype($value);
        if($key == "launch_site"){
          foreach ($jArray['data']['launches'][$x]['launch_site'] as $key => $value) {
            echo "\n". $value;
            $finalArray[$x][$key] = $value;
          }
          
        }
        if($key == "links"){
          if(empty($jArray['data']['launches'][$x]['links']['flickr_images'])){
            $finalArray[$x]['flickr_images'] = "NULL";
          }
          else{
            for ($y = 0; $y <= count($jArray['data']['launches'][$x]['links']['flickr_images']) -1; $y++) {
              echo "\n".$jArray['data']['launches'][$x]['links']['flickr_images'][$y];
              $finalArray[$x]['flickr_images'] .= $jArray['data']['launches'][$x]['links']['flickr_images'][$y].",";
            }
          }
        }
        if($key == "mission_id"){
          if(empty($jArray['data']['launches'][$x]['mission_id'])){
            $finalArray[$x]['mission_id'] = "NULL";
          }
          else{
            for ($y = 0; $y <= count($jArray['data']['launches'][$x]['mission_id']) -1; $y++) {
              echo "\n".$jArray['data']['launches'][$x]['mission_id'][$y];
              $finalArray[$x]['mission_id'] = $jArray['data']['launches'][$x]['mission_id'][$y];
            }
          }
        }
        if($key == "rocket"){
          foreach ($jArray['data']['launches'][$x]['rocket'] as $key => $value) {
            echo "\n". $value;
            $finalArray[$x][$key] = $value;
          }
        }
      }
      else{
        echo "\n".$key." ".$value." ".gettype($value);
      }
  
     }// end inner loop key=>value
    echo "\n";
    print_r($jArray['data']['launches'][$x]);
  
  }// end main loop
  
  return $finalArray;
}// end function



function getData($curl, $url){

  // ceate curl
  curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  //CURLOPT_POSTFIELDS =>'{ launches(limit: 5) { id launch_date_local launch_site { site_name  site_name_long  }  launch_year  links {  flickr_images  article_link  }  }  mission(id: "") {  id  name  }  rocket(id: "") {  name  type  }}',
  CURLOPT_POSTFIELDS =>'{"query":" {\\n  launches(limit: 5) {\\n    id\\n    launch_date_local\\n   launch_site {\\n      site_id\\n      site_name_long\\n      site_name\\n    }\\n   launch_year\\n links {\\n  flickr_images\\n  article_link\\n }\\n   mission_id\\n    mission_name\\n   rocket {\\n      rocket_name\\n      rocket_type\\n    }\\n  }\\n}",  "variables":{}}',
  CURLOPT_HTTPHEADER => array('Content-Type: application/json'),));

  // execute and get response
  $response = curl_exec($curl);

  // clode curl
  curl_close($curl);

  return $response;

}// end function


// close php file
?> 