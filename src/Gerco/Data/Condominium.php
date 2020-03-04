<?php
/**
 * Condominium.php
 *
 * Classe gérant une copropriété immobilière
 *
 * La classe Condominium gère une copropriété immobilière.
 * Elle effectue diverses opérations demandées à partir
 * de requêtes qui lui sont transmises.
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


namespace Gerco\Data ;

/**
 * Class Condominium
 *
 * @package Gerco\Data
 * @author  R. Thiéry
 */
class Condominium extends DataObject
{
    /**
     * @var Lots
     */
    public Lots                $lots;
    /**
     * @var Residents
     */
    public Residents           $residents;
    /**
     * @var Owners
     */
    public Owners              $owners;
    /**
     * @var Suppliers
     */
    public Suppliers           $suppliers;
    /**
     * @var Invoices
     */
    public Invoices            $invoices;
    /**
     * @var AccountingPlan
     */
    public AccountingPlan      $accountingPlan;
    /**
     * @var Imputations
     */
    public Imputations         $imputations;
    /**
     * @var AccountingExercises
     */
    public AccountingExercises $accountingExercises;
    /**
     * @var GeneralMeetings
     */
    public GeneralMeetings     $generalMeetings;

    /**
     * Condominium constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     */
    public function build()
    {
        $this->lots = new Lots();
        $this->lots->readYamlFile($this->data['lotsPathName']);

        $this->residents = new Residents();
        $this->residents->readYamlFile($this->data['residentsPathName']);

        $this->owners = new Owners();
        $t0 = microtime(true);
        $this->owners->readYamlFile($this->data['ownersPathName']) ;
        $t1 = microtime(true);
        printf("\nTIME %f\n",($t1-$t0)*1e6);

        $this->suppliers = new Suppliers();
        $this->suppliers->readYamlFile($this->data['suppliersPathName']);

        $this->invoices = new Invoices();
        $this->invoices->readYamlFile($this->data['invoicesPathName']);

        $this->accountingPlan = new AccountingPlan();
        $this->accountingPlan->readYamlFile($this->data['accountingPlanPathName']);

        $this->imputations = new Imputations();
        $this->imputations->readYamlFile($this->data['imputationsPathName']);

        $this->accountingExercises = new AccountingExercises();
        $this->accountingExercises->readYamlFile($this->data['accountingExercisesPathName']);

        $this->generalMeetings = new GeneralMeetings();
        $this->generalMeetings->readYamlFile($this->data['generalMeetingsPathName']);

        $this->lots->setImputations($this->imputations);

        $this->lots->calculateMilliemes();
        $this->lots->joinWithData($this->owners, "owner", "ownerData");
        $this->residents->joinWithData($this->lots, "lot", "lotData");
        $this->owners->joinWithData($this->lots, "owner", "lotData");
        $this->owners->joinWithData($this->residents, "owner", "residentData");


        $this->accountingPlan->createOwnersAccount($this->owners);
        $this->accountingPlan->sortAccounts();

        $this->imputations->setInvoices($this->invoices);
        $this->imputations->setAccountingPlan($this->accountingPlan);
        $this->imputations->createOwnersKeys($this->owners);

        $this->accountingExercises->setAccountingPlan($this->accountingPlan);
        $this->accountingExercises->setImputations($this->imputations);

        $this->generalMeetings->setOwners($this->owners);
        $this->generalMeetings->setLots($this->lots);
        $this->generalMeetings->setImputations($this->imputations);
    }

    /**
     * @param $year0
     *
     * @return array
     */
    public function getAccountingYearDates($year0) : array
    {
        if ( !isset($this->data['accountingYear'])) {
            throw new \Error ("accountingYear is not defined") ;
        }
        if (! array_key_exists($year0, $this->data['accountingYear'])) {
            $msg = sprintf("Erreur: l'exercice comptable $year0 n'est pas défini.\n");
            throw new \Exception($msg) ;
        }

        $year = $this->data['accountingYear'][$year0] ;
        if (preg_match("/(\d{4})-(\d{4})/", $year, $matches)) {
            $year1 = $matches[1] ;
            $year2 = $matches[2] ;
        } else {
            $msg = sprint("Erreur: l'année de l'exercice comptable n'est pas défini.\n");
            throw new \Exception($msg) ;
        }
        $startDate = $year1.'-'.$this->data['startDate'] ;
        $endDate = $year2.'-'.$this->data['endDate'] ;

        return array($startDate,$endDate) ;
    }


