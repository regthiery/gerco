<?php

namespace Gerco\Data ;

include_once "Logger/DataLogger.php" ;
use Gerco\Logger\DataLogger;

	class DataObjects
{
	public string $filename ;
	public array $objects ;
	public array $filteredObjects ;
	public array $selectedObject ;
	public int $objectsCount ;
	public int $filteredCount ;
	public string $primaryKey ;
	public array $sums ;
	public array $objectsKeys ;
	public DataLogger $logger ;
	
	public function __construct ()
		{
		$this->logger = new DataLogger($this) ;
		}
		
	public function setPrimaryKey ($primaryKey)
		{
		$this->primaryKey = $primaryKey ;
		}	
	
	public function getObjects ()
		{
		return $this->objects ;
		}	
	public function getFiltered ()
		{
		return $this->filteredObjects ;
		}	
		
	public function getObjectWithKey ($key)	
		{
		if (array_key_exists($key,$this->objects))
			return $this->objects[$key] ;
		else
			{
			$className = get_class($this) ;
			echo "Key $key does not exist in $className:objects.\n" ;
			return NULL ;
			}	
		}

	public function selectObject($index) {
	    $this->selectedObject = $this->objects[$index] ;
    }

    public function getSelectedObject() : array {
	    return $this->selectedObject ;
    }

	public function getFilteredWithKey ($key)	
		{
		if (array_key_exists($key,$this->filteredObjects))
			return $this->filteredObjects[$key] ;
		else
			{
			$className = get_class($this) ;
			echo "Key $key does not exist in $className:filteredObjects.\n" ;
			return NULL ;
			}	
		}
		
	public function getObjectWithKeyValue ($key0,$value0)	
		{
		foreach ($this->objects as  $key => $item)
			{
			if ( array_key_exists($key0, $item))
				{
				$value = $item[$key0] ;
				if ( ! strcmp ($value,$value0))
					{ return $item ; }
				}
			}
		return NULL ;	
		}


		public function convertDateToEng ($date) : string
        {
            @list ($day,$month,$year) = explode ('/', $date) ;
            $date0 =  date ('Y-m-d', mktime(0,0,0,$month,$day,$year)) ;
            return $date0 ;
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
		$primaryValue = NULL ;
		
		$this->setFileName ($filename) ;
            if ( ! file_exists($filename)) {
                printf ("Error: cannot open %s file \n.", $filename) ;
                return $this ;
            }

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
						$value=preg_replace ('/\s\s+/', ' ', $value) ;
						$valuesArray = explode (' ', $value ) ;

						if ( count ($valuesArray) > 0 && !empty($valuesArray[0]))
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
					    if (isset($primaryValue) && isset($object)) {
                            $this->objects [$primaryValue] = $object ;
                        }
					    else {
					        print("Erreur de lecture du fichier $filename: aucune valeur définie pour la clé $primaryLabel\n") ;
                        }

					$new = 0 ;
					    unset($primaryValue) ;
					}
				}
			}

		$this->filteredObjects = $this->objects ;
		$this->objectsCount = count ($this->objects) ;

		$this->objectsKeys = array_keys($this->objects) ;

		$this->logger->printf("Read file %s\n",$filename) ;
		$this->logger->displayCount() ;
		return $this ;
		}
		
	public function joinWithData (DataObjects $dataObject, $primaryKey, $objectKey)
		{
		$objectsData = $dataObject -> getObjects () ;

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
	
	public function getCount () : int
		{
		return $this -> objectsCount ;
		}
		
	public function getFilteredCount ()	: int
		{
		return $this->filteredCount ;
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

	public function getKeyValue ($object,$key)
    {
        $keys = preg_split("/:/",$key) ;
        $n = count($keys) ;
        if ( $n == 1 )
        {
            if ( ! (array_key_exists($key,$object)) )
            {
                return NULL ;
            }
            $value = $object[$key] ;
        }
        elseif ( $n == 2 )
        {
            $key0 = $keys [0] ;
            $key1 = $keys [1] ;
            if (! array_key_exists($key0,$object) )
            {
                return NULL ;
            }
            if (! array_key_exists($key1,$object[$key0]) )
            {
                return NULL ;
            }
            $value = $object[$key0][$key1] ;
        }
        elseif ( $n == 3 )
        {
            $key0 = $keys [0] ;
            $key1 = $keys [1] ;
            $key2 = $keys [2] ;
            if (! array_key_exists($key0,$object) )
            {
                return NULL ;
            }
            if (! array_key_exists($key1,$object[$key0]) )
            {
                return NULL ;
            }
            if (! array_key_exists($key2,$object[$key0][$key1]) )
            {
                return NULL ;
            }
            $value = $object[$key0][$key1][$key2] ;
        }
        else
        {
            $value = '' ;
        }
     return $value ;
    }
		
	public function selectByKey ($operator, $key0, $value0)
		{
		$array = array_filter (
			(!strcmp($operator,"and")) ? $this->filteredObjects : $this->objects ,
			function ($item) use($key0, $value0)
				{
				$value = $this->getKeyValue ($item, $key0) ;

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
		
	public function selectBetweenDates ($operator,$key0,$startDate, $endDate)	
		{
		    $startDateEng = $this->convertDateToEng($startDate) ;
		    $endDateEng = $this->convertDateToEng($endDate) ;
		$array = array_filter (
			(!strcmp($operator,"and")) ? $this->filteredObjects : $this->objects ,
			function ($item) use($key0, $startDateEng, $endDateEng)
				{
				$value=$this->getKeyValue($item,$key0."Eng") ;

				$res = strcmp ($startDateEng, $value) ;
				if ( $res > 0 )
					{ return false ; }
				$res = strcmp ($endDateEng, $value) ;
				if ( $res < 0 )
					{ return false ; }
				
				return (true) ;
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
				$value = $this->getKeyValue($item,$key0) ;

                    if ( $value == NULL )
                        return FALSE ;

                    $res =  preg_match($pattern,$value) ;
                    //return ( preg_match($pattern,$value) ) ;
                    return $res ;
				} 
			) ;
		if (!strcmp ($operator,"or"))	
			{
			$this->filteredObjects = array_merge ($this->filteredObjects, $array) ;
			}
		elseif ( $operator === 'andNot' )
        {
            $this->filteredObjects =  array_diff_assoc($this->filteredObjects, $array) ;
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

        public function sortLiteral ($key0)
        {
            uasort ($this->filteredObjects,
                function ($a,$b) use ($key0)
                {
                    if ( ! array_key_exists($key0,$a) )
                    { return -1 ; }
                    if ( ! array_key_exists($key0,$b) )
                    { return 1 ; }
                    return (!strcmp($a[$key0], $b[$key0])) ;
                }
            ) ;
            return $this ;
        }

        public function sortNumeric ($key0)
		{
		uasort ($this->filteredObjects, 
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
		uasort ($this->filteredObjects,
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
				$value = $this->getKeyValue($object,$key0) ;
				if ( $value != NULL )
                    $this->sums[$key0] += $value ;
				}
			}
		return $this ;	
		}

	public function getSum($key0)
		{
		return $this->sums[$key0] ;
		}	
}	