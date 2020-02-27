<?php
/**
 * GeneralMeetings.php
 *
 * Classe gérant les assemblées générales d'une copropriété immobilière
 *
 * La classe GeneralMeetings traite de l'organisation et de la tenue des assemblées générales.
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

use Gerco\Data\Resolutions;

/**
 * Class GeneralMeetings
 *
 * @package Gerco\Data
 */
class GeneralMeetings extends DataObjects
{

    /**
     * @var Owners
     */
    protected Owners $owners;
    /**
     * @var Lots
     */
    protected Lots $lots;

    /**
     * @var \Gerco\Data\Resolutions
     */
    protected Resolutions $resolutions;
    /**
     * @var
     */
    protected $meetingIndex;
    /**
     * @var
     */
    protected $meeting;

    /**
     * GeneralMeetings constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPrimaryKey("index");
        $this->resolutions = new Resolutions();
        $this->resolutions->setGeneralMeetings($this);
    }

    /**
     * @param $owners
     * @return void
     */
    public function setOwners($owners)
    {
        $this->owners = $owners;
        $this->resolutions->setOwners($owners);
    }

    /**
     * @param $lots
     */
    public function setLots($lots)
    {
        $this->lots = $lots;
    }

    /**
     * @param $imputations
     */
    public function setImputations($imputations)
    {
        $this->resolutions->setImputations($imputations);
    }

    /**
     * @param $index
     */
    public function setMeetingIndex($index)
    {
        $this->meetingIndex = $index;
        $this->meeting = $this->getObjectWithKey($this->meetingIndex);
        $filename = $this->meeting["resolutionsFileName"];
        $this->resolutions->readFile("../00-data/$filename");
    }

    /**
     * @return mixed
     */
    public function getMeeting()
    {
        return $this->meeting;
    }


    /**
     *    Récupère la liste des copropriétaires présents et toutes les informations s'y rapportant.
     *    Calcule la liste des absents
     */
    public function checkAttendance()
    {
        $meeting = &$this->meeting;

        $presentsData = array();
        $presentsByBatData = array();
        $presentGeneralSum = 0;
        $presentSpecialSum = array();
        foreach ($meeting["presents"] as $i => $pseudo) {
            $owner = $this->owners->getObjectWithKeyValue("pseudo", $pseudo);
            if ($owner == null) {
                print('There are no data for the owner \"$pseudo\" \n');
            } else {
                $batiment = $owner['lotData']['batiment'];
                $special = $owner['lotData']['imputations']["special$batiment"];
                $owner['lotData']['special'] = $special;

                $presentsData[$pseudo] = $owner;
                $presentsByBatData[$batiment][$pseudo] = $owner;
                $presentGeneralSum += $owner['general'];
                if (array_key_exists($batiment, $presentSpecialSum)) {
                    $presentSpecialSum[$batiment] += $special;
                } else {
                    $presentSpecialSum[$batiment] = 0;
                }
            }
        }

        foreach ($presentsByBatData as $batiment => $ownersOfBat) {
            uasort($ownersOfBat, function ($a, $b) {
                return strcmp($a["pseudo"], $b["pseudo"]);
            });
        }

        ksort($presentsByBatData);


        $absents = array();
        $absentByBat = array();
        $absentGeneralSum = 0;
        $absentSpecialSum = array();

        foreach ($this->owners->getObjects() as $ownerKey => $ownerData) {
            $pseudo = $ownerData["pseudo"];
            if (array_key_exists("closed", $ownerData) && $ownerData["closed"] === "yes") {
                continue;
            }
            if (!in_array($pseudo, $meeting["presents"])) {
                $absents[$pseudo] = $ownerData;

                $batiment = $ownerData['lotData']['batiment'];
                $special = $ownerData['lotData']['imputations']["special$batiment"];
                $ownerData['lotData']['special'] = $special;

                $absentsData[$pseudo] = $ownerData;
                $absentsByBatData[$batiment][$pseudo] = $ownerData;
                $absentGeneralSum += $ownerData['general'];
                if (array_key_exists($batiment, $absentSpecialSum)) {
                    $absentSpecialSum[$batiment] += $special;
                } else {
                    $absentSpecialSum[$batiment] = 0;
                }
            }
        }

        ksort($absentsData);
        foreach ($absentsByBatData as $batiment => $ownersOfBat) {
            ksort($ownersOfBat);
            $absentsByBatData[$batiment] = $ownersOfBat;
        }

        ksort($absentsByBatData);

        $meeting['presentsData'] = &$presentsData;
        $meeting['presentsByBatData'] = &$presentsByBatData;
        $meeting['absentsData'] = &$absentsData;
        $meeting['absentsByBatData'] = &$absentsByBatData;

        $meeting['presentsGeneralSum'] = $presentGeneralSum;
        $meeting['absentsGeneralSum'] = $absentGeneralSum;
        $meeting['presentsCount'] = count($presentsData);
        $meeting['absentsCount'] = count($absentsData);
        $meeting['presentSpecialSum'] = &$presentSpecialSum;
        $meeting['absentSpecialSum'] = &$absentSpecialSum;

        //$this->objects[$this->meetingIndex] = $meeting ;
        //$this->meeting = $meeting ;
    }


    /**
     *    Affiche la liste des copropriéaires (présents/représentés ou absents)
     *
     */

