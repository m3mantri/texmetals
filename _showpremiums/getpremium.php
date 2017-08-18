<?php
if(isset($_GET['sku'])) 
{
//Set our variables
$whichsku = "";
$whichsku = strtoupper($_GET['sku']);
//echo $whichsku;
$numunits = 1;  //pre-set to initialize
if(isset($_GET['units']))
  {$numunits = $_GET['units'];}
else
  {$numunits = 1;}
$customergroup = 1;  //pre-set to initialize
if(isset($_GET['group'])) //0=retail,2=wholesale
  {$customergroup = $_GET['group'];}
else
  {$customergroup = 0;}  
$strike = 1;  //pre-set to initialize
if(isset($_GET['strike']))
  {$strike = $_GET['strike'];}
else
  {$strike = 1;}

//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
//die('index is working. commment out this die() before continuing');
/**
 * The index file is used to show the premiums in a list.
 * 
 * There is no authentication on this, so it should be in a password-protected
 * directory on the web server.
 */
define('_VAL', true);

$connection = include '../app/etc/env.php';
// $xml = simplexml_load_file($file);

$mysql_config = array
(
	'hostname'	=>	$connection['db']['connection']['default']['host'],
	'username'	=>	$connection['db']['connection']['default']['username'],
	'password'	=>	$connection['db']['connection']['default']['password'],
	'database'	=>	$connection['db']['connection']['default']['dbname'],
);

// unset($xml);

require_once('DEBUG.php');
require_once('FSSQL.php');
require_once('TIME.php');

function get_sku_weight($productsku){
global $SQL, $config, $debug, $whichsku, $numunits, $customergroup;
$weight = 0;

                $weight_query = "SELECT catalog_product_entity_decimal.value FROM catalog_product_entity_decimal LEFT JOIN catalog_product_entity ON catalog_product_entity.entity_id = catalog_product_entity_decimal.entity_id WHERE catalog_product_entity_decimal.attribute_id = 101";
$weight_query .= " AND catalog_product_entity.sku = '".$productsku."'";
//echo $weight_query;

		$weight_result = $SQL->get_array($weight_query);
		$weightnumresults = count($weight_result);
		//print_r($weight_result);
		if ($weightnumresults > 0){
			$weight = $weight_result[0];
			//echo " Weight: " . $weight;
			//echo " Type: " . $base_premium_type;
			}
		return $weight;
}

function get_Premium($product, $howmany, $soldprice){
	global $SQL, $config, $debug, $whichsku, $numunits, $customergroup;
	$finalvalue = 0;	
	//echo $product . " Unit " . $howmany . " Price " . $soldprice . " ";
		//first get entity_id
		$entity_id_query = "SELECT `catalog_product_entity`.`entity_id`, `catalog_product_entity`.`sku`
			FROM  `catalog_product_entity`
			WHERE `sku` LIKE '$product'";
		
		$result = $SQL->get_array($entity_id_query, 'entity_id');
		//print_r($result);
		$foundentity_id = key($result);
		//echo " Entity ID: " . $foundentity_id;
		$base_premium_amount = 0;
		$base_premium_type = "";
		
		//get base price
		$base_query = "SELECT `entity_id`, `premium`, `type` as `premium_type` FROM `_premiums` WHERE `tier_id`='0' AND `entity_id`=".$foundentity_id.";";
		$base_result = $SQL->get_array($base_query);
		$basenumresults = count($base_result);
		//print_r($base_result);
		if ($basenumresults > 0){
			$base_premium_row = $base_result['counter_1'];
			$base_premium_amount = $base_premium_row['premium'];
			$base_premium_type = $base_premium_row['premium_type']; 
			//echo " Amount: " . $base_premium_amount;
			//echo " Type: " . $base_premium_type;
			}
		
		//next get tier info
		$premium_query = "SELECT `value_id`, `catalog_product_entity_tier_price`.`entity_id`, `qty`, ROUND(`value`,2) as `value`, 
			`premium`, `type` as `premium_type`
			FROM `catalog_product_entity_tier_price`
				LEFT JOIN `_premiums`
					ON `catalog_product_entity_tier_price`.`value_id`=`_premiums`.`tier_id`
			WHERE  `catalog_product_entity_tier_price`.`entity_id` = ".$foundentity_id." ORDER BY `qty`";
			
			
		$result2 = $SQL->get_array($premium_query);
		$numresults2 = count($result2);
		//echo " <br> beginning of full<br> ";
		//print_r($result2);
		//echo $numresults2;
		//echo " <br> end of full<br> ";
		//echo " Amount: " . $base_premium_amount;
		
		//check how many tiers there are and iterate through to find which tier/premium is used		
		if ($numresults2 >= 1){
			$r = 0;
			//echo " In Loop Amount: " . $base_premium_amount;
			foreach ($result2 as $result3) {
				$row2 = $result3;
				//echo "<br>";
				//print_r($row2);
				//check for lower than first tier, then need to use base premium
				if (($r==0) AND ($row2['qty']>$howmany)) {
					if ($base_premium_type=="flat"){
						$finalvalue = $base_premium_amount;}
					else {
						$orig_price = ($soldprice*(1+($base_premium_amount/100)));
			    		$calculatedprem = $orig_price-$soldprice;		    
						$finalvalue = round($calculatedprem,2);}
					break;}
				//not using base, keep checking for right tier
				if ($row2['qty']<=$howmany){  //found one possibly correct tier
					$prem_qty = $row2['qty'];
					$prem_value = $row2['value'];
					$prem_amount = $row2['premium'];
					$prem_type = $row2['premium_type'];
		
					if ($prem_type=="flat"){
						$finalvalue = $prem_amount;}
					else {     //need to calculate premium from percent
		    			if ($prem_amount > 0) {
			    			$orig_price = ($soldprice*(1+($prem_amount/100)));
			    			$calculatedprem = $orig_price-$soldprice;
			    			$finalvalue = round($calculatedprem,2);
						}}
					}
				$r++;
				}
			}
			//no tiers
			else {
				if ($base_premium_type == "flat"){
						//echo " Amount: " . $base_premium_amount;
						$finalvalue = $base_premium_amount;}
					else {
						$orig_price = ($soldprice/(1+($base_premium_amount/100)));
			    		$calculatedprem = $soldprice-$orig_price;		    
						$finalvalue = round($calculatedprem,2);}
				}
		//echo $howmany;
		//print_r($row2);
		//echo $finalvalue;
	    return $finalvalue;
	}
	
$calculated_premium = get_Premium($whichsku, $numunits, $strike);
//echo $calculated_premium;
$weight_check = get_sku_weight($whichsku);
//$calculated_totalcost = (($strike + $calculated_premium)*$numunits);
if ($weight_check > 1){
	$calculated_premium = round($calculated_premium/$weight_check,2);
	$calculated_totalcost = round((($strike + $calculated_premium) * $weight_check * $numunits),2);
}
else if ($weight_check < 1){
	//$calculated_totalcost = round((($strike* $weight_check) +$calculated_premium)*$numunits,2);
        $calculated_totalcost = round((($strike+$calculated_premium) * $weight_check)*$numunits,2);
	$calculated_premium = round($calculated_premium*$weight_check,2);
        //$calculated_premium = round($calculated_premium,2);
}
else {
	$calculated_totalcost = (($strike + $calculated_premium)*$numunits);
}
//echo $numunits . " unit(s) of " . $whichsku . " would have a premium of " . $calculated_premium;	
//echo "<br> Using a strike price of " . $strike . " the total cost would be $" . (($strike + $calculated_premium)*$numunits);
$calculated_totalcost_string = "$calculated_totalcost";
$info = array();
$info[] = array( 'sku' => $whichsku, 'premium' => $calculated_premium, 'strike' => $strike, 'totalcost' => $calculated_totalcost_string );
echo json_encode($info); 

}
else { echo "Error.  No SKU."; }
 
?>