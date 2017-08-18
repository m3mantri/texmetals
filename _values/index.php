<?php
//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
//die('index is working. commment out this die() before continuing');
/**
 * The index file is used to update the premiums in a web-based form.
 * 
 * There is no authentication on this, so it should be in a password-protected
 * directory on the web server.
 */
define('_VAL', true);

$file = '../app/etc/env.php';
$env_data = require $file;

$table_prefix = $env_data['db']['table_prefix'];
//$table_prefix = '';

$mysql_config = array
(
	'hostname'	=>	$env_data['db']['connection']['default']['host'],
	'username'	=>	$env_data['db']['connection']['default']['username'],
	'password'	=>	$env_data['db']['connection']['default']['password'],
	'database'	=>	$env_data['db']['connection']['default']['dbname'],
);


//mysql_connect($mysql_config['hostname'],$mysql_config[username],$mysql_config[password]);

require_once('DEBUG.php');
require_once('FSSQL.php');
require_once('TIME.php');

class Products
{

	public $list, $values, $trends, $changes;
	
	public function Products()
	{
		$this->_get_products();
	}
	
	
	// Sept 2013 : 
	private function _get_products()
	{
		global $SQL, $config, $debug,$table_prefix;
		$debug->log('--Assembling product data.');
		
		$query = "SELECT `value_id`, `".$table_prefix."catalog_product_entity_tier_price`.`entity_id`, `qty`, `value`, 
			`premium`, `type` as `premium_type`, customer_group_id, all_groups
			FROM `".$table_prefix."catalog_product_entity_tier_price`
				LEFT JOIN `_premiums`
					ON `".$table_prefix."catalog_product_entity_tier_price`.`value_id`=`_premiums`.`tier_id`
			ORDER BY `entity_id`, customer_group_id, `qty`";
		$tiers = $SQL->get_array($query, 'entity_id', 'value_id');
		$debug->log('----Retrieved ' . count($tiers) . ' products from tiers table.');

		$query = "SELECT `entity_id`, `premium`, `type` as `premium_type`
			FROM `_premiums` WHERE `tier_id`='0'";
		$bases = $SQL->get_array($query, 'entity_id');
		$debug->log('----Retrieved ' . count($bases) . ' products from _premiums table.');

		
		$query = "SELECT `".$table_prefix."catalog_product_entity`.`entity_id`, `".$table_prefix."catalog_product_entity`.`sku`,
				`".$table_prefix."catalog_product_entity_varchar`.`value` as `name`,
				`".$table_prefix."catalog_product_entity_decimal`.`value` as `weight`
			FROM  `".$table_prefix."catalog_product_entity`
				INNER JOIN `".$table_prefix."catalog_product_entity_varchar`
					ON `".$table_prefix."catalog_product_entity`.`entity_id`=`".$table_prefix."catalog_product_entity_varchar`.`entity_id`
						AND `".$table_prefix."catalog_product_entity_varchar`.`attribute_id`='96'
				INNER JOIN `".$table_prefix."catalog_product_entity_decimal`
					ON `".$table_prefix."catalog_product_entity`.`entity_id`=`".$table_prefix."catalog_product_entity_decimal`.`entity_id`
						AND `".$table_prefix."catalog_product_entity_decimal`.`attribute_id`='101'
			WHERE `sku` LIKE 'AG%' OR `sku` LIKE 'AU%' OR `sku` LIKE 'PT%' OR `sku` LIKE 'PD%' 
			ORDER BY `".$table_prefix."catalog_product_entity`.`sku`";
		$this->list = $SQL->get_array($query, 'entity_id');
		
		
		foreach ($this->list as $entity_id => $foo)
		{
			
			if (!empty($bases[$entity_id]))
			{
				$this->list[$entity_id]['tiers'][0] = $bases[$entity_id];
			}
			else
			{
				$this->list[$entity_id]['tiers'][0] = array('premium'=>0,'premium_type'=>'flat','customer_group_id'=>0,'all_groups'=>1);
			}
			if (!empty($tiers[$entity_id]))
			{
				$lowest = null;
				foreach ($tiers[$entity_id] as $key => $value)
				{
					if (is_null($lowest) || $value < $lowest)
						$this->list[$entity_id]['lowest_tier'] = $key;
					$this->list[$entity_id]['tiers'][$key] = $value;
				}
			}
			
		}
		
		unset($tiers);
	}
	public function set_premium($entity_id, $tier_id, $premium, $type)
	{
		global $SQL;
		$query = "INSERT INTO `_premiums` VALUES ('$entity_id', '$tier_id', '$premium', '$type') ON DUPLICATE KEY UPDATE `premium`='$premium', `type`='$type'";
		$SQL->query($query);
		$this->list[$entity_id]['tiers'][$tier_id]['premium'] = $premium;
		$this->list[$entity_id]['tiers'][$tier_id]['premium_type'] = $type;
	}
        public function getHidden(){
                global $SQL;
                $hidden_products_sql = 'SELECT * FROM hidden_products';
                $hidden = $SQL->get_array($hidden_products_sql); 
                return $hidden;
        }        
	
}

