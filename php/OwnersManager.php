<?php

include_once "LotsManager.php" ;

#=============================================================================
	class OwnersManager extends HashManager
#=============================================================================
{
	
	public function __construct ()
		{
		echo "OwnersManager object created\n" ;
		$this->setPrimaryKey("owner") ;
		}
				
	public function show ($batiment)
		{
		$this->unselect () ;
		$this->selectByKey ("or","lotData:batiment", $batiment) ;
		$this->display("lotData:type", "lotData:floor","lotData:situation","lotData:general","lastname","firstname", "syndicCode") ;
		}
		
	public function showOwners()		
		{
		$this->selectAll () ;
		$this->sortNumeric("owner") ;
		$this->display ("general","lastname","firstname","syndicCode") ;
		}

	public function showSortedBySyndicCode()		
		{
		$this->selectAll () ;
		$this->sortNumeric("syndicCode") ;
		$this->display ("syndicCode","general","lastname","firstname") ;
		}
}