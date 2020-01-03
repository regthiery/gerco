<?php

#=============================================================================
	class ImputationsController extends HashController
#=============================================================================
{
	protected $invoicesController ;
	
	public function __construct ()
		{
		echo "ImputationsController created\n" ;
		$this->setPrimaryKey ("code") ;
		}
		
	public function setInvoicesController ($invoicesController)	
		{
		$this->invoicesController = $invoicesController ;
		}
		
	public function makeAccountStatement ()
		{
		foreach ($this->objects as $imputationKey => $imputation)
			{
			printf ("%10s \t %-30s\n", $imputation["code"], $imputation["label"]) ;
			
			$this->objects[$imputationKey]["invoices"] = array () ;


			
			foreach ($this->invoicesController->getObjects() as $invoiceKey => $invoice )
				{
				if ( ! array_key_exists ("calculatedImputations", $invoice))
					{
					print ("Error : the imputations have not been calculated for invoice $invoiceKey\n") ;
					return ;
					}
				$imputations = $invoice["calculatedImputations"] ;
				if ( array_key_exists ($imputationKey, $imputations) )
					{
					$imputationValue = $imputations [$imputationKey] ;
					$this->objects[$imputationKey]["invoices"][$invoiceKey] =  $imputationValue  ;
					$accountCode = $invoice["accountCode"] ;
					printf ("\t%-10s \t % 10.2f\t %8d\n", $invoiceKey, $imputationValue, $accountCode) ;
					}

				}
				
			}
		//print_r ($this->objects) ;
		}	
	
}