$products = new Products();
$hiddenProducts = $products->getHidden();
//    print_r($products->list);

if (!empty($_POST))
{
	foreach ($_POST as $key => $value)
	{
		$key = substr($key, 8);
		$key = explode('_', $key);
		$entity_id = $key[0];
		$tier_id = $key[1];

		if(substr($value, -1) == '%') {
			$value = substr($value, 0, -1);
			$type = 'percent';
		} else {
			$type = 'flat';
		}
		
		if (!empty($value) &&
			($value != $products->list[$entity_id]['tiers'][$tier_id]['premium'] ||
			$type != $products->list[$entity_id]['tiers'][$tier_id]['premium_type'])
		   )
		{
			// echo "$entity_id, $tier_id, $value, $type<br/>";

			$products->set_premium($entity_id, $tier_id, $value, $type);
		}
	}
}
?>
<html lang="en">

<head class="html5reset-bare-bones">

    <meta http-equiv="Expires" content="Tue, 01 Jan 1995 12:12:12 GMT">
    <meta http-equiv="Pragma" content="no-cache">

	<meta charset="utf-8">

	<!--[if IE]><![endif]-->
	
	<title>Premium Manager</title>
	
	<meta name="description" content="">

	<link rel="stylesheet" href="_/css/core_new.css"/>
	
	<!--[if IE]>
	<link rel="stylesheet" href="_/css/_patches/win-ie-all.css">
	<![endif]-->
	<!--[if IE 7]>
	<link rel="stylesheet" href="_/css/_patches/win-ie7.css">
	<![endif]-->
	<!--[if lt IE 7]>
	<link rel="stylesheet" href="_/css/_patches/win-ie-old.css">
	<![endif]-->
	<script type='text/javascript' src='_/js/jquery.js'></script>
	<script type='text/javascript'>
	$(document).ready(function() {
		$('fieldset').hover(function(e){
//			$(this).find($('input.base')).focus();
		});
		$('fieldset .row').hover(function(e){
			$(this).find('input').focus();
		});
	});
	</script>
</head>

<body>
<form id="unhideskusform" method="post" action="unhideskus.php">
    <input type="hidden" name="skustohide" id="skustohide">
</form>    
<header>
        <h1>Product Premium Management</h1>
</header>
	
<?php $rowCount=1; ?> 
<?php $tabindex = 1; ?>
<?php $rowcount = 1; ?>
<?php


$tabindex = 1;

