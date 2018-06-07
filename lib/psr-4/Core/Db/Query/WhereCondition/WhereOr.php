<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 06.06.2018
 * Time: 15:43
 *
 *
 */


namespace Core\Db\Query\WhereCondition;


use Core\Db\Query\SelectQueryBuilder;

class WhereOr extends WhereConditionAbstract
{
    /**
     * WhereOr constructor.
     */
    public function __construct(SelectQueryBuilder $selectQueryBuilder)
    {
        parent::__construct("OR", "", "", new WhereValue($selectQueryBuilder->combineFinalFilter(), false), $selectQueryBuilder);
    }
    
    public function getCombinedFilter()
    {
        return $this->getRightOperandValue();
    }
    
    
}