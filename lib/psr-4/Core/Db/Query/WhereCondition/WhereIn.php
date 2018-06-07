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

class WhereIn extends WhereConditionAbstract
{
    protected $logicOperator;
    protected $leftOperandField;
    protected $operator;
    protected $rightOperandValue;
    protected $queryBuilder;
    
    /**
     * WhereConditionAbstract constructor.
     *
     * @param $logicOperator
     * @param $leftOperandField
     * @param $rightOperandValue
     * @param $queryBuilder
     */
    public function __construct( $logicOperator, $leftOperandField, $rightOperandValue, SelectQueryBuilder $queryBuilder )
    {
        $sqlIn = DbHelper::wrapWithBraketsAndTabulate( DbHelper::implodeAndEscape( $rightOperandValue, "'" ) );
        
        parent::__construct( $logicOperator, $leftOperandField, " IN ", new WhereValue($sqlIn, false), $queryBuilder );
    }
}