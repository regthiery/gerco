<?php

namespace Gerco\Data ;

/**
 * Class Condominium
 * @package Gerco\Data
 * @author R. Thiéry
 */
class Condominium extends DataObject
{
    public Lots                $lots;
    public Residents           $residents;
    public Owners              $owners;
    public Suppliers           $suppliers;
    public Invoices            $invoices;
    public AccountingPlan      $accountingPlan;
    public Imputations         $imputations;
    public AccountingExercises $accountingExercises;
    public GeneralMeetings     $generalMeetings;

    public function __construct()
    {
        parent::__construct();
    }

    public function build()
    {

        $this->lots = new Lots();
        $this->lots->readFile($this->data['lotsPathName']);

        $this->residents = new Residents();
        $this->residents->readFile($this->data['residentsPathName']);

        $this->owners = new Owners();
        $this->owners->readFile($this->data['ownersPathName']);

        $this->suppliers = new Suppliers();
        $this->suppliers->readFile($this->data['suppliersPathName']);

        $this->invoices = new Invoices();
        $this->invoices->readFile($this->data['invoicesPathName']);

        $this->accountingPlan = new AccountingPlan();
        $this->accountingPlan->readFile($this->data['accountingPlanPathName']);

        $this->imputations = new Imputations();
        $this->imputations->readFile($this->data['imputationsPathName']);

        $this->accountingExercises = new AccountingExercises();
        $this->accountingExercises->readFile($this->data['accountingExercisesPathName']);

        $this->generalMeetings = new generalMeetings();
        $this->generalMeetings->readFile($this->data['generalMeetingsPathName']);

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

    public function getAccountingYearDates($year0) : array
    {
        if ( ! array_key_exists ($year0, $this->data['accountingYear']))
            $this->logger->print("Erreur: l'exercice comptable $accountingYear n'est pas défini.\n") ;
        $year = $this->data['accountingYear'][$year0] ;
        if (preg_match("/(\d{4})-(\d{4})/",$year,$matches))
        {
            $year1 = $matches[1] ;
            $year2 = $matches[2] ;
        }
        else
        {
            $this->logger->print("Erreur: l'année de l'exercice comptable n'est pas défini.\n") ;
            return NULL ;
        }
        $startDate = $this->data['startDate'].'/'.$year1 ;
        //$startDate = $this->convertDateToEng($startDate) ;
        $endDate = $this->data['endDate'].'/'.$year2 ;
        //$endDate = $this->convertDateToEng($endDate) ;

        return array($startDate,$endDate) ;
    }


    public function display()
    {
        $this->logger->displayData("name", "syndicName", "creationDate");
    }

    public function handleRequest($action, $params)
    {
        switch ($action) {
            case 'copro' :
                $this->display();
                break;
            case 'owners' :
                $this->lots->showOwners();
                break;
            case 'owners1' :
                $this->owners->showOwners();
                break;
            case 'owners2' :
                $this->owners->showSortedBySyndicCode();
                break;
            case 'prices' :
                $this->lots->showPrices();
                break;
            case 'residents' :
                $this->residents->show();
                break;
            case 'milliemes' :
                $this->lots->showGeneralMilliemes();
                $this->lots->showSpecialMilliemes();
                break;
            case 'handicap' :
                $this->lots->showGarageHandicap();
                break;
            case 'parkings' :
                $this->lots->showParkings();
                break;
            case 'invoices' :
                $this->invoices->showInvoices();
                break;
            case 'electricite' :
                $this->invoices->showInvoicesWithKeyword("Electricité");
                break;
            case 'ascenseur' :
                $this->invoices->showInvoicesWithKeyword("Ascenseur");
                break;
            case 'entretien' :
                $this->invoices->showInvoicesWithKeyword("Entretien");
                break;
            case 'eau' :
                $this->invoices->showInvoicesWithKeyword("Eau");
                break;
            case 'imputations' :
                $imputationKeys = $this->invoices->calculateImputationKeysList();
                print_r($imputationKeys);
                break;
            case 'accountingPlan' :
                $this->accountingPlan->display() ;
                break ;
            case 'checkInvoices' :
                $this->invoices -> selectAll () ;
                $this->invoices->checkWithAccountingPlan($this->accountingPlan) ;
                $this->invoices -> displayInvoicesList () ;
                break ;
            case 'extract' :
                $startDate = $params[0] ;
                $endDate   = $params[1] ;

                $this->invoices -> selectAll () ;
                $this->invoices -> selectBetweenDates ("and","date",$startDate, $endDate) ;
                $this->invoices -> calculateImputations () ;
                $this->invoices -> checkWithAccountingPlan ($this->accountingPlan) ;
                $this->invoices -> displayInvoicesList () ;
                break ;
            case 'journal' :
                $year = $params[0] ;
                list($startDate,$endDate) = $this->getAccountingYearDates($year) ;
                $this->invoices -> selectAll () ;
                $this->invoices -> selectBetweenDates ("and","date",$startDate, $endDate) ;
                $this->invoices -> calculateImputations () ;
                $this->invoices -> checkWithAccountingPlan ($this->accountingPlan) ;
                $this->invoices -> displayInvoicesList () ;
                break ;
            case 'accountStatement' :
                $year = $params[0] ;
                list($startDate,$endDate) = $this->getAccountingYearDates($year) ;
                $this->invoices -> selectAll () ;
                $this->invoices -> selectBetweenDates ("and","date",$startDate, $endDate) ;
                $this->invoices -> calculateImputations () ;
                $this->invoices -> checkWithAccountingPlan ($this->accountingPlan) ;
                $this->invoices -> displayInvoicesList () ;
                $this->imputations -> makeAccountStatement () ;
                $this->imputations -> displayAccountStatement () ;
                break ;
            case 'exercise' :
                $year = $params[0] ;
                $this->accountingExercises -> calculateImputations ($year) ;
                $this->accountingExercises -> displayPrevisionalBudget ($year) ;
                break ;
            case 'suppliers' :
                $this -> suppliers -> displaySuppliers () ;
                break ;
            case 'meeting' :
                $index = $params[0] ;
                $this->generalMeetings -> setMeetingIndex ($index) ;
                $this->generalMeetings -> checkAttendance () ;
                $this->generalMeetings -> displayAttendance () ;
                $this->generalMeetings -> displayResolutions () ;
                $this->generalMeetings -> calculateVotingResults () ;
                $this->generalMeetings -> displayVotingResults () ;
                break ;
            default:
                throw new \Exception("Commande $action non attendue.\n");
                break ;

        }
    }
}
    /*
        elseif ( $argv[1] === "suppliers")
        {
//                $suppliers -> displaySuppliers () ;
        }
        elseif ( $argv[1] === "meeting")
        {
                          $index = $argv[2] ;
                          $generalMeetings -> setMeetingIndex ($index) ;
                          $generalMeetings -> checkAttendance () ;
                          $generalMeetings -> displayAttendance () ;
                          $generalMeetings -> displayResolutions () ;
                          $generalMeetings -> calculateVotingResults () ;
                          $generalMeetings -> displayVotingResults () ;
        }
        */



