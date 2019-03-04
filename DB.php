<?php

namespace GouuseCore\BaseMysql;


/**
 * Class BaseMysql
 * @package GouuseCore\BaseMysql
 * mysql 基本处理
 * author ChenJun
 */
class DB
{
    protected $host = '';

    protected $user = '';

    public $database = '';

    protected $port = 3306;

    protected $table = '';

    protected $real_table = '';

    protected $link = '';

    protected $pri;

    protected $last_sql = '';

    protected $where = '';

    protected $order = '';

    protected $need_field = '*';

    protected $limit = 10000;

    protected $offset = 0;

    protected $group = '';

    protected $fields = [];

    protected $insert_id = 0;

    protected $leftJoin = '';

    protected $rightJoin = '';

    protected $Join = '';

    protected $having = '';

    protected $is_close = 0;

    protected $alias = 0;

    /**
     * DB constructor.
     * @param null $database
     * @throws \Exception
     */
    public function __construct($database = null)
    {
        if (!$this->link && $this->is_close == 0)
            $this->Connect($database);
    }

    /**
     * @param bool $database
     * @return \mysqli|string
     */
    public static function getMysqlLink($database = false)
    {
        try {
            if (!$database)
                $database = env('DB_DATABASE');
            $Link = mysqli_connect(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), $database, env('DB_PORT'));
            return $Link;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return \mysqli
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param $database
     * @return $this
     * @throws \Exception
     */
    protected function Connect($database)
    {
        if (!function_exists('env')) {
            function env($some = false, $default = false)
            {
                return $default;
            }
        }
        $this->database = $database ?? env('DB_DATABASE', '');
        $this->host = env('DB_HOST', '127.0.0.1');
        $this->user = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', 'root');
        $this->port = env('DB_PORT', 3306);
        $this->link = mysqli_connect($this->host, $this->user, $password, $this->database, (int)$this->port ?? 3306);
        if (!$this->link) {
            $this->ErrorMsg(mysqli_connect_error());
        }
        return $this;
    }

    /**
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;
        $some_pos = strpos($table, ' as');
        if ($some_pos !== false) {
            $this->alias = 1;
            $this->real_table = substr_replace($table, '', $some_pos, 100);
        } else
            $this->real_table = $table;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table ?? '';
    }

    /**
     * @param array $wh
     * @return $this
     */
    public function where(array $wh = [])
    {
        if ($wh)
            $this->where = $this->getWhere($wh);
        return $this;
    }

    /**
     * @param string $where
     * @return $this
     *
     */
    public function whereString(string $where = '')
    {
        if ($where)
            $this->where = $where;
        return $this;
    }

    /**
     * @param string $string
     * @return $this
     */
    public function groupBy($string = '')
    {
        if ($string)
            $this->group = 'group by ' . $string;
        return $this;
    }

    /**
     * @param $order
     * @return $this
     */
    public function orderBy(string $order = '')
    {
        $this->order = 'order by ' . $order;
        return $this;
    }

    /**
     * @param $order
     * @return $this
     */
    public function order(string $order = '')
    {
        $this->order = 'order by ' . $order;
        return $this;
    }

    /**
     * @return bool|string|null
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param string $need_field
     * @return object
     */
    public function field(string $need_field = '')
    {
        $this->need_field = $need_field;
        return $this;

    }

    /**
     * @param int $offset
     * @param int $limit
     * @return object
     */
    public function limit(int $offset, int $limit)
    {
        $this->offset = $offset;
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getAll()
    {
        if (!$this->table)
            $this->ErrorMsg('table is null');
        if ($this->where)
            $sql = "select {$this->need_field} from {$this->table} {$this->Join} where {$this->where}";
        else
            $sql = "select {$this->need_field} from {$this->table} {$this->Join}";
        $this->last_sql = $sql;
        $this->clearSomeCondition();
        $arr = [];
        try {
            $res = $this->link->query($sql);
            if (!$res)
                $this->ErrorMsg('sql error');
            while ($row = $res->fetch_assoc()) {
                $arr[] = $row;
            }
            return $arr;
        } catch (\Exception $e) {
            $this->ErrorMsg($e->getMessage());
        }
        return $arr;
    }

    /**
     * @return array|string
     * @throws \Exception
     */
    public function select()
    {
        if (!$this->table)
            return 'table is null';
        if ($this->group)
            $this->group = $this->having ? $this->group . ' ' . $this->having : $this->group;
        if ($this->where)
            $sql = "select {$this->need_field} from {$this->table} {$this->Join} where {$this->where} {$this->group} {$this->order} limit {$this->offset},{$this->limit} ";
        else
            $sql = "select {$this->need_field} from {$this->table} {$this->Join} {$this->group} {$this->order} limit {$this->offset},{$this->limit} ";
        $this->last_sql = $sql;
        $this->clearSomeCondition();
        $arr = [];
        try {
            $res = $this->link->query($sql);
            if (!$res)
                $this->ErrorMsg('sql error');
            while ($row = $res->fetch_assoc()) {
                $arr[] = $row;
            }
            return $arr;
        } catch (\Exception $e) {
            $this->ErrorMsg($e->getMessage());
        }
    }

    /**
     * @return array|string
     * @throws \Exception
     */
    public function getSelect()
    {
        return $this->select();
    }

    /**
     * 关闭链接
     */
    public function close()
    {
        $this->link->close();
        $this->is_close = 1;
    }

    /**
     * @param string $joinString
     * @param string $type
     * @return $this
     * join支持
     */
    public function join($joinString = '', $type = 'inner')
    {
        if ($joinString)
            $this->Join = "{$type} join " . $joinString;
        return $this;
    }

    /**
     * @param bool $table
     * @return array
     * get table columns
     */
    public function getField($table = false)
    {
        $t = $this->real_table ?? '';
        if ($table)
            $t = $table;
        $sql = "show columns from {$t}";
        $res = $this->link->query($sql)->fetch_all();
        foreach ($res as $f_v) {
            if ($f_v[3] == 'PRI')
                $this->pri = $f_v[0];
        }
        if ($res)
            $res = array_column($res, 0);
        $this->fields = $res;
        return $res;
    }

    /**
     * @param $sql
     * @return mixed
     */
    public function query(string $sql = '')
    {
        return $this->link->query($sql);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function find()
    {
        return $this->getOne();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getOne()
    {
        if ($this->where)
            $sql = "select {$this->need_field} from {$this->table} {$this->Join} where {$this->where} {$this->order} limit 1 ";
        else
            $sql = "select {$this->need_field} from {$this->table} {$this->Join} {$this->order} limit 1 ";
        $this->last_sql = $sql;
        $this->clearSomeCondition();
        try {
            $res = $this->link->query($sql);
            if (!$res)
                $this->ErrorMsg('sql error');
            return mysqli_fetch_assoc($res);
        } catch (\Exception $e) {
            $this->ErrorMsg($e->getMessage());
            return [];
        }
    }

    /**
     * @return array|null
     * sql执行分析
     */
    public function getExplain()
    {
        if (!$this->getLastSql())
            return [];
        $sql_string = $this->getLastSql();
        $res = $this->link->query("explain " . $sql_string);
        $query_res = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $query_res[] = $row;
        }
        $ruturn['query_status'] = $query_res;
        $ruturn['query_sql'] = $sql_string;
        return $ruturn;
    }

    /**
     * 某些条件只能使用一次
     */
    protected function clearSomeCondition()
    {
        $this->Join = '';
        $this->need_field = '*';
        $this->order = '';
        $this->limit = 10000;
        $this->offset = 0;
        $this->group = '';
        $this->where = '';
        $this->having = '';
        $this->alias = 0;
    }

    /**
     * @return string
     */
    public function getLastSql()
    {
        return $this->last_sql;
    }

    /**
     * @return mixed
     */
    public function getErrorMsg()
    {
        return $this->link->error;
    }

    /**
     * @param array $where
     * @return string
     * 处理where条件
     */
    public function getWhere(array $where)
    {
        if (!$where)
            return '';
        $fields = $this->getField($this->real_table);
        $string = '';
        foreach ($where as $w_k => $w_v) {
            if (!in_array($w_k, $fields) && $this->alias == 0)
                continue;
            if (!is_array($w_v) || count($w_v) == 1) {
                $sign = '=';
            } else {
                if (count($w_v) != count($w_v, 1))
                    $w_v = array_values($w_v);
                $sign = $w_v[0];
                $w_v = $w_v[1];
            }
            if (in_array($sign, ['between', 'in', 'not in'])) {
                if (empty($w_v) || !is_array($w_v))
                    continue;
                if ($sign == 'between')
                    $w_v = $w_v[0] . ' AND ' . $w_v[1];
                else
                    $w_v = '(' . implode(',', $w_v) . ')';
            } else {
                if (in_array($sign, ['=', '>', '<', '<>', '!=', 'like', 'regexp'])) {
                    if ($sign == 'like')
                        $w_v = "'%{$w_v}%'";
                    elseif ($sign == 'regexp')
                        $w_v = "'^{$w_v}'";
                    else
                        $w_v = "'{$w_v}'";
                } else
                    continue;
            }
            $string = $string ? $string . ' AND ' . $w_k . ' ' . $sign . ' ' . $w_v : $w_k . ' ' . $sign . ' ' . $w_v;
        }
        return $string;
    }

    /**
     * @param array $data
     * @return bool|int|string
     * @throws \Exception
     */
    public function add(array $data)
    {
        $add = $this->doFilter($data);
        if ($add) {
            try {
                $res = mysqli_query($this->link, $add);
                if (!$res)
                    $this->ErrorMsg('sql error');
                $this->insert_id = mysqli_insert_id($this->link);
                return $this->insert_id;
            } catch (\Exception $e) {
                $this->ErrorMsg($e->getMessage());
            }
        }
        return false;
    }

    /**
     * @param array $data
     * @return bool|\mysqli_result
     * @throws \Exception
     * 批量插入
     */
    public function addAll(array $data)
    {
        $add = $this->doFilterMulti($data);
        if ($add) {
            try {
                $res = mysqli_query($this->link, $add);
                if (!$res)
                    $this->ErrorMsg('sql error');
                return $res;
            } catch (\Exception $e) {
                $this->ErrorMsg($e->getMessage());
            }
        }
        return false;
    }

    /**
     * @param string $havingString
     * @return $this
     */
    public function having(string $havingString = '')
    {
        if ($havingString)
            $this->having = $havingString;
        return $this;
    }

    /**
     * @return array|bool|null
     * @throws \Exception
     * 删除操作
     */
    public function delete()
    {
        if (!$this->table)
            $this->ErrorMsg('table is null');
        if (!$this->where)
            return false;
        $sql = "delete from {$this->table} where {$this->where}";
        $this->clearSomeCondition();
        try {
            $this->link->query($sql);
            $rows = $this->link->affected_rows;
            return $rows;
        } catch (\Exception $e) {
            $this->ErrorMsg($e->getMessage());
            return false;
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        if (!$this->table)
            return 0;
        if ($this->where)
            $sql = "select count(*) as number from {$this->table} where {$this->where}";
        else
            $sql = "select count(*) as number from {$this->table}";
        $this->clearSomeCondition();
        $this->last_sql = $sql;
        $count_result = $this->link->query($sql);
        if ($count_result) {
            $res = mysqli_fetch_assoc($count_result);
            if (!isset($res['number']))
                return 0;
            return (int)$res['number'];
        } else
            return 0;
    }

    /**
     * @param $save
     * @return bool
     * @throws \Exception
     */
    public function update($save)
    {
        if (!$this->where)
            return false;
        $sql = $this->UpdateFilter($save);
        if (!$sql)
            return true;
        try {
            $res = $this->link->query($sql);
            return $res;
        } catch (\Exception $e) {
            $this->ErrorMsg($e->getTraceAsString());
        }
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function UpdateFilter($data)
    {
        $fields = $this->getField();
        $need_update = [];
        foreach ($fields as $field) {
            if ($field == $this->pri || !isset($data[$field]) || (empty($data[$field]) && $data[$field] !== 0))
                continue;
            $need_update[] = $field . '=' . "'" . $data[$field] . "'";
        }
        if (empty($need_update))
            return false;
        $up_string = implode(',', $need_update);
        $sql = "update {$this->table} set {$up_string} where {$this->where}";
        $this->last_sql = $sql;
        return $sql;
    }

    /**
     * @param array $data
     * @return bool|string
     */
    public function doFilter(array $data)
    {
        $fields = $this->getField();
        $need_add = [];
        $in_field = [];
        foreach ($fields as $field) {
            if ($field == $this->pri || !isset($data[$field]))
                continue;
            $in_field[] = $field;
            if (is_array($data[$field]))
                $data[$field] = json_encode($data[$field], JSON_UNESCAPED_UNICODE);
            $need_add[] = "'" . $data[$field] . "'";
        }
        $some = array_filter($need_add);
        if (!$some)
            return false;
        $in_field = implode(',', $in_field);
        $need_add = implode(',', $need_add);
        $sql = "insert into {$this->table} ({$in_field}) values ({$need_add})";
        $this->last_sql = $sql;
        return $sql;
    }


    /**
     * @param array $data
     * @return bool|string
     */
    public function doFilterMulti(array $data)
    {
        $fields = $this->getField();
        $in_field = [];
        $temp = [];
        foreach ($fields as $field) {
            if ($field == $this->pri)
                continue;
            $in_field[] = $field;
        }
        foreach ($data as $k => $des) {
            if (!is_array($des))
                continue;
            $need_add = [];
            foreach ($in_field as $fd) {
                if (!isset($des[$fd]))
                    $des[$fd] = '';
                if (is_array($des[$fd]))
                    $des[$fd] = json_encode($des[$fd], JSON_UNESCAPED_UNICODE);
                $need_add[] = "'" . $des[$fd] . "'";
            }
            if (empty(array_filter($need_add)))
                continue;
            $temp[] = '(' . implode(',', $need_add) . ')';
        }
        if (empty($temp))
            return false;
        $add_string = implode(',', $temp);
        $in_field = implode(',', $in_field);
        $sql = "insert into {$this->table} ({$in_field}) values {$add_string}";
        $this->last_sql = $sql;
        return $sql;
    }

    /**
     * @param string $msg
     * @throws \Exception
     */
    protected function ErrorMsg($msg = '数据库操作失败')
    {
        $arr['code'] = 500;
        $arr['sql'] = $this->getLastSql();
        $arr['error'] = $this->link->error;
        $arr['msg'] = $msg;
        $arr['data_base'] = $this->database;
        $arr['table'] = $this->real_table;
        header("Content-Type:text/html;charset=UTF-8");
        exit(json_encode($arr, JSON_UNESCAPED_UNICODE));
    }


}