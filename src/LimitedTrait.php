<?php namespace Xjchen\LimitedTrait;

trait LimitedTrait
{
    /**
     * Boot the limited trait for a model.
     *
     * @return void
     */
    public static function bootLimitedTrait()
    {
        static::addGlobalScope(new LimitedScope);
        static::creating(function ($model) {
            $limitedColumns = $model->getLimitedColumns();
            foreach ($limitedColumns as $limitedColumn) {
                $getValueMethod = 'getLimited'.$limitedColumn;
                if (method_exists($model, $getValueMethod)) {
                    if (!$model->{$limitedColumn}) {
                        $model->{$limitedColumn} = $model->{$getValueMethod}();
                    }
                }
            }
        });
    }

    public function getLimitedColumns()
    {
        if (!property_exists($this, 'limitedColumns')) {
            return [];
        } else {
            $limitedColumns = $this->limitedColumns;
            if (!is_array($limitedColumns)) {
                return [];
            } else {
                return $limitedColumns;
            }
        }
    }

    public function getQualifiedLimitedColumn($column)
    {
        return $this->getTable().'.'.$column;
    }

    public function getQualifiedLimitedColumns()
    {
        $limitedColumns = $this->getLimitedColumns();
        $qualifiedLimitedColumns = [];
        foreach($limitedColumns as $limitedColumn) {
            $qualifiedLimitedColumns[] = $this->getQualifiedLimitedColumn($limitedColumn);
        }
        return $qualifiedLimitedColumns;
    }

    public function notLimited()
    {
        return (new static)->newQueryWithoutScope(new LimitedScope());
    }
}
