<?php
/**
 * Invoices.php
 *
 * Classe gérant les factures de la copropriété
 *
 * La classe Invoices traite des factures reçues par le syndicat de copropriétaire.
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
 * Class Invoices
 *
 * @package Gerco\Data
 */
class Invoices extends DataObjects
{
    /**
     * @var
     */
    protected $imputationKeys;
    /**
     * @var AccountingPlan
     */
    protected AccountingPlan $accountingPlan;


    /**
     * Invoices constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPrimaryKey("index");
    }

    /**
     *
     */
    public function calculateImputations()
    {
        foreach ($this->filteredObjects as $key => $invoice) {
            $sum = 0;
            if (array_key_exists("imputations", $invoice)) {
                $imputations = $invoice["imputations"];
                $this->filteredObjects[$key]["calculatedImputations"] = array();
                foreach ($imputations as $key0 => $imputation) {
                    $value = $invoice["value"];

                    if (preg_match("/(.*)=>(.*)/", $imputation, $matches)) {
                        $imputationKey = $matches[1];
                        $imputationValue = $matches[2];

                        if (preg_match("/(.*)x(.*)/", $imputationValue, $imputationValues)) {
                            $ncopro = $imputationValues [1];
                            $valueByCopro = $imputationValues [2];
                            $imputationValue = $ncopro * $valueByCopro;
                        }

                        if (preg_match("/(.*)\%/", $imputationValue, $percents)) {
                            $percent = $percents[1];
                            $this->filteredObjects[$key]["calculatedImputations"][$imputationKey] = $value * $percent / 100.0;
                        } else {
                            $this->filteredObjects[$key]["calculatedImputations"][$imputationKey] = $imputationValue;
                        }
                        //printf("Facture %s value %f clé %s somme %f\n", $key, $imputationValue, $imputationKey, $sum) ;
                        $sum += $this->filteredObjects[$key]["calculatedImputations"][$imputationKey];
                    }
                }

                $res = abs($invoice["value"] - $sum);
                if ($res > 1e-2) {
                    $index = $this->filteredObjects[$key][$this->primaryKey];
                    print("Erreur sur les imputations de la facture $index\n");
                    print("Son montant de $value euros n'est pas égale à la somme des imputations $sum\n");
                    print_r($imputations);
                }
            } else {
                print("Error: no imputation defined for invoice $key !\n");
            }
        }
    }

    /**
     * @return array
     */
    public function calculateImputationKeysList()
    {
        $this->imputationKeys = array();
        foreach ($this->objects as $key => $invoice) {
            if (array_key_exists("imputations", $invoice)) {
                $imputations = $invoice["imputations"];
                foreach ($imputations as $i => $imputation) {
                    if (preg_match("/(.*)=>(.*)/", $imputation, $matches)) {
                        $imputationKey = $matches[1];
                        // $imputationValue = $matches[2];
                        if (array_key_exists($imputationKey, $this->imputationKeys)) {
                            $this->imputationKeys [$imputationKey]++;
                        } else {
                            $this->imputationKeys [$imputationKey] = 1;
                        }
                    }
                }
            }
        }
        ksort($this->imputationKeys);
        return $this->imputationKeys;
    }

    /**
     *
     */
    public function showInvoices()
    {
        $this->selectAll();
        $this->logger->displayData("to>10", "date>12", "value>12", "from>20", "object>25", "imputations>25", "info");
    }

    /**
     * @param $keyword
     *
     * @return array
     */
    public function getBuildingsListFor($keyword): array
    {
        $this->selectAll();
        $this->selectByKeyExt("and", "object", "/$keyword/");

        $batiments = array();

        foreach ($this->filteredObjects as $k => $item) {
            if (preg_match("/copro\d+/", $item['to'])) {
                if (array_key_exists($item['to'], $batiments)) {
                    ++$batiments[$item['to']];
                } else {
                    $batiments[$item['to']] = 1;
                }
            } else {
                $matches = preg_split('//', $item['to'], -1, PREG_SPLIT_NO_EMPTY);
                $n = count($matches);
                for ($i = 0; $i < $n; $i++) {
                    $c = $matches[$i];
                    if (array_key_exists($c, $batiments)) {
                        ++$batiments[$c];
                    } else {
                        $batiments[$c] = 1;
                    }
                }
            }
        }
        ksort($batiments);
        return $batiments;
    }

