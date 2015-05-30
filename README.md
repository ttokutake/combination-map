# CombinationMap [![Build Status](https://travis-ci.org/ttokutake/combination-map.svg?branch=master)](https://travis-ci.org/ttokutake/combination-map) [![Coverage Status](https://coveralls.io/repos/ttokutake/combination-map/badge.png?branch=master)](https://coveralls.io/r/ttokutake/combination-map)

## Requirements

- PHP 5.3 or higher
- But PHP 5.5 or higher if you want to try [phpunit](https://phpunit.de/) tests.

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
