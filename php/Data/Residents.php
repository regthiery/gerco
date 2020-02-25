<?php

namespace Gerco\Data;

class Residents extends DataObjects
{

    public function __construct()
    {
        parent::__construct();
        $this->setPrimaryKey("lot");
    }

    public function getBuildings(): array
    {
        $batiments = array();
        foreach ($this->objects as $key => $item) {
            if (array_key_exists($item['batiment'], $batiments))
                ++$batiments[$item['batiment']];
            else
                $batiments[$item['batiment']] = 1;
        }
        return $batiments;
    }


    public function showBatiment($batiment)
    {
        $this->unselect();
        $this->selectByKey("or", "batiment", $batiment);
        $this->logger->displayData("lotData:type>6", "lotData:floor>6", "lotData:situation>6", "lastname>16", "firstname>16");
    }

    public function show()
    {
        $batiments = $this->getBuildings();
        foreach ($batiments as $batiment => $count) {
            $this->showBatiment($batiment);
        }
    }
}