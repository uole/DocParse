# DocParse
PHP method comment parse

## USAGE

```

class User{

	/**
	 * user add
	 * @name user add
	 * @param int $id  user id
	 * @param string $name user name
	 * @return boolean result  if result is true means add successful otherwise return false
	 */
	public function add($id,$name){
		/**
		* do some thinf
		*/
	}
}

$parse = new DocParse();
$markdown = '';
$user = new User();
$method = new \ReflectionMethod($user, 'add');
$comment =  $method->getDocComment();
if($parse->parse($comment)){
	$markdown = $parse->markdown();
}
echo $markdown;

```
