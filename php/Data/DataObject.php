<?php

namespace Gerco\Data;

use Gerco\Logger\Logger;

class DataObject
{
    public string $filename;
    public Logger $logger;
    public array $data;

    public function __construct()
    {
        $this->logger = new Logger($this);
    }

    public function setFileName($filename)
    {
        $this->filename = $filename;
    }

    public function readFile($filename)
    {
        $this->setFileName($filename);
        if (!file_exists($filename)) {
            printf("Error: cannot open %s file \n.", $filename);
            return $this;
        }

        $txt = file($this->filename);

        foreach ($txt as $line) {
            if (!preg_match('/^#/', $line)) {
                if (preg_match('/\S+/', $line)) {
                    $array = preg_split("/:/", $line);
                    $key = $array[0];
                    $value = $array[1];
                    $key = trim($key);
                    $value = trim($value);

                    if (preg_match("/(.*)Array/", $line, $matches)) {
                        $key = $matches[1];
                        $key = lcfirst($key);
                        $value = preg_replace('/\s\s+/', ' ', $value);
                        $valuesArray = explode(' ', $value);

                        if (count($valuesArray) > 0 && !empty($valuesArray[0]))
                            $this->data[$key] = $valuesArray;
                    } elseif (preg_match("/(.*)Date/", $line, $matches)) {
                        $key = $matches[1] . "Date";
                        $key = lcfirst($key);
                        $this->data[$key] = $value;
                        @list ($day, $month, $year) = explode('/', $value);
                        $date0 = @date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
                        $this->data["$key" . "Eng"] = $date0;
                    } else {
                        $key = lcfirst($key);
                        $this->data ["$key"] = $value;
                    }
                }
            }
        }

        return $this;
    }

    public function convertDateToEng($date): string
    {
        @list ($day, $month, $year) = explode('/', $date);
        return date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
    }
}


