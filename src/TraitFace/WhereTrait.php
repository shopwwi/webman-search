<?php
/**
 *-------------------------------------------------------------------------p*
 *
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2022 Shopwwi Inc. (http://www.shopwwi.com)
 *-------------------------------------------------------------------------c*
 * @license    http://www.shopwwi.com        s h o p w w i . c o m
 *-------------------------------------------------------------------------e*
 * @link       http://www.shopwwi.com by 象讯科技 phcent.com
 *-------------------------------------------------------------------------n*
 * @since      shopwwi象讯·PHP商城系统Pro
 *-------------------------------------------------------------------------t*
 */

namespace Shopwwi\WebmanMeilisearch\TraitFace;
trait WhereTrait
{
    protected $wheres = [];
    /**
     * 可以是=, !=, >, >=, <, 或<=
     */
    public function where($column, $operator = null, $value = null, string $boolean = 'AND')
    {
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }
        $type = 'Basic';
        $this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');
        return $this;
    }

    /**
     * @param $column
     * @param $operator
     * @param $value
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * 区间
     * @param $column
     * @param array $values
     * @param string $boolean
     * @return $this
     */
    public function whereBetween($column,array $values, string $boolean = 'AND')
    {
        $type = 'Between';
        $this->wheres[] = compact('type', 'column', 'values', 'boolean');
        return $this;
    }

    /**
     * @param $column
     * @param array $values
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereIn($column,array $values, string $boolean = 'AND', bool $not = false)
    {
        $type = $not ? 'NotIn' : 'In';

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');
        return $this;
    }
    /**
     *
     * @param string $column
     * @param  mixed  $values
     * @return $this
     */
    public function orWhereIn(string $column, array $values)
    {
        return $this->whereIn($column, $values, 'OR');
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param string $column
     * @param  mixed  $values
     * @param string $boolean
     * @return $this
     */
    public function whereNotIn(string $column,array $values, string $boolean = 'AND')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param string $column
     * @param  mixed  $values
     * @return $this
     */
    public function orWhereNotIn(string $column, $values)
    {
        return $this->whereNotIn($column, $values, 'OR');
    }
    /**
     * Add a raw where clause to the query.
     *
     * @param  string  $sql
     * @param  mixed  $bindings
     * @param  string  $boolean
     * @return $this
     */
    public function whereRaw($sql,  $boolean = 'AND')
    {
        $this->wheres[] = ['type' => 'Raw', 'sql' => $sql, 'boolean' => $boolean];
        return $this;
    }

    /**
     *
     * @param $operator
     * @return bool
     */
    protected function invalidOperator($operator)
    {
        return ! is_string($operator) || ! in_array(strtolower($operator), ['=', '!=', '>', '>=', '<', '<='], true);
    }

}