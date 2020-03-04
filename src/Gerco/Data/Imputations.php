<?php
/**
 * Imputations.php
 *
 * Classe gérant les clés de répartition des charges d'une copropriété.
 *
 * La classe Imputations traite des clés de répartition des dépenses d'une copropriété.
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
 * Class Imputations
 *
 * @package Gerco\Data
 */
class Imputations extends DataObjects
{
    /**
     * @var Invoices
     */
    protected Invoices $invoices;
    /**
     * @var AccountingPlan
     */
    protected AccountingPlan $accountingPlan;

    /**
     * Imputations constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPrimaryKey("code");
    }

    /**
     * @param $invoices
     */
    public function setInvoices($invoices)
    {
        $this->invoices = $invoices;
    }

    /**
     * @param $accountingPlan
     */
    public function setAccountingPlan($accountingPlan)
    {
        $this->accountingPlan = $accountingPlan;
    }

    /**
     * @param $startDate
     * @param $endDate
     */
    public function setAccountingYear($startDate, $endDate)
    {
        $this->invoices->selectBetweenDates("and", "date", $startDate, $endDate);
    }

    /**
     * @param Owners $owners
     */
    public function createOwnersKeys(Owners $owners)
    {
        foreach ($owners->getObjects() as $ownerKey => $ownerData) {
            //print_r ($ownerData) ;
            $lastname = $ownerData ["lastname"];
            $firstname = $ownerData ["firstname"];
            $syndicCode = $ownerData ["syndicCode"];
            $this->objects["copro$ownerKey"] = array("code" => "copro$ownerKey",
                "label" => "Copropriétaire $lastname $firstname",
                "index" => 1000 + $syndicCode);
        }
    }

    /**
     *
     */
    public function makeAccountStatement()
    {
        foreach ($this->objects as $imputationKey => $imputation) {
            //printf ("%10s \t %-30s\n", $imputation["code"], $imputation["label"]) ;

            $this->objects[$imputationKey]["invoices"] = array();

            foreach ($this->invoices->getFiltered() as $invoiceKey => $invoice) {
                if (!array_key_exists("calculatedImputations", $invoice)) {
                    print("Error : the imputations have not been calculated for invoice $invoiceKey\n");
                    return;
                }
                $imputations = $invoice["calculatedImputations"];
                if (array_key_exists($imputationKey, $imputations)) {
                    $imputationValue = $imputations [$imputationKey];
                    $this->objects[$imputationKey]["invoices"][$invoiceKey]["value"] = $imputationValue;
                    $accountCode = $invoice["accountCode"];
                    $this->objects[$imputationKey]["invoices"][$invoiceKey]["accountCode"] = $accountCode;
                    //printf ("\t%-10s \t % 10.2f\t %8d\n", $invoiceKey, $imputationValue, $accountCode) ;
                }
            }
            $invoices = $this->objects[$imputationKey]["invoices"];
            $accounts = array();
            foreach ($invoices as $invoiceKey => $invoiceData) {
                $accountCode = $invoiceData["accountCode"];
                $invoice = $this->invoices->getFilteredWithKey($invoiceKey);

                if (!array_key_exists($accountCode, $accounts)) {
                    $accounts[$accountCode] = array();
                }

                if (!array_key_exists("count", $accounts[$accountCode])) {
                    $accounts[$accountCode]["count"] = 1;
                } else {
                    $accounts[$accountCode]["count"]++;
                }

                if (!array_key_exists("invoicesKeys", $accounts[$accountCode])) {
                    $accounts[$accountCode]["invoicesKeys"] = array();
                }
                if (!array_key_exists("invoicesDates", $accounts[$accountCode])) {
                    $accounts[$accountCode]["invoicesDates"] = array();
                }

                if (!array_key_exists("invoicesValues", $accounts[$accountCode])) {
                    $accounts[$accountCode]["invoicesValues"] = array();
                }

                $accounts[$accountCode]["invoicesKeys"][] = $invoiceKey;
                $accounts[$accountCode]["invoicesDates"][] = $invoice["date"];
                $accounts[$accountCode]["invoicesValues"][] = $invoice["calculatedImputations"][$imputationKey];
            }


            $imputationTotal = 0;
            foreach ($accounts as $accountCode => $accountData) {
                array_multisort($accountData["invoicesDates"], $accountData["invoicesKeys"], $accountData["invoicesValues"]);
                $accounts[$accountCode]["invoicesKeys"] = $accountData["invoicesKeys"];
                $accounts[$accountCode]["invoicesDates"] = $accountData["invoicesDates"];
                $accounts[$accountCode]["invoicesValues"] = $accountData["invoicesValues"];
                $accounts[$accountCode]["invoicesTotal"] = array_sum($accountData["invoicesValues"]);
                $imputationTotal += $accounts[$accountCode]["invoicesTotal"];
            }

            $this->objects[$imputationKey]["total"] = $imputationTotal;

            ksort($accounts);
            $this->objects[$imputationKey]["accounts"] = $accounts;
        }

        //print_r ($this->objects) ;
    }


    /**
     *
     */
    public function displayAccountStatement()
    {
        foreach ($this->objects as $imputationKey => $imputation) {
            printf("\n\033[1;38;5;4m%-30s\033[0m\n", $imputation["label"]);
            foreach ($imputation["accounts"] as $accountCode => $accountData) {
                $accountLabel = $this->accountingPlan->objects[$accountCode]["label"];
                printf("\033[1m\t%10d : %-32s\033[0m\n", $accountCode, $accountLabel);
                foreach ($accountData["invoicesKeys"] as $invoiceKey) {
                    $invoice = $this->invoices->filteredObjects[$invoiceKey];
                    $imputationValue = $invoice["calculatedImputations"][$imputationKey];


                    printf("\t\t\t%-10s %-12s %12.2f €\n", $invoiceKey, $invoice["date"], $imputationValue);
                }
                printf("\t\t\t\033[31;1mTotal\t\t\t%12.2f €\033[0m\n", $this->objects[$imputationKey]["accounts"][$accountCode]["invoicesTotal"]);
            }
            printf("\t\t\033[1mTOTAL\t\t\t\t%12.2f €\033[0m\n", $this->objects[$imputationKey]["total"]);
        }
    }
}
