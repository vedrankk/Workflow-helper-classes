<?php
include 'Model.php';

class Test extends Model
{
	public function tableName() : string
	{
		return 'test';
	}

	public function attributes() : array
	{
		return ['t_id', 'name', 'age'];
	}
}

$test = new Test();

/*
* NOTICE: The select method using the one() function can use the flag asArray(). If that is used, the data will be retured as a simple array, instead of an object.
*/

// //Select all the data from the table
$test->select()->all();

// //Selects all the data from another table
$test->select()->from('tableName')->all();

// //Selects one row as array
$test->select()->asArray()->one();

// //Example of WHERE 
// //Returns SELECT * FROM tableName WHERE t_id = 1 AND name = 'Vedran' LIMIT 1 
$test->select()->where(['t_id' => 1, 'name' => 'Vedran'])->one();

// //Example of LEFT JOIN and of where using a string NOTICE: If you are searching for a string, ex name=Vedran, it has to be like 'name="Vedran"'
// //Returns the query SELECT * FROM test LEFT JOIN otherTableName ON otherTableName.field = currentTableName.field WHERE t_id = 1 LIMIT 1 
$test->select()->where('t_id = 1')->leftJoin(['otherTableName', 'otherTableName.field', 'currentTableName.field'])->one();

// //Example of using andWhere and orWhere
// //Returns the query SELECT * FROM test WHERE t_id = 1 AND name = Vedran OR age = 22 LIMIT 1 
$test->select()->where(['t_id' => 4])->andWhere(['name' => 'Vedran'])->orWhere(['age' => 22])->one();

// //Example of LIMIT and ORDER BY
// //Returns the query SELECT * FROM test ORDER BY name DESC LIMIT 1
$test->select()->limit('1')->orderBy('name DESC')->all();


/*
* INSERT
* This is the manual way, all the attributes have to be set like this
*/
$insertTest = new Test();
$insertTest->name = 'Vedran';
$insertTest->age = 22;
$insertTest->save();
//Get the last insert ID
$insertTest->getLastInsertId();

/*
* This is using the POST request from the form
*/
$_POST['name'] = 'Vedsadsadsadran';
$_POST['age'] = 22;
$insertTest->load($_POST);
$insertTest->save();
$insertTest->getLastInsertId();


/*
* UPDATE
* This is the manual way
* Update returns the number of rows affected
*/

$updateTest = new Test();

$user = $updateTest->select()->where(['t_id' => 6])->one();
$user->name = 'Johny Blaze';
$user->save();

/*
* This is using a POST request, same as insert
* If there is a primaryId in the POST request, that it will update, in other cases in will insert new row
*/

$_POST['name'] = 'Vedsadsadsadran';
$_POST['age'] = 22;
$_POST['t_id'] = 2;

$updateTest->load($_POST);
$updateTest->save();


/*
* DELETE
* There are three ways, two using the Model and one manualy
* When using the model, it looks like this
* First you get the model you want from the table
* And than just call the delete() function
*/
$deleteTest = new Test();
$deleteTest->select()->where(['t_id' => 2])->one();
$deleteTest->delete();

/*
* The other way is easier, just set the primary id and call the delete function
*/
$deleteTest->t_id = 1;
$deleteTest->delete();

/*
* Third way, manually can be used for special cases, it uses the where functionality
* In the deleteWhere you can specify the table from where to delete. The default is the tableName()
*/
$deleteTest->where(['t_id' => 1])->andWhere(['name' => 'Vedran'])->orWhere(['age' => 35])->deleteWhere();