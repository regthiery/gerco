<?php

namespace Gerco\Data;

class AccountingExercises extends DataObjects
{

    protected AccountingPlan $accountingPlan;
    protected Imputations $imputations;

    public function __construct()
    {
        parent::__construct();
        $this->setPrimaryKey("exercise");
    }

    public function setAccountingPlan($accountingPlan)
    {
        $this->accountingPlan = $accountingPlan;
    }

    public function setImputations($imputations)
    {
        $this->imputations = $imputations;
    }


    public function calculateImputations($e)
    {
        $exercise = $this->getObjectWithKey($e);

        $accounts = array();
        foreach ($exercise as $key => $item) {
            if (preg_match("/provision(\d+)/", $key, $matches)) {
                $accountKey = $matches[1];
                if (array_key_exists($accountKey, $accounts)) {
                    print ("AccountingExercises:calculateImputations : this account $accountKey has already been defined.\n");
                    return;
                }
                $label = $this->accountingPlan->getObjectWithKey($accountKey)["label"];

                $item = preg_replace('/\s\s+/', ' ', $item);
                $imputationsArray = explode(' ', $item);

                $imputations = array();
                foreach ($imputationsArray as $key0 => $data) {
                    if (preg_match('/(.*)=>(.*)/', $data, $s)) {
                        $imputationName = $s[1];
                        $imputationValue = $s[2];
                        $imputations[$imputationName] = $imputationValue;
                    }
                }

                $accounts[$accountKey] = array(
                    // "code" => $accountKey,
                    "label" => $label,
                    "imputations" => $imputations);
            }
        }


        $imputations = array();
        foreach ($accounts as $code => $data) {
            foreach ($data["imputations"] as $imputationKey => $imputationValue) {
                $imputations[$imputationKey]["accounts"][$code] = array("label" => $data["label"],
                    "value" => $imputationValue);
            }
        }


        foreach ($imputations as $imputationCode => $imputationData) {
            ksort($imputations[$imputationCode]["accounts"], SORT_STRING);
            $sum = 0;
            foreach ($imputationData["accounts"] as $accountCode => $accountData) {
                $sum += $accountData["value"];
            }
            $imputations[$imputationCode]["total"] = $sum;
            $imputation = $this->imputations->getObjectWithKey($imputationCode);
            $imputationIndex = $imputation["index"];
            $imputations[$imputationCode]["index"] = $imputationIndex;
        }

        $this->objects[$e]["accounts"] = $accounts;
        $this->objects[$e]["imputations"] = $imputations;
    }


    public function displayPrevisionalBudget($e)
    {
        $exercise = $this->getObjectWithKey($e);
        print_r($exercise);

        $imputations = $exercise["imputations"];
        uasort($imputations,
            function ($a, $b) {
                if ($a["index"] == $b["index"]) return 0;
                return ($a["index"] < $b["index"]) ? -1 : 1;
            });

        foreach ($imputations as $imputationKey => $imputationData) {
            printf("\033[1;38,5m%-60s (%s)\033[0m\n", $this->imputations->getObjectWithKey($imputationKey)["label"], $imputationKey);
            $accountsList = $imputationData["accounts"];
            foreach ($accountsList as $accountCode => $accountData) {
                printf("\t%-10d %10.2f\t\t %s\n", $accountCode, $accountData["value"], $accountData["label"]);
            }
            printf("\t\033[1mTotal %15.2f\033[0m\n", $exercise["imputations"][$imputationKey]["total"]);
        }
    }
}		