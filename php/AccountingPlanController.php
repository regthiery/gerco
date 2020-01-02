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
}