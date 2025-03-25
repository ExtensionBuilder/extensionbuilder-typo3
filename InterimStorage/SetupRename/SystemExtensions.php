<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Setup;

class SystemExtensions
{

    final function setupRemove(): void {
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

        $sysExt['core'] = [];
        $sysExt['core']['t3v'] = '12';
        $sysExt['core']['selectable'] = true;

        $sysExt['dashboard'] = [];
        $sysExt['dashboard']['t3v'] = '12';
        $sysExt['dashboard']['selectable'] = true;

        $sysExt['extbase'] = [];
        $sysExt['extbase']['t3v'] = '12';
        $sysExt['extbase']['selectable'] = true;

        $sysExt['extensionmanager'] = [];
        $sysExt['extensionmanager']['t3v'] = '12';
        $sysExt['extensionmanager']['selectable'] = true;

        $sysExt['felogin'] = [];
        $sysExt['felogin']['t3v'] = '12';
        $sysExt['felogin']['selectable'] = true;

        $sysExt['filelist'] = [];
        $sysExt['filelist']['t3v'] = '12';
        $sysExt['filelist']['selectable'] = true;

        $sysExt['filemetadata'] = [];
        $sysExt['filemetadata']['t3v'] = '12';
        $sysExt['filemetadata']['selectable'] = true;

        $sysExt['fluid'] = [];
        $sysExt['fluid']['t3v'] = '12';
        $sysExt['fluid']['selectable'] = true;

        $sysExt['fluid_styled_content'] = [];
        $sysExt['fluid_styled_content']['t3v'] = '12';
        $sysExt['fluid_styled_content']['selectable'] = true;

        $sysExt['form'] = [];
        $sysExt['form']['t3v'] = '12';
        $sysExt['form']['selectable'] = true;

        $sysExt['frontend'] = [];
        $sysExt['frontend']['t3v'] = '12';
        $sysExt['frontend']['selectable'] = true;

        $sysExt['impexp'] = [];
        $sysExt['impexp']['t3v'] = '12';
        $sysExt['impexp']['selectable'] = true;

        $sysExt['indexed_search'] = [];
        $sysExt['indexed_search']['t3v'] = '12';
        $sysExt['indexed_search']['selectable'] = true;

        $sysExt['info'] = [];
        $sysExt['info']['t3v'] = '12';
        $sysExt['info']['selectable'] = true;

        $sysExt['install'] = [];
        $sysExt['install']['t3v'] = '12';
        $sysExt['install']['selectable'] = true;

        $sysExt['linkvalidator'] = [];
        $sysExt['linkvalidator']['t3v'] = '12';
        $sysExt['linkvalidator']['selectable'] = true;

        $sysExt['lowlevel'] = [];
        $sysExt['lowlevel']['t3v'] = '12';
        $sysExt['lowlevel']['selectable'] = true;

        $sysExt['opendocs'] = [];
        $sysExt['opendocs']['t3v'] = '12';
        $sysExt['opendocs']['selectable'] = true;

        $sysExt['reactions'] = [];
        $sysExt['reactions']['t3v'] = '12';
        $sysExt['reactions']['selectable'] = true;

        $sysExt['recycler'] = [];
        $sysExt['recycler']['t3v'] = '12';
        $sysExt['recycler']['selectable'] = true;

        $sysExt['redirects'] = [];
        $sysExt['redirects']['t3v'] = '12';
        $sysExt['redirects']['selectable'] = true;

        $sysExt['reports'] = [];
        $sysExt['reports']['t3v'] = '12';
        $sysExt['reports']['selectable'] = true;

        $sysExt['rte_ckeditor'] = [];
        $sysExt['rte_ckeditor']['t3v'] = '12';
        $sysExt['rte_ckeditor']['selectable'] = true;

        $sysExt['scheduler'] = [];
        $sysExt['scheduler']['t3v'] = '12';
        $sysExt['scheduler']['selectable'] = true;

        $sysExt['seo'] = [];
        $sysExt['seo']['t3v'] = '12';
        $sysExt['seo']['selectable'] = true;

        $sysExt['setup'] = [];
        $sysExt['setup']['t3v'] = '12';
        $sysExt['setup']['selectable'] = true;

        $sysExt['sys_note'] = [];
        $sysExt['sys_note']['t3v'] = '12';
        $sysExt['sys_note']['selectable'] = true;

        $sysExt['tstemplate'] = [];
        $sysExt['tstemplate']['t3v'] = '12';
        $sysExt['tstemplate']['selectable'] = true;

        $sysExt['viewpage'] = [];
        $sysExt['viewpage']['t3v'] = '12';
        $sysExt['viewpage']['selectable'] = true;

        $sysExt['webhooks'] = [];
        $sysExt['webhooks']['t3v'] = '12';
        $sysExt['webhooks']['selectable'] = true;

        $sysExt['workspaces'] = [];
        $sysExt['workspaces']['t3v'] = '12';
        $sysExt['workspaces']['selectable'] = true;
    }

}