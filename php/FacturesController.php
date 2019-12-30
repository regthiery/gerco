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
		
	public function calculateImputations ()
		{
		foreach ( $this->objects as $key => $facture )
			{
			if ( array_key_exists ("imputations", $facture) )
				{
				$imputations = $facture["imputations"] ;
				foreach ( $imputations as $key0 => $imputation )
					{
					if ( preg_match("/(.*)=>(.*)/", $imputation, $matches) )
						{
						if ( preg_match ("/(.*)%/", $matches[2], $percents ))
							{
							}
						else
							{
							$this->objects[$key][$matches[1]] = $matches[2] ;
							}	
						}
					}
				}
			}
		}	
		
	public function showFactures ()
		{
		$this -> selectAll () ;
		$this -> display ( "to", "date", "value", "from", "object", "imputations", "info") ;
		}
		
	public function showElectriciteBatiment ($batiment)
		{
		$this -> selectAll () ;
		$this -> selectByKeyExt ("and", "to", "/$batiment/") ;
		$this -> selectByKeyExt ("and", "object", "/Electricité/") ;
		$this -> sortByDate ("date") ;
		$this -> display ( "to", "date", "value", "from", "object", "imputations", "info") ;
		}			

	public function showAscenseurBatiment ($batiment)
		{
		$this -> selectAll () ;
		$this -> selectByKeyExt ("and", "to", "/$batiment/") ;
		$this -> selectByKeyExt ("and", "object", "/Ascenseur/") ;
		$this -> sortByDate ("date") ;
		$this -> display ( "to", "date", "value", "ascenseur$batiment", "from", "object", "imputations", "info" ) ;


		$this -> sumKeys ("value", "ascenseur$batiment") ;
		$this -> displaySums ("value", "ascenseur$batiment") ;
		$sum = $this -> getSum("ascenseur$batiment") ;
		return ($sum) ;
		}			

}		
		
