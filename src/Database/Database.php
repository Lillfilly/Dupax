<?php
class Database{
    private $options;
    private $db = null;
    private $stmt = null;
    private static $numQueries = 0;
    private static $queries = array();
    private static $params = array();

    public function __construct($options){
	//Database options
	$default = array(
	    'dsn' => null,
	    'username' => null,
	    'password' => null,
	    'driver_options' => null,
	    'fetch_style' => PDO::FETCH_OBJ,
	);
	$this->options = array_merge($default, $options);
	//connect to the database
	try {
	    $this->db = new PDO($this->options['dsn'], $this->options['username'], $this->options['password'], $this->options['driver_options']);
	}catch(Exception $e){
	    throw new PDOException('Failed to connect to database');
	}

	$this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->options['fetch_style']);
    }
    /**
    * Execute a select-query with arguments and return the resultset.
    * 
    * @param string $query the SQL query with ?.
    * @param array $params array which contains the argument to replace ?.
    * @param boolean $debug defaults to false, set to true to print out the sql query before executing it.
    * @return array with resultset.
    */
    public function executeSelectAndFetchAll($query, $params=array(), $debug=false) {
	self::$queries[] = $query; 
	self::$params[]  = $params; 
	self::$numQueries++;

	if($debug) {
	    echo "<p>Query = <br/><pre>{$query}</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>".print_r($params, 1)."</pre></p>";
	}
	$this->stmt = $this->db->prepare($query);
	$this->stmt->execute($params);
	return $this->stmt->fetchAll();
    }
    /**
    * Get the last inserted id
    * return string The id.
    */
    public function lastInsertId(){
	return $this->db->lastInsertId();
    }
    /**
   * Execute a SQL-query and ignore the resultset.
   *
   * @param string $query the SQL query with ?.
   * @param array $params array which contains the argument to replace ?.
   * @param boolean $debug defaults to false, set to true to print out the sql query before executing it.
   * @return boolean returns TRUE on success or FALSE on failure. 
   */
    public function executeQuery($query, $params = array(), $debug=false) {
	self::$queries[] = $query; 
	self::$params[]  = $params; 
	self::$numQueries++;
 
    if($debug) {
      echo "<p>Query = <br/><pre>{$query}</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>".print_r($params, 1)."</pre></p>";
    }
 
    $this->stmt = $this->db->prepare($query);
    return $this->stmt->execute($params);
  }
    /**
    * Get a html representation of all queries made, for debugging and analysing purpose.
    * 
    * @return string with html.
    */
    public function dump() {
	$html  = '<p><i>You have made ' . self::$numQueries . ' database queries.</i></p><pre>';
	foreach(self::$queries as $key => $val) {
	    $params = empty(self::$params[$key]) ? null : htmlentities(print_r(self::$params[$key], 1)) . '<br/></br>';
	    $html .= $val . '<br/></br>' . $params;
	}
	$html .= '</pre>';
	return $html;
    }
}