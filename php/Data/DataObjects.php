<?php
/**
 * DataObjects.php
 *
 * Classe encapsulant une liste de tableaux associatifs
 *
 * La classe DataObjects encapsule une liste de tableaux associatifs.
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

use Gerco\Logger\DataLogger;

/**
 * Class DataObjects
 *
 * @category Gerco
 * @package  Gerco
 * @author   R. Thiéry <regthiery@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://localhost
 */
class DataObjects
{
    /**
     * Nom du fichier texte
     *
     * @var string $filename
     */
    public string $filename;
    /**
     * Tableau associatif contenant les données
     * telles qu'elles sont lues dans le fichier
     *
     * @var array $objects
     */
    public array $objects;
    /**
     * Tableau associatif contenant les données
     * filtrées et triées
     *
     * @var array $filteredObjects
     */
    public array $filteredObjects;
    /**
     * Nombre d'items dans $objects
     *
     * @var int $objectsCount
     */
    public int $objectsCount;
    /**
     * Nombre d'items dans $filteredObjects
     *
     * @var int $filteredCount
     */
    public int $filteredCount;
    /**
     * Nom de la clé primaire
     *
     * @var string $primaryKey
     */
    public string $primaryKey;
    /**
     * Tableau simple stockant les sommes calculées par colonne
     *
     * @var array $sums
     */
    public array $sums;
    /**
     * Tableau simple contenant les noms des clés
     *
     * @var array $objectsKeys
     */
    public array $objectsKeys;
    /**
     * Utilisé pour afficher les données dans un terminal
     *
     * @var DataLogger $logger
     */
    public DataLogger $logger;

    /**
     * DataObjects constructor.
     */
    public function __construct()
    {
        $this->logger = new DataLogger($this);
    }

    /**
     * Setter function
     *
     * @param $primaryKey
     * Nom de la clé primaire
     *
     * @return DataObjects
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
        return $this ;
    }

    /**
     * Getter function
     *
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Getter function
     *
     * @return array
     */
    public function getFiltered()
    {
        return $this->filteredObjects;
    }

    /**
     * Retourne l'item indiquée par $key
     *
     * @param $key
     * Clé utilisée pour sélectionner un item
     *
     * @return mixed|null
     */
    public function getObjectWithKey($key)
    {
        if (array_key_exists($key, $this->objects)) {
            return $this->objects[$key];
        } else {
            $className = get_class($this);
            echo "Key $key does not exist in $className:objects.\n";
            return null;
        }
    }

    /**
     * Sélectionne un item dans $filteredObjects
     *
     * @param $key
     * Clé utilisée pour sélectionner l'item
     *
     * @return mixed|null
     */
    public function getFilteredWithKey($key)
    {
        if (array_key_exists($key, $this->filteredObjects)) {
            return $this->filteredObjects[$key];
        } else {
            $className = get_class($this);
            echo "Key $key does not exist in $className:filteredObjects.\n";
            return null;
        }
    }

    /**
     * @param $key0
     * @param $value0
     *
     * @return mixed|null
     */
    public function getObjectWithKeyValue($key0, $value0)
    {
        foreach ($this->objects as $key => $item) {
            if (array_key_exists($key0, $item)) {
                $value = $item[$key0];
                if (!strcmp($value, $value0)) {
                    return $item;
                }
            }
        }
        return null;
    }


    /**
     * Convertit une date du format français au format anglais
     *
     * @param $date
     * La date à convertir
     *
     * @return string
     */
    public function convertDateToEng($date): string
    {
        @list($day, $month, $year) = explode('/', $date);
        return date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
    }


    /**
     * Spécifie le nom du fichier texte
     *
     * @param $filename
     * Nom du fichier texte
     */
    public function setFileName($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Lit les données d'un fichier texte
     *
     * @param $filename
     * Nom du fichier texte
     *
     * @return $this
     */
    public function readFile($filename)
    {
        $new = 0;
        $this->objects = array();

        $primaryLabel = ucfirst($this->primaryKey);
        $primaryValue = null;

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

                    if ($key === $primaryLabel) {
                        $new = 1;
                        $primaryValue = $value;
                        $object = array($this->primaryKey => $value);
                    } elseif (preg_match("/(.*)Array/", $line, $matches)) {
                        $key = $matches[1];
                        $key = lcfirst($key);
                        $value = preg_replace('/\s\s+/', ' ', $value);
                        $valuesArray = explode(' ', $value);

                        if (count($valuesArray) > 0 && !empty($valuesArray[0])) {
                            $object[$key] = $valuesArray;
                        }
                    } elseif (preg_match("/(.*)Date/", $line, $matches)) {
                        $key = $matches[1] . "Date";
                        $key = lcfirst($key);
                        $object[$key] = $value;
                        @list($day, $month, $year) = explode('/', $value);
                        $date0
                            = @date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
                        $object["$key" . "Eng"] = $date0;
                    } else {
                        $key = lcfirst($key);
                        $object ["$key"] = $value;
                    }
                } elseif ($new == 1) {
                    if (isset($primaryValue) && isset($object)) {
                        $this->objects [$primaryValue] = $object;
                    } else {
                        print("Erreur de lecture du fichier $filename:\\
                         aucune valeur définie pour la clé $primaryLabel\n");
                    }

                    $new = 0;
                    unset($primaryValue);
                }
            }
        }

