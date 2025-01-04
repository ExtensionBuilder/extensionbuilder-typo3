<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3Core\Setup;

use ExtensionBuilder\ExtensionbuilderTypo3Core\BuildExtensionCoreBuildAbstract;

class SystemExtensions extends BuildExtensionCoreBuildAbstract
{

    final function setupRemove(): void {
        $this->parent->toolsBuildLog->usage(__CLASS__, __FUNCTION__);

        $this->parent->global['systemExtensions'] = [];

        $sysExt = &$this->parent->global['systemExtensions'];

        $sysExt['adminpanel'] = [];
        $sysExt['adminpanel']['t3v'] = '12';
        $sysExt['adminpanel']['selectable'] = true;

        $sysExt['backend'] = [];
        $sysExt['backend']['t3v'] = '12';
        $sysExt['backend']['selectable'] = false;

        $sysExt['belog'] = [];
        $sysExt['belog']['t3v'] = '12';
        $sysExt['belog']['selectable'] = true;

        $sysExt['beuser'] = [];
        $sysExt['beuser']['t3v'] = '12';
        $sysExt['beuser']['selectable'] = true;
		
        $sysExt[''] = [];
        $sysExt['']['t3v'] = '12';
        $sysExt['']['selectable'] = true;

    }

}