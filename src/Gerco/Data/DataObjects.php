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
use Symfony\Component\Yaml\Yaml;

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
     * @var string
     */
    public string $filename;

    /**
     * Tableau associatif contenant les données
     * telles qu'elles sont lues dans le fichier
     *
     * @var array
     */
    public array $objects;

    /**
     * Tableau associatif contenant les données
     * filtrées et triées
     *
     * @var array
     */
    public array $filteredObjects;

    /**
     * Nombre d'items dans $objects
     *
     * @var int
     */
    public int $objectsCount;

    /**
     * Nombre d'items dans $filteredObjects
     *
     * @var int
     */
    public int $filteredCount;

    /**
     * Nom de la clé primaire
     *
     * @var string
     */
    public string $primaryKey;

    /**
     * Tableau simple stockant les sommes calculées par colonne
     *
     * @var array
     */
    public array $sums;

    /**
     * Tableau simple contenant les noms des clés
     *
     * @var array
     */
    public array $objectsKeys;

    /**
     * Utilisé pour afficher les données dans un terminal
     *
     * @var DataLogger
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
     * @param string $primaryKey Nom de la clé primaire
     *
     * @return DataObjects
     */
    final public function setPrimaryKey(string $primaryKey) : DataObjects
    {
        $this->primaryKey = $primaryKey;
        return $this ;
    }

    /**
     * Getter function
     *
     * @return array
     */
    final public function getObjects() : array
    {
        return $this->objects;
    }

    /**
     * Getter function
     *
     * @return array
     */
    final public function getFiltered() : array
    {
        return $this->filteredObjects;
    }

    /**
     * Retourne l'item indiquée par $key
     *
     * @param string $key Clé utilisée pour sélectionner un item
     *
     * @return mixed|null
     */
    final public function getObjectWithKey(string $key) : ?array
    {
        if (array_key_exists($key, $this->objects)) {
            return $this->objects[$key];
        } else {
            $className = get_class($this);
            throw new \Error("Key $key does not exist in $className objects.") ;
            return null;
        }
    }

    /**
     * Sélectionne un item dans $filteredObjects
     *
     * @param string $key Clé utilisée pour sélectionner l'item
     *
     * @return mixed|null
     */
    final public function getFilteredWithKey(string $key) : ?array
    {
        if (array_key_exists($key, $this->filteredObjects)) {
            return $this->filteredObjects[$key];
        } else {
            $className = get_class($this);
            throw new \Error ("Key $key does not exist in $className:filteredObjects.") ;
            return null;
        }
    }

    /**
     * Récupère l'item identifié par sa valeur $value0 pour la clé $key0
     *
     * @param string $key0   la clé
     * @param string $value0 la valeur correspondante recherchée
     *
     * @return mixed|null
     */
    final public function getObjectWithKeyValue(string $key0, string $value0) : array
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
     * Spécifie le nom du fichier texte
     *
     * @param string $filename Nom du fichier texte
     *                       
     * @return DataObjects
     */
    final public function setFileName(string $filename) : DataObjects
    {
        $this->filename = $filename;
        return $this ;
    }

    final public function readYamlFile (string $filename) : DataObjects
    {
        $this->objects = Yaml::parseFile($filename) ;
        $this->filteredObjects = $this->objects;
        $this->objectsCount = count($this->objects);
        $this->filteredCount = $this->objectsCount;

        $this->objectsKeys = array_keys($this->objects);

        $this->logger->printf("Read file %s\n", $filename);
        $this->logger->displayCount();

        return $this ;
    }

    /**
     * Lit les données d'un fichier texte
     *
     * @param string $filename Nom du fichier texte
     *
     * @return $this
     */
    final public function readFile(string $filename) : DataObjects
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
     * @param DataObjects $dataObject L'autre entité DataObjects
     *                                avec laquelle on fait une jointure
     * @param string      $primaryKey Spécifie la clé qui sera utilisée pour
     *                                relier un item avec un autre item dans
     *                                $dataObject
     * @param string      $objectKey  Nom de la clé qui sera
     *                                utilisée pour copier l'autre
     *                                item dans l'item courant
     *
     * @return DataObjects
     */
    final public function joinWithData(DataObjects $dataObject, string $primaryKey,
        string $objectKey
    ) : DataObjects {
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
        return $this ;
    }

    /**
     * Déselectionne tous les items
     *
     * @return $this
     */
    final public function unselect() : DataObjects
    {
        $this->filteredObjects = array();
        $this->filteredCount = 0;
        return $this;
    }

    /**
     * Sélectionne tous les items de $objects
     * et les stocke dans $filteredObjects
     *
     * @return DataObjects
     */
    final public function selectAll() : DataObjects
    {
        $this->filteredObjects = $this->objects;
        $this->filteredCount = $this->objectsCount;
        return $this;
    }

    /**
     * Retourne la valeur stockée dans un item pour une clé donnée
     *
     * @param array  $object Item
     * @param string $key    Clé dans Item
     *
     * @return mixed|string|null
     */
    final public function getKeyValue(array $object, string $key)
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
     * @param string $operator Peut être and ou or
     * @param string $key0     Clé utilisée pour sélectionner un item
     * @param string $value0   Valeur utilisée pour sélectionner un item
     *
     * @return $this
     */
    final public function selectByKey(string $operator, string $key0, string $value0)
        : DataObjects
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
     * @param string $operator  Peut être and ou or
     * @param string $key0      Clé spécifiant une date
     * @param string $startDate Date de départ
     * @param string $endDate   Date d'arrivée
     *
     * @return $this
     */
    final public function selectBetweenDates(string $operator, string $key0,
        string $startDate, string $endDate
    ) : DataObjects {
        $startDate = strtotime($startDate) ;
        $endDate = strtotime($endDate) ;

        $array = array_filter(
            (!strcmp($operator, "and")) ? $this->filteredObjects : $this->objects,
            function ($item) use ($key0, $startDate, $endDate) {
                $value = $this->getKeyValue($item, $key0 );

                if ($startDate > $value) {
                    return false;
                }
                if ($endDate < $value) {
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
     * @param string $operator Peut être and ou or
     * @param string $key0     Clé sur laquelle l'expression régulière sera
     *                         appliquée
     * @param string $pattern  Masque de l'expression
     *                         régulière
     *
     * @return DataObjects
     */
    final public function selectByKeyExt(string $operator, string $key0,
        string $pattern
    ) : DataObjects {
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
                = array_diff_key($this->filteredObjects, $array);
        } else {
            $this->filteredObjects = $array;
        }

        $this->filteredCount = count($this->filteredObjects);
        return $this;
    }

    /**
     * Sélectionne des items pour lesquelles une clé particulière est définie
     *
     * @param string $operator Peut être and ou or
     * @param string $key0     Clé
     *                         utilisée
     *
     * @return DataObjects
     */
    final public function selectDefinedKey(string $operator, string $key0)
        : DataObjects
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
     * @param string $key0 La clé de triage
     *
     * @return DataObjects
     */
    final public function sortNumeric(string $key0) : DataObjects
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
     * @param string $key0 La clé de triage
     *
     * @return DataObjects
     */
    final public function sortByDate(string $key0) : DataObjects
    {
        print("Sort by date\n");
        $key = $key0 ;
        uasort(
            $this->filteredObjects,
            function ($a, $b) use ($key) {
                if (!array_key_exists($key, $a)) {
                    return -1;
                }
                if (!array_key_exists($key, $b)) {
                    return 1;
                }
                if ($a[$key] < $b[$key]) {
                    return 1;
                } elseif ($a[$key] > $b[$key]) {
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
     * @param mixed ...$keys Clés des colonnes
     *
     * @return DataObjects
     */
    final public function sumKeys(...$keys) : DataObjects
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
     * @param string $key0 Clé de la colonne
     *
     * @return array
     */
    final public function getSum(string $key0) : array
    {
        return $this->sums[$key0];
    }
}
