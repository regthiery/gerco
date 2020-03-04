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

require 'Vendor/autoload.php' ;

use Gerco\Data\Condominium;


setlocale(LC_ALL, 'fr_FR.UTF8', 'fr_FR', 'fr', 'fr', 'fra', 'fr_FR@euro');

$condominium = new Condominium();
$condominium->readYamlFile("00-data/yaml/06-condominium.yaml");
$condominium->build();

if (isset($argc)) {
    if ($argc >= 2) {
        $action = $argv[1];
        array_splice($argv, 0, 2);
        try {
            $condominium->handleRequest($action, $argv);
        } catch (TypeError $e) {
            echo "\nErreur ".$e->getMessage() ;
            exit('Gerco: program TypeError') ;
        }
        catch (\Error $e) {
            var_dump(($e->getMessage()));
            var_dump($e->getTraceAsString()) ;
            exit('Gerco: program Error') ;
        }
        catch (\Exception $e) {
            var_dump(($e->getMessage()));
            exit('Gerco: program Exception') ;
        }
    }


    echo "\n";
}
