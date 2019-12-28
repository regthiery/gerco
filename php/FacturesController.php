<?php

#=============================================================================
	class FacturesController extends HashController
#=============================================================================
{
	
	public function __construct ()
		{
		echo "FacturesController object created\n" ;
		$this->setPrimaryKey("index") ;
		}
		
	public function showFactures ()
		{
		$this -> selectAll () ;
		$this -> display ( "to", "date", "value", "object", "from", "imputationsArray", "info") ;
		}	
}		
		
