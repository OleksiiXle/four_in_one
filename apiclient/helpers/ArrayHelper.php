<?php

namespace helpers;

use Closure;

/**
 * Class ArrayHelper
 * @package helpers
 */
class ArrayHelper
{
    /**
     * Map array
     *
     * @param $input
     * @param $from
     * @param $to
     * @return array
     */
    public static function map($input, $from, $to = null)
    {
        $result = [];
        if ($input != null) {
            foreach ($input as $element) {
                if (is_object($element)) {
                    $element = self::toArray($element);
                }
                if ($to == null) {
                    $result[] = $element[$from];
                } else {
                    $result[$element[$from]] = $element[$to];
                }
            }
        }
        return $result;
    }


    /**
     * Map array with closure,
     * @param array $input
     * @param Closure $from
     * @param Closure $to
     * @return array
     */
    public static function mapWithClosure($input, Closure $from, Closure $to = null)
    {
        $result = [];
        if ($input != null) {
            foreach ($input as $element) {
                if (is_object($element)) {
                    $element = self::toArray($element);
                }

                $fromValue = $from($element);
                $toValue = $to($element);
                if ($fromValue === false) {
                    continue;
                }

                if ($to == null) {
                    $result[] = $fromValue;
                } else {
                    $result[$fromValue] = $toValue;
                }
            }
        }
        return $result;
    }

