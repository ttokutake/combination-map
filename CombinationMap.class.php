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
      $this->delimiter        = $delimiter;
      $this->quoted_delimiter = $this->quote($delimiter);
      $this->array            = array();
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

   public function apply(array $combination, $function)
   {
      ensure(is_callable($function), type_violation_message('The second argument', 'callable', $function));
      $key = $this->toKey($combination);
      $this->array[$key] = $function(array_get($this->array, $key));
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

   public function map($function)
   {
      ensure(is_callable($function), type_violation_message('The first argument', 'callable', $function));
      $cm        = new CombinationMap($this->delimiter);
      $cm->array = array_map($function, $this->array);
      return $cm;
   }

   public function reduce($function, $initialize = null)
   {
      ensure(is_callable($function), type_violation_message('The first argument', 'callable', $function));
      return array_reduce($this->array, $function, $initialize);
   }

   public function toAssociative()
   {
      $associative = array();
      foreach ($this->array as $key => $value)
      {
         $combination = explode($this->delimiter, $key);

         $pointer = &$associative;
         foreach ($combination as $group) {
            $pointer = &$pointer[$group];
         }
         $pointer = $value;
      }
      return $associative;
   }

   public function fromAssociative(array $associative)
   {
      foreach ($associative as $key => $value) {
         if (is_array($value)) {
            $this->chainFrom($value, $key);
         } else {
            $this->array[$key] = $value;
         }
      }
   }

   public function toArrays()
   {
      $arrays = array();
      foreach ($this->array as $keys => $value) {
         $arrays[] = array_shoe(explode($this->delimiter, $keys), $value);
      }
      return $arrays;
   }

   public function fromArrays(array $arrays)
   {
      foreach ($arrays as $array) {
         ensure(is_array($array), type_violation_message('Each element', 'array', $array));
         list($combination, $value) = array_depeditate($array);
         $this->array[$this->toKey($combination)] = $value;
      }
   }

   public function shave(array $partial_combination)
   {
      $regex  = '^' . follow_join($this->quoted_delimiter, $this->escape($partial_combination));
      $shoven = array();
      foreach ($this->array as $key => $value) {
         $shoven[preg_replace($this->wrap($regex), '', $key)] = $value;
      }
      $cm        = new CombinationMap($this->delimiter);
      $cm->array = $shoven;
      return $cm;
   }

   public function startWith(array $partial_combination)
   {
      return $this->part('left', $partial_combination);
   }

   public function endWith(array $partial_combination)
   {
      return $this->part('right', $partial_combination);
   }


   private function toKey(array $combination)
   {
      return implode($this->delimiter, $combination);
   }

   private function chainFrom(array $associative, $key_chain)
   {
      foreach ($associative as $key => $value) {
         if (is_array($value)) {
            $this->chainFrom($value, $this->toKey(array($key_chain, $key)));
         } else {
            $this->array[$this->toKey(array($key_chain, $key))] = $value;
         }
      }
   }

   private function quote($str)
   {
      return preg_quote($str, '/');
   }
   private function wrap($regex) {
      return "/$regex/u";
   }

   private function part($type, array $first_combination, array $second_combination = array())
   {
      $first_regex = implode($this->quoted_delimiter, $this->escape($first_combination));
      switch ($type) {
         case 'left':
            $regex = "^$first_regex";
            break;
         case 'right':
            $regex = "$first_regex$";
            break;
         case 'both':
            $second_regex = implode($this->quoted_delimiter, $this->escape($second_combination));
            $regex        = "^$first_regex.*$second_regex$";
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
      $cm        = new CombinationMap($this->delimiter);
      $cm->array = $part;
      return $cm;
   }

   private function escape(array $combination)
   {
      return array_map(function ($key) { return $key === '*' ? "[^$this->quoted_delimiter]*" : $this->quote($key); }, $combination);
   }


   public function dump()
   {
      print_r($this->array);
   }
}
