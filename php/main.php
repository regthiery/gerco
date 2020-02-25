#!/usr/local/bin/php

<?php

include_once "Data/DataObject.php" ;
include_once "Data/DataObjects.php" ;

include_once "Data/Lots.php" ;
include_once "Data/Residents.php" ;
include_once "Data/Owners.php" ;
include_once "Data/Suppliers.php" ;
include_once "Data/Invoices.php" ;
include_once "Data/AccountingPlan.php" ;
include_once "Data/Imputations.php" ;
include_once "Data/Condominium.php" ;
include_once "Data/AccountingExercises.php" ;
include_once "Data/Resolutions.php" ;
include_once "Data/GeneralMeetings.php" ;

    setlocale(LC_ALL, 'fr_FR.UTF8', 'fr_FR','fr','fr','fra','fr_FR@euro');

$condominium = new \Gerco\Data\Condominium() ;
$condominium -> readFile ("../00-data/06-condominium.txt") ;
$condominium -> build () ;

    if (isset($argc))
    {
        if ( $argc >= 2 )
        {
            $action = $argv[1] ;
            array_splice ($argv,0,2) ;
            $condominium -> handleRequest($action, $argv) ;
        }



        echo "\n" ;
    }

