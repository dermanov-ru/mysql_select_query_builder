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

class WhereConditionAbstract
{
    protected $logicOperator;
    protected $notOperator = false;
    protected $leftOperandField;
    protected $operator;
    protected $rightOperandValue;
    protected $queryBuilder;
    
    /**
     * WhereConditionAbstract constructor.
     *
     * @param $logicOperator
     * @param $leftOperandField
     * @param $operator
     * @param $rightOperandValue
     * @param $queryBuilder
     */
    public function __construct( $logicOperator, $leftOperandField, $operator, WhereValue $rightOperandValue, SelectQueryBuilder $queryBuilder )
    {
        $this->logicOperator = $logicOperator;
        $this->leftOperandField = $leftOperandField;
        $this->operator = $operator;
        $this->rightOperandValue = $rightOperandValue;
        $this->queryBuilder = $queryBuilder;
    }
    
    /**
     * @return mixed
     */
    public function getLogicOperator()
    {
        return $this->logicOperator;
    }
    
    /**
     * @param mixed $logicOperator
     */
    public function setLogicOperator( $logicOperator )
    {
        $this->logicOperator = $logicOperator;
    }
    
    /**
     * @return bool
     */
    public function isNotOperator()
    {
        return $this->notOperator;
    }
    
    /**
     * @param bool $notOperator
     */
    public function setNotOperator( $notOperator )
    {
        $this->notOperator = $notOperator;
    }
    
    /**
     * @return mixed
     */
    public function getLeftOperandField()
    {
        return $this->leftOperandField;
    }
    
    /**
     * @param mixed $leftOperandField
     */
    public function setLeftOperandField( $leftOperandField )
    {
        $this->leftOperandField = $leftOperandField;
    }
    
    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }
    
    /**
     * @param mixed $operator
     */
    public function setOperator( $operator )
    {
        $this->operator = $operator;
    }
    
    /**
     * @return mixed
     */
    public function getRightOperandValue()
    {
        return $this->rightOperandValue->getValue();
    }
    
    /**
     * @param mixed $rightOperandValue
     */
    public function setRightOperandValue( $rightOperandValue )
    {
        $this->rightOperandValue = $rightOperandValue;
    }
    
    /**
     * @return SelectQueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
    
    /**
     * @param SelectQueryBuilder $queryBuilder
     */
    public function setQueryBuilder( $queryBuilder )
    {
        $this->queryBuilder = $queryBuilder;
    }
    
    public function getWhereCondition(  )
    {
        $combinedFilter = $this->getCombinedFilter();
        $not = $this->notOperator ? " NOT " : "";
    
        $result = $this->logicOperator . $not . DbHelper::wrapWithBraketsAndTabulate( $combinedFilter );
        
        return $result;
    }
    
    protected function getCombinedFilter(  )
    {
        $leftOperand = DbHelper::fieldWithTable($this->queryBuilder->getTable(), $this->leftOperandField);
        $rightOperand = $this->getRightOperandValue();
    
        $result = $leftOperand . $this->operator . $rightOperand;

        return $result;
    }
    
    
}