        $this->filteredObjects = $this->objects;
        $this->objectsCount = count($this->objects);

        $this->objectsKeys = array_keys($this->objects);

        $this->logger->printf("Read file %s\n", $filename);
        $this->logger->displayCount();
        return $this;
    }

    /**
     * Réalise une jointure avec un autre DataObjects
     *
     * @param DataObjects $dataObject
     * Autre entité de classe DataObjects
     *
     * @param $primaryKey
     * Spécifie la clé qui sera utilisée pour relier
     * un item avec un autre item dans $dataObject
     *
     * @param $objectKey
     * Nom de la clé qui sera utilisée pour copier
     * l'autre item dans l'item courant
     */
    public function joinWithData(DataObjects $dataObject, $primaryKey, $objectKey)
    {
        $objectsData = $dataObject->getObjects();

        foreach ($this->objects as $key => $item) {
            if (array_key_exists($primaryKey, $item)) {
                $joinKey = $item[$primaryKey];
                if (!empty($joinKey)) {
                    if (array_key_exists($joinKey, $objectsData)) {
                        $this->objects[$key][$objectKey] = $objectsData[$joinKey];
                    }
                }
            }
        }
    }

    /**
     * Déselectionne tous les items
     *
     * @return $this
     */
    public function unselect()
    {
        $this->filteredObjects = array();
        $this->filteredCount = 0;
        return $this;
    }

    /**
     * Sélectionne tous les items de $objects
     * et les stocke dans $filteredObjects
     *
     * @return $this
     */
    public function selectAll()
    {
        $this->filteredObjects = $this->objects;
        $this->filteredCount = $this->objectsCount;
        return $this;
    }

    /**
     * Retourne la valeur stockée dans un item pour une clé donnée
     *
     * @param $object
     * Item
     *
     * @param $key
     * Clé
     *
     * @return mixed|string|null
     */
    public function getKeyValue($object, $key)
    {
        $keys = preg_split("/:/", $key);
        $n = count($keys);
        if ($n == 1) {
            if (!(array_key_exists($key, $object))) {
                return null;
            }
            $value = $object[$key];
        } elseif ($n == 2) {
            $key0 = $keys [0];
            $key1 = $keys [1];
            if (!array_key_exists($key0, $object)) {
                return null;
            }
            if (!array_key_exists($key1, $object[$key0])) {
                return null;
            }
            $value = $object[$key0][$key1];
        } elseif ($n == 3) {
            $key0 = $keys [0];
            $key1 = $keys [1];
            $key2 = $keys [2];
            if (!array_key_exists($key0, $object)) {
                return null;
            }
            if (!array_key_exists($key1, $object[$key0])) {
                return null;
            }
            if (!array_key_exists($key2, $object[$key0][$key1])) {
                return null;
            }
            $value = $object[$key0][$key1][$key2];
        } else {
            $value = '';
        }
        return $value;
    }

    /**
     * Sélectionne des items dans objects et les range dans $filteredObjects
     *
     * @param $operator
     * Peut être and ou or
     *
     * @param $key0
     * Clé utilisée pour sélectionner un item
     *
     * @param $value0
     * Valeur utilisée pour sélectionner un item
     *
     * @return $this
     */
    public function selectByKey($operator, $key0, $value0)
    {
        $array = array_filter(
            (!strcmp($operator, "and")) ? $this->filteredObjects : $this->objects,
            function ($item) use ($key0, $value0) {
                $value = $this->getKeyValue($item, $key0);

                return (!strcmp($value, $value0));
            }
        );
        if (!strcmp($operator, "or")) {
            $this->filteredObjects = array_merge($this->filteredObjects, $array);
        } else {
            $this->filteredObjects = $array;
        }

        $this->filteredCount = count($this->filteredObjects);
        return $this;
    }

    /**
     * Sélectionne des items ayant une date, spécifiée par $key0
     *
     * @param $operator
     * Peut être and ou or
     *
     * @param $key0
     * Clé spécifiant une date
     *
     * @param $startDate
     * Date de départ
     *
     * @param $endDate
     * Date d'arrivée
     *
     * @return $this
     */
    public function selectBetweenDates($operator, $key0, $startDate, $endDate)
    {
        $startDateEng = $this->convertDateToEng($startDate);
        $endDateEng = $this->convertDateToEng($endDate);
        $array = array_filter(
            (!strcmp($operator, "and")) ? $this->filteredObjects : $this->objects,
            function ($item) use ($key0, $startDateEng, $endDateEng) {
                $value = $this->getKeyValue($item, $key0 . "Eng");

                $res = strcmp($startDateEng, $value);
                if ($res > 0) {
                    return false;
                }
                $res = strcmp($endDateEng, $value);
                if ($res < 0) {
                    return false;
                }

                return (true);
            }
        );
        if (!strcmp($operator, "or")) {
            $this->filteredObjects = array_merge($this->filteredObjects, $array);
        } else {
            $this->filteredObjects = $array;
        }

        $this->filteredCount = count($this->filteredObjects);
        return $this;
    }

    /**
     * Sélectionne des items à partir d'une expression régulière
     *
     * @param $operator
     * Peut être and ou or
     *
     * @param $key0
     * Clé sur laquelle l'expression régulière sera appliquée
     *
     * @param $pattern
     * Masque de l'expression régulière
     *
     * @return $this
     */
    public function selectByKeyExt($operator, $key0, $pattern)
    {
        $array = array_filter(
            (!strcmp($operator, "and")) ? $this->filteredObjects : $this->objects,
            function ($item) use ($key0, $pattern) {
                $value = $this->getKeyValue($item, $key0);

                if ($value == null) {
                    return false;
                }

                //return ( preg_match($pattern,$value) ) ;
                return preg_match($pattern, $value);
            }
        );
        if (!strcmp($operator, "or")) {
            $this->filteredObjects = array_merge($this->filteredObjects, $array);
        } elseif ($operator === 'andNot') {
            $this->filteredObjects
                = array_diff_assoc($this->filteredObjects, $array);
        } else {
            $this->filteredObjects = $array;
        }

        $this->filteredCount = count($this->filteredObjects);
        return $this;
    }

    /**
     * Sélectionne des items pour lesquelles une clé particulière est définie
     *
     * @param $operator
     * Peut être and ou or
     *
     * @param $key0
     * Clé utilisée
     *
     * @return $this
     */
    public function selectDefinedKey($operator, $key0)
    {
        $array = array_filter(
            (!strcmp($operator, "and")) ? $this->filteredObjects : $this->objects,
            function ($item) use ($key0) {
                if (!array_key_exists($key0, $item)) {
                    return false;
                }
                $value = $item[$key0];
                $checkNonEmpty = (empty($value) == false);
                return ($checkNonEmpty);
            }
        );

        if (!strcmp($operator, "or")) {
            $this->filteredObjects = array_merge($this->filteredObjects, $array);
        } else {
            $this->filteredObjects = $array;
        }

        $this->filteredCount = count($this->filteredObjects);

        return $this;
    }

    /**
     * Trie le tableau $filteredObjects
     *
     * @param $key0
     * Clé de triage
     *
     * @return $this
     */
    public function sortNumeric($key0)
    {
        uasort(
            $this->filteredObjects,
            function ($a, $b) use ($key0) {
                if (!array_key_exists($key0, $a)) {
                    return -1;
                }
                if (!array_key_exists($key0, $b)) {
                    return 1;
                }
                if ($a[$key0] < $b[$key0]) {
                    return -1;
                }
                if ($a[$key0] == $b[$key0]) {
                    return 0;
                }
                return 1;
            }
        );
        return $this;
    }

    /**
     * Trie le tableau $filteredObjects
     *
     * @param $key0
     * Clé de triage
     *
     * @return $this
     */
    public function sortByDate($key0)
    {
        print("Sort by date\n");
        $key = $key0 . "Eng";
        uasort(
            $this->filteredObjects,
            function ($a, $b) use ($key) {
                if (!array_key_exists($key, $a)) {
                    return -1;
                }
                if (!array_key_exists($key, $b)) {
                    return 1;
                }
                $ta = strtotime($a[$key]);
                $tb = strtotime($b[$key]);
                if ($ta < $tb) {
                    return 1;
                } elseif ($ta > $tb) {
                    return -1;
                } else {
                    return 0;
                }
            }
        );
        return $this;
    }

    /**
     * Calcule la somme des valeurs contenues dans tous les items
     * de filteredObjects dans les colonnes indiquées par $keys
     *
     * @param mixed ...$keys
     * Clés des colonnes
     *
     * @return $this
     */
    public function sumKeys(...$keys)
    {
        $this->sums = array();

        foreach ($keys as $key0) {
            $this->sums[$key0] = 0;
            foreach ($this->filteredObjects as $key => $object) {
                $value = $this->getKeyValue($object, $key0);
                if ($value != null) {
                    $this->sums[$key0] += $value;
                }
            }
        }
        return $this;
    }

    /**
     * Retourne la somme des valeurs d'une colonne pour $filteredObjects
     *
     * @param $key0
     * Clé de la colonne
     *
     * @return mixed
     */
    public function getSum($key0)
    {
        return $this->sums[$key0];
    }
}
