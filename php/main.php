#!/usr/local/bin/php

<?php

include_once "GlobalVariables.php" ;
include_once "HashController.php" ;
include_once "LotsController.php" ;
include_once "ResidentsController.php" ;
include_once "OwnersController.php" ;
include_once "FacturesController.php" ;

$lotsController = new LotsController ;
$lotsController -> readFile ("../00-data/00-lots.txt") ;

$residentsController = new ResidentsController ;
$residentsController -> readFile ("../00-data/03-residents.txt") ;

$ownersController = new OwnersController ;
$ownersController -> readFile ("../00-data/02-owners.txt") ;

$facturesController = new FacturesManager ;
$facturesController -> readFile ("../00-data/01-factures.txt") ;


$lotsController      -> joinWithData ($ownersController, "owner", "ownerData" ) ;
$residentsController -> joinWithData ($lotsController  , "lot"  , "lotData"   ) ;
$ownersController    -> joinWithData ($lotsController  , "owner", "lotData"   ) ;

$lotsController -> calculateMilliemes () ;

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
			$lotsController -> showOwners("A") ;
			$lotsController -> showOwners("B") ;
			$lotsController -> showOwners("C") ;
			$lotsController -> showOwners("D") ;
			$lotsController -> showOwners("E") ;
			}
		elseif ( $argv[1] === "owners1")
			{
			$ownersController -> showOwners() ;
			}	
		elseif ( $argv[1] === "owners2")
			{
			$ownersController -> showSortedBySyndicCode() ;
			}	
		elseif ( $argv[1] === "prices")
			{
			$lotsController -> showPrices () ;
			}
		elseif ( $argv[1] === "residents")
			{
			$residentsController -> show ("A") ;
			$residentsController -> show ("B") ;
			$residentsController -> show ("C") ;
			$residentsController -> show ("D") ;
			$residentsController -> show ("E") ;
			}
		elseif ( $argv[1] === "milliemes")
			{
			$lotsController -> checkGeneralMilliemes () ;
			$lotsController -> checkMilliemes () ;
			}
		elseif ( $argv[1] === "handicap")
			{
			$lotsController -> showGarageHandicap () ;
			}
		elseif ( $argv[1] === "parkings")
			{
			$lotsController -> showParkings () ;
			}
		elseif ( $argv[1] === "factures" )	
			{
			$facturesController -> showFactures () ;
			}
		}
		
	echo "\n" ;
	}
