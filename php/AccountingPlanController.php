<?php

#=============================================================================
	class AccountingPlanController extends HashController
#=============================================================================
{
	public function __construct ()
		{
		echo "AccountingPlanController created\n" ;
		$this -> setPrimaryKey ("code") ;
		}

	public function display ()
		{
		foreach ($this->objects as $key => $item )
			{
			$n = strlen($key) ;
			for ($i=0 ; $i<$n; $i++)
				{ print ("\t") ;}
			$label = $item["label"] ;
			print ("$key : $label\n") ;
			}
		}
	
	public function getAccountIndex ($shortName)
		{
		$array = array_column($this->objects, 'shortname') ;
		$index = array_search ($shortName, array_column($this->objects, 'shortname') ) ;
		return $index ;
		}
		
	public function getAccountCode ($index)	
		{
		$key = $this->objectsKeys [$index] ;
		$account = $this->objects[$key] ;
		return $account["code"] ;
		}
	public function getAccountLabel ($index)	
		{
		$key = $this->objectsKeys [$index] ;
		$account = $this->objects[$key] ;
		return $account["label"] ;
		}
}