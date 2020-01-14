<?php

#=============================================================================
	class AccountingExercisesController extends HashController
#=============================================================================
{

	protected $accountingPlanController ;
	protected $imputationsController ;

	public function __construct ()
		{
		$this->setPrimaryKey("exercise") ;
		}
		
	public function setAccountingPlanController (&$accountingPlanController)	
		{
		$this->accountingPlanController = $accountingPlanController ;
		}

	public function setImputationsController (&$imputationsController)	
		{
		$this->imputationsController = $imputationsController ;
		}
		

	public function calculateImputations ($e)		
		{
		$exercise = $this->getObjectWithKey ($e) ;
		print_r ($exercise) ;
		
		$accounts = array () ;
		foreach ($exercise as $key=>$item)
			{
			print("$key\t\t$item\n") ;
			if ( preg_match("/provision(\d+)/", $key, $matches) )
				{
				$accountKey = $matches[1] ;
				if ( array_key_exists($accountKey,$accounts))
					{
					print ("AccountingExercises:calculateImputations : this account $accountKey has already been defined.\n") ;
					return ;
					}
				$label = $this->accountingPlanController->getObjectWithKey($accountKey)["label"] ;
				
				$item = preg_replace('/\s\s+/', ' ' , $item) ;
				$imputationsArray = explode( ' ', $item ) ;
				
				$imputations = array () ;
				foreach ($imputationsArray as $key0 => $data)
					{
					if (preg_match('/(.*)=>(.*)/', $data, $s))
						{
						$imputationName = $s[1] ;
						$imputationValue = $s[2] ;
						$imputations[$imputationName] =  $imputationValue ;
						}
					}
				
				$accounts[$accountKey] = array (
					// "code" => $accountKey,
					"label" => $label,
					"imputations" => $imputations ) ;
				}
			}

		$imputations = array () ;			
		foreach ($accounts as $code => $data )	
			{
			foreach ($data["imputations"] as $imputationKey => $imputationValue)
				{
				$imputations[$imputationKey][$code] = array ("label" => $data["label"],
					"value" => $imputationValue );
				}
			}
		
//		ksort ($accounts) ;

		$this->objects[$e]["accounts"] = $accounts ;
		$this->objects[$e]["imputations"] = $imputations ;
		}


	public function displayPrevisionalBudget ($e)		
		{
		$exercise = $this->getObjectWithKey ($e) ;
		print_r ($exercise) ;
		
		$imputations = $exercise["imputations"] ;
		foreach ( $imputations as $imputationKey => $imputationData)
			{
			printf ("\033[1;38,5m%-60s %-60s\033[0m\n", $imputationKey, $this->imputationsController->getObjectWithKey($imputationKey)["label"]) ;
			foreach ($imputationData as $accountCode => $accountData)
				{
				printf ("%10d %10.2f\t\t %s\n", $accountCode, $accountData["value"], $accountData["label"] ) ;
				}
			}
		}
}		