<?php
namespace ArrayGroupBy;

class OperationDispatcher  {
  
  public function callAction($actionName, $parameters) {
    $method = 'get'.ucfirst($actionName);
    if (method_exists($this, $method)) {
      return call_user_func_array([$this, $method], $parameters);  
    }
    throw new \Exception('Not Found Action:'.$actionName);
  }

 public function getEqual($val, $compare_val)
 {
    $bool = ($val == $compare_val);
    return $bool;
 }

 public function getNotEqual($val, $compare_val)
 {
    $bool = ($val != $compare_val);
    return $bool;
 }

 public function getLessThanEqual($val, $compare_val)
 {
    $bool = ($val <= $compare_val);
    return $bool;
 }

 public function getGreatThanEqual($val, $compare_val)
 {
    $bool = ($val >= $compare_val);
    return $bool;
 }

 public function getLessThan($val, $compare_val)
 {
    $bool = ($val < $compare_val);
    return $bool;
 }

 public function getGreatThan($val, $compare_val)
 {
    $bool = ($val > $compare_val);
    return $bool;
 }
}