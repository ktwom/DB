#数据库BaseMysql 支持跨库操作,DB类：
```php
use GouuseCore\BaseMysql\DB;
//指定数据库操作
$db = new DB('base_form');
```
#查询示例:where条件
```php
$where['a.id'] = 1;
$where['field']=['>',2];
//兼容现在的写法
$where['status']=['sign'=>'=','value'=>3];
//正则查询
$where['type']=['regexp','com'];
//join查询
$res = $db->table('base_form as a')->join('form_wigets as b on b.form_id=a.id', 'left')->where($where)->select();
//获取上一次执行SQL:
$db->getLastSql();
分页：
$db->table('table_name')->limit(0,20)->select();
groupBy And having:
$db->table('table_name')->where($where)->groupBy('id')->having('status > 2')->select();
//add
add方法和addAll
//delete
```
需要提供有效的where条件才能删除成功