<?php

include_once "LotsManager.php" ;

#=============================================================================
	class ResidentsManager extends HashManager
#=============================================================================
{
	
	public function __construct ()
		{
		echo "ResidentsManager object created\n" ;
		$this->setPrimaryKey("lot") ;
		}
		
	public function joinWithLotsData (LotsManager &$lotsManager)	
		{
		$lotsData = $lotsManager -> getObjets () ;

		foreach ($this->objects as $key => $resident )
			{
			$lot = $resident["lot"] ;
			$this->objects[$lot]["lotData"] = $lotsData[$lot] ;
			}
		}
		
	public function show ($batiment)
		{
		$this->unselect () ;
		$this->selectByKey ("or","batiment", $batiment) ;
		$this->display("lotData:type", "lotData:floor","lotData:situation","lastname","firstname") ;
		}	
}