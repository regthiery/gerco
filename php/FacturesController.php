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
						if ( preg_match ("/(.*)\%/", $matches[2], $percents ))
							{
							$value = $facture["value"] ;
							$percent = $percents[1] ;
							$this->objects[$key][$matches[1]] = $value * $percent / 100.0 ;
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
		
	public function showEntretienForBatiment($batiment)	
		{
		$this -> selectAll () ;
		$this -> selectByKeyExt ("and", "to", "/$batiment/") ;
		$this -> selectByKeyExt ("and", "object", "/Entretien/") ;
		$this -> sortByDate ("date") ;
		$this -> display ( "to", "date", "value", "special$batiment", "escalier$batiment", "from", "imputations", "info") ;

		$this -> sumKeys ("value", "special$batiment", "escalier$batiment") ;
		$this -> displaySums ("value", "special$batiment", "escalier$batiment") ;
		$special = $this -> getSum("special$batiment") ;
		$escalier = $this -> getSum("escalier$batiment") ;
		return array($special,$escalier) ;
		}
		
	public function showElectriciteBatiment ($batiment)
		{
		$this -> selectAll () ;
		$this -> selectByKeyExt ("and", "to", "/$batiment/") ;
		$this -> selectByKeyExt ("and", "object", "/Electricité/") ;
		$this -> sortByDate ("date") ;
		$this -> display ( "to", "date", "value", "special$batiment", "from", "object", "imputations", "info") ;


		$this -> sumKeys ("value", "special$batiment") ;
		$this -> displaySums ("value", "special$batiment") ;
		$sum = $this -> getSum("special$batiment") ;
		return ($sum) ;
		}			

	public function showElectriciteGarage ($garage)
		{
		$this -> selectAll () ;
		$this -> selectByKeyExt ("and", "to", "/$garage/") ;
		$this -> selectByKeyExt ("and", "object", "/Electricité/") ;
		$this -> sortByDate ("date") ;
		$this -> display ( "to", "date", "value", "garage$garage", "from", "object", "imputations", "info") ;


		$this -> sumKeys ("value", "garage$garage") ;
		$this -> displaySums ("value", "garage$garage") ;
		$sum = $this -> getSum("garage$garage") ;
		return ($sum) ;
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
		
