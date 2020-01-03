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
				if ( array_key_exists ($imputationKey, $invoice) )
					{
					$imputationValue = $invoice [$imputationKey] ;
					$this->objects[$imputationKey]["invoices"][$invoiceKey] =  $imputationValue  ;
					print ("\t$invoiceKey $imputationValue \n") ;
					}

				}
			}
		print_r ($this->objects) ;
		}	
	
}