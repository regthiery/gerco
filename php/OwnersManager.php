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
		
	public function joinWithLotsData (LotsManager &$lotsManager)	
		{
		$lotsData = $lotsManager -> getObjets () ;

		foreach ($this->objects as $key => $owner )
			{
			$lot = $owner["owner"] ;
			if ( array_key_exists($lot,$lotsData))
				{
				$this->objects[$lot]["lotData"] = $lotsData[$lot] ;
				}
			}
		}
		
	public function show ($batiment)
		{
		$this->unselect () ;
		$this->selectByKey ("or","lotData:batiment", $batiment) ;
		$this->display("lotData:type", "lotData:floor","lotData:situation","lotData:general","lastname","firstname") ;
		}
		
	public function showOwners()		
		{
		$this->selectAll () ;
		$this->sortNumeric("owner") ;
		$this->display ("general","lastname","firstname") ;
		}
}