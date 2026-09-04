<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Tools;

/**
 * Migration:
 * - Target: ExtensionBuilder Core 1.x
 * - Status: legacy
 *
 * @extensionbuilderCoreMajorVersion 0
 * @extensionbuilderMigrationStatus legacy
 *
 * @since 0.12
 */
class ConfigArray
{
    /**
     * @since 0.12
     */
    public static function checkFieldsToBool(
        array &$fields,
        array &$bodyParams,
    ): void {
        foreach ($fields as $fieldKey => $fieldValue) {
            if ($fieldValue['type'] == 'check') {
                if ($fieldValue['array'] ?? false) {
                    // ToDo multi array
                    $bodyParams[$fieldValue['array'][0]][$fieldKey] = !empty($bodyParams[$fieldValue['array'][0]][$fieldKey]);
                } else {
                    $bodyParams[$fieldKey] = !empty($bodyParams[$fieldKey]);
                }
            }
        }
    }

    /**
     * @since 0.12
     */
    public static function searchAndReplace(
        array &$tmpConfigArray,
        string $search,
        string $replace,
    ): void {
        if (!is_array($tmpConfigArray)) {
            return;
        }

        foreach ($tmpConfigArray ?? [] as $configArrayName => $configArray) {
            if (is_array($configArray)) {
                self::searchAndReplace($tmpConfigArray[$configArrayName], $search, $replace);
            } else {
                if (is_string($configArray)) {
                    $pos = strpos($configArray, $search);
                    if ($pos !== false) {
                        $tmpConfigArray[$configArrayName]
                            = substr($configArray, 0, $pos) . $replace . substr($configArray, strlen($search) + $pos);
                    }
                }
            }
        }
    }

    /**
     * @since 0.12
     */
    public static function arrayMerge(
        array &$array1,
        array &$array2,
    ): void {
        foreach ($array2 as $array2Key => $array2Value) {
            if (!($array1[$array2Key] ?? false)) {

                $array1[$array2Key] = $array2Value;
            } else {
                if (is_array($array1[$array2Key])) {
                    if (is_array($array2Value)) {
                        self::arrayMerge($array1[$array2Key], $array2Value);
                    } else {
                        $array1[$array2Key] = $array2Value;
                    }
                } else {
                    $array1[$array2Key] = $array2Value;
                }
            }
        }
    }

    /**
     * @since 0.12
     */
    public static function removeUsageToRemove(
        array &$array,
    ): array {
        $return = [];

        foreach ($array ?? [] as $key => $value) {
            if (is_array($value)) {
                $return[$key] = self::removeUsage($value);
                if (empty($return[$key])) {
                    unset($return[$key]);
                }
            } else {
                if (($key === 'usageCounter') && ($value === 0)) {
                    $return[$key] = $value;
                }
            }
        }

        return $return;
    }

    /**
     * @since 0.12
     */
    public static function changeToBool(
        array &$array,
        int $depthCount = 0,
    ): void {
        if ($depthCount > 10) {
            return;
        }

        foreach ($array as $arrayKey => $arrayValue) {
            if (is_array($array[$arrayKey])) {
                $depthCount++;
                self::changeToBool($array[$arrayKey], $depthCount);
                $depthCount--;
            } else {
                if (is_string($arrayValue)) {
                    switch ($arrayValue) {
                        case 'true':
                            unset($array[$arrayKey]);
                            $array[$arrayKey] = true;
                            break;
                        case 'false':
                            unset($array[$arrayKey]);
                            $array[$arrayKey] = false;
                            break;
                    }
                }
            }
        }
    }

}
