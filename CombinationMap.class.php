<?php

/**
 * This file is part of the php-utils.php package.
 *
 * Copyright (C) 2015 Tadatoshi Tokutake <tadatoshi.tokutake@gmail.com>
 *
 * Licensed under the MIT License
 */


require_once implode(DIRECTORY_SEPARATOR, array(__DIR__, 'php-utils', 'php-utils.php'));


class CombinationMap
{
   private $delimiter;
   private $quoted_delimiter;
   private $array;

   public function __construct($delimiter = ',')
   {
      ensure_string   ($delimiter, 'The first argument');
      ensure_non_empty($delimiter, 'The first argument');

      $this->delimiter        = $delimiter;
      $this->quoted_delimiter = $this->quote($delimiter);
      $this->array            = array();
   }

   public static function fromAssociative(array $associative, $delimiter = ',')
   {
      $cm = new CombinationMap($delimiter);
      foreach ($associative as $key => $value) {
         if (is_array($value)) {
            $cm->chainFrom($value, array($key));
         } else {
            $cm->array[$key] = $value;
         }
      }
      return $cm;
   }
   private function chainFrom(array $associative, array $key_chain)
   {
      foreach ($associative as $key => $value) {
         $keys = array_shoe($key_chain, $key);
         if (is_array($value)) {
            $this->chainFrom($value, $keys);
         } else {
            $this->set($keys, $value);
         }
      }
   }

   public static function fromArrays(array $arrays, $delimiter = ',')
   {
      $cm = new CombinationMap($delimiter);
      foreach ($arrays as $array) {
         ensure_array($array, 'Each element');
         list($combination, $value) = array_depeditate($array);
         $cm->set($combination, $value);
      }
      return $cm;
   }


   public function size()
   {
      return count($this->array);
   }

   public function set(array $combination, $value)
   {
      $this->array[$this->toKey($combination)] = $value;
   }

   public function get(array $combination)
   {
      return array_get($this->array, $this->toKey($combination));
   }

   public function exist(array $combination)
   {
      return array_key_exists($this->toKey($combination), $this->array);
   }

   public function apply(array $combination, $closure)
   {
      ensure_callable($closure, 'The second argument');

      $key = $this->toKey($combination);
      $this->array[$key] = $closure(array_get($this->array, $key));
   }

   public function erase(array $combination)
   {
      unset($this->array[$this->toKey($combination)]);
   }

   public function values()
   {
      return array_values($this->array);
   }

   public function sum()
   {
      return array_sum($this->array);
   }

   public function map($closure)
   {
      ensure_callable($closure, 'The first argument');

      return $this->baby(array_map($closure, $this->array));
   }

   public function reduce($closure, $initialize)
   {
      ensure_callable($closure, 'The first argument');

      return array_reduce($this->array, $closure, $initialize);
   }

   public function filter($closure)
   {
      ensure_callable($closure, 'The first argument');

      return $this->baby(array_filter($this->array, $closure));
   }

   public function shave(array $partial_combination)
   {
      $regex  = '^' . follow_join($this->quoted_delimiter, $this->escape($partial_combination));
      $shoven = array();
      foreach ($this->array as $key => $value) {
         $shoven[preg_replace($this->wrap($regex), '', $key)] = $value;
      }
      return $this->baby($shoven);
   }

   public function startWith(array $partial_combination)
   {
      return $this->part('left', $partial_combination);
   }

   public function endWith(array $partial_combination)
   {
      return $this->part('right', $partial_combination);
   }

   public function have(array $partial_combination)
   {
      return $this->part('include', $partial_combination);
   }


   public function toAssociative()
   {
      return array_reduce_with_key($this->array, function($carry, $key, $value) {
            return array_merge_recursive($carry, aoa_set(array(), $this->toCombination($key), $value));
         }, array());
   }

   public function toArrays()
   {
      $arrays = array();
      foreach ($this->array as $keys => $value) {
         $arrays[] = array_shoe($this->toCombination($keys), $value);
      }
      return $arrays;
   }

   public function bundle()
   {
      return self::fromAssociative($this->toAssociative(), $this->delimiter);
   }

   public function merge(CombinationMap $cm)
   {
      return self::fromAssociative(array_merge_recursive($this->toAssociative(), $cm->toAssociative()));
   }


   private function baby(array $array)
   {
      $cm        = new CombinationMap($this->delimiter);
      $cm->array = $array;
      return $cm;
   }

   private function toKey(array $combination)
   {
      return implode($this->delimiter, $combination);
   }

   private function toCombination($key)
   {
      ensure_string($key, 'The first argument');

      return explode($this->delimiter, $key);
   }

   private function quote($str)
   {
      return preg_quote($str, '/');
   }
   private function wrap($regex) {
      return "/$regex/u";
   }

   private function part($type, array $partial_combination)
   {
      $regex = implode($this->quoted_delimiter, $this->escape($partial_combination));
      switch ($type) {
         case 'left':
            $regex = "^$regex";
            break;
         case 'right':
            $regex = "$regex$";
            break;
         case 'include':
            $start_with = "^$regex{$this->quoted_delimiter}";
            $end_with   = "{$this->quoted_delimiter}$regex$";
            $just       = "^$regex$";
            $include    = wrap($regex, $this->quoted_delimiter);
            $regex      = "$start_with|$end_with|$include|$just";
            break;
         default:
            throw new LogicException('This line must not be passed!');
      }

      $part = array();
      foreach ($this->array as $key => $value) {
         if (preg_match($this->wrap($regex), $key) === 1) {
            $part[$key] = $value;
         }
      }
      return $this->baby($part);
   }

   private function escape(array $combination)
   {
      return array_map(function($key) { return $key === '*' ? "[^$this->quoted_delimiter]*" : $this->quote($key); }, $combination);
   }


   public function dump()
   {
      echo pretty($this->array);
   }
}
