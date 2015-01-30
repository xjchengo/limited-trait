<?php namespace Xjchen\LimitedTrait;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ScopeInterface;

class LimitedScope implements ScopeInterface
{

    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = ['NotLimited'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function apply(Builder $builder)
    {
        $model = $builder->getModel();

        $limitedColumns = $model->getLimitedColumns();

        foreach ($limitedColumns as $limitedColumn) {
            $getValueMethod = 'getLimited'.$limitedColumn;
            if (method_exists($model, $getValueMethod)) {
                $limitedValue = $model->{$getValueMethod}();
                $builder->where($model->getQualifiedLimtedColumn($limitedColumn), $limitedValue);
            }
        }

        $this->extend($builder);
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function remove(Builder $builder)
    {
        $qualifiedColumns = $builder->getModel()->getQualifiedLimitedColumns();

        $query = $builder->getQuery();

        foreach ((array)$query->wheres as $key => $where) {
            // If the where clause is a soft delete date constraint, we will remove it from
            // the query and reset the keys on the wheres. This allows this developer to
            // include deleted model in a relationship result set that is lazy loaded.
            if ($this->isLimitedConstraint($where, $qualifiedColumns)) {
                $this->removeWhere($query, $key);

                // Here SoftDeletingScope simply removes the where
                // but since we use Basic where (not Null type)
                // we need to get rid of the binding as well
                $this->removeBinding($query, $key);
            }
        }
    }

    /**
     * Remove scope constraint from the query.
     *
     * @param  \Illuminate\Database\Query\Builder  $builder
     * @param  int  $key
     * @return void
     */
    protected function removeWhere(BaseBuilder $query, $key)
    {
        unset($query->wheres[$key]);

        $query->wheres = array_values($query->wheres);
    }

    /**
     * Remove scope constraint from the query.
     *
     * @param  \Illuminate\Database\Query\Builder  $builder
     * @param  int  $key
     * @return void
     */
    protected function removeBinding(BaseBuilder $query, $key)
    {
        $bindings = $query->getRawBindings()['where'];

        unset($bindings[$key]);

        $query->setBindings($bindings);
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    public function addNotLimited(Builder $builder)
    {
        $builder->macro('notLimited', function (Builder $builder) {
            $this->remove($builder);
            return $builder;
        });
    }

    /**
     * Determine if the given where clause is a limited constraint.
     *
     * @param  array $where
     * @param  array $columns
     * @return bool
     */
    protected function isLimitedConstraint(array $where, $columns)
    {
        return ($where['type'] == 'Basic' && in_array($where['column'], $columns));
    }

}
