<?php
/**
 * Owners.php
 *
 * Classe stockant la liste des copropriétaires (actuels ou anciens) de la copropriété
 *
 * La classe Owners liste les copropriétaires (actuels ou anciens) de la copropriété.
 *
 * PHP version 7
 *
 * @category Gerco
 * @package  Gerco
 * @author   R. Thiéry <regthiery@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @version  GIT:0.1
 * @link     http://localhost
 */


namespace Gerco\Data;

/**
 * Class Owners
 *
 * @package Gerco\Data
 */
class Owners extends DataObjects
{

    /**
     * Owners constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPrimaryKey("owner");
    }

    /**
     * Affiche la liste des copropriétaires dans un bâtiment
     *
     * @param string $batiment
     */
    public function show(string $batiment)
    {
        $this->unselect();
        $this->selectByKey("or", "lotData:batiment", $batiment);
        $this->logger->displayData(
            "lotData:type",
            "lotData:floor",
            "lotData:situation",
            "lotData:general",
            "lastname",
            "firstname",
            "syndicCode"
        );
    }

    /**
     * Affiche la liste de tous les copropriétaires de la copropriété
     *
     */
    public function showOwners()
    {
        $this->selectAll();
        $this->selectByKeyExt('andNot', 'closed', "/yes/");
        $this->sortNumeric("owner");
        $this->sumKeys("general", "lotData:imputations:general");
        $this->logger->displayData(
            "general>9",
            "lastname>16",
            "firstname>16",
            "syndicCode>8",
            "lotData:batiment>6",
            "lotData:imputations:general",
            "closed"
        );
        $this->logger->displaySums("general", "lotData:imputations:general");
    }

    /**
     * Affiche la liste des copropriétaires triée par le numéro de code du syndic
     */
    public function showSortedBySyndicCode()
    {
        $this->selectAll();
        $this->sortNumeric("syndicCode");
        $this->sumKeys("general", "lotData:imputations:general");
        $this->logger->displayData(
            "syndicCode>6",
            "general>12",
            "lotData:imputations:general>12",
            "lastname",
            "firstname"
        );
        $this->logger->displaySums("general", "lotData:imputations:general");
    }
}
