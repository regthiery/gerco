<?php
/**
 * DataObject.php
 *
 * Classe encapsulant un tableau associatif
 *
 * La classe DataObject encapsule un tableau associatif.
 * Les données (clé => valeur) sont lues dans un fichier texte.
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

use Gerco\Logger\Logger;

/**
 * Class DataObject
 *
 * @category DataObject
 * @package  DataObject
 * @author   R. Thiéry <regthiery@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://localhost
 */
class DataObject
{
    public string $filename;
    public Logger $logger;
    public array $data;

    /**
     * DataObject constructor.
     */
    public function __construct()
    {
        $this->logger = new Logger($this);
    }

    /**
     * Setter function
     *
     * @param $filename
     *     Fichier texte à lire
     *
     * @return $this
     */
    public function setFileName($filename)
    {
        $this->filename = $filename;
        return $this ;
    }

    /**
     * This function reads (key=>value) data and populates the associative array.
     *
     * @param string $filename
     *  Fichier texte à lire
     *
     * @return $this
     */
    public function readFile(string $filename)
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

                        if (count($valuesArray) > 0 && !empty($valuesArray[0])) {
                            $this->data[$key] = $valuesArray;
                        }
                    } elseif (preg_match("/(.*)Date/", $line, $matches)) {
                        $key = $matches[1] . "Date";
                        $key = lcfirst($key);
                        $this->data[$key] = $value;
                        @list($day, $month, $year) = explode('/', $value);
                        $date0 = @date(
                            'Y-m-d',
                            mktime(
                                0,
                                0,
                                0,
                                $month,
                                $day,
                                $year
                            )
                        );
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

    /**
     * Convertit une date du format français DD/MM/YYYY au format anglais YYYY-MM-DD
     *
     * @param string $date
     *   date à convertir
     *
     * @return string
     */
    public function convertDateToEng(string $date): string
    {
        @list($day, $month, $year) = explode('/', $date);
        return date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
    }
}
