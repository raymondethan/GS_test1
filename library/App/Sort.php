<?php 

class App_Sort {
	
	protected $column;
	
	function __construct($column) {
    		$this->column = $column;
  	}
  
  	function sort($table,$order) {
    		usort($table, array($this, 'compare_'.strtolower($order)));
    		return $table;
  	}
  	
  	function compare_asc($a, $b) {
  		if (is_numeric($a[$this->column]) && is_numeric($b[$this->column])) {
	    		if ($a[$this->column] == $b[$this->column]) {
	      		return 0;
	    		}
	    		return ($a[$this->column] < $b[$this->column]) ? -1 : 1;
  		}
		
  		return strcmp($a[$this->column],$b[$this->column]);
  	}
  	
  	function compare_desc($a, $b) {
  		if (is_numeric($a[$this->column]) && is_numeric($b[$this->column])) {
	    		if ($a[$this->column] == $b[$this->column]) {
	      		return 0;
	    		}
	    		return ($a[$this->column] > $b[$this->column]) ? -1 : 1;
  		}
  		
  		$r = strcmp($a[$this->column],$b[$this->column]);
  		$r = ($r == 1) ? -1 : $r;
  		$r = ($r == -1) ? 1 : $r;
  		
		return $r;
  	}
}

?>

