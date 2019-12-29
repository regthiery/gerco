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
		$this -> display ( "to", "date", "value", "from", "object", "imputationsArray", "info") ;
		}
		
	public function showElectriciteBatiment ()
		{
		$this -> selectAll () ;
		$this -> selectByKeyExt ("and", "to", "/A/") ;
		$this -> selectByKeyExt ("and", "object", "/Electricité/") ;
		$this -> sortByDate ("date") ;
		$this -> display ( "to", "date", "value", "from", "object", "imputationsArray", "info") ;
		}			
}		
		
