<?php

namespace Gerco\Data ;


use Gerco\Data\DataObjects;

/**
*	Gère les informations relatives aux lots
*
*	@package Gerco
*	@author Régis THIERY
*/

	class Lots extends DataObjects
{
	protected DataObjects $imputations ;
	
	public function __construct ()
		{
        parent::__construct();
        $this->setPrimaryKey("lot") ;
		}
		
	public function setImputations ($imputations)
		{
		$this->imputations = $imputations ;
		}
		
	public function checkSpecialMilliemes ($imputationKey)
		{
		$this->unselect () ;
		$this->selectByKeyExt('or',"imputations:$imputationKey","/.*/") ;
		$this->sumKeys("imputations:$imputationKey") ;
		$this->logger->displayData("lot>7","batiment>7","imputations:$imputationKey>8",
            "ownerData:lastname>16") ;
		$this->logger->displaySums("imputations:$imputationKey") ;
		}
		
	public function showGeneralMilliemes ()
		{
		$this->selectAll () ;
		$this->sumKeys("general","imputations:general") ;
		$this->sortNumeric("lot") ;
		$this->logger->displayData("lot>8","batiment>6","type>12","general>10",
            "imputations:general>10","ownerData:lastname>16") ;
		$this->logger->displaySums("general","imputations:general") ;
		}	

	public function showSpecialMilliemes ()
		{
		    foreach ($this->imputations->objects as $imputationKey => $imputation) {
		        if ((preg_match("/^copro(\d+)/", $imputationKey)
                    || preg_match("/^unitaire/", $imputationKey)) == FALSE)
                {
                    $this->checkSpecialMilliemes($imputationKey) ;
                }
            }
		}
	
	public function showGarageHandicap ()
		{
		$this->unselect () ;
		$this->selectByKey("or","handicap","1") ;
		$this->sortNumeric ("lot") ;
		$this->logger->displayData("lot>8","batiment>8","type>8","handicap>8","ownerData:lastname>16") ;
		$this->logger->displayFilteredCount () ;
		}
		
		
	public function showParkings ()
		{
		    $batiments = $this->getBuildings() ;
            foreach ($batiments as $batiment=>$count) {
                $this -> showParkingsLinkedTo ($batiment) ;
		    }
		}
		
	public function showParkingsLinkedTo ($bat)
		{
		$this->unselect () ;
		$this->selectByKey("or","type","garage") ;
		$this->selectByKey("or","type","parking") ;
		$this->selectByKey("and","with",$bat) ;
		$this->sortNumeric ("lot") ;
		$this->logger->displayData("batiment>8","type>6","with>6","situation>6","ownerData:lastname>16") ;
		$this->logger->displayFilteredCount () ;
		}	

	public function calculateMilliemes ()
		{
		foreach ($this->objects as $key => $item)
			{
			if ( $item['type'][0] == 'T' )
				{
				foreach ($this->imputations->getObjects() as $imputationKey => $imputationData)
	 				{
					if ( array_key_exists ($imputationKey, $this->objects[$key]))
						$this->objects[$key]['imputations'][$imputationKey] = $this->objects[$key][$imputationKey] ;
		 			}

				if ( array_key_exists('dependances',$item))
					{
					//$bat = $item['batiment'] ;
					$dep = $item['dependances'] ;
					
					if ( empty($dep) )
						continue ;

					foreach ($this->imputations->getObjects() as $imputationKey => $imputationData)
						{
						foreach ( $dep as $i => $depIndex)
							{
							$dependentLot = $this->getObjectWithKey($depIndex) ;
							if ($dependentLot == NULL)
									continue ;
							// print_r($dependanceLot) ;
							$this->objects[$key]['dependanceLots'][$depIndex] = $dependentLot ;
							if ( array_key_exists ($imputationKey, $dependentLot) )
								{
								if ( ! array_key_exists ($imputationKey, $this->objects[$key]['imputations']))
									$this->objects[$key]['imputations'][$imputationKey] = 0 ;
								$this->objects[$key]['imputations'][$imputationKey] += $dependentLot[$imputationKey] ;
								}
							}
						}
					}
				}
			}
		}	
		
	public function showPrices ()
		{
		$this->unselect () ;
		$this->selectDefinedKey("or","prix") ;
		$this->sortNumeric ("lot") ;
		$this->logger->displayData("batiment>8","type>5","prix>15","prixm2>15","owner>6","floor>6","situation>6",
            "general>12","imputations:general>12","ownerData:lastname>15") ;
		}	


	public function getBuildings () : array
    {
        $batiments = array () ;
        foreach ($this->objects as $lot => $data) {
            if ($data['type'][0] === 'T')
            {
                if ( array_key_exists($data['batiment'],$batiments))
                    ++ $batiments[$data['batiment']]  ;
                else
                    $batiments[$data['batiment']] = 1 ;

            }
        }
        ksort($batiments) ;
        return $batiments ;
    }

	public function showOwners ()
    {
        $batiments = $this -> getBuildings() ;

        foreach ($batiments as $batiment => $count) {
            $this->showOwnersByBatiment($batiment) ;
        }
    }

	public function showOwnersByBatiment ($batiment)
		{

		    $this->unselect () ;
		    $this->selectByKey("or","batiment",$batiment) ;

			// Sélectionne les lots qui sont des appartements		
            $this->filteredObjects = array_filter ( $this->filteredObjects,
				function ($item)
					{
					return ( $item ['type'][0] === 'T' ) ? 1 : 0 ;
					}
			) ;
            $this->sortNumeric ("lot") ;
            $this->sumKeys("imputations:general", "imputations:special$batiment") ;

            $this->logger->displayData("lot>7", "batiment>6","type>6","prix>10","prixm2>10","owner>15",
                "floor>6", "situation>6","imputations:general>9",
                "imputations:special$batiment>9","ownerData:lastname>18") ;
            $this->logger->displaySums("imputations:general", "imputations:special$batiment") ;
		}
}	