    /**
     * @param $keyword
     */
    public function showInvoicesWithKeyword($keyword)
    {
        $batiments = $this->getBuildingsListFor($keyword);
        print_r($batiments);
        foreach ($batiments as $batiment => $count) {
            $this->showInvoicesWithKeywordByBatiment($keyword, $batiment);
        }
    }

    /**
     * @param $keyword
     * @param $bat
     */
    public function showInvoicesWithKeywordByBatiment($keyword, $bat)
    {
        $this->selectAll();
        $this->selectByKeyExt("and", "to", "/$bat/");
        $pattern = "/$keyword/";
        $this->selectByKeyExt("and", "object", $pattern);
        $this->calculateImputations();
        $this->sortByDate("date");
        if (preg_match("/copro/", $bat)) {
            $imputationKey = $bat;
        } else {
            if ($keyword === "Electricité") {
                $imputationKey = "special$bat";
            } elseif ($keyword === "Ascenseur") {
                $imputationKey = "ascenseur$bat";
            } elseif ($keyword === "Eau") {
                $imputationKey = "special$bat";
            }
        }
        if ($keyword === "Entretien") {
            $this->sumKeys("calculatedImputations:special$bat", "calculatedImputations:escalier$bat>12");
            $this->logger->displayData(
                "to>10",
                "date>12",
                "value>10",
                "calculatedImputations:special$bat>12",
                "calculatedImputations:escalier$bat>12",
                "from>12",
                "object>20",
                "info>20"
            );
            $this->logger->displaySums(
                "calculatedImputations:special$bat",
                "calculatedImputations:escalier$bat>12"
            );
        } else {
            $this->sumKeys("calculatedImputations:$imputationKey");
            $this->logger->displayData(
                "to>10",
                "date>12",
                "value>10",
                "calculatedImputations:$imputationKey>12",
                "from>12",
                "object>20",
                "info>20"
            );
            $this->logger->displaySums("calculatedImputations:$imputationKey");
        }
    }


    /**
     * @param AccountingPlan $accountingPlan
     */
    public function checkWithAccountingPlan(AccountingPlan $accountingPlan)
    {
        $this->accountingPlan = $accountingPlan;
        //$this->selectAll () ;

        $this->sortByDate('date');


        $invoicesCount = $this->filteredCount;
        $invoiceIndex = $invoicesCount;
        foreach ($this->filteredObjects as $key => $invoice) {
            $invoiceKey = $invoice["index"];
            $invoiceShortName = $invoice["object"];
            //$date = $invoice["date"];
            $accountIndex = $this->accountingPlan->getAccountIndex($invoiceShortName);
            //$from = $invoice["from"];

            $accountCode = $this->accountingPlan->getAccountCode($accountIndex);
            $accountLabel = $this->accountingPlan->getAccountLabel($accountIndex);


            $this->filteredObjects[$invoiceKey]["accountCode"] = $accountCode;
            $this->filteredObjects[$invoiceKey]["accountLabel"] = $accountLabel;
            $this->filteredObjects[$invoiceKey]["invoiceIndex"] = $invoiceIndex;

            $invoiceIndex--;
        }
    }

    /**
     * @param $index
     *
     * @return mixed|null
     */
    public function getInvoiceKeyWithIndex($index)
    {
        foreach ($this->filteredObjects as $key => $data) {
            if ($data['invoiceIndex'] == $index) {
                return $data['index'];
            }
        }
        return null;
    }

    /**
     *
     */
    public function displayInvoicesList()
    {
        $invoicesCount = $this->filteredCount;


        for ($i = 0; $i < $invoicesCount; $i++) {
            $invoiceIndex = $i + 1;
            $invoiceKey = $this->getInvoiceKeyWithIndex($invoiceIndex);

            $invoice = $this->filteredObjects[$invoiceKey];
            $date = $invoice["date"];
            $accountCode = $invoice['accountCode'];
            $from = $invoice['from'];
            $invoiceShortName = $invoice["object"];
            printf("%12s\t%6d\t%10s\t%-32s \t %-50s\n", $date, $invoiceIndex, $accountCode, $from, $invoiceShortName);
        }
    }
}
