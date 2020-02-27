<?php
/**
 * Residents.php
 *
 * Classe stockant la liste des résidents (actuels ou anciens) de la copropriété
 *
 * La classe Residents regroupe toutes les informations relatives
 * aux résidents actuels ou anciens.
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
 * Class Residents
 *
 * @package Gerco\Data
 */
class Residents extends DataObjects
{

    /**
     * Residents constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPrimaryKey("lot");
    }

    /**
     * @return array
     */
    public function getBuildings(): array
    {
        $batiments = array();
        foreach ($this->objects as $key => $item) {
            if (array_key_exists($item['batiment'], $batiments)) {
                ++$batiments[$item['batiment']];
            } else {
                $batiments[$item['batiment']] = 1;
            }
        }
        return $batiments;
    }


    /**
     * @param $batiment
     */
    public function showBatiment($batiment)
    {
        $this->unselect();
        $this->selectByKey("or", "batiment", $batiment);
        $this->logger->displayData(
            "lotData:type>6",
            "lotData:floor>6",
            "lotData:situation>6",
            "lastname>16",
            "firstname>16"
        );
    }

    /**
     *
     */
    public function show()
    {
        $batiments = $this->getBuildings();
        foreach ($batiments as $batiment => $count) {
            $this->showBatiment($batiment);
        }
    }
}
