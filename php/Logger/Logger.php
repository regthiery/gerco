<?php

namespace Gerco\Logger;

use Gerco\Data\DataObject;

class Logger
{
public DataObject $object ;

    public function __construct(DataObject $dataObject)
    {
        $this->object = $dataObject;
    }

    public function displayData(...$keys)
    {
        $formats = array();
        $keys0[] = array();
        $k = 0;
        foreach ($keys as $key0) {
            if (preg_match('/(.*)>(.*)/', $key0, $matches)) {
                $keys0[$k] = $matches[1];
                $formats[$k] = "  : %-" . $matches[2] . "s\t";
            } else {
                $keys0[$k] = $key0;
                $formats[$k] = "  : %-20s\t";
            }
            ++$k;
        }

        $k = 0;
        foreach ($keys0 as $key0) {
            printf("\033[1m%-15s \e[0m", $key0);

            $value = (array_key_exists($key0, $this->object->data)) ?
                $this->object->data[$key0] : "";

            if (is_array($value)) {
                $values = implode('    ', $value);
                printf($formats[$k], $values);
            } else {
                printf($formats[$k], $value);

            printf("\n");
            }
            ++$k;
        }
        printf("\n");
    }

    public function print($text) {
        print ($text) ;
    }

    public function printf($format, ...$params) {
        printf ($format,...$params) ;
    }
}


