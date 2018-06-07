<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 04.06.2018
 * Time: 12:41
 *
 *
 */


namespace Core\Db\Query\AgregatFunction;


use Core\Db\DbHelper;

class AgregatFunctionAbstract
{
    protected $distinct;
    protected $func;
    
    public function __construct( $func, $distinct = false )
    {
        $this->func = $func;
        $this->distinct = $distinct;
    }
    
    /**
     * @return mixed
     */
    public function getSelectFunc($fieldWithTable)
    {
        $distinct = $this->distinct ? " DISTINCT " : "";
        $result = $this->func. "( " . $distinct . $fieldWithTable . " ) " ;
    
        return $result;
    }
}