<?php
namespace ArrayGroupBy;
use ArrayGroupBy\OperationDispatcher;
use ArrayGroupBy\Validator;
class BaseAbstractModel {
  use Validator;
  protected $_result = [];
  protected $_page = 1;
  protected $_per_page = 100;
  /**
   * Define resource model
   */
  protected function _construct() {
    
  }

  /**
   * 設定資料來源
   * @method     setSource
   * @author John Lin <john.lin@flyelephant.com.tw>
   * @version    [version]
   * @modifyDate 2017-01-17T16:26:49+0800
   * @param      Array                   $result 
   */
  public function setSource($result)
  {
    $this->_result = array_reverse($result);
    return $this;
  }

  /**
   * 對資料分組
   * @method     group
   * @author John Lin <john.lin@flyelephant.com.tw>
   * @version    [version]
   * @modifyDate 2017-01-17T16:27:33+0800
   * @param      array                    $groupByField [description]
   * @param      array                    $extendOpt    [description]
   * @return     object                   this
   */
  public function group( $groupByField = [],  $extendOpt = [])
  {
    $data = $this->_result;
    $return = [];
    $exist = [];
    $group_str = implode(',', $groupByField);
    $tmp_data = [];
    $total = count($data);
    for($i = 0 ; $i< $total ; $i++) {
        $value = $data[$i];
        $key = $i;
        $implode_str = '';
        foreach ($groupByField as $groupKey => $field) {
            $implode_str .= isset($value[$field]) ? $value[$field] : ' ';
            $implode_str .= ',';
        }
        if (!isset($exist[$group_str][$implode_str]) || $extendOpt ) {
            $exist[$group_str][$implode_str] = 1;
            $this->executeExtendOpt($extendOpt, $implode_str, $value, $tmp_data);
        }
      $return[$implode_str] = $value;
    }
    unset($data);
    $count = count($return);
    $output = [];
    $returnToNumIndex = [];
    foreach ($return as $key => $value) {
        $returnToNumIndex[] = $value;
    }
    $this->setSource($returnToNumIndex);
    return $this;
  } 

  /**
   * 執行額外的操作
   * @method     executeExtendOpt
   * @author John Lin <john.lin@flyelephant.com.tw>
   * @version    [version]
   * @modifyDate 2017-01-17T16:28:13+0800
   * @param      Array                   $extendOpt   [description]
   * @param      String                   $implode_str 對操作的欄位字串
   * @param      Array                   &$value      當下的值
   * @param      array                    &$tmp_data   暫存變數
   * @return     object                    this
   */
  private function executeExtendOpt($extendOpt, $implode_str,  &$value, &$tmp_data = [] )
  {
    foreach ($extendOpt as $opt => $extend) {
      if (isset($extend['operation'])) {
          switch ($extend['operation']) {
              case 'sum':
                $tmp_data['extend_sum'] = isset($tmp_data['extend_sum']) ? $tmp_data['extend_sum'] : [];
                $tmp_data['extend_sum'][$extend['alias']][$implode_str] = isset($tmp_data['extend_sum'][$extend['alias']][$implode_str]) ? $tmp_data['extend_sum'][$extend['alias']][$implode_str] : 0;
                if (isset($extend['condition']) && ($extend['condition'])) {
                  $condition_ary = $extend['condition'];
                  foreach ($condition_ary as $field => $condition) {
                    if (!isset($value[$condition['column_field']])) {
                          break;
                    }
                    $this->condition_field_check($condition);
                    $bool = false;
                    $bool = $this->validOperate($condition['op'], $value[$condition['column_field']],  $condition['value']);
                    if (isset($condition['over_ride_column_field']) && $condition['over_ride_column_field']) {
                      $tmp_data['extend_sum'][$extend['alias']][$implode_str] += isset($condition['over_ride_column_field'][$bool]) ? $condition['over_ride_column_field'][$bool] : 1;
                    } else {
                      $tmp_data['extend_sum'][$extend['alias']][$implode_str] += $value[$extend['column_field']];
                    }
                  }
                } else {
                  $tmp_data['extend_sum'][$extend['alias']][$implode_str] += $value[$extend['column_field']];
                }
                $value[$extend['alias']] = $tmp_data['extend_sum'][$extend['alias']][$implode_str];  
                break;
              case 'count':
                $tmp_data['extend_count'] = isset($tmp_data['extend_count']) ? $tmp_data['extend_count'] : [];
                $tmp_data['extend_count'][$extend['alias']][$implode_str][] = $value[$extend['column_field']];
                $tmp_data['extend_count'][$extend['alias']][$implode_str] = array_unique($tmp_data['extend_count'][$extend['alias']][$implode_str]);
                $value[$extend['alias']] = count($tmp_data['extend_count'][$extend['alias']][$implode_str]);
                  break;
              case 'max':
                  $tmp_data['extend_max'] = isset($tmp_data['extend_max']) ? $tmp_data['extend_max'] : [];
                  $tmp_data['extend_max'][$extend['alias']][$value[$extend['column_field']]] = isset($tmp_data['extend_max'][$extend['alias']][$value[$extend['column_field']]]) ? ( $tmp_data['extend_max'][$extend['alias']][$value[$extend['column_field']]] < $value[$extend['column_field']] ? $value[$extend['column_field']] : $tmp_data['extend_max'][$extend['alias']][$value[$extend['column_field']]] )  : $value[$extend['column_field']];
                  $value[$extend['alias']] = $tmp_data['extend_max'][$extend['alias']][$value[$extend['column_field']]];
                  break;
              default:
                  # code...
                  break;
          }
      }
    }
  }

