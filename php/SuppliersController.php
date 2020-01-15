<?php

#=============================================================================
	class SuppliersController extends HashController
#=============================================================================
{
	
	public function __construct ()
		{
		$this->setPrimaryKey("index") ;
		}
				
	public function displaySuppliers ()
		{
		$this->selectAll () ;
		$this->sortNumeric("index") ;
		$this->displayData ("shortName","name") ;
		}
		
}