# arrayGroupBy
do array group、operate like RDBMS operate

1. Install 
  composer install aloha1003/arrayGroupBy
2. How to use It.
```
    use ArrayGroupBy\BaseAbstractModel;

    $baseAbstractModel = new BaseAbstractModel();
    $data = [];  //DataSource
    $extendOptionAry = [];  //進行分組計算，目前支援sum、count、max
    $condition = [
                  'column_field' => 'column_field'
                  'op' => 'equal',
                  'value' => '',
                  'over_ride_column_field' => 
                               [
                                 true => 1, 
                                 false => 0
                               ],
    ];
    `
    $extendOption = [
                    'operation' => 'count',
                    'column_field' => 'id',
                    'alias'    => 'sum',  //別名
                    'condition' => [
                                    $condition
                                   ]

                  ];
    $extendOptionAry[] = $extendOption;
    $baseAbstractModel->setSource($data)
                  ->group(['field_1', 'field_2', .....], $extendOptionAry)
                  ->sortBy(['column_field_1' => 'asc', 'column_field_2' => 'desc'])
                  ->getData();
```
3.extendOption 欄位解說
```
  operation  => 操作( count 、max、sum)
column_field => 對那個欄位做計算
alias  =>  別名
condition => 狀態 同義SQL的 CASE condition statement.
```
4. condition欄位解說，以sql CASE condition statement.為例

  SQL : ``` CASE WHEN column_field = 'value' THEN 'yes' ELSE NULL END AS alias ```
  
  同義
  
  ```
  column_field :欄位
  op : 欄位 跟value 用何種計算，可用以下字串計算 :
    equal: 等於
    lessThanEqual:小於等於
    notEqual: 不等於
    greatThanEqual:大於等於
    lessThan: 小於
    greatThan: 大於
  value : 比較數值
  over_ride_column_field:比較得到的結果
 ```
5. 排序使用方法: 
    
   傳入key-value的陣列
   ```
   key:欄位
   value: ASC|DESC  
   ```