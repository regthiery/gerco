<?php
/**
 * AccountingPlan.php
 *
 * Classe gérant le plan comptable d'une copropriété
 *
 * La classe AccountingPlan traite du plan comptable de la copropriété.
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
 * Class AccountingPlan
 *
 * @package Gerco\Data
 */
class AccountingPlan extends DataObjects
{
    /**
     * AccountingPlan constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPrimaryKey("code");
    }

    /**
     *
     */
    public function display()
    {
        foreach ($this->objects as $key => $item) {
            $n = strlen($key);
            for ($i = 0; $i < $n; $i++) {
                print("\t");
            }
            $label = $item["label"];
            print("$key : $label\n");
        }
    }

    /**
     * @param $shortName
     *
     * @return false|int|string
     */
    public function getAccountIndex($shortName)
    {
        return array_search($shortName, array_column($this->objects, 'shortname'));
    }

    /**
     * Cette fonction récupère le code du compte référencé par $index
     *
     * @param $index
     *
     * @return mixed
     */
    public function getAccountCode($index)
    {
        $key = $this->objectsKeys [$index];
        $account = $this->objects[$key];
        return $account["code"];
    }

    /**
     * Cette fonction récupère l'intitulé du compte référencé par $index
     *
     * @param $index
     *
     * @return mixed
     */
    public function getAccountLabel($index)
    {
        $key = $this->objectsKeys [$index];
        $account = $this->objects[$key];
        return $account["label"];
    }

    /**
     * @param Owners $owners
     */
    public function createOwnersAccount(Owners $owners)
    {
        foreach ($owners->getObjects() as $ownerKey => $ownerData) {
            //			print_r ($ownerData) ;
            $syndicCode = $ownerData["syndicCode"];
            $lastName = $ownerData ["lastname"];
            $firstName = $ownerData ["firstname"];
            $accountCode = "450$syndicCode";
            $newAccount = array(
                "code" => $accountCode,
                "label" => "Compte propriétaire (lot $ownerKey) = $lastName $firstName",
                "shortName" => "$lastName $firstName"
            );
            $this->objects[$accountCode] = $newAccount;
        }
    }

    /**
     *
     */
    public function sortAccounts()
    {
        ksort($this->objects, SORT_STRING);
    }
}
