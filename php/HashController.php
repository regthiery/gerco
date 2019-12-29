<?php

#===============================================================================
	class HashController
#===============================================================================
{
	protected $filename ;
	protected $objects ;
	protected $filteredObjects ;
	protected $objectsCount ;
	protected $filteredCount ;
	protected $primaryKey ;
	protected $sums ;
	
	public function __construct ()
		{
		echo "HashController created\n" ;
		}
	public function setPrimaryKey ($primaryKey)
		{
		$this->primaryKey = $primaryKey ;
		}	
	
	public function getObjets ()
		{
		return $this->objects ;
		}	
	public function getFiltered ()
		{
		return $this->filteredObjects ;
		}	
	
	public function setFileName ($filename)	
		{
		$this->filename = $filename ;
		}
	
	public function readFile ($filename)
		{
		$new = 0 ;
		$this->objects = array () ;
		
		$primaryLabel = ucfirst($this->primaryKey) ;
		
		print ("HashController readFile $filename $primaryLabel \n") ;

		$this->setFileName ($filename) ;
		$txt = file($this->filename) ;
				
		foreach ($txt as $line)
			{
			if (! preg_match ('/^#/', $line ) )
				{
				if (preg_match('/\S+/',$line))
					{
					$array = preg_split ("/:/",$line) ;
					$key = $array[0] ;
					$value = $array[1] ;
					$key = trim ($key) ;
					$value = trim ($value) ;
			
					if ( $key === $primaryLabel )
						{
						$new = 1 ;
						$primaryValue = $value ;
						$object = array
							( $this->primaryKey => $value ) ;
						}
					elseif ( preg_match ("/(.*)Array/", $line, $matches))
						{
						$key = $matches[1] ;
						$key = lcfirst ($key) ;
						$valuesArray = explode (' ', $value ) ;
						$object[$key] = $valuesArray ;
						}
					elseif ( preg_match ("/(.*)Date/", $line, $matches))
						{
						$key = $matches[1]."Date" ;
						$key = lcfirst ($key) ;
						$object[$key] = $value ;
						@list ($day,$month,$year) = explode ('/', $value) ;
						$date0 =  @date ('Y-m-d', mktime(0,0,0,$month,$day,$year)) ;
						$object["$key"."Eng"] = $date0 ;
						}
					else
						{
						$key = lcfirst ($key) ;
						$object ["$key"] = $value ;
						}	
					}
				elseif ( $new == 1 )	
					{
					$this->objects [$primaryValue] = $object ;
					$new = 0 ;
					}
				}
			}

		$this->filteredObjects = $this->objects ;
		$this->objectsCount = count ($this->objects) ;

		return $this ;
		}
		
	public function display (...$keys)
		{
		$i = 1 ;
		$primaryLabel = ucfirst ($this->primaryKey) ;
		printf ("\t%4s %8s %18s ", "#", "-", " ") ;
		foreach ($keys as $key0)
			{
			printf ( "%18s", $key0) ;
			}
		printf ("\n") ;
		foreach ( $this->filteredObjects as $key => $value )
			{
			$objectPrimaryValue = $this->filteredObjects[$key][$this->primaryKey] ;
			printf ("\t%4d) %8s %8s ", $i, $primaryLabel, $objectPrimaryValue ) ;
			
			foreach ($keys as $key0)
				{
				$keys1 = preg_split("/:/",$key0) ;
				
				$n = count($keys1) ;
				if ( $n == 1 )
					{
					$key00 = $keys1 [0] ;
					$value = (array_key_exists($key0,$this->filteredObjects[$key])) ? $this->filteredObjects[$key][$key0] : "" ;
					}
				elseif ( $n == 2 )	
					{
					$key00 = $keys1 [0] ;
					$key01 = $keys1 [1] ;
					if (! array_key_exists($key00, $this->filteredObjects[$key]))
						{
						$value = "" ;
						}
					else
						{
						$value = (array_key_exists($key01,$this->filteredObjects[$key][$key00])) ? $this->filteredObjects[$key][$key00][$key01] : "" ;
						}	
					}

				if ( is_array($value))
					{
					$values = implode (' ', $value) ;
					printf ("  : %s", $values) ;
					}
				else
					{
					printf (" : %16s", $value) ;
					}
				}
			printf("\n") ;
			
			$i ++ ;
			}
		return $this ;	
		}
	
	
	public function joinWithData (HashController &$hashController, $primaryKey, $objectKey)
		{
		$objectsData = $hashController -> getObjets () ;

		foreach ($this->objects as $key => $item )
			{
			if ( array_key_exists($primaryKey,$item))
				{
				$joinKey =   $item[$primaryKey] ;
				if ( ! empty($joinKey))
					{
					if ( array_key_exists($joinKey,$objectsData))
						{
						$this->objects[$key][$objectKey] = $objectsData[$joinKey] ;
						}
					}
				}
			}
		}
	
	public function getCount ()
		{
		return $this -> objectsCount ;
		}
		
	public function getFilteredCount ()	
		{
		return $this->filteredCount ;
		}
		
	public function displayCount ()
		{
		printf ("%5d %s\n", $this->objectsCount, $this->primaryKey) ;
		return $this ;
		}
		
	public function displayFilteredCount ()
		{
		printf ("%5d selected items\n", $this->filteredCount) ;
		return $this ;
		}
			

	public function unselect ()
		{
		$this->filteredObjects = array () ;
		$this->filteredCount = 0 ;
		return $this ;
		}
		
	public function selectAll ()
		{
		$this->filteredObjects = $this->objects ;
		$this->filteredCount = $this->objectsCount ;
		return $this ;
		}			
		
	public function selectByKey ($operator, $key0, $value0)
		{
		$array = array_filter (
			(!strcmp($operator,"and")) ? $this->filteredObjects : $this->objects ,
			function ($item) use($key0, $value0)
				{
				
				
				$keys1 = preg_split("/:/",$key0) ;
				$n = count($keys1) ;
				if ( $n == 1 )
					{
					$key00 = $keys1 [0] ;
					if ( ! (array_key_exists($key0,$item)) )
						{
						return false ;
						}
					$value = $item[$key0] ;
					}
				elseif ( $n == 2 )	
					{
					$key00 = $keys1 [0] ;
					$key01 = $keys1 [1] ;
					if (! array_key_exists($key00,$item) )
						{
						return false ;
						}
					if (! array_key_exists($key01,$item[$key00]) )
						{
						return false ;
						}
					$value = $item[$key00][$key01] ;
					}

				return (!strcmp($value,$value0)) ;
				} 
			) ;
		if (!strcmp ($operator,"or"))	
			{
			$this->filteredObjects = array_merge ($this->filteredObjects, $array) ;
			}
		else
			{
			$this->filteredObjects = $array ;
			}
			
		$this->filteredCount = count($this->filteredObjects)	;
		return $this ;
		}

	public function selectByKeyExt ($operator, $key0, $pattern)
		{
		$array = array_filter (
			(!strcmp($operator,"and")) ? $this->filteredObjects : $this->objects ,
			function ($item) use($key0, $pattern)
				{
				$keys1 = preg_split("/:/",$key0) ;
				$n = count($keys1) ;
				if ( $n == 1 )
					{
					$key00 = $keys1 [0] ;
					if ( ! (array_key_exists($key0,$item)) )
						{
						return false ;
						}
					$value = $item[$key0] ;
					}
				elseif ( $n == 2 )	
					{
					$key00 = $keys1 [0] ;
					$key01 = $keys1 [1] ;
					if (! array_key_exists($key00,$item) )
						{
						return false ;
						}
					if (! array_key_exists($key01,$item[$key00]) )
						{
						return false ;
						}
					$value = $item[$key00][$key01] ;
					}

				return ( preg_match($pattern,$value) ) ;
				} 
			) ;
		if (!strcmp ($operator,"or"))	
			{
			$this->filteredObjects = array_merge ($this->filteredObjects, $array) ;
			}
		else
			{
			$this->filteredObjects = $array ;
			}
			
		$this->filteredCount = count($this->filteredObjects)	;
		return $this ;
		}
		
	public function selectDefinedKey ($operator, $key0)	
		{
		$array = array_filter (
			(!strcmp($operator,"and")) ? $this->filteredObjects : $this->objects ,
			function ($item) use($key0)
				{
				if ( ! array_key_exists ($key0, $item))
					return false ;
				$value = $item[$key0] ;
				$checkNonEmpty =  (empty($value) == false) ;
				return ($checkNonEmpty) ;
				} 
			) ;

		if (!strcmp ($operator,"or"))	
			{
			$this->filteredObjects = array_merge ($this->filteredObjects, $array) ;
			}
		else
			{
			$this->filteredObjects = $array ;
			}
			
		$this->filteredCount = count($this->filteredObjects)	;

		return $this ;
		}
		
	public function sortNumeric ($key0)
		{
		usort ($this->filteredObjects, 
			function ($a,$b) use ($key0)
				{
				if ( ! array_key_exists($key0,$a) )
					{ return -1 ; }
				if ( ! array_key_exists($key0,$b) )
					{ return 1 ; }
				if ($a[$key0] < $b[$key0]) 
					{ return -1 ; }
				if ($a[$key0] == $b[$key0]) 
					{ return 0 ; }
				return 1 ;
				}
			) ;
		return $this ;
		}
		
	public function sortByDate ($key0)	
		{
		print ("Sort by date\n") ;
		$key = $key0."Eng" ;
		usort ($this->filteredObjects,
			function($a,$b) use ($key)
				{
				if ( ! array_key_exists($key,$a) )
					{ return -1 ; }
				if ( ! array_key_exists($key,$b) )
					{ return 1 ; }
				$ta = strtotime($a[$key]) ;
				$tb = strtotime($b[$key]) ;
				if ( $ta < $tb )
					return 1 ;
				else if ( $ta > $tb )	
					return -1 ;
				else
					return 0 ;	
				}
			) ;
		return $this ;	
		}
		
	public function sumKeys (...$keys)
		{
		$this->sums = array () ;
		
		foreach ($keys as $key0)
			{
			$this->sums[$key0] = 0 ;
			foreach ($this->filteredObjects as $key => $object)
				{
				if ( array_key_exists ($key0,$object))
					{
					$this->sums[$key0] += $object[$key0] ;
					}
				}
			}
		return $this ;	
		}
	
	public function displaySums (...$keys)	
		{
		foreach ($keys as $key0)
			{
			printf ("Sum %s : %8f \n", $key0, $this->sums[$key0]) ;
			}
		return $this ;	
		}
}	