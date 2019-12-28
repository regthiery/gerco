<?php

include_once "LotsController.php" ;

#=============================================================================
	class ResidentsController extends HashController
#=============================================================================
{
	
	public function __construct ()
		{
		echo "ResidentsController object created\n" ;
		$this->setPrimaryKey("lot") ;
		}
		

	public function show ($batiment)
		{
		$this->unselect () ;
		$this->selectByKey ("or","batiment", $batiment) ;
		$this->display("lotData:type", "lotData:floor","lotData:situation","lastname","firstname") ;
		}	
}