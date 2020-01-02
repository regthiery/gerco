<?php

#=============================================================================
	class LotsController extends HashController
#=============================================================================
{
	
	public function __construct ()
		{
		echo "LotsController object created\n" ;
		$this->setPrimaryKey("lot") ;
		}
		
	public function checkMilliemesForBatiment ($bat)	
		{
		$this->unselect () ;
            if ( $bat === "A" or $bat === "B" or $bat === "C")
            	{
	            $this->selectByKey ("or", "batiment", $bat) ;
	            $this->selectByKey ("or", "batiment", "Z") ;
	            $this->sumKeys ("general", "special$bat", "specialAscenseur$bat", "specialEscalier$bat") ;
	            $this->sortNumeric ("lot") ;
	            $this->displayData ("batiment","type","general","special$bat","specialAscenseur$bat","specialEscalier$bat","numeros","ownerData:lastname") ;
      	      $this->displayFilteredCount () ;
	            $this->displaySums ("general", "special$bat", "specialAscenseur$bat", "specialEscalier$bat") ;
            	}
            elseif ( $bat === "D" or $bat === "E" )	
            	{
	            $this->selectByKey ("or", "batiment", $bat) ;
            	$this->selectByKey ("or", "batiment", "X") ;
	            $this->sumKeys ("general", "special$bat", "specialAscenseur$bat", "specialEscalier$bat", "specialExt") ;
	            $this->sortNumeric ("lot") ;
	            $this->displayData ("batiment","type","general","special$bat","specialAscenseur$bat","specialEscalier$bat","specialExt", "numeros","ownerData:lastname") ;
		      $this->displayFilteredCount () ;
	            $this->displaySums ("general", "special$bat", "specialAscenseur$bat", "specialEscalier$bat", "specialExt") ;
            	}
            elseif ( $bat === "Z" )	
            	{
	            $this->selectByKey ("or", "batiment", $bat) ;
	            $this->sumKeys ("general",  "specialZ") ;
	            $this->sortNumeric ("lot") ;
	            $this->displayData ("batiment","type","general","specialZ", "double", "handicap", "numeros","ownerData:lastname") ;
		      $this->displayFilteredCount () ;
	            $this->displaySums ("general", "specialZ") ;
            	}
            elseif ( $bat === "DEX" )
            	{
	            $this->selectByKey ("or", "batiment", "D") ;
	            $this->selectByKey ("or", "batiment", "E") ;
	            $this->selectByKey ("or", "batiment", "X") ;
	            $this->sumKeys ("general",  "specialExt") ;
	            $this->sortNumeric ("lot") ;
	            $this->displayData ("batiment","type","specialExt", "numeros","ownerData:lastname") ;
	            $this->displaySums ("general",  "specialExt") ;
            	}
		}
		
	public function checkGeneralMilliemes () 
		{
		$this->selectAll () ;
		$this->sumKeys("general") ;
		$this->sortNumeric("lot") ;
		$this->displayData("lot","batiment","type","general","ownerData:lastname") ;
		$this->displaySums("general") ;
		}	

	public function checkMilliemes ()
		{
		$this -> checkMilliemesForBatiment ("A") ;
		$this -> checkMilliemesForBatiment ("B") ;
		$this -> checkMilliemesForBatiment ("C") ;
		$this -> checkMilliemesForBatiment ("D") ;
		$this -> checkMilliemesForBatiment ("E") ;
		$this -> checkMilliemesForBatiment ("DEX") ;
		$this -> checkMilliemesForBatiment ("Z") ;
		}
	
	public function showGarageHandicap ()
		{
		$this->unselect () ;
		$this->selectByKey("or","batiment","Z") ;
		$this->selectByKey("or","batiment","D") ;
		$this->selectByKey("or","batiment","X") ;
		$this->selectByKey("and","handicap","1") ;
	      $this->sortNumeric ("lot") ;
		$this->displayData("batiment","type","handicap","ownerData:lastname") ;
		$this->displayFilteredCount () ;
		}
		
		
	public function showParkings ()
		{
		$this -> showParkingsLinkedTo ("A") ;
		$this -> showParkingsLinkedTo ("B") ;
		$this -> showParkingsLinkedTo ("C") ;
		$this -> showParkingsLinkedTo ("D") ;
		$this -> showParkingsLinkedTo ("E") ;
		}	
		
	public function showParkingsLinkedTo ($bat)
		{
		$this->unselect () ;
		$this->selectByKey("or","batiment","Z") ;
		$this->selectByKey("or","batiment","D") ;
		$this->selectByKey("or","batiment","X") ;
		$this->selectByKey("and","with",$bat) ;
	      $this->sortNumeric ("lot") ;
		$this->displayData("batiment","type","with","situation","ownerData:lastname") ;
		$this->displayFilteredCount () ;
		}	

	public function calculateMilliemes ()
		{
		foreach ($this->objects as $key => $item)
			{
			if ( array_key_exists("dependances",$item))
				{
				$type = $item["type"] ;
				if ( $type === "T1" or $type === "T2" or $type === "T3" or $type === "T4")
					{
					$lot = $item["lot"] ;
					$bat = $item["batiment"] ;
					$dep = $item["dependances"] ;
				
					$general0 = $item["general"] ;
					$general = $general0 ;
					foreach ( $dep as $i => $depItem )
						{
						if ( empty($depItem))
							$value = 0 ;
						else
							{
							$value = $this->objects[$depItem]["general"] ;
							}	
						$general += $value ;
						}
					$this->objects[$key]["cgeneral"] = $general ;
					}
				}
			}
		}	
		
	public function showPrices ()
		{
		$this->unselect () ;
		$this->selectDefinedKey("or","prix") ;
	      $this->sortNumeric ("lot") ;
		$this->display("batiment","type","prix","prixm2","owner","floor","situation","general","cgeneral","ownerData:lastname") ;
		}	
		
	public function showOwners ($batiment)
		{
		$this->unselect () ;
		$this->selectByKey("or","batiment",$batiment) ;
		
		$array = array_filter ( $this->filteredObjects,
				function ($item)
					{
					$type = $item ["type"] ;
					if ( $type === "T1" or $type === "T2" or $type === "T3" or $type === "T4")
						return 1 ;
					else
						return 0 ;	
					}
			) ;
		$this->filteredObjects = $array ;	
		
	      $this->sortNumeric ("lot") ;
		$this->displayData("batiment","type","prix","prixm2","owner","floor","situation","general","cgeneral","ownerData:lastname") ;
		}	
}	