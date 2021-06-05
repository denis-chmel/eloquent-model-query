<?php declare(strict_types=1);

namespace EloquentModelQuery;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

abstract class AbstractQuery extends EloquentBuilder
{
    /**
     * @param EloquentBuilder $builder
     * @return EloquentBuilder|static
     */
    public static function getInstance(EloquentBuilder $builder): self
    {
        return $builder;
    }

    /**
     * @param \Closure $closure
     * @param string $boolean
     * @return static
     */
    public function whereAll(\Closure $closure, string $boolean = 'AND'): self
    {
        return $this->where($closure, $boolean);
    }

    /**
     * Change the logic of an inner wheres (1 level deep) logic from "AND WHERE" to "OR WHERE"
     * @param \Closure $closure
     * @param string $boolean
     * @return static
     */
    public function whereAny(\Closure $closure, string $boolean = 'AND'): self
    {
        // Slightly modified Eloquent\Builder::where(), to make OR logic inside the where
        $closure($query = $this->model->newQueryWithoutRelationships());
        foreach ($query->getQuery()->wheres as &$where) {
            $where['boolean'] = 'OR';
        }
        unset($where);
        $this->query->addNestedWhereQuery($query->getQuery(), $boolean);
        return $this;
    }

    /**
     * @param string $field
     * @param string $needle
     * @return static
     */
    protected function fieldContains(string $field, string $needle): self
    {
        return $this->where($field, 'LIKE', '%' . str_replace('%', '\%', $needle) . '%');
    }
}
