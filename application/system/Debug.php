<?php

class Debug
{
    protected static $_instance;

    protected $debug = false;
    protected $queries = array();


    private function __construct()
    {

    }
    private function __clone(){}

    /**
     *
     * @return Debug
     */
    public static function getInstance()
    {
        if (null === self::$_instance)
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function isAllowed()
    {
        return $this->debug;
    }

    public function setDebugging($debug)
    {
        $this->debug = $debug ? true : false;
    }

    public function addQuery($config, $sql)
    {
        if( $this->debug )
        {
            $this->queries[] = "[" . $config['dbname'] . "]" .  $sql;
        }
    }

    public function getQueries()
    {
        return $this->queries;
    }

}

?>
