<?php

#=============================================================================
	class FacturesManager extends HashManager
#=============================================================================
{
	
	public function __construct ()
		{
		echo "FacturesManager object created\n" ;
		$this->setPrimaryKey("index") ;
		}
		
	public function showFactures ()
		{
		$this -> unselect () ;
		$this -> display ("index", "object", "value", "from", "date", "to", "info")
		}	
}		
		
