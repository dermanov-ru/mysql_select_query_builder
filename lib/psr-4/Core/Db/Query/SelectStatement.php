<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 07.06.2018
 * Time: 23:03
 *
 *
 */


namespace Core\Db\Query;


use Core\Db\DbHelper;
use Core\Db\Query\AgregatFunction\AgregatFunctionAbstract;

class SelectStatement
{
    protected $table;
    protected $field;
    protected $alias;
    
    /**
     * @var AgregatFunctionAbstract $agregatFunction
     * */
    protected $agregatFunction;
    
    /**
     * SelectSatement constructor.
     *
     * @param $field
     * @param $alias
     */
    public function __construct( $table, $field, $alias = "", $agregatFunction = null )
    {
        $this->table = $table;
        $this->field = $field;
        $this->alias = $alias;
        $this->agregatFunction = $agregatFunction;
    }
    
    public function getSelectField(  )
    {
        $result = DbHelper::fieldWithTable($this->table, $this->field);
        
        if ($this->agregatFunction) {
            $result = $this->agregatFunction->getSelectFunc($result);
        }
        
        if ($this->alias)
            $result .= " AS " . DbHelper::escapeWithBacktick($this->alias) ;
        
        return $result;
    }
}