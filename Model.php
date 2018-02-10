<?php

include 'Db.php';
include 'ModelInterface.php';

class Model extends DB
{
	protected $method = '';
	protected $table= '';
	protected $select_what = '';
	protected $select_where= '';
	protected $select_join= [];
	protected $select_limit= '';
	protected $select_order= '';
	protected $new_row = false;
	protected $where_values = [];
	protected $as_array = false;

	const METHOD_SELECT = 'select';
	const METHOD_UPDATE = 'update';
	CONST METHOD_CREATE = 'create';



	private function flushValues()
	{
		 $this->method = '';
		 $this->table= '';
		 $this->select_what = '';
		 $this->select_where= '';
		 $this->select_join= [];
		 $this->select_limit= '';
		 $this->select_order= '';
		 $this->new_row = false;
		 $this->where_values = [];
	}

	/*
	* Sets the attributes as properties
	*/
	public function __construct()
	{
		foreach(static::attributes() as $attribute)
		{
			$this->$attribute = '';
		}
		parent::__construct();
	}

	/*
	* Name of the table
	*/
	public function tableName() : string
	{
		return '';
	}

	/*
	* Atrribute array
	*/
	public function attributes() : array
	{
		return [];
	}

	/*
	* Primary key, always the first place in attributes array
	*/
	public function primaryKey() : string
	{
		return static::attributes()[0];
	}

	/*
	* Sets the method to select and the values to be retrieved
	*/
	public function select($what = '') : Model
	{
		$this->method = self::METHOD_SELECT;
		$this->select_what = !empty($what) ? $what : '*';
		return $this;
	}

	/*
	* In case we want a different table, that is set here
	*/
	public function from(string $table = '') : Model
	{
		$this->table = $table !== '' ? $table : self::tableName();
		return $this;
	}

	/*
	* Sets the where part of the SQL query. Can be done with array or string
	* In case of array it has to be in formay [$key => $value]
	*/
	public function where($where) : Model
	{
		$this->createWhereString($where);
		return $this;
	}

	private function createWhereString($where)
	{
		if(is_array($where))
		{
			foreach($where as $key => $val)
			{
				$val = is_integer($val) ? $val : "'".$val."'";
				$this->select_where .= sprintf('%s = %s AND ', $key, $val);
			}
			$this->select_where = rtrim($this->select_where, ' AND ');
		}
		else{
			$this->select_where .= $where;
		}
	}

	/*
	* Adds to the where part of the SQL query using the 'AND'
	*/
	public function andWhere($where) : Model
	{
		if(is_array($where))
		{
			foreach($where as $key => $val)
			{
				$this->select_where .= sprintf(' AND %s = "%s"', $key, $val);
			}
		}
		else{
			$this->select_where .= $where;
		}
		return $this;
	}

	/*
	* Adds to the where part of the SQL query using the 'OR'
	*/
	public function orWhere($where) : Model
	{
		if(is_array($where))
		{
			foreach($where as $key => $val)
			{
				$this->select_where .= sprintf(' OR %s = "%s"', $key, $val);
			}
		}
		else{
			$this->select_where .= $where;
		}
		return $this;
	}

	/*
	* Creates the left join part of the query
	* If used as an array has to be in formay[$tableName, $field, $field]
	*/
	public function leftJoin(array $join) : Model
	{
		if(is_array($join))
		{
			if(is_string($join[0]) && is_string($join[1]) && is_string($join[2]))
			{
				$this->select_join[] = sprintf('%s ON %s = %s', $join[0], $join[1], $join[2]);
			}
		}
		elseif(is_string($join)){
			$this->select_join[] = $join;
		}
		return $this;
	}

	/*
	* Limits the data retrieved to one and returns the query
	* If the asArray is active, returns the data as an array
	* If not, returns the object
	*/
	public function one()
	{
		$this->select_limit = '1';
		$data =  $this->query();
		if(!empty($data)){
			foreach(static::attributes() as $attribute)
			{
				$this->$attribute = $data[0][$attribute];
			}
			if($this->as_array)
			{
				$this->as_array = false;
				return $this->returnArray();
			}
			return $this;
		}
		else{
			return false;
		}
	}

	/*
	*  Sets the limit part of the SQL query
	*/
	public function limit(string $limit) : Model
	{
		if(is_string($limit))
		{
			$this->select_limit = $limit;
		}
		return $this;
	}

	/*
	* Sets the order by part of the SQL query
	*/
	public function orderBy(string $order) : Model
	{
		if(is_string($order))
		{
			$this->select_order = $order;
		}
		return $this;
	}

	/*
	* Creates the select SQL query from all the parts needed
	*/
	public function createSelectQuery() : string
	{
		$select = '';
		$this->select_where = !empty($this->select_where) ?  'WHERE ' .$this->select_where : '';
		$this->table = !empty($this->table) ? $this->table : static::tableName();
		$selectJoin = $this->createJoin();
		$selectOrder = !empty($this->select_order) ? 'ORDER BY '.$this->select_order : '';
		$selectLimit = !empty($this->select_limit) ? 'LIMIT '.$this->select_limit : '';
		if(!empty($this->select_what) && !empty($this->table) && !empty($this->method))
		{
			$select = sprintf('SELECT %s FROM %s %s %s %s %s', $this->select_what, $this->table, $selectJoin, $this->select_where, $selectOrder, $selectLimit);
		}
		return $select;
	}

