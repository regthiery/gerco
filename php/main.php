#!/usr/local/bin/php

<?php

include_once "HashController.php" ;
include_once "LotsController.php" ;
include_once "ResidentsController.php" ;
include_once "OwnersController.php" ;
include_once "SuppliersController.php" ;
include_once "InvoicesController.php" ;
include_once "AccountingPlanController.php" ;
include_once "ImputationsController.php" ;
include_once "CondominiumController.php" ;
include_once "AccountingExercisesController.php" ;
include_once "GeneralMeetingController.php" ;

setlocale(LC_ALL, 'fr_FR.UTF8', 'fr_FR','fr','fr','fra','fr_FR@euro');

$lotsController = new LotsController ;
$lotsController -> readFile ("../00-data/00-lots.txt") ;

$residentsController = new ResidentsController ;
$residentsController -> readFile ("../00-data/03-residents.txt") ;

$ownersController = new OwnersController ;
$ownersController -> readFile ("../00-data/02-owners.txt") ;

$suppliersController = new SuppliersController ;
$suppliersController -> readFile ("../00-data/08-suppliers.txt") ;

$invoicesController = new InvoicesController ;
$invoicesController -> readFile ("../00-data/01-invoices.txt") ;

$accountingPlanController = new AccountingPlanController ;
$accountingPlanController -> readFile ("../00-data/04-accountingPlan.txt") ;

$imputationsController = new ImputationsController ;
$imputationsController -> readFile ("../00-data/05-imputations.txt") ;

$condominiumController = new CondominiumController ;
$condominiumController -> readFile ("../00-data/06-condominium.txt") ;

$accountingExercisesController = new AccountingExercisesController ;
$accountingExercisesController -> readFile ("../00-data/07-accountingExercises.txt") ;

$generalMeetingController = new GeneralMeetingController ;
$generalMeetingController -> readFile ("../00-data/09-generalMeeting.txt") ;


$lotsController -> setImputationsController ($imputationsController) ;

$lotsController -> calculateMilliemes () ;
$lotsController      -> joinWithData ($ownersController, "owner", "ownerData" ) ;
$residentsController -> joinWithData ($lotsController  , "lot"  , "lotData"   ) ;
$ownersController    -> joinWithData ($lotsController  , "owner", "lotData"   ) ;
$ownersController    -> joinWithData ($residentsController, "owner", "residentData") ;

$accountingPlanController -> createOwnersAccount ($ownersController) ;
$accountingPlanController -> sortAccounts () ;

$imputationsController -> setInvoicesController ($invoicesController) ;
$imputationsController -> setAccountingPlanController ($accountingPlanController) ;
$imputationsController -> createOwnersKeys ($ownersController) ;

$accountingExercisesController -> setAccountingPlanController ($accountingPlanController) ;
$accountingExercisesController -> setImputationsController ($imputationsController) ;

$generalMeetingController -> setOwnersController ($ownersController) ;
$generalMeetingController -> setLotsController ($lotsController) ;
$generalMeetingController -> setImputationsController ($imputationsController) ;

if (isset($argc))
	{
	if ( $argc >= 2 )	
		{
		if ( $argv[1] === "copro" )
			{
			$condominiumController -> displayData ("name", "syndicName", "creationDate") ;
			}
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
		elseif ( $argv[1] === "invoices" )	
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
			
		elseif ( $argv[1] === "accountingPlan")	
			{
			$accountingPlanController -> display () ;
			}
		elseif ( $argv[1] === "checkInvoices")	
			{
			$invoicesController -> checkWithAccountingPlan ($accountingPlanController) ;
			}
		elseif ( $argv[1] === "extract")	
			{
			$startDate = $argv[2] ;
			$endDate   = $argv[3] ;

			$invoicesController -> selectAll () ;
			$invoicesController -> selectBetweenDates ("and","date",$startDate, $endDate) ;
			$invoicesController -> calculateImputations () ;
			$invoicesController -> checkWithAccountingPlan ($accountingPlanController) ;
			$invoicesController -> displayInvoicesList () ;
			}

		elseif ( $argv[1] === "journal")	
			{
			$year = $argv[2] ;
			if ( $year == 0 )
				{
				$startDate = "2018-06-01" ;
				$endDate = date("Y-m-d") ;
				}
			elseif ( $year == 1 )
				{
				$startDate = "2018-06-01" ;
				$endDate   = "2019-06-30" ;
				}
			elseif ( $year == 2 )	
				{
				$startDate = "2019-06-30" ;
				$endDate   = "2020-06-30" ;
				}
			$invoicesController -> selectAll () ;
			$invoicesController -> selectBetweenDates ("and","date",$startDate, $endDate) ;
			$invoicesController -> calculateImputations () ;
			$invoicesController -> checkWithAccountingPlan ($accountingPlanController) ;
			$invoicesController -> displayInvoicesList () ;
			}
		elseif ( $argv[1] === "accountStatement")	
			{
			$year = $argv[2] ;
			if ( $year == 0 )
				{
				$startDate = "2018-06-01" ;
				$endDate = date("Y-m-d") ;
				}
			elseif ( $year == 1 )
				{
				$startDate = "2018-06-01" ;
				$endDate   = "2019-06-30" ;
				}
			elseif ( $year == 2 )	
				{
				$startDate = "2019-06-30" ;
				$endDate   = "2020-06-30" ;
				}

			$invoicesController -> selectAll () ;
			$invoicesController -> selectBetweenDates ("and","date",$startDate, $endDate) ;
			$invoicesController -> calculateImputations () ;
			$invoicesController -> checkWithAccountingPlan ($accountingPlanController) ;
			//$invoicesController -> displayInvoicesList () ;

			$imputationsController -> makeAccountStatement () ;
			$imputationsController -> displayAccountStatement () ;
			}
		elseif ( $argv[1] === "exercise")
			{
			$year = $argv[2] ;
			//print_r ($accountingExercisesController) ;
			$accountingExercisesController -> calculateImputations ($year) ;
			$accountingExercisesController -> displayPrevisionalBudget ($year) ;
			}
		elseif ( $argv[1] === "suppliers")	
			{
			$suppliersController -> displaySuppliers () ;
			}
		elseif ( $argv[1] === "meeting")	
			{
			$index = $argv[2] ;
			$generalMeetingController -> setMeetingIndex ($index) ;
			$generalMeetingController -> checkAttendance () ;
			$generalMeetingController -> displayAttendance () ;
			//$generalMeetingController -> displayResolutions () ;
			//$generalMeetingController -> calculateVotingResults () ;
			//$generalMeetingController -> displayVotingResults () ;
			}
		}
		
		
		
	echo "\n" ;
	}
