# CombinationMap ![Build Status](https://travis-ci.org/ttokutake/combination-map.svg?branch=master)

## Requirements

- PHP 5.3 or higher
- But PHP 5.5 or higher if you want to try phpunit's tests.

## Licence

MIT Licence

## Example

```php
require_once PATH_TO_COMBINATION_MAP . 'CombinationMap.class.php';

$assoc = [
   'fruit' => [
      'apple'  => 100,
      'orange' =>  50,
      'lemon'  =>  80,
   ],
   'sweet' => [
      'snickers' => 110,
      'kitkat'   => 150,
      'lolipop'  =>  30,
   ],
];
$cm = CombinationMap::fromAssociative($assoc);
$cm->dump();
```
