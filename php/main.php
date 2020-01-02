#!/usr/local/bin/php

<?php

include_once "GlobalVariables.php" ;
include_once "HashController.php" ;
include_once "LotsController.php" ;
include_once "ResidentsController.php" ;
include_once "OwnersController.php" ;
include_once "InvoicesController.php" ;
include_once "AccountingPlanController.php" ;

setlocale(LC_ALL, 'fr_FR.UTF8', 'fr_FR','fr','fr','fra','fr_FR@euro');

$lotsController = new LotsController ;
$lotsController -> readFile ("../00-data/00-lots.txt") ;

$residentsController = new ResidentsController ;
$residentsController -> readFile ("../00-data/03-residents.txt") ;

$ownersController = new OwnersController ;
$ownersController -> readFile ("../00-data/02-owners.txt") ;

$invoicesController = new InvoicesController ;
$invoicesController -> readFile ("../00-data/01-factures.txt") ;

$accountingPlanController = new AccountingPlanController ;
$accountingPlanController -> readFile ("../00-data/04-accountingPlan.txt") ;


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
			$invoicesController -> showFactures () ;
			}
		elseif ( $argv[1] === "electricité" )	
			{
			$invoicesController -> calculateImputations () ;

			$invoicesController -> showElectriciteBatiment ("A") ;
			$invoicesController -> showElectriciteBatiment ("B") ;
			$invoicesController -> showElectriciteBatiment ("C") ;
			$invoicesController -> showElectriciteBatiment ("D") ;
			$invoicesController -> showElectriciteBatiment ("E") ;

			$invoicesController -> showElectriciteGarage ("1011") ;
			$invoicesController -> showElectriciteGarage ("1314") ;
			}
		elseif ( $argv[1] === "ascenseur" )	
			{
			$invoicesController -> calculateImputations () ;

			$invoicesController -> showAscenseurBatiment ("A") ;
			$invoicesController -> showAscenseurBatiment ("B") ;
			$invoicesController -> showAscenseurBatiment ("C") ;
			$invoicesController -> showAscenseurBatiment ("D") ;
			}
		elseif ( $argv[1] === "entretien" )	
			{
			$invoicesController -> calculateImputations () ;
			
			$invoicesController -> showEntretienForBatiment ("A") ;
			$invoicesController -> showEntretienForBatiment ("B") ;
			$invoicesController -> showEntretienForBatiment ("C") ;
			$invoicesController -> showEntretienForBatiment ("D") ;
			$invoicesController -> showEntretienForBatiment ("E") ;
			$invoicesController -> showEntretienForBatiment ("Z") ;
			}
			
		elseif ( $argv[1] === "imputations" )	
			{
			$imputationKeys = $invoicesController -> calculateImputationKeysList () ;
			print_r ($imputationKeys) ;
			}
			
		elseif ( $argv[1] === "planComptable")	
			{
			$accountingPlanController -> display () ;
			}
		elseif ( $argv[1] === "checkInvoices")	
			{
			$invoicesController -> checkWithAccountingPlan ($accountingPlanController) ;
			}
		}
		
	echo "\n" ;
	}
