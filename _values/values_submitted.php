<?php
define('_VAL', true);
defined('_VAL') or die('Unauthorized');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
$currenttime = time();
header( "refresh:5;url=index.php?nocache=$currenttime" );
  echo 'You\'ll be redirected in about 5 secs. If not, click <a href="index.php?nocache='.$currenttime.'">here</a>.</br>'; 
$file = '../app/etc/env.php';
$env_data = require $file;

$table_prefix = $env_data['db']['table_prefix'];

$mysql_config = array
(
	'hostname'	=>	$env_data['db']['connection']['default']['host'],
	'username'	=>	$env_data['db']['connection']['default']['username'],
	'password'	=>	$env_data['db']['connection']['default']['password'],
	'database'	=>	$env_data['db']['connection']['default']['dbname'],
);

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
	
	
	private function _get_products()
	{
		global $SQL, $config, $debug, $table_prefix;
		$debug->log('--Assembling product data.');
		
		$query = "SELECT `value_id`, `".$table_prefix."catalog_product_entity_tier_price`.`entity_id`, `qty`, `value`, 
			`premium`, `type` as `premium_type` 
			FROM `".$table_prefix."catalog_product_entity_tier_price`
				LEFT JOIN `_premiums`
					ON `".$table_prefix."catalog_product_entity_tier_price`.`value_id`=`_premiums`.`tier_id`
			ORDER BY `entity_id`, `qty`";
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
				$this->list[$entity_id]['tiers'][0] = array('premium'=>0,'premium_type'=>'flat');
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
        
        public function hide_premium($entity_id){
            global $SQL;
            //$query = 'UPDATE  _premiums SET hide = '.$hide.' WHERE entity_id = '.$entity_id;
            $query = "INSERT INTO `hidden_products` (entity_id) VALUES('$entity_id') ON DUPLICATE KEY UPDATE `entity_id`='$entity_id'";
            //$query.';<br>';
            $SQL->query($query);
        }
	
}

$products = new Products();

//print_r($products);
//echo $products->list[$entity_id]['tiers'][$tier_id]['premium'];
if (!empty($_POST))
{
            //echo '<pre>';print_r($_POST);echo '</pre>';
            foreach ($_POST as $key => $value)
            {
                    $key = substr($key, 8);
                    $key = explode('_', $key);
                    $entity_id = $key[0];
                    $tier_id = $key[1];
                    
                    if(isset($_POST['hide'][$entity_id])){
                        //echo $entity_id.'-'.$_POST['hide'][$entity_id].'<br>';
                        if($_POST['hide'][$entity_id]=='on'){
                            $products->hide_premium($entity_id);
                        }
                    }
                    if(!is_array($value)){
                        if(substr($value, -1) == '%') {
                                $value = substr($value, 0, -1);
                                $type = 'percent';
                        } else {
                                $type = 'flat';
                        }
                    }
                    
                    if (!empty($value) &&
                            ($value != $products->list[$entity_id]['tiers'][$tier_id]['premium'] ||
                            $type != $products->list[$entity_id]['tiers'][$tier_id]['premium_type']) && !is_array($value)
                       )
                    {
			echo "Setting ".$products->list[$entity_id]['sku'].": $entity_id, $tier_id, $value, $type<br/>";
                        if(isset($_POST['hide'][$entity_id])){
                            $hide = 1;
                        }else{
                            $hide = 0;
                        }
			$products->set_premium($entity_id, $tier_id, $value, $type, $hide, $hide);
                    }
            }
        }
else
{
	echo "nada";
	}