	public function deleteWhere(string $table = '')
	{
		$tableForDelete = !empty($table) ? $table : static::tableName();
		if(!empty($this->select_where))
		{
			$sql = sprintf('DELETE FROM %s WHERE %s', $tableForDelete, $this->select_where);
			try{
				$this->conn->exec($sql);
				return true;
			}
			catch(Exception $e){
				echo $e->getMessage();
				exit;
			}
		}
	}

	public function delete()
	{
		if(isset($this->{$this->primaryKey()}) && !empty($this->{$this->primaryKey()}))
		{
			$sql = sprintf('DELETE FROM %s WHERE %s=%s', static::tableName(), $this->primaryKey(), $this->{$this->primaryKey()});
			try{
				$this->conn->exec($sql);
				return true;
			}
			catch(Exception $e){
				echo $e->getMessage();
				exit;
			}
		}
	}

	/*
	*  Creates the join part of the SQL query from the select_join array
	*/
	private function createJoin()
	{
		$join = '';
		if(empty($this->select_join))
		{
			return '';
		}
		foreach($this->select_join as $key => $val)
		{
			$join .= sprintf(' LEFT JOIN %s', $val);
		}
		return $join;
	}

	/*
	* Loads the data from the POST request if it exists
	*/
	public function load($request) : Model
	{
		foreach($request as $key => $val)
		{
			if(in_array($key, static::attributes()))
			{
				$this->$key = $val;
			}
		}
		$this->isNewRow();
		return $this;
	}

	/*
	*  Checks if the data loaded is a new row or not
	*/
	private function isNewRow()
	{
		if(empty($this->{static::primaryKey()}))
		{
			$this->new_row = true;
		}
	}

	/*
	* Saves the data in the Model object
	* Checks if it is new data or existing and creates the coresponding query
	*/
	public function save(array $where = [])
	{
		$allSet = true;
		foreach(static::attributes() as $attribute)
		{
			if(!isset($this->$attribute))
			{
				$allSet = false;
				break;
			}
		}
		if($allSet)
		{
			$this->isNewRow();
			return $this->new_row ? $this->insert() : $this->update($where);
		}
	}

	/*
	* Returns the attribute array without the primary id
	*/
	private function attrNoPK() : array
	{
		return array_filter(static::attributes(), function($str){return $str !== static::primaryKey();});
	}

	/*
	* Creates the insert query and inserts the data
	*/
	private function insert()
	{
		$sql = 'INSERT INTO '.static::tableName() .' (';
		$fields = implode(', ', $this->attrNoPK());
		$values = implode(', ', array_map(function($str){return sprintf('"%s"', $this->$str);}, $this->attrNoPK()));
		
		$sql .= $fields.') VALUES('.$values.')';
		try{
			$this->conn->exec($sql);
			return true;
		}
		catch(Exception $e){
			echo $e->getMessage();
			exit;
		}
	}

	/*
	* Gets the last ID inserted
	*/
	public function getLastInsertId()
	{
		return $this->conn->lastInsertId();
	}

	/*
	* Creates the update query, prepares the data and finishes the query
	* @return the number of rows affected
	*/
	private function update(array $where = [])
	{
		$where = empty($where) ? [static::primaryKey(), $this->{static::primaryKey()}] : $where;
		$sql = 'UPDATE '.static::tableName() .' SET ';
		$set = implode(', ', array_map(function($str){return sprintf('%s = :%s', $str, $str);}, $this->attrNoPK()));
		$whereStr = sprintf(' WHERE %s = :%s', $where[0], $where[0]);
		$sql .= $set .$whereStr;
		try{
			$stmt = $this->conn->prepare($sql);
			$stmt->execute($this->createBindArray($sql, $where));
			return $stmt->rowCount();
		}
		catch(Exception $e){
			echo $e->getMessage();
			exit;
		}
	}

	/*
	* Creates the array for binding the values to the SQL
	*/
	private function createBindArray(string $sql, array $where)
	{
		$bind = [];
		foreach($this->attrNoPK() as $attribute)
		{
			$bind[$attribute] = $this->{$attribute};
		}
		$bind[$where[0]] = $where[1];
		return $bind;
	}

	/*
	* Returns all the data as an array, can only be used if the method is select
	*/
	public function all()
	{
		if($this->method == static::METHOD_SELECT)
		{
			return $this->query();
		}
		else{
			return false;
		}
	}

	/*
	* Finishes the SQL select query
	*/
	private function query()
	{
		try{
			$sql = $this->createSelectQuery();
			$stmt = $this->conn->prepare($sql);
			$stmt->execute();
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$this->flushValues();
			return $stmt->fetchAll(); 
		}
		catch(Exception $e){
			echo $e->getMessage();
			exit;
		}
	}

	/*
	* Sets the flag that the values are soposed to be returned as an array
	*/
	public function asArray()
	{
		$this->as_array = true;
		return $this;
	}

	/*
	* Creates the array to be returned
	*/
	private function returnArray()
	{
		$this->valueArray = [];
		array_map(function($str){$this->valueArray[$str] = $this->{$str};}, $this->attributes());
		return $this->valueArray;
	}
}