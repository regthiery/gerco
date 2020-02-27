<?php
/**
 * Logger.php
 *
 * Classe traitant de l'affichage des données d'une entité de classe DataObject
 *
 * La classe Logger affiche les donnée d'un objet appartenant
 * à une classe DataObject.
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


namespace Gerco\Logger;

use Gerco\Data\DataObject;

/**
 * Class Logger
 *
 * @package Gerco\Logger
 */
class Logger
{
    /**
     * @var DataObject
     */
    public DataObject $object;

    /**
     * Logger constructor.
     *
     * @param DataObject $dataObject
     */
    public function __construct(DataObject $dataObject)
    {
        $this->object = $dataObject;
    }

    /**
     * @param mixed ...$keys
     */
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

    /**
     * @param $text
     */
    public function print($text)
    {
        print ($text);
    }

    /**
     * @param $format
     * @param mixed ...$params
     */
    public function printf($format, ...$params)
    {
        printf($format, ...$params);
    }
}