  /**
   * 驗證操作
   * @method     validOperate
   * @author John Lin <john.lin@flyelephant.com.tw>
   * @version    [version]
   * @modifyDate 2017-01-17T16:30:44+0800
   * @param      String                   $op    [description]
   * @param      String                   $val_1 [description]
   * @param      String                   $val_2 [description]
   * @return     bool                          [description]
   */
  private function validOperate($op, $val_1, $val_2)
  {
    $operationDispatcher = new OperationDispatcher();
    return $operationDispatcher->callAction($op, [$val_1, $val_2]);
  }

  /**
   * 驗證每個condition的欄位
   * @method     condition_field_check
   * @author John Lin <john.lin@flyelephant.com.tw>
   * @version    [version]
   * @modifyDate 2017-01-17T16:31:41+0800
   * @param      Array                   $condition 
   * @return     null                              
   */
  private function condition_field_check($condition)
  {
    $paramsCheckField = [
                          'op' => ['required'],
                          'value' => ['required'],
                          'over_ride_column_field' => ['array'],
                        ];
    if (!$this->validate($paramsCheckField, $condition)) {
      throw new \Exception($this->getError(), 1);
    }                        
  }
  /**
   * 取得資料
   * @method     getData
   * @author John Lin <john.lin@flyelephant.com.tw>
   * @version    [version]
   * @modifyDate 2017-01-17T16:32:56+0800
   * @return     array                   [description]
   */
  public function getData()
  {
    $output = [];
    $count = count($this->_result);
    $start = ($this->_page-1)*$this->_per_page;
    $end = ($this->_page)*$this->_per_page;
    $end = ($end <= $count) ? $end : $count;
    for($i = $start ; $i < $end; $i++) {
        $output[] = $this->_result[$i];
    }
    return $output;
  }

  /**
   * 計算資料數量
   * @method     count
   * @author John Lin <john.lin@flyelephant.com.tw>
   * @version    [version]
   * @modifyDate 2017-01-17T16:33:34+0800
   * @return     int                   數量
   */
  public function count()
  {
    $count = count($this->_result);
    return $count;
  }

  /**
   *  排序
   * @method     sortBy
   * @author John Lin <john.lin@flyelephant.com.tw>
   * @version    [version]
   * @modifyDate 2017-01-17T16:34:11+0800
   * @param      array                   $order 排序方式，格式為 [['欄位' => 'ASC|DESC']
   * 
   * @return     object                this                          
   */
  public function sortBy($order)
  {
    $dynamicSort = function ($field, $descend) {
        return function($a, $b) use ($field, $descend ) {
            if (!isset($a[$field])) {
              return false;
            }
            if (strtoupper($descend) == 'DESC') {
                return ( $a[$field] > $b[$field]) ? -1:1;
            } else {
                return ( $a[$field] > $b[$field]) ? 1: -1;
            }
        };
    };
    foreach ($order as $key => $value) {
        usort($this->_result, $dynamicSort($key, $value));
    }
    return $this;
  }
}