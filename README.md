# BonOptions
**BonOptions** is a helper class to handle *getting/setting* actions.

<br />

## Example & Usage:
### Start
```php
$bonOptions = new BonOptions('My Info');
```

### Simple Getting/Setting
```php
$bonOptions->set('name', 'Amir Hossein');
echo $bonOptions->get('name'); // Amir Hossein
```
### Multiple Getting/Setting
```php
$bonOptions->set(
    array(
        'age' => 25,
        'city' => 'Tehran'
    )
);
echo $bonOptions->get('city'); // Tehran
var_dump($bonOptions->get());
/*
array(4) {
    ["name"]   => string(12) "Amir Hossein"
    ["age"]    => int(26)
    ["city"]   => string(6) "Tehran"
    ["gender"] => string(4) "Male"
}
*/

var_dump($bonOptions->get('name', 'city'));
/*
array(4) {
    ["name"]   => string(12) "Amir Hossein"
    ["city"]   => string(6) "Tehran"
}
*/
```

### Using Validators
```php
$bonOptions->setReaderValidator(
    function($key) {
        if ($key == 'age') {
            return false;
        }
        return true;
    }
);
var_dump($bonOptions->get('age')); // NULL

$bonOptions->setReaderValidator(function($key){ return true; }); // Reset ReaderValidator

$bonOptions->setWriterValidator(
    function($key, $value, $min, $max)
    {
        if ($key == 'age') {
            return ($value >= $min && $value <= $max);
        }
        return true;
    }, array(23, 27)
);
$bonOptions->set('age', 20);
echo $bonOptions->get('age'); // 25
$bonOptions->set('age', 30);
echo $bonOptions->get('age'); // 25
$bonOptions->set('age', 26);
echo $bonOptions->get('age'); // 26
```

### Hydration
```php
$bonOptions->gender = 'Male';
var_dump($bonOptions->gender); // NULL
var_dump(isset($bonOptions->gender)); // bool(false)

$bonOptions->hydration(true);
$bonOptions->gender = 'Male';
var_dump($bonOptions->gender); // string(4) "Male"
var_dump(isset($bonOptions->gender)); // bool(true)
$bonOptions->set('name', 'Amir Hossein');
var_dump($bonOptions->name); // string(12) "Amir Hossein"
var_dump($bonOptions->get('gender')); // string(4) "Male"

$bonOptions->hydration(false);
var_dump($bonOptions->gender); // NULL
var_dump($bonOptions->get('gender')); // string(4) "Male"
```

### Reset
```php
$bonOptions->reset(); // $bonOptions is empty now
```

### Filter
```php
$bonOptions->set('date', time());
echo $bonOptions->filter('date', function($time) {
    return date('Y-m-d H:i', $time);
});
```

### Class Key
```php
$profile = new BonOptions('Profile');
$profile->set('name', 'TheName');
$bank = new BonOptions('Bank Info');
$bank->set('account', '47657789');
$options = array($profile, $bank);
foreach ($options as $bonOptions) {
    if ($bonOptions->key() == 'Profile') {
        echo 'Name: ' . $bonOptions->get('name'); // Name: TheName
    }
    if ($bonOptions->key() == 'Bank Info') {
        echo 'Account No: ' . $bonOptions->get('account'); // Account No: 47657789
    }
}
```

### Using as config class
```php
class Config extends BonOptions
{
    /**
     * @static
     * @access private
     * @var Config Instance
     */
    static private $self;

    /**
     * Constructor
     *
     * @access private
     */
    public function __construct()
    {
        trigger_error('Contructor is not public. Please use Config::getInstance() instead.', E_USER_ERROR);
    }

    /**
     * Get instance
     *
     * @static
     * @return Config
     */
    static public function getInstance()
    {
        if (!isset(self::$self)) {
            self::$self = new BonOptions('Config');
        }
        return self::$self;
    }
}

Config::getInstance()->set('name', 'Amir');
echo Config::getInstance()->get('name'); // Amir
```

<br />
<br />

## Class Methods
### Constructor
Create New Instance

	void __construct([string $keyName]);
#### Arguments:
- string $keyName (optional) Optional Key for object

#### Returns:
- void

<br />

### Set
Setter

	void set(array|string $key(s) [, mixed|null $value]);
#### Arguments:
- Two Arguments
	- string $key key for option
	- mixed $value Value for option
- One Argument
	- array $keys Key/Value array to add to options

#### Returns:
- void

<br />

### Get
Getter

	mixed|array get([string $key1, string $key2 [,string $key3 [,…]]]);
#### Arguments:
- No Argument
- One Argument
	- string $key Option key
- More Arguments
	- string $key1 Option key 1
	- string $key2 Option key 2
	- ...

#### Returns:
- No Argument
	- array All options
- One Argument
	- mixed Option value
- More Arguments
	- array Values of given keys

<br />

### Filter
Filter Data

	mixed|array filter(array|string $key, callable $filterFunction [,array $filterArguments]);

#### Arguments:
- String key or Array Keys
- Function to call for each value
- Optional Arguments for filterFunction

#### Returns:
- Filtered value(s) of given key(s)


<br />

### Has
Check for key

	bool has(string $key);

#### Arguments
- Key to check

#### Returns
- bool True if value exists and is accessible

<br />

### SetReaderValidator
Set validator function to call while getting

	void setReaderValidator(callable $callable [, array $additionalArguments]);

#### Arguments
- callable $callable Function to call
- array $additionalArguments Arguments to pass to callable function. First argument always is $key.

#### Returns
- void

<br />

### SetWriterValidator
Set validator function to call while setting

	void setWriterValidator(callable $callable [, array $additionalArguments]);

#### Arguments
- callable $callable Function to call
- array $additionalArguments Arguments to pass to callable function. First and second arguments always are $key and $value.

#### Returns
- void

<br />

### Reset
Reset options storage

	void reset();

#### Arguments
- void

#### Returns
- void

<br />

### Hydration
To access options via instance properties.

	bool hydration([bool $isHydration]);

#### Arguments
- bool $isHydration Turn on/off hydration

#### Returns
- bool Hydration


<br />

### Key
Get/Set instance key

	bool key([string $key]);

#### Arguments
- string $key Instance Key

#### Returns
- string Current instance Key


<br />
<br />

## Licence
**GPL**

## Author
**Amir Hossein Hodjati Pour** <_me [at] Amir-Hossein.com_>