    public function displayAttendance()
    {
        $meeting = &$this->meeting;

        $i = 1;
        $generalSum = 0;
        printf(
            "\033[1mCopropriété \033[0m (%d présents (ou représentés) sur %d copropriétaires (%.2f %%)\n",
            $meeting['presentsCount'],
            $meeting['presentsCount'] + $meeting['absentsCount'],
            $meeting['presentsCount'] / ($meeting['presentsCount'] + $meeting['absentsCount']) * 100.0
        );
        foreach ($meeting["presentsData"] as $pseudo => $ownerData) {
            $lastname = $ownerData['lastname'];
            $firstname = $ownerData['firstname'];
            $general = $ownerData['general'];
            $batiment = $ownerData['lotData']['batiment'];
            printf("%5d \t%-20s \t%-20s \t%5s %8.0f\n", $i, $lastname, $firstname, $batiment, $general);
            $generalSum += $general;
            $i++;
        }
        printf(
            "\033[1mGeneral %8.0lf/%-6.0lf %8.2lf%%\033[0m\n\n",
            $meeting['presentsGeneralSum'],
            $meeting['presentsGeneralSum'] + $meeting['absentsGeneralSum'],
            $meeting['presentsGeneralSum'] / ($meeting['presentsGeneralSum'] + $meeting['absentsGeneralSum']) * 100.0
        );

        foreach ($meeting["presentsByBatData"] as $batiment => $owners) {
            printf(
                "\033[1mBatiment %s \033[0m (%d présents ou représentés sur %d copropriétaires, soit %.2f %%) \n",
                $batiment,
                count($meeting['presentsByBatData'][$batiment]),
                count($meeting['presentsByBatData'][$batiment]) + count($meeting['absentsByBatData'][$batiment]),
                count($meeting['presentsByBatData'][$batiment])
                / (count($meeting['presentsByBatData'][$batiment]) + count($meeting['absentsByBatData'][$batiment]))
                * 100.0
            );
            $i = 1;
            foreach ($owners as $pseudo => $ownerData) {
                $lastname = $ownerData['lastname'];
                $firstname = $ownerData['firstname'];
                $special = $ownerData['lotData']['imputations']["special$batiment"];
                printf("%5d \t%-20s \t%-20s \t%5s %8.0f\n", $i, $lastname, $firstname, $batiment, $special);
                $i++;
            }
            printf(
                "\033[1mSpécial\033[0m %8.0lf/%-6.0lf, soit %5.2f %%\n\n",
                $meeting['presentSpecialSum'][$batiment],
                $meeting['presentSpecialSum'][$batiment] + $meeting['absentSpecialSum'][$batiment],
                $meeting['presentSpecialSum'][$batiment] / ($meeting['presentSpecialSum'][$batiment] + $meeting['absentSpecialSum'][$batiment]) * 100.0
            );
        }

        $i = 1;
        $generalSum = 0;
        printf(
            "\033[1mCopropriété \033[0m (%d absents sur %d copropriétaires (%.2f %%)\n",
            $meeting['absentsCount'],
            $meeting['presentsCount'] + $meeting['absentsCount'],
            $meeting['absentsCount'] / ($meeting['presentsCount'] + $meeting['absentsCount']) * 100.0
        );

        foreach ($meeting["absentsData"] as $pseudo => $ownerData) {
            $lastname = $ownerData["lastname"];
            $firstname = $ownerData["firstname"];
            $general = $ownerData["general"];
            $batiment = $ownerData["lotData"]["batiment"];
            printf("%5d \t%-20s \t%-20s \t%5s %8.0f\n", $i, $lastname, $firstname, $batiment, $general);
            $generalSum += $general;
            $i++;
        }
        printf(
            "\033[1mGeneral %8.0lf/%-6.0lf %8.2lf%%\033[0m\n\n",
            $meeting['absentsGeneralSum'],
            $meeting['presentsGeneralSum'] + $meeting['absentsGeneralSum'],
            $meeting['absentsGeneralSum'] / ($meeting['presentsGeneralSum'] + $meeting['absentsGeneralSum']) * 100.0
        );


        foreach ($meeting["absentsByBatData"] as $batiment => $owners) {
            printf(
                "\033[1mBatiment %s \033[0m (%d absents sur %d copropriétaires, soit %.2f %%) \n",
                $batiment,
                count($meeting['absentsByBatData'][$batiment]),
                count($meeting['presentsByBatData'][$batiment]) + count($meeting['absentsByBatData'][$batiment]),
                count($meeting['absentsByBatData'][$batiment])
                / (count($meeting['presentsByBatData'][$batiment]) + count($meeting['absentsByBatData'][$batiment]))
                * 100.0
            );

            $i = 1;
            foreach ($owners as $pseudo => $ownerData) {
                $lastname = $ownerData["lastname"];
                $firstname = $ownerData["firstname"];
                $special = $ownerData["lotData"]['imputations']["special$batiment"];
                printf("%5d \t%-20s \t%-20s \t%5s %8.0f\n", $i, $lastname, $firstname, $batiment, $special);
                $i++;
            }
            printf(
                "\033[1mSpécial\033[0m %8.0lf/%-6.0lf, soit %5.2f %%\n\n",
                $meeting['absentSpecialSum'][$batiment],
                $meeting['presentSpecialSum'][$batiment] + $meeting['absentSpecialSum'][$batiment],
                $meeting['absentSpecialSum'][$batiment] / ($meeting['presentSpecialSum'][$batiment] + $meeting['absentSpecialSum'][$batiment]) * 100.0
            );
        }

        //print_r ($meeting["absentsByBatData"])	;
    }

    /**
     *
     */
    public function displayResolutions()
    {
        $meeting = $this->meeting;
        $this->resolutions->displayResolutions();
    }


    /**
     *
     */
    public function calculateVotingResults()
    {
        $this->resolutions->calculateVotingResults();
    }

    /**
     *
     */
    public function displayVotingResults()
    {
        $this->resolutions->displayVotingResults();
    }
}