    /**
     * @param array $input
     * @param string $hashKey
     * @param bool $multiRows For add more items for one index
     * @return array|bool
     */
    public static function hash($input, $hashKey, $multiRows = false)
    {
        if (!is_array($input)) {
            return false;
        }
        $result = [];

        foreach ($input as $key => $value) {
            foreach ($value as $key2 => $value2) {
                if ($key2 == $hashKey) {
                    if ($multiRows) {
                        $result[$value2][] = $value;
                    } else {
                        $result[$value2] = $value;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Convert object to array
     *
     * @param $object
     * @param $skip
     * @return array
     */
    public static function toArray($object, $skip = [])
    {
        $result = [];
        if (is_array($object)) {
            foreach ($object as $element) {
                $item = self::toArray($element, $skip);
                if (!empty($skip)) {
                    foreach ($item as $key => $value) {
                        if (in_array($key, $skip)) {
                            unset($item[$key]);
                        }
                    }
                }
                $result[] = $item;
            }
        } elseif (is_object($object)) {
            $result = get_object_vars($object);
            if (method_exists($object, 'toArrayExtraAttributes') and !empty($object->toArrayExtraAttributes())) {
                foreach ($object->toArrayExtraAttributes() as $attribute) {
                    $result[$attribute] = $object->$attribute;
                }
            }
            if (!empty($skip)) {
                foreach ($result as $key => $value) {
                    if (in_array($key, $skip)) {
                        unset($result[$key]);
                    }
                }
            }
        } else {
            $result = [];
        }
        return $result;
    }

    public static function merge($a, $b)
    {
        $args = array_filter(func_get_args(), function ($val) {
            return is_array($val) || (is_object($val) && ($val instanceof \Traversable));
        });

        $result = array_shift($args);

        if ($result instanceof \Generator || $result instanceof \ArrayIterator) {
            $result = iterator_to_array($result);
        }

        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_integer($k)) {
                    if (isset($result[$k])) {
                        $result[] = $v;
                    } else {
                        $result[$k] = $v;
                    }
                } elseif (is_array($v) && isset($result[$k]) && is_array($result[$k])) {
                    $result[$k] = self::merge($result[$k], $v);
                } else {
                    $result[$k] = $v;
                }
            }
        }
        return $result;
    }

    /**
     * Get diff of two array
     * @param array $array1
     * @param array $array2
     * @param bool $strict Use strict comparison
     * @param array $rules Attributes rules
     * @return array
     */
    public static function diff(array $array1, array $array2, $strict = true, array $rules = [])
    {
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) or !is_array($array2[$key])) {
                    $result[$key] = $value;
                } else {
                    $diff = self::diff($value, $array2[$key], $strict);
                    if ($diff !== []) {
                        $result[$key] = $diff;
                    }
                }
            } elseif (isset($array2[$key])) {
                if ($strict and $array2[$key] !== $value) {
                    $result[$key] = $value;
                } else {
                    $type = isset($rules[$key]['type']) ? $rules[$key]['type'] : null;
                    if (!CompareHelper::compare($array2[$key], $value, $type)) {
                        $result[$key] = $value;
                    }
                }
            } else {
                $result[$key] = $value;
            }
        }

        return !isset($result) ? [] : $result;
    }

    /**
     * Explode string to array
     *
     * @param $data
     * @param string $delimiter
     * @param bool $combine
     * @return array
     */
    public static function explode($data, $delimiter = ',', $combine = false)
    {
        if (is_array($data)) {
            $array = $data;
        } else {
            if (strpos($data, $delimiter) !== false) {
                $array = explode($delimiter, $data);
                $array = array_map('trim', $array);
            } else {
                $array = [$data];
            }
        }

        if ($combine == true) {
            $array = array_combine($array, $array);
        }

        return $array;
    }

    /**
     * Check if array is associative.
     *
     * @param $array
     * @return bool
     */
    public static function isAssociative($array)
    {
        if (!is_array($array)) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    public static function mikrotikCompare($n, $e, &$ret = [])
    {
        if (!is_array($e)) {
            return false;
        }
        if (!is_array($n)) {
            return false;
        }

        $ret = array_diff_assoc($n, $e);

        foreach ($ret as $k => $v) {
            if (empty($v) && empty($e[$k])) {
                unset($ret[$k]);
            }
        }

        if (!count($ret)) {
            return true;
        }

//        if (count($ret) == 1 and (isset($ret['queue']))) {
//            return true;
//        }
        // this hack should be live...
//        if (count($ret) == 1 and (isset($ret['!src-mac-address']) && !isset($e['src-mac-address']))) {
//            return true;
//        }

        return false;
    }

    public static function mikrotikSimpleCompare($n, $e)
    {
        $ret = array_diff_assoc($n, $e);
        if (!count($ret)) {
            return true;
        }
        return false;
    }

    public static function safeExplode($delimiter, $string, $count = 2)
    {
        return array_pad(explode($delimiter, $string, $count), $count, null);
    }

    public static function inMultiarray($elem, $array)
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                if (self::inMultiarray($elem, $value)) {
                    return true;
                }
            } else {
                if ($elem == $value) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function findKey($array, $keySearch)
    {
        foreach ($array as $key => $item) {
            if ($key == $keySearch) {
                return true;
            } else {
                if (is_array($item) && static::findKey($item, $keySearch)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $array
     * @param $element
     * @return array
     */
    public static function removeElement($array, $element)
    {
        if (!is_array($array)) {
            return [];
        }

        $key = array_search($element, $array);
        if ($key !== false) {
            unset($array[$key]);
        }
        return $array;
    }

    /**
     * Strpos for array needles
     *
     * @param $haystack
     * @param $needle
     * @return bool|int
     */
    public static function striposArray($haystack, $needle)
    {
        if (!is_array($needle)) {
            $needle = [$needle];
        }
        foreach ($needle as $what) {
            if (($pos = stripos($haystack, $what)) !== false) {
                return $pos;
            }
        }
        return false;
    }

    /**
     * Find $key_search in multilevel $array
     *
     * @param string $key_search
     * @param array $array
     * @return bool|array
     */
    public static function checkKeyInMultilevelArray($key_search, $array)
    {
        if (count($array) < 1) {
            return false;
        }
        $result = false;
        foreach ($array as $key => $value) {
            if (strcmp($key, $key_search) === 0) {
                return true;
            } elseif (is_array($value)) {
                $result = static::checkKeyInMultilevelArray($key_search, $value);
                if ($result == true) {
                    return true;
                }
            }
        }
        return $result;
    }

    /**
     * Get first found value from multilevel array
     * @param string $key
     * @param array $array
     * @param null $default
     *
     * @return null
     */
    public static function getValueFromMultiLevelArray($key, array $array, $default = null)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $index => $value) {
            if ($index === $key) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Recursively remove $key from multidimensional array
     * @param string $key
     * @param array $array
     *
     * @return array
     */
    public static function removeKeyRecursive($key, array $array)
    {
        if (array_key_exists($key, $array)) {
            unset($array[$key]);
        }

        foreach ($array as $index => $value) {
            if (is_array($value)) {
                $array[$index] = static::removeKeyRecursive($key, $value);
            }
        }

        return $array;
    }

    /**
     * Clear $array from empty elements and elements width spaces
     * trim elements
     * @param $array
     * @return array
     */
    public static function clearFromEmpty($array)
    {
        foreach ($array as $key => $element) {
            $array[$key] = trim($array[$key]);
            if (empty($array[$key]) && $array[$key] != '0') {
                unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * Convert php array to INI string
     * @param array $a
     * @param array $parent
     * @return string
     */
    public static function array2ini(array $a, array $parent = [])
    {
        $out = '';
        foreach ($a as $k => $v) {
            if (is_array($v)) {
                $sec = array_merge((array)$parent, (array)$k);
                $out .= '[' . join('.', $sec) . ']' . PHP_EOL;
                $out .= static::array2ini($v, $sec);
            } else {
                if (preg_match('/[\W]+/', $v)) {
                    $out .= "$k=\"$v\"" . PHP_EOL;
                } else {
                    $out .= "$k=$v" . PHP_EOL;
                }
            }
        }
        return $out;
    }

    /**
     * Require external (not encoded) php array file.
     * Returned null when decode failed.
     * @param string $path - path to file
     * @return array|null
     */
    public static function includeExternalArray($path)
    {
        $command = 'php -r \'echo json_encode(require("' . $path . '"));\'';
        $response = shell_exec($command);

        return json_decode($response, 1);
    }

    /**
     * Convert string to array to delimiter
     * @param string $string Data
     * @param string $delimiter Delimiter
     * @return array
     */
    public static function convertToArrayByDelimiter($string, $delimiter = ',')
    {
        if (strpos($string, $delimiter) !== false) {
            $result = self::explode($string, $delimiter);
        } else {
            $result = [$string];
        }
        return static::cleanUpArray($result);
    }

    /**
     * Delete all empty elements, delete spaces (trim), resort
     * @param $array
     * @return array
     */
    public static function cleanUpArray($array)
    {
        return array_values(array_map("trim", array_filter($array, function ($value) {
            return trim($value) !== '';
        })));
    }

    /**
     * Remove empty values for model data.
     *
     * @param array $data
     * @return array
     * @throws \Exception if data is not array
     */
    public static function deleteEmptyValuesFromModelData($data)
    {
        if (!is_array($data)) {
            throw new \Exception('Data should be array');
        }

        foreach ($data as $key => $value) {
            if ($key == 'additional_attributes' && !empty($value)) {
                foreach ($value as $keyA => $valueA) {
                    if ($valueA === '') {
                        unset($data[$key][$keyA]);
                    }
                }
            }
            if ($value === '') {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Concat two arrays
     * @param array $a
     * @param array $b
     * @return array
     */
    public static function concatArrays($a, $b)
    {
        foreach ($b as $item) {
            $a[] = $item;
        }

        return $a;
    }

    /**
     * Change array length.
     *
     * @param array $data
     * @param int $targetLength
     * @param string $symbolToInsert
     * @return array
     * @example
     * normalizeArrayLength(['a', 'b', 'c'], 5)
     * // Output: ['a', 'b', 'c', '', '']
     *
     * normalizeArrayLength(['a', 'b', 'c'], 2)
     * // Output: ['a', 'b']
     *
     */
    public static function normalizeArrayLength($data, $targetLength, $symbolToInsert = '')
    {
        $countData = count($data);

        if ($targetLength > $countData) {
            return array_pad($data, $targetLength, $symbolToInsert);
        } elseif ($countData > $targetLength) {
            return array_slice($data, 0, $targetLength);
        }

        return $data;
    }

    public static function safeArrayCombine($columns, $data)
    {
        $data = static::normalizeArrayLength($data, count($columns));
        return array_combine($columns, $data);
    }

    /**
     * Convert array of ranges to array of numbers.
     * @param array $ranges
     * @return array
     * @example
     * Input:
     * [[1, 5], 8, [10, 13]]
     *
     * Output:
     * [1, 2, 3, 4, 5, 8, 10, 11, 12, 13]
     */
    public static function rangesToNumbers($ranges)
    {
        $numbers = [];

        foreach ($ranges as $item) {
            if (is_array($item)) {
                for ($i = $item[0]; $i <= $item[1]; $i++) {
                    $numbers[] = $i;
                }
            } else {
                $numbers[] = $item;
            }
        }

        return $numbers;
    }

    /**
     * @param array $array
     * @param string $prefix
     * @return array|false
     */
    public static function addPrefixToKey($array, $prefix)
    {
        return array_combine(
            array_map(function ($k) use ($prefix) {
                return $prefix . $k;
            }, array_keys($array)),
            $array
        );
    }

    /**
     * @param array $array
     * @return array
     */
    public static function formatForSelect2($array)
    {
        $toReturn = [];

        foreach ($array as $key => $value) {
            $toReturn[] = [
                'id' => $key,
                'text' => $value,
            ];
        }

        return $toReturn;
    }

    /**
     * Check if array is multidimensional
     * @param $array
     * @return bool
     */
    public static function isMultidimensional($array)
    {
        return count($array) === count(array_filter($array, 'is_array'));
    }

    /**
     * Sort objects array by field
     * @param array $objects
     * @param string $field
     */
    public static function usortObjectsByField(&$objects, $field)
    {
        usort($objects, function ($a, $b) use ($field) {
            if ($a->$field == $b->$field) {
                return 0;
            }
            return ($a->$field < $b->$field) ? 1 : -1;
        });
    }

    /**
     * Check if array is empty. Check all arrays elements if it is empty.
     * Works for multidimensional arrays
     * @param array $array
     * @return bool
     */
    public static function checkIfEmptyArray($array)
    {
        $empty = true;
        if (is_array($array)) {
            foreach ($array as $value) {
                if (!static::checkIfEmptyArray($value)) {
                    $empty = false;
                }
            }
        } elseif (!empty($array)) {
            $empty = false;
        }

        return $empty;
    }
}
