<?php
namespace Gerco\Data ;

use Gerco\Data\DataObjects;

    class AccountingPlan extends DataObjects
{
	public function __construct ()
		{
            parent::__construct() ;
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
	
	public function createOwnersAccount ($owners)
		{
		foreach ($owners->getObjects() as $ownerKey => $ownerData)
			{
//			print_r ($ownerData) ;
			$syndicCode = $ownerData["syndicCode"] ;
			$lastName = $ownerData ["lastname"] ;
			$firstName = $ownerData ["firstname"] ;
			$accountCode = "450$syndicCode" ;
			$newAccount = array (
				"code" => $accountCode,
				"label" => "Compte propriétaire (lot $ownerKey) = $lastName $firstName" ,
				"shortName" => "$lastName $firstName"
				) ;
			$this->objects[$accountCode]	= $newAccount ;
			}
		}
		
	public function sortAccounts ()
		{
		ksort ($this->objects, SORT_STRING) ;
		}	
}