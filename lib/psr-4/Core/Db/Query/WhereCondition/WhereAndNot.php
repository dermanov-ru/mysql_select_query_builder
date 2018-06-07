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


use Core\Db\DbHelper;
use Core\Db\Query\SelectQueryBuilder;

class WhereAndNot extends WhereConditionAbstract
{
    /**
     * WhereOr constructor.
     */
    public function __construct(SelectQueryBuilder $selectQueryBuilder)
    {
        parent::__construct("AND", "", "", new WhereValue($selectQueryBuilder->combineFinalFilter(), false), $selectQueryBuilder);
        $this->setNotOperator(true);
    }
    
    public function getCombinedFilter()
    {
        return $this->getRightOperandValue();
    }
}