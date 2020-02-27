#!/usr/local/bin/php

<?php

/**
 * Gestion d'une copropriété immobilière
 *
 * PHP version 7
 *
 * @category Main_Entry_Point
 * @package  Gerco
 * @author   R. Thiéry <regthiery@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://localhost
 */

use Gerco\Data\Condominium;

require_once "Data/DataObject.php";
require_once "Data/DataObjects.php";

require_once "Data/Lots.php";
require_once "Data/Residents.php";
require_once "Data/Owners.php";
require_once "Data/Suppliers.php";
require_once "Data/Invoices.php";
require_once "Data/AccountingPlan.php";
require_once "Data/Imputations.php";
require_once "Data/Condominium.php";
require_once "Data/AccountingExercises.php";
require_once "Data/Resolutions.php";
require_once "Data/GeneralMeetings.php";
require_once "Logger/Logger.php";
require_once "Logger/DataLogger.php";


setlocale(LC_ALL, 'fr_FR.UTF8', 'fr_FR', 'fr', 'fr', 'fra', 'fr_FR@euro');

$condominium = new Condominium();
$condominium->readFile("../00-data/06-condominium.txt");
$condominium->build();

if (isset($argc)) {
    if ($argc >= 2) {
        $action = $argv[1];
        array_splice($argv, 0, 2);
        try {
            $condominium->handleRequest($action, $argv);
        } catch (Exception $e) {
        }
    }


    echo "\n";
}
