#!/usr/local/bin/php

<?php

include_once "GlobalVariables.php" ;
include_once "HashManager.php" ;
include_once "LotsManager.php" ;
include_once "ResidentsManager.php" ;
include_once "OwnersManager.php" ;

$lotsManager = new LotsManager ;
$lotsManager -> readFile ("../00-data/00-lots.txt") ;

$residentsManager = new ResidentsManager ;
$residentsManager -> readFile ("../00-data/03-residents.txt") ;

$ownersManager = new OwnersManager ;
$ownersManager -> readFile ("../00-data/02-owners.txt") ;

# $lotsManager      -> joinWithOwnersData ($ownersManager) ;

$lotsManager      -> joinWithData ($ownersManager, "owner", "ownerData" ) ;
$residentsManager -> joinWithData ($lotsManager  , "lot"  , "lotData"   ) ;
$ownersManager    -> joinWithData ($lotsManager  , "owner", "lotData"   ) ;

# $residentsManager -> joinWithLotsData ($lotsManager) ;
# $ownersManager    -> joinWithLotsData ($lotsManager) ;

$lotsManager -> calculateMilliemes () ;

if (isset($argc))
	{
	for ( $i = 0 ; $i < $argc ; $i++)
		{
		echo "argument $i $argv[0]" ;
		}
	echo "\n" ;
	
	if ( $argc == 2 )	
		{
		if ( $argv[1] === "owners")
			{
			$lotsManager -> showOwners("A") ;
			$lotsManager -> showOwners("B") ;
			$lotsManager -> showOwners("C") ;
			$lotsManager -> showOwners("D") ;
			$lotsManager -> showOwners("E") ;
			}
		elseif ( $argv[1] === "owners1")
			{
			$ownersManager -> showOwners() ;
			}	
		elseif ( $argv[1] === "owners2")
			{
			$ownersManager -> showSortedBySyndicCode() ;
			}	
		elseif ( $argv[1] === "prices")
			{
			$lotsManager -> showPrices () ;
			}
		elseif ( $argv[1] === "residents")
			{
			$residentsManager -> show ("A") ;
			$residentsManager -> show ("B") ;
			$residentsManager -> show ("C") ;
			$residentsManager -> show ("D") ;
			$residentsManager -> show ("E") ;
			}
		elseif ( $argv[1] === "milliemes")
			{
			$lotsManager -> checkGeneralMilliemes () ;
			$lotsManager -> checkMilliemes () ;
			}
		elseif ( $argv[1] === "handicap")
			{
			$lotsManager -> showGarageHandicap () ;
			}
		elseif ( $argv[1] === "parkings")
			{
			$lotsManager -> showParkings () ;
			}
		}
		
	echo "\n" ;
	}
