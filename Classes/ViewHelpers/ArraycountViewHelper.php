<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

// https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Fluid/DevelopCustomViewhelper.html


/**
 * Example
 * {namespace m=TYPO3\ExtensionName\ViewHelpers}
 * <m:customName param="nicecontent"></m:customName>
  */
final class ArraycountViewHelper extends AbstractViewHelper
{

    public function initializeArguments()
    {
        $this->registerArgument('myvar', 'string', 'The email address to resolve the gravatar for', true);
    }

    public function render()
    {
		$count = 0;
        $arg = $this->arguments['myvar'];
        foreach ($arg ?? [] as $key => $value) {
            if (is_array($value)) { $count++; }
        }
		return $count;
   }

}