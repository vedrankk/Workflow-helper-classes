These are classes made by me for practice, to see what I know and what I can do.
Everything works as as it should and it is tested.

**NOTICE: If anyone is actualy reading this/using the class ,I am still a junior programmer, so if there are any big mistakes feel free to point them out, I am eager to hear it.**
The Model class was made for easier access to the database, and faster work.


The Model class is suposed to be used with another, as seen in the test.php file. 
Guidelines
- The tableName() and attributes() function have to be set.
- The attributes array has to be made so it is in the same order as the data in the table, with the priary key being the first value in the array.

Explanation code is in the test.php file

# SELECT

**NOTICE: The select method using the one() function can use the flag asArray(). If that is used, the data will be retured as a simple array, instead of an object.**

**Select all the data from the table**
```php
$test->select()->all();
```


**Selects all the data from another table**
```php
$test->select()->from('tableName')->all();
```


**Selects one row as array**
```php
$test->select()->asArray()->one();
```


**Example of WHERE**
```php
$test->select()->where(['t_id' => 1, 'name' => 'Vedran'])->one();
```
*Returns the query*
```mysql
SELECT * FROM tableName WHERE t_id = 1 AND name = 'Vedran' LIMIT 1
```


**Example of LEFT JOIN and of where using a string** 

*NOTICE: If you are searching for a string, ex name=Vedran, it has to be like 'name="Vedran"'*
```php 
$test->select()->where('t_id = 1')->leftJoin(['otherTableName', 'otherTableName.field', 'currentTableName.field'])->one();
```
*Returns the query*
```mysql
SELECT * FROM test LEFT JOIN otherTableName ON otherTableName.field = currentTableName.field WHERE t_id = 1 LIMIT 1
```


**Example of using andWhere and orWhere**
```php
$test->select()->where(['t_id' => 4])->andWhere(['name' => 'Vedran'])->orWhere(['age' => 22])->one();
```
*Returns the query*
```mysql
SELECT * FROM test WHERE t_id = 1 AND name = Vedran OR age = 22 LIMIT 1
```



**Example of LIMIT and ORDER BY**
```php
$test->select()->limit('1')->orderBy('name DESC')->all();
```
*Returns the query*
```mysql
SELECT * FROM test ORDER BY name DESC LIMIT 1
```



# INSERT

**Manual way, all the attributes have to be set like this**
```php
$insertTest = new Test();
$insertTest->name = 'Vedran';
$insertTest->age = 22;
$insertTest->save();
```
*This gets the last inserted ID*
```php
$insertTest->getLastInsertId();
```

**This is using a POST request from a form**
```php
$insertTest->load($_POST);
$insertTest->save();
$insertTest->getLastInsertId();
```

# UPDATE

**Manual way**

*Update returns the number of rows affected*
```php
$updateTest = new Test();

$user = $updateTest->select()->where(['t_id' => 6])->one();
$user->name = 'Johny Blaze';
$user->save();
```

**This is using the POST request, same as insert**

*NOTICE: If there is a primaryId in the POST request, that it will update, in other cases in will insert new row*
```php
$_POST['name'] = 'Johny';
$_POST['age'] = 22;
$_POST['t_id'] = 2;

$updateTest->load($_POST);
$updateTest->save();
```


# DELETE

There are three ways to delete a row.

**Using a model object**
```php
$deleteTest = new Test();
$deleteTest->select()->where(['t_id' => 2])->one();
$deleteTest->delete();
```

**Creating a new object and setting the primaryId if the row to be deleted. This is the better way because it only does one query**
```php
$deleteTest->t_id = 1;
$deleteTest->delete();
```

**This way is more complicated, but more dynamic. It uses the WHERE functionality.**

*In the deleteWhere you can specify the table from where to delete. The default is the tableName()*
```php
$deleteTest->where(['t_id' => 1])->andWhere(['name' => 'Vedran'])->orWhere(['age' => 35])->deleteWhere();
```

# HTML Class

This class is created to help with the creation of the html tags, especialy lists which have dynamic values.

**TAG**
```php
echo Html::tag('p', 'Hello', ['class' => 'world']);
```
*Displays*
```html
<p class="world">Hello</p>
```

**a**
```php
echo Html::a('index', 'Index', ['class' => 'link']);
```
*Displays*
```html
<a href="index" class="link">Index</a>
```

**ul/ol**
```php
$data = ['Multiple' => ['ul', ['Johny' => 'Johny', 'Blaze' => 'Blaze'], ['class' => 'ul', 'class' => 'li']], 'Second' => 'Second', 't' => 'Third'];
echo Html::ol($data, ['class' => 'ol_class'], ['class' => 'li_class']);
```
*Displays*
```html
<ol class="ol_class">
	<li class="li_class">
		<ul class="li">
			<li>Johny</li>
			<li>Blaze</li> 
		</ul>
	</li>
	<li class="li_class">Second</li>
	<li class="li_class">Third</li> 
</ol>
```

# FormHelper
**NOTICE: Not finished yet. It is intended to work with the model, which will also be upgraded with the rules for the fields.**
