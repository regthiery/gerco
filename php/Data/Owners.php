<?php

namespace Gerco\Data;

class Owners extends DataObjects
{

    public function __construct()
    {
        parent::__construct();
        $this->setPrimaryKey("owner");
    }

    public function show($batiment)
    {
        $this->unselect();
        $this->selectByKey("or", "lotData:batiment", $batiment);
        $this->logger->displayData("lotData:type", "lotData:floor", "lotData:situation", "lotData:general", "lastname", "firstname", "syndicCode");
    }

    public function showOwners()
    {
        $this->selectAll();
        $this->selectByKeyExt('andNot', 'closed', "/yes/");
        $this->sortNumeric("owner");
        $this->sumKeys("general", "lotData:imputations:general");
        $this->logger->displayData("general>9", "lastname>16", "firstname>16", "syndicCode>8", "lotData:batiment>6", "lotData:imputations:general", "closed");
        $this->logger->displaySums("general", "lotData:imputations:general");
    }

    public function showSortedBySyndicCode()
    {
        $this->selectAll();
        $this->sortNumeric("syndicCode");
        $this->sumKeys("general", "lotData:imputations:general");
        $this->logger->displayData("syndicCode>6", "general>12", "lotData:imputations:general>12", "lastname", "firstname");
        $this->logger->displaySums("general", "lotData:imputations:general");
    }
}