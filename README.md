#数据库BaseMysql 
支持跨库操作,DB类：
```php
use BaseMysql\DB;
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
```
#删除
需要提供有效的where条件才能删除成功
```php
$where['form_id']=1;
$res=$db->table('table_name')->delete();
if($res)
    echo '删除成功';
else
    echo '删除失败';
```
#插入
```php
$data['user_name']='Janchan';
$data['user_email']='767903684@qq.com';
//返回插入ID
$res=$db->table('user_data')->add($data);
echo $res;
//addAll
for($i=0;$i+=1;$i<6){
    $alldata[]=$data;
}
//返回true or flase
$res=$db->table('user_data')->add($alldata);
```
#获取最后一次执行SQL
```php
//返回 sql语句
$db->getLastSql();
```
#获取Explain
```php
//返回mysql Explain结果数组
$db->getExplain();
```
#getErrorMsg获取错误信息
```php
$db->getErrorMsg();
```