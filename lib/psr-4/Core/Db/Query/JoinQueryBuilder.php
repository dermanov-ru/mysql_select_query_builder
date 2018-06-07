<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 04.06.2018
 * Time: 22:30
 *
 *
 */


namespace Core\Db\Query;


use Core\Db\DbHelper;
use Core\Db\Query\AgregatFunction\AgregatFunctionAbstract;

class JoinQueryBuilder
{
    protected $tableFrom;
    protected $tableFromAlias;
    protected $fieldFrom;
    protected $tableTo;
    protected $fieldTo;
    protected $joinType = "INNER";
    
    /**
     * @var SelectQueryBuilder $where
     * */
    protected $where = null;
    protected $select = [];
    protected $orderby = [];
    
    /**
     * Join constructor.
     *
     * @param        $tableFrom
     * @param        $fieldFrom
     * @param        $tableTo
     * @param        $fieldTo
     * @param string $joinType
     */
    public function __construct( $tableFrom, $fieldFrom, $tableTo, $fieldTo, $joinType = "INNER", $tableFromAlias = "" )
    {
        $this->tableFrom = $tableFrom;
        
        if ($tableFromAlias)
            $this->tableFromAlias = $tableFromAlias;
        else
            $this->tableFromAlias = $tableFrom . "_" . mt_rand(100, 1000);
        
        $this->fieldFrom = $fieldFrom;
        $this->tableTo = $tableTo;
        $this->fieldTo = $fieldTo;
        $this->joinType = $joinType;
    }
    
    public function addOrderbyField( $table, $field, $asc = true )
    {
        $direction = $asc ? "ASC" : "DESC";
        $this->orderby[] = DbHelper::fieldWithTable($table, $field). " " . $direction;
        
        return $this;
    }
    
    public function addOrderbyAlias( $alias, $asc = true )
    {
        $direction = $asc ? "ASC" : "DESC";
        $this->orderby[] = DbHelper::escapeWithBacktick($alias). " " . $direction;
        
        return $this;
    }
    
    public function getOrderby(  )
    {
        return $this->orderby;
    }
    
    public function setWhere( SelectQueryBuilder $queryBuilder )
    {
        $queryBuilder->setTable($this->tableFromAlias);
        //$this->where = $queryBuilder->combineFinalFilter();
        $this->where = $queryBuilder;
        
        return $this;
    }
    
    public function getWhere( )
    {
        return $this->where;
    }
    
    public function getSql(  )
    {
        $result = "$this->joinType JOIN `$this->tableFrom` AS `$this->tableFromAlias` ON `$this->tableFromAlias`.`$this->fieldFrom` = `$this->tableTo`.`$this->fieldTo`";
        
        return $result;
    }
    
    public function getSelect(  )
    {
        $result = $this->select ? $this->select : ["`$this->tableFromAlias`.*"];
        
        return $result;
    }
    
    public function addSelectField( $field, $alias = "", AgregatFunctionAbstract $agregatFunction = null )
    {
        $this->select[] = ( new SelectStatement($this->tableFromAlias, $field, $alias, $agregatFunction) )->getSelectField();
        
        return $this;
    }
}