<?php
/**
 * Suppliers.php
 *
 * Classe listant les fournisseurs et prestataires de services pour la copropriété.
 *
 * La classe Suppliers encapsule une liste de tableaux associatifs.
 * Les données (clé => valeur) sont lues dans un fichier texte.
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
 * Class Suppliers
 *
 * @package Gerco\Data
 */
class Suppliers extends DataObjects
{

    /**
     * Suppliers constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPrimaryKey("index");
    }

    /**
     *
     */
    public function displaySuppliers()
    {
        $this->selectAll();
        $this->sortNumeric("index");
        $this->logger->displayData("shortName>34", "name>35");
    }
}
