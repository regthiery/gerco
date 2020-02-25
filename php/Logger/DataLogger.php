<?php

namespace Gerco\Logger ;

	use Gerco\Data\DataObjects;

    class DataLogger
{
	public DataObjects $dataObjects ;

	public function __construct (DataObjects $dataObjects)
		{
		    $this->dataObjects = $dataObjects ;
		}
		
		
	public function displayData (...$keys)
		{
		$formats = array () ;
		$keys0[] = array () ;
		$k = 0 ;
        printf ("\033[1m\t%-8s ", "    #") ;
		foreach ($keys as $key0)
			{
                if (preg_match('/(.*)>(.*)/',$key0,$matches) ) {
                    $keys0[$k] = $matches[1];
                    $formats[$k] = "  : %-" . $matches[2] . "s\t";
                }
                else {
                    $keys0[$k] = $key0 ;
                    $formats[$k] = "  : %-20s\t";
                }
                printf($formats[$k], $keys0[$k]);
                ++$k ;
			}
		printf ("\033[0m\n") ;

        $i = 1 ;
		foreach ( $this->dataObjects->filteredObjects as $key => $item )
			{
			printf ("\t%4d)    ", $i ) ;

			$k = 0 ;
			foreach ($keys0 as $key0)
				{
				$value = $this->dataObjects->getKeyValue($item,$key0) ;

				if ( is_array($value))
					{
					$values = implode ('    ', $value) ;
					printf ($formats[$k], $values) ;
					}
				else
					{
					printf ($formats[$k], $value) ;
					}
				++ $k ;
				}
			printf("\n") ;
			
			++ $i;
			}
		printf("\n") ;
		return $this ;	
		}

        public function displaySums (...$keys)
        {
            foreach ($keys as $key)
            {
                printf ("Sum %-25s : %8.2f \n", $key, $this->dataObjects->sums[$key]) ;
            }
            printf("\n") ;
            return $this ;
        }

        public function displayCount ()
        {
            printf ("%5d %s\n\n", $this->dataObjects->objectsCount, $this->dataObjects->primaryKey) ;
            return $this ;
        }

        public function displayFilteredCount ()
        {
            printf ("%5d selected items\n\n", $this->dataObjects->filteredCount) ;
            return $this ;
        }

        public function print($text) {
            print ($text) ;
        }

        public function printf($format, ...$params) {
            printf ($format,...$params) ;
        }

    }