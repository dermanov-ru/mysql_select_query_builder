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

class WhereLike extends WhereConditionAbstract
{
    protected $inFront;
    protected $inEnd;
    
    /**
     * WhereConditionAbstract constructor.
     */
    public function __construct( $logicOperator, $leftOperandField, $rightOperandValue, $inFront, $inEnd, SelectQueryBuilder $queryBuilder )
    {
        $this->inFront = $inFront;
        $this->inEnd = $inEnd;
        
        parent::__construct( $logicOperator, $leftOperandField, " LIKE ", new WhereValue($rightOperandValue, false), $queryBuilder );
    }
    
    public function getRightOperandValue()
    {
        $frontLike = $this->inFront ? "%" : "";
        $endLike   = $this->inEnd ? "%" : "";
    
        $value = parent::getRightOperandValue();
        $result = DbHelper::escapeWithSingleQuotes( $frontLike . $value . $endLike );
        
        return $result;
    }
    
    
}