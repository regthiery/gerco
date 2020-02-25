<?php
namespace Gerco\Data ;

use Gerco\Data\DataObjects;

class Suppliers extends DataObjects
{
	
	public function __construct ()
		{
            parent::__construct();
            $this->setPrimaryKey("index") ;
		}
				
	public function displaySuppliers ()
		{
		$this->selectAll () ;
		$this->sortNumeric("index") ;
		$this->logger->displayData ("shortName>34","name>35") ;
		}
		
}