echo "<form method='post' action='values_submitted.php'  id='premiumform'>";
echo "<input type='submit' value='Save Changes' tabindex='$tabindex'/>";     
echo "<button id='showhidden' >Show Hidden</button>";
echo "<button id='hideselected' >Hide Selected</button>";
echo "<button id='unhideselected' >UnHide Green</button>";
foreach ($products->list as $id => $product)
{
    
if((int)$rowCount%2==0):
    $rowType = 'odd';
else:
    $rowType = 'even';    
endif;
$n1 = 9.2;
$n2 = 3.65;
$gpfraction = $n1.'/'.$n2;
$w = array();
if(in_array($product['entity_id'], $hiddenProducts)){
    $hideClass = 'hiderow';
    $checked = 'checked="checked"';
    $skuClass=" hiddensku";
}
if(!in_array($product['entity_id'], $hiddenProducts)){
    $hideClass = 'showrow';
    $checked = '';
    $skuClass="";
}

   
	//print_r($product);
	if (isset($product['type'])) {
	if($product['type'] == 'percent') {
		$product['premium'] = $product['premium'].'%';
	}}
	
	
	echo "<div class='row $rowType $hideClass'>";
?>        
        <div class="chkbox"><input type="checkbox" name="hide[<?php echo $product['entity_id'] ?>]" <?php echo $checked; ?> ></div>
<?php        
	echo "<div class='sku $skuClass'  data-id='".$product['entity_id']."' >" . $product['sku'] . "</div>".
	     "<div class='title'>" . $product['name'] . "</div>";	
	     //"<div> <em>" . number_format((float)$product['weight'], 2, '.', '') . " ozt</em></div> ";

//	if (empty($product['tiers']))
//	{
//		echo "<span class='alert'>The premium manager requires each product to use pricing tiers, but no tiers are set up on this product.</span>";
//	}
//	else
		//echo "<span class='show'>Update Premiums</span>";
	//echo "</tr><tr><td></td><td colspan='2' style='padding-top:7px'>&nbsp;&nbsp;";
         echo "<div class='prices'>";
	//echo "<fieldset class='clearfix'><legend>Premiums</legend>";

	$wholesale_newline = true;	
	foreach ($product['tiers'] as $tier_id => $tier)
	{
		if ($tier_id == 0)
		{
			$class = 'base';
			$label = 'Base';
		}
		else
		{
			$class = '';
			$label = ($tier['qty'] * 1) . ' and up';
		}
		
		if (isset($tier['customer_group_id']) AND ($tier['customer_group_id'] == 2) AND $wholesale_newline)
		{
			echo "<div title='Whole Sale'  class='price' style='color: red;padding:0 10px;font-weight:bold;'> W</div> ";
			$wholesale_newline = false;
		}
		else if (isset($tier['customer_group_id']) AND ($tier['customer_group_id'] == 2) AND !$wholesale_newline)
		{
			$wholesale_newline = false;
		}
		else if (isset($tier['customer_group_id']) AND ($tier['customer_group_id'] != 2) AND !$wholesale_newline)
		{
			$wholesale_newline = true;
		}
		$key = 'premium_' . $id . '_' . $tier_id;
		$value = $tier['premium'] * 1;
		if ($tier['premium_type'] == 'percent')
			$value .= '%';
		//echo "<div class='row clearfix'><label for='$key'>" . $label . ":</label><input tabindex='$tabindex' type='text' name='$key' id='$key' value='" . $value . "' class='$class'/></div>";
		echo "<div class='price'><label for='$key'>" . str_replace(' and up','+',$label) . ":</label><input tabindex='$tabindex' type='text' name='$key' id='$key' value='" . $value . "' class='$class'/></div>";
		$tabindex++;
	}		
	//echo "</fieldset>";
	echo "</div>";

	echo "</div>";
        $rowCount++;
}

//echo "<input type='submit' value='Save Changes' tabindex='$tabindex'/>"; 
echo "</form>";
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<?php echo '
<script>  
$("#showhidden").click(function(){    
    $(".hiderow input[type=checkbox]").attr("disabled",true);
    $(".hiderow").show();
    $(this).hide();
    $("#hideselected").show();

    return false;
});
$("#hideselected").click(function(){    
    $(".hiderow").hide();
    $(this).hide();
    $("#showhidden").show();
    return false;
});
    
var fd = $("#premiumform").serialize();
//alert(fd);
$("#loader").show();
$("#setpremium").click(function(){    
    var datastring = $("#premiumform").serialize();
        $.ajax({
            type: "POST",
            url: "process_form.php",
            data: datastring,
            success: function(data) {
                 alert(data);
            }
        });    
});   

$(".hiddensku").click(function(){
    if(!$(this).hasClass("unhide")){
        $(this).css("color","green");
        $(this).addClass("unhide");
    }else{
        $(this).removeAttr("style");
        $(this).removeClass("unhide");
    }
});

$("#unhideselected").click(function(){
    var str = "";
    $(".unhide").each(function(){
        str += $(this).attr("data-id")+",";        
    });
    $("#skustohide").val(str);
    $("#unhideskusform").submit();
    return false;
});

</script>';
?>


</body>
</html>
