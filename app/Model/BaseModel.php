<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BaseModel extends Model
{
    protected function insertIgnore($values)
    {
        if (empty($values)) {
            return true;
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);
                $values[$key] = $value;
            }
        }

        $static = new static();
        $columns = implode(', ',  array_map([$static, 'wrap'], array_keys(reset($values))));

        $parameters = [];

        foreach ($values as $record) {
            $parameters[] = '('.implode(', ', array_map([$static, 'parameter'], $record)).')';
        }

        $parameters = implode(', ', $parameters);

        DB::insert("insert ignore into $static->table ($columns) values $parameters");
    }

    protected function wrap($value)
    {
        return '`'.str_replace('`', '``', $value).'`';
    }

    protected function parameter($value)
    {
        return "'" . $value . "'";
    }
}
