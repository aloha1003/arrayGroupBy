<?php
namespace ArrayGroupBy;
trait Validator {
  protected $error = [];
  protected $message = [];
  public function validate($rules, array $inputAry, $message = [])
  {
    $this->message = $message;
    foreach ($rules as $ruleKey => $rule) {
      if (is_string($rule))
      {
        $this->valiateRule($rule, $ruleKey, $inputAry);
      } elseif (is_array($rule)) {
        foreach ($rule as $subRuleKey => $subRule) {
          if (is_array($subRule)) {
            $this->valiateRule($subRuleKey, $ruleKey, $inputAry, $subRule);
          } else {
            $this->valiateRule($subRule, $ruleKey, $inputAry);  
          }
          
        }
      }
    }
    return $this->isValiate();
  }

  public function isValiate()
  {
    if ($this->error) {
      return false;
    } else {
      return true;
    }
  }

  public function getError()
  {
    return $this->error;
  }
  protected function valiateRule($rule, $ruleKey, $inputAry, $extendRule = [])
  {
    try {
      $method = 'get'.ucfirst($rule);
      if (method_exists($this, $method)) {
        $this->$method($ruleKey, $inputAry, $extendRule);
      } else {
        throw new \Exception("Not found this validate method:".$rule, 1);
      }
    } catch (\Exception $ex) {
      throw new \Exception("Not found this validate method:".$rule, 1);
    }
    return false;
  }

  protected function getEmail($ruleKey, $inputAry)
  {
    if (!isset($inputAry[$ruleKey]))
    {
      return true;
    }
    if (!filter_var($inputAry[$ruleKey], FILTER_VALIDATE_EMAIL))
    {
      $this->error[$ruleKey] = isset( $this->message[$ruleKey]) ? str_replace(['%ruleKey%', '%value%'], [$ruleKey, $inputAry[$ruleKey]],$this->message[$ruleKey]) : ' this '.$ruleKey .' is not a validate email , value:'.$inputAry[$ruleKey]; 
    }
  }

  protected function getInt($ruleKey, $inputAry)
  {
    if (!isset($inputAry[$ruleKey]) || trim($inputAry[$ruleKey]) =='') 
    {
      return true;
    }
    if (!is_numeric($inputAry[$ruleKey]))
    {
      $this->error[$ruleKey] = isset( $this->message[$ruleKey]) ? str_replace(['%ruleKey%', '%value%'], [$ruleKey, $inputAry[$ruleKey]],$this->message[$ruleKey]) : ' this '.$ruleKey .' is not a validate int , value:'.$inputAry[$ruleKey]; 
    }
  }
  protected function getRequired($ruleKey, $inputAry)
  {
    if (!isset($inputAry[$ruleKey]) || trim($inputAry[$ruleKey]) =='') 
    {
      $this->error[$ruleKey] = isset( $this->message[$ruleKey]) ? str_replace(['%ruleKey%', '%value%'], [$ruleKey, $inputAry[$ruleKey]],$this->message[$ruleKey]) : ' this '.$ruleKey .' is required'; 
    } 
  }
  protected function getArray($ruleKey, $inputAry)
  {
    if (!isset($inputAry[$ruleKey]))
    {
      return true;
    }

    if (!is_array($inputAry[$ruleKey])) {
      $this->error[$ruleKey] = isset( $this->message[$ruleKey]) ? str_replace(['%ruleKey%', '%value%'], [$ruleKey, $inputAry[$ruleKey]],$this->message[$ruleKey]) : ' this '.$ruleKey .' is not a validate array , value:'.$inputAry[$ruleKey]; 
    } 
  }

  protected function getString($ruleKey, $inputAry)
  {
    if (!isset($inputAry[$ruleKey]))
    {
      return true;
    }

    if (!is_string($inputAry[$ruleKey])) {
      $this->error[$ruleKey] = isset( $this->message[$ruleKey]) ? str_replace(['%ruleKey%', '%value%'], [$ruleKey, $inputAry[$ruleKey]],$this->message[$ruleKey]) : ' this '.$ruleKey .' is not a validate string , value:'.$inputAry[$ruleKey]; 
    } 
  }
  

  protected function getDate($ruleKey, $inputAry, $formatAry = [])
  {
    if (!isset($inputAry[$ruleKey]) || (trim($inputAry[$ruleKey]) == ''))
    {
      return true;
    }
    if (isset($formatAry['format'])) {
      $format = $formatAry['format'];
    } else {
      $format = 'Y-m-d H:i:s';
    }
    if (!$this->validateDate($inputAry[$ruleKey], $format))
    {
      $this->error[$ruleKey] = isset( $this->message[$ruleKey]) ? str_replace(['%ruleKey%', '%value%'], [$ruleKey, $inputAry[$ruleKey]],$this->message[$ruleKey]) : ' this '.$ruleKey .' is not a validate date , value:'.$inputAry[$ruleKey]; 
    } 
  }

  protected function getJson($ruleKey, $inputAry)
  {
    if (!isset($inputAry[$ruleKey]) || trim($inputAry[$ruleKey]) =='') 
    {
      return true;
    }
    json_decode($inputAry[$ruleKey]);
    
    if (json_last_error())
    {
      $this->error[$ruleKey] = isset( $this->message[$ruleKey]) ? str_replace(['%ruleKey%', '%value%'], [$ruleKey, $inputAry[$ruleKey]],$this->message[$ruleKey]) : ' this '.$ruleKey .' is not a validate json , value:'.$inputAry[$ruleKey]; 
    }
  }

  protected function validateDate($date, $format = 'Y-m-d H:i:s')
  {
    $d = \DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
  }

  protected function getExist($ruleKey, $inputAry)
  {
    if (!isset($inputAry[$ruleKey]))
    {
      $this->error[$ruleKey] = isset( $this->message[$ruleKey]) ? str_replace(['%ruleKey%', '%value%'], [$ruleKey],$this->message[$ruleKey]) : ' this '.$ruleKey .' Must Be Exist '; 
      return false;
    }
  }
}