<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Tools;

class ConfigArray
{

    static function searchAndReplace(
        array &$tmpConfigArray,
        string $search,
        string $replace,
    ): void {

        if (!is_array($tmpConfigArray)) { return; }
        foreach ($tmpConfigArray ?? [] as $configArrayName => $configArray) {
            if (is_array($configArray)) {
				self::searchAndReplace($tmpConfigArray[$configArrayName], $search, $replace);
			} else {
				if (is_string($configArray)) {
				    $pos = strpos($configArray, $search);
				    if ($pos !== false) {
                       $tmpConfigArray[$configArrayName] = 
                           substr($configArray, 0, $pos) . $replace.substr($configArray, strlen($search) + $pos);
				    }
				}
		    }
        }
    }

    static  function arrayMerge(
        array &$array1,
        array &$array2,
    ): void {
        foreach ($array2 as $array2Key => $array2Value) {
            if (!($array1[$array2Key] ?? false)) {
				$array1[$array2Key] = $array2Value;
            }
            if (is_array($array1[$array2Key])) {
                self::arrayMerge($array1[$array2Key], $array2Value);
            } else {
                $array1[$array2Key] = $array2Value;
            }
        }
	}

    public static function removeUsage(
        array &$array,
    ):array {
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

}