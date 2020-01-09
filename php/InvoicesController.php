<?php

#=============================================================================
	class InvoicesController extends HashController
#=============================================================================
{
	protected $imputationKeys ;
	protected $accountingPlanController ;
	
	
	public function __construct ()
		{
		echo "InvoicesController object created\n" ;
		$this->setPrimaryKey("index") ;
		}
		
	public function calculateImputations ()
		{
		foreach ( $this->filteredObjects as $key => $invoice )
			{
			$sum = 0 ;
			if ( array_key_exists ("imputations", $invoice) )
				{
				$imputations = $invoice["imputations"] ;
				$this->filteredObjects[$key]["calculatedImputations"] = array () ;
				foreach ( $imputations as $key0 => $imputation )
					{
					$value = $invoice["value"] ;

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
							// $this->objects[$key][$imputationKey] = $value * $percent / 100.0 ;
							$this->filteredObjects[$key]["calculatedImputations"][$imputationKey] = $value * $percent / 100.0 ;
							}
						else
							{
							// $this->objects[$key][$imputationKey] = $imputationValue ;
							$this->filteredObjects[$key]["calculatedImputations"][$imputationKey] = $imputationValue ;
							}	
						// $sum += $this->objects[$key][$imputationKey] ;
						$sum += $this->filteredObjects[$key]["calculatedImputations"][$imputationKey] ;
						}
					}
					
				$res = abs ($invoice["value"] - $sum) ;
				if ( $res >1e-2 )	
					{
					$index = $this->filteredObjects[$key][$this->primaryKey] ;
					print ("Erreur sur les imputations de la facture $index\n") ;
					print ("Son montant de $value euros n'est pas égale à la somme des imputations $sum\n") ;
					print_r ($imputations) ;
					}
				}
			else
				{
				print ("Error: no imputation defined for invoice $key !\n") ;
				}	
			}
		}
		
	public function calculateImputationKeysList ()		
		{
		$this->imputationKeys = array () ;
		foreach ( $this->objects as $key => $invoice )
			{
			if ( array_key_exists ("imputations", $invoice) )
				{
				$imputations = $invoice["imputations"] ;
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

	public function checkWithAccountingPlan (AccountingPlanController &$accountingPlanController)
		{
		$this->accountingPlanController = $accountingPlanController ;
		//$this->selectAll () ;

		$this->sortByDate('date') ;
		
		
		$invoicesCount = $this->filteredCount ;
		$invoiceIndex = $invoicesCount ;
		foreach ($this->filteredObjects as $key => $invoice)
			{
			$invoiceKey = $invoice["index"] ;
			$invoiceShortName = $invoice["object"] ;
			$date = $invoice["date"] ;
			$accountIndex = $this->accountingPlanController -> getAccountIndex ($invoiceShortName) ;
			$from = $invoice["from"] ;

			$accountCode  = $this->accountingPlanController -> getAccountCode  ($accountIndex) ;
			$accountLabel = $this->accountingPlanController -> getAccountLabel ($accountIndex) ;
			
			
			$this->filteredObjects[$invoiceKey]["accountCode"]  = $accountCode ;
			$this->filteredObjects[$invoiceKey]["accountLabel"] = $accountLabel ;
			$this->filteredObjects[$invoiceKey]["invoiceIndex"] = $invoiceIndex ;
			
			$invoiceIndex -- ;
			}
		}
		
	public function getInvoiceKeyWithIndex ($index)	
		{
		foreach ($this->filteredObjects as $key => $data)
			{
			if ( $data['invoiceIndex'] == $index )
				{
				return $data['index'] ;
				}
			}
		return null ;	
		}
		
	public function displayInvoicesList ()
		{
		$invoicesCount = $this->filteredCount ;
		

		for ( $i=0 ; $i < $invoicesCount ; $i ++ )
			{
			$invoiceIndex = $i + 1 ;
			$invoiceKey = $this->getInvoiceKeyWithIndex ($invoiceIndex) ;

			$invoice = $this->filteredObjects[$invoiceKey] ;
			$date = $invoice["date"] ;
			$accountCode = $invoice['accountCode'] ;
			$from = $invoice['from'] ;
			$invoiceShortName = $invoice["object"] ;
			printf ("%12s\t%6d\t%10s\t%-32s \t %-50s\n", $date, $invoiceIndex, $accountCode, $from, $invoiceShortName) ;
			}
		}	
}		
		