    /**
     *
     */
    public function display()
    {
        $this->logger->displayData("name", "syndicName", "creationDate");
    }

    /**
     * Cette fonction traite les requêtes envoyées par le client
     * @param $action
     * @param $params
     *
     * @throws \Exception
     */
    public function handleRequest($action, $params)
    {
        switch ($action) {
        case 'copro':
            $this->display();
            break;
        case 'owners':
            $this->lots->showOwners();
            break;
        case 'owners1':
            $this->owners->showOwners();
            break;
        case 'owners2':
            $this->owners->showSortedBySyndicCode();
            break;
        case 'prices':
            $this->lots->showPrices();
            break;
        case 'residents':
            $this->residents->show();
            break;
        case 'milliemes':
            $this->lots->showGeneralMilliemes();
            $this->lots->showSpecialMilliemes();
            break;
        case 'handicap':
            $this->lots->showGarageHandicap();
            break;
        case 'parkings':
            $this->lots->showParkings();
            break;
        case 'invoices':
            $this->invoices->showInvoices();
            break;
        case 'electricite':
            $this->invoices->showInvoicesWithKeyword("Electricité");
            break;
        case 'ascenseur':
            $this->invoices->showInvoicesWithKeyword("Ascenseur");
            break;
        case 'entretien':
            $this->invoices->showInvoicesWithKeyword("Entretien");
            break;
        case 'eau':
            $this->invoices->showInvoicesWithKeyword("Eau");
            break;
        case 'imputations':
            $imputationKeys = $this->invoices->calculateImputationKeysList();
            print_r($imputationKeys);
            break;
        case 'accountingPlan':
            $this->accountingPlan->display();
            break ;
        case 'checkInvoices':
            $this->invoices -> selectAll();
            $this->invoices->checkWithAccountingPlan($this->accountingPlan);
            $this->invoices -> displayInvoicesList();
            break ;
        case 'extract':
            $startDate = $params[0] ;
            $endDate   = $params[1] ;

            $this->invoices -> selectAll();
            $this->invoices -> selectBetweenDates(
                "and",
                "date",
                $startDate,
                $endDate
            );
            $this->invoices -> calculateImputations();
            $this->invoices -> checkWithAccountingPlan($this->accountingPlan);
            $this->invoices -> displayInvoicesList();
            break ;
        case 'journal':
            $year = $params[0] ;
            list($startDate, $endDate) = $this->getAccountingYearDates($year);
            $this->invoices -> selectAll();
            $this->invoices -> selectBetweenDates(
                "and",
                "date",
                $startDate,
                $endDate
            );
            $this->invoices -> calculateImputations();
            $this->invoices -> checkWithAccountingPlan($this->accountingPlan);
            $this->invoices -> displayInvoicesList();
            break ;
        case 'accountStatement':
            $year = $params[0] ;
            if (isset($year)) {
                list($startDate, $endDate) = $this->getAccountingYearDates($year);
                $this->invoices -> selectAll();
                $this->invoices -> selectBetweenDates(
                    "and",
                    "date",
                    $startDate,
                    $endDate
                );
                $this->invoices -> calculateImputations();
                $this->invoices -> checkWithAccountingPlan($this->accountingPlan);
                $this->invoices -> displayInvoicesList();
                $this->imputations -> makeAccountStatement();
                $this->imputations -> displayAccountStatement();
            }
            break ;
        case 'exercise':
            $year = $params[0] ;
            $this->accountingExercises -> calculateImputations($year);
            $this->accountingExercises -> displayPrevisionalBudget($year);
            break ;
        case 'suppliers':
            $this -> suppliers -> displaySuppliers();
            break ;
        case 'meeting':
            $index = $params[0] ;
            $this->generalMeetings -> setMeetingIndex($index);
            $this->generalMeetings -> checkAttendance();
            $this->generalMeetings -> displayAttendance();
            $this->generalMeetings -> displayResolutions();
            $this->generalMeetings -> calculateVotingResults();
            $this->generalMeetings -> displayVotingResults();
            break ;
        default:
            throw new \Exception("Commande $action non attendue.\n");
                break ;

        }
    }
}
