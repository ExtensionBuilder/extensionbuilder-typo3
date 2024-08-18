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
 * Nice description ;-)
 *
 * @package TYPO3
 * @subpackage ExtensionName
 * @version
 */
final class IsarrayViewHelper extends AbstractViewHelper
{

    public function initializeArguments()
    {
        $this->registerArgument('myvar', 'string', 'The email address to resolve the gravatar for', true);
//    $this->registerArgument('size', 'integer', 'The size of the gravatar, ranging from 1 to 512', false, 80);
    }

    public function render()
    {
        $arg = $this->arguments['myvar'];
        $argType = gettype($arg);

        if (preg_match('/array/i', "$argType")) { // ToDO
            return '1'; //match
        } else {
            return '0'; //No match
        }
    }

}