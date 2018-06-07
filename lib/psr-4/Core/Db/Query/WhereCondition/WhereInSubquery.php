<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 06.06.2018
 * Time: 12:38
 *
 *
 */


namespace Core\Db\Query\WhereCondition;


use Core\Db\DbHelper;
use Core\Db\Query\SelectQueryBuilder;

class WhereInSubquery extends WhereConditionAbstract
{
    public function __construct( $logicOperator, $leftOperandField, SelectQueryBuilder $rightOperandValue, SelectQueryBuilder $parentQueryBuilder )
    {
        $sqlIn = DbHelper::wrapWithBraketsAndTabulate( $rightOperandValue->getSql() );
        
        parent::__construct( $logicOperator, $leftOperandField, " IN ", new WhereValue($sqlIn, false), $parentQueryBuilder );
    }
}