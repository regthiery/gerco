<?php

#=============================================================================
	class InvoicesController extends HashController
#=============================================================================
{
	protected $imputationKeys ;
	
	
	public function __construct ()
		{
		echo "InvoicesController object created\n" ;
		$this->setPrimaryKey("index") ;
		}
		
	public function calculateImputations ()
		{
		foreach ( $this->objects as $key => $facture )
			{
			$sum = 0 ;
			if ( array_key_exists ("imputations", $facture) )
				{
				$imputations = $facture["imputations"] ;
				foreach ( $imputations as $key0 => $imputation )
					{
					$value = $facture["value"] ;

					if ( preg_match("/(.*)=>(.*)/", $imputation, $matches) )
						{
						$imputationKey = $matches[1] ;
						$imputationValue = $matches[2] ;
						
						if ( preg_match("/copro(.*)/", $imputationKey,$coproKey))
							{
							if ( preg_match("/(.*)x(.*)/",$imputationValue,$imputationValues) )
								{
								$ncopro = $imputationValues [1] ;
								$valueByCopro = $imputationValues [2] ;
								$imputationValue = $ncopro * $valueByCopro ;
								}
							}
						
						if ( preg_match ("/(.*)\%/", $imputationValue, $percents ))
							{
							$percent = $percents[1] ;
							$this->objects[$key][$imputationKey] = $value * $percent / 100.0 ;
							}
						else
							{
							$this->objects[$key][$imputationKey] = $imputationValue ;
							}	
						$sum += $this->objects[$key][$imputationKey] ;
						}
					}
					
				$res = abs ($facture["value"] - $sum) ;
				if ( $res >1e-3 )	
					{
					$index = $this->objects[$key][$this->primaryKey] ;
					print ("Erreur sur les imputations de la facture $index\n") ;
					print ("Son montant de $value euros n'est pas la somme des imputations \n") ;
					print_r ($imputations) ;
					}
				}
			}
		}
		
	public function calculateImputationKeysList ()		
		{
		$this->imputationKeys = array () ;
		foreach ( $this->objects as $key => $facture )
			{
			if ( array_key_exists ("imputations", $facture) )
				{
				$imputations = $facture["imputations"] ;
				foreach ( $imputations as $i => $imputation )
					{
					if ( preg_match("/(.*)=>(.*)/", $imputation, $matches) )
						{
						$imputationKey = $matches[1] ;
						$imputationValue = $matches[2] ;
						if ( array_key_exists ($imputationKey, $this->imputationKeys ) )
							{
							$this->imputationKeys [$imputationKey] ++ ;
							}
						else
							{
							$this->imputationKeys [$imputationKey] = 1 ;
							}	
						}
					}
				}
			}
		ksort ($this->imputationKeys)	;
		return $this->imputationKeys ;	
		}
		
	public function showFactures ()
		{
		$this -> selectAll () ;
		$this -> displayData ( "to", "date", "value", "from", "object", "imputations", "info") ;
		}
		
	public function showEntretienForBatiment($batiment)	
		{
		$this -> selectAll () ;
		$this -> selectByKeyExt ("and", "to", "/$batiment/") ;
		$this -> selectByKeyExt ("and", "object", "/Entretien/") ;
		$this -> sortByDate ("date") ;
		$this -> displayData ( "to", "date", "value", "special$batiment", "escalier$batiment", "from", "info") ;

		$this -> sumKeys ("value", "special$batiment", "escalier$batiment") ;
		$this -> displaySums ("value", "special$batiment", "escalier$batiment") ;
		$special = $this -> getSum("special$batiment") ;
		$escalier = $this -> getSum("escalier$batiment") ;
		$total = $special + $escalier ;
		print ("Total entretien batiment $batiment : $total \n") ;
		return array($special,$escalier) ;
		}
		
	public function showElectriciteBatiment ($batiment)
		{
		$this -> selectAll () ;
		$this -> selectByKeyExt ("and", "to", "/$batiment/") ;
		$this -> selectByKeyExt ("and", "object", "/Electricité/") ;
		$this -> sortByDate ("date") ;
		$this -> displayData ( "to", "date", "value", "special$batiment", "from", "object", "imputations", "info") ;


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
		$this -> displayData ( "to", "date", "value", "garage$garage", "from", "object", "imputations", "info") ;


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
		$this -> displayData ( "to", "date", "value", "ascenseur$batiment", "from", "object", "imputations", "info" ) ;


		$this -> sumKeys ("value", "ascenseur$batiment") ;
		$this -> displaySums ("value", "ascenseur$batiment") ;
		$sum = $this -> getSum("ascenseur$batiment") ;
		return ($sum) ;
		}			

}		
		
