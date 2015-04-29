<?php

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'php-utils', 'php-utils.php']);
require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'CombinationMap.class.php']);

class CombinationMapTest extends PHPUnit_Framework_TestCase
{
   private $pairs = [
      [['os', 'windows', 'version'  ], '8.1'  ],
      [['os', 'windows', 'valuation'], 7      ],
      [['os', 'osx'    , 'version'  ], '10.10'],
      [['os', 'osx'    , 'valuation'], 6      ],
      [['os', 'linux'  , 'ubuntu', 'version'  ], '15.04'],
      [['os', 'linux'  , 'ubuntu', 'valuation'], 8      ],
      [['os', 'linux'  , 'centos', 'version'  ], '7'    ],
      [['os', 'linux'  , 'centos', 'valuation'], 7      ],
      [['os', 'linux'  , 'gentoo', 'version'  ], '12.01'],
      [['os', 'linux'  , 'gentoo', 'valuation'], 5      ],
      [['blowser', 'ie'    , 'version'  ], '11' ],
      [['blowser', 'ie'    , 'valuation'], 9    ],
      [['blowser', 'safari', 'version'  ], '8'  ],
      [['blowser', 'safari', 'valuation'], 6    ],
      [['blowser', 'chrome', 'version'  ], 'v41'],
      [['blowser', 'chrome', 'valuation'], 8    ],
   ];
   private $associative = [
      'os' => [
         'windows' => [
            'version'   => '8.1',
            'valuation' => 7    ,
         ],
         'osx' => [
            'version'   => '10.10',
            'valuation' => 6      ,
         ],
         'linux' => [
            'ubuntu' => [
               'version'   => '15.04',
               'valuation' => 8      ,
            ],
            'centos' => [
               'version'   => '7',
               'valuation' => 7  ,
            ],
            'gentoo' => [
               'version'   => '12.01',
               'valuation' => 5      ,
            ],
         ],
      ],
      'blowser' => [
         'ie' => [
            'version'   => '11',
            'valuation' => 9 ,
         ],
         'safari' => [
            'version'   => '8',
            'valuation' => 6 ,
         ],
         'chrome' => [
            'version'   => 'v41',
            'valuation' => 8 ,
         ],
      ],
   ];


   public function testSetAndSize()
   {
      $cm = new CombinationMap('/');
      foreach ($this->pairs as list($key, $value)) {
         $cm->set($key, $value);
      }
      $this->assertEquals(count($this->pairs), $cm->size());
      return $cm;
   }

   /**
    * @depends testSetAndSize
    */
   public function testGet($cm)
   {
      foreach ($this->pairs as list($key, $value)) {
         $this->assertEquals($value, $cm->get($key));
      }
   }

   /**
    * @depends testSetAndSize
    * @depends testGet
    */
   public function testApply($cm) {
      $cm = clone $cm;

      foreach ($this->pairs as list($key, $value)) {
         $cm->apply($key, 'withln');
         $this->assertEquals(withln($value), $cm->get($key));
      }
   }

   /**
    * @depends testSetAndSize
    */
   public function testExist($cm)
   {
      foreach ($this->pairs as list($key, $value)) {
         $this->assertTrue($cm->exist($key));
      }
      $this->assertFalse($cm->exist(['os', 'firefox']));
   }

   /**
    * @depends testSetAndSize
    * @depends testExist
    */
   public function testErase($cm)
   {
      $cm = clone $cm;
      foreach ($this->pairs as list($key)) {
         $cm->erase($key);
         $this->assertFalse($cm->exist($key));
      }
      $this->assertEquals(0, $cm->size());
   }


   /**
    * @depends testSetAndSize
    */
   public function testToAssociative($cm)
   {
      $this->assertEquals($this->associative, $cm->toAssociative());
   }

   /**
    * @depends testSetAndSize
    * @depends testToAssociative
    */
   public function testFromAssociative($cm)
   {
      $from_associative = CombinationMap::fromAssociative($this->associative, ':');
      $this->assertEquals($cm->toAssociative(), $from_associative->toAssociative());
   }

   /**
    * @depends testSetAndSize
    */
   public function testToArrays($cm)
   {
      $expected = array_map('array_flat', $this->pairs);
      $this->assertEquals($expected, $cm->toArrays());
   }

