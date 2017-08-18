<?php
/**
 * Full Service MySQLi
 * MYSQLI.php
 * 
 * @author Chris Sigler <chris.sigler@gmail.com>
 * @copyright Copyright (c) 2010 Chris Sigler
 * @link http://chrissigler.com
 * @version 0.2
 * @license http://creativecommons.org/licenses/MIT/ MIT License 
 * 
 */
 
/**
 * $SQL is a presumed instance of the Full Service MySQLi class
 */
$SQL = new fullservice_sql();

/**
 * provides an extension to mysqli that returns logic-ready data
 */ 
class fullservice_sql extends mysqli 
{
	private $_ret, $_sql, $_kids;
	
	function __construct($host = null, $user = null, $pass = null, $db = null)
	{
		global $mysql_config;
		if (empty($host) && !empty($mysql_config['hostname']))
			$host = $mysql_config['hostname'];
		if (empty($user) && !empty($mysql_config['username']))
			$user = $mysql_config['username'];
		if (empty($pass) && !empty($mysql_config['password']))
			$pass = $mysql_config['password'];
		if (empty($db) && !empty($mysql_config['database']))
			$db = $mysql_config['database'];
		
		if (empty($host))
			$this->_display_error('Vital SQL Data Missing', 'No hostname provided.');
		if (empty($user))
			$this->_display_error('Vital SQL Data Missing', 'No username provided.');
		if (empty($pass))
			$this->_display_error('Vital SQL Data Missing', 'No password provided.');
		if (empty($db))
			$this->_display_error('Vital SQL Data Missing', 'No default database provided.');
		
		parent::__construct($host, $user, $pass, $db);
		if (mysqli_connect_error())
		{
			$this->_display_error('SQL Connection Failed (' . mysqli_connect_errno() . ')');
        }
	}
	
	/**
	 * Runs a MySQL query via MySQLi. Handy because it'll display an error.
	 * 
	 * @param string $query The query to run.
	 * @return mixed The result of the mysqli query.
	 */
	public function run_query($query)
	{
		if ($this->_result = parent::query($query))
			return $this->_result;
		else
		{
			$this->_display_error("An error occurred on the query.", "Details (" . mysqli_errno($this) . "): \n" . mysqli_error($this) . "\n\n" . $query);
			return $this->_result;
		}
	}
	
	/**
	 * Runs a MySQL query and returns the result in a heirarchal array
	 * 
	 * @param string $query The query to run. Must call in the columns you reference in the other two args.
	 * @param string $key The column of the unique key to use for your array.
	 * @param string $parent_key The column that specifies the $key of the parent.
	 * @return array A heirarchal array of results (look for a key of '__children' for child arrays).  
	 */
	public function get_heirarchy($query, $key, $parent_key)
	{
		$result = $this->run_query($query);
		$this->_ret = array();
		$this->_kids = array(); 
		while($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			if (empty($row[$parent_key]))
				$this->_ret[$row[$key]] = $row;
			else
				$this->_kids[$row[$parent_key]][$row[$key]] = $row;
		}
		$this->_ret = $this->_heir_helper($this->_ret);
		return $this->_ret;
	}
	
	/**
	 * Internal function that assists get_heirarchy() by building child arrays.
	 * 
	 * @param array $arr The array to parse through and build child arrays in.
	 * @return array The input array with child arrays included under '__children'
	 */
	private function _heir_helper($arr)
	{
		foreach ($arr as $key => $values)
		{
			if (!empty($this->_kids[$key]))
			{
				$arr[$key]['__children'] = $this->_kids[$key];
				foreach ($arr[$key]['__children'] as $child_id => $child)
				{
					$arr[$key]['__children'][$child_id] = $this->_heir_helper($child);
				}
				unset($kids[$key]);
			}
		}
		return $arr;
	}
	
	
	/**
	 * Runs a query and returns an array of the first row of results.
	 * 
	 * @param string $query The query to run.
	 * @return array A one-dimensional array of the resulting row.
	 */
	public function get_single($query)
	{
		$result = $this->run_query($query);
		$this->_ret = $result->fetch_array(MYSQLI_ASSOC);
		return $this->_ret;
	}
	
	/**
	 * Runs a query and returns the result in a smart-ish array.
	 * 
	 * @param string $query The query to run.
	 * @param string $key The name of the column to use as the result's key.
	 * @param string $key2 The name of the column to use as a secondary key [$key][$key2], making a three dimensional array.
	 * @result array The results of the query in an array. Two column results without $key are formatted as key/value.
	 */
	public function get_array($query, $key = null, $key2 = null)
	{
		$result = $this->run_query($query);
		$this->_ret = array();

		if(empty($result))
			return;
		
		if (empty($key) && $result->field_count == 1) // 1 dimensional
		{
			while($row = $result->fetch_array(MYSQLI_NUM))
			{
				$this->_ret[] = $row[0];
			}
		}
		else if (empty($key) && $result->field_count == 2) // 2 dimensional
		{
			while($row = $result->fetch_array(MYSQLI_NUM))
			{
				$this->_ret[$row[0]] = $row[1];
			}
		}
		else
		{
			$count = 0;
			while($row = $result->fetch_array(MYSQLI_ASSOC))
			{
				$count++;
				if (empty($key))
					$akey = 'counter_' . $count;
				else if (isset($row[$key]))
					$akey = $row[$key];
				else
					$akey = 'invalid_key_column_' . $count;
				if (!is_null($key2))
				{
					if (isset($row[$key2]))
						$akey2 = $row[$key2];
					else
						$akey = 'invalid_key2_column_' . $count;
				}
				foreach ($row as $column => $value)
				{
					if (!is_null($key2))
						$this->_ret[$akey][$akey2][$column] = $value;
					else
						$this->_ret[$akey][$column] = $value;
				}
			}
		}
		return $this->_ret;
	}
	
	/**
	* Runs a query and returns a resulting string.
	*
	* @param string $query The query to run.
	* @return string The result of the query. If a query has more than one result, the first column of the first row.
	*/
	public function get_string($query)
	{
		$result = $this->run_query($query);
		$this->_ret = $result->fetch_array(MYSQLI_NUM);
		$this->_ret = $this->_ret[0];
		return $this->_ret;
	}

	/**
	 * Used internally to display errors and die(). It looks for a global $debug to be true to display details visibly. 
	 * 
	 * @param string $heading The main portion of the error data.
	 * @param string $detail The details of the error data.
	 * @return void
	 */
	private function _display_error($heading, $detail = null)
	{
		global $debug;		
		if ($debug)
		{
			//Error::send($heading . ": " . $detail);
		}
		else
		{
			//Error::send('A database error has occurred.');
		}
	}
	
	/**
	 * Sanitizes a string for safe SQL access. Any dynamic input should be run through this before being placed in a query.
	 * 
	 * @param string $string The bit of input to be cleaned up and made safe.
	 * @return string A sanitized $string.
	 */
	public function prepare($string)
	{
		$return = trim($string);
		$return = stripslashes($return);
		$return = parent::real_escape_string($return);
		return $return;
	}
}