   /**
    * @depends testSetAndSize
    * @depends testToArrays
    */
   public function testFromArrays($cm)
   {
      $from_arrays = CombinationMap::fromArrays(array_map('array_flat', $this->pairs), '|');
      $this->assertEquals($cm->toArrays(), $from_arrays->toArrays());
   }

   /**
    * @depends testSetAndSize
    * @depends testToArrays
    */
   public function testStartWith($cm)
   {
      $partial_key = ['os', 'windows'];
      $filtered    = array_filter($this->pairs, function ($pair) use($partial_key) {
            return array_take($pair[0], 2) == $partial_key;
         });
      $expected    = array_map('array_flat', to_seq($filtered));
      $this->assertEquals($expected, $cm->startWith($partial_key)->toArrays());
   }

   /**
    * @depends testSetAndSize
    * @depends testToArrays
    */
   public function testEndWith($cm)
   {
      $partial_key = ['valuation'];
      $filtered    = array_filter($this->pairs, function ($pair) use($partial_key) {
            return array_take_right($pair[0], 1) == $partial_key;
         });
      $expected    = array_map('array_flat', to_seq($filtered));
      $this->assertEquals($expected, $cm->endWith($partial_key)->toArrays());
   }

  /**
    * @depends testSetAndSize
    * @depends testToArrays
    */
   public function testHave($cm)
   {
      $partial_key = ['linux', 'ubuntu'];
      $filtered    = array_filter($this->pairs, function ($pair) use($partial_key) {
            return array_for_all($partial_key, function ($word) use($pair) { return in_array($word, $pair[0]); });
         });
      $expected    = array_map('array_flat', to_seq($filtered));
      $this->assertEquals($expected, $cm->have($partial_key)->toArrays());
   }


   /**
    * @depends testSetAndSize
    */
   public function testValues($cm)
   {
      $this->assertEquals(aoa_values($this->pairs, 1), $cm->values());
   }

   /**
    * @depends testSetAndSize
    * @depends testEndWith
    * @depends testValues
    */
   public function testSum($cm)
   {
      $partial_key = ['valuation'];
      $filtered_cm = $cm->endWith($partial_key);
      $this->assertEquals(array_sum($filtered_cm->values()), $filtered_cm->sum());
   }

   /**
    * @depends testSetAndSize
    * @depends testEndWith
    * @depends testValues
    */
   public function testMap($cm)
   {
      $partial_key = ['valuation'];
      $closure     = function ($int) { return $int / 2; };
      $filtered_cm = $cm->endWith($partial_key);
      $this->assertEquals(array_map($closure, $filtered_cm->values()), $filtered_cm->map($closure)->values());
   }

   /**
    * @depends testSetAndSize
    * @depends testEndWith
    * @depends testSum
    */
   public function testReduce($cm)
   {
      $partial_key = ['valuation'];
      $closure     = function ($sum, $int) { return $sum + $int; };
      $filtered_cm = $cm->endWith($partial_key);
      $this->assertEquals($filtered_cm->sum(), $filtered_cm->reduce($closure, 0));
   }

   /**
    * @depends testSetAndSize
    * @depends testStartWith
    * @depends testToArrays
    */
   public function testShave($cm)
   {
      $partial_key = ['os'];
      $filtered_cm = $cm->startWith($partial_key);
      $expected    = array_map(function ($array) { return array_drop($array, 1); }, $filtered_cm->toArrays());
      $this->assertEquals($expected, $filtered_cm->shave($partial_key)->toArrays());
   }

   /**
    * @depends testSetAndSize
    * @depends testEndWith
    * @depends testToArrays
    */
   public function testBundle($cm)
   {
      $versions   = $cm->endWith(['version'  ])->toArrays();
      $valuations = $cm->endWith(['valuation'])->toArrays();
      $merged     = CombinationMap::fromArrays(array_merge($versions, $valuations));
      $this->assertEquals($cm->toArrays(), $merged->bundle()->toArrays());
   }

   /**
    * @depends testSetAndSize
    * @depends testEndWith
    * @depends testToArrays
    */
   public function testMerge($cm)
   {
      $versions   = $cm->endWith(['version'  ]);
      $valuations = $cm->endWith(['valuation']);
      $this->assertEquals($cm->toArrays(), $versions->merge($valuations)->toArrays());
   }
}
