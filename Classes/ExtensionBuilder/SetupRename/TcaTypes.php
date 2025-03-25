<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Setup;

class TcaTypes
{

    final function setup(): void {
        $this->parent->global['columnTypes'] = [];

        $tca = &$this->parent->global['columnTypes'];

        // category
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Category/Index.html
        $tca['category'] = [];
        $tca['category']['t3v'] = '12';
        $tca['category']['sql'] = [];
        $tca['category']['sql']['automatically'] = 12;
        $tca['category']['sql']['type'] = 'INT';
        $tca['category']['sql']['unsigned'] = true;
        $tca['category']['sql']['notNull'] = true;
        $tca['category']['sql']['default'] = 0;
        $tca['category']['php'] = [];
        $tca['category']['php']['type'] = 'string';
        $tca['category']['tca'] = [];
        $tca['category']['tca']['config'] = [];
        $tca['category']['tca']['config']['type'] = 'category';
        $tca['category']['fluid'] = [];

        // check
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Index.html
        $tca['check'] = [];
        $tca['check']['t3v'] = '12';
        $tca['check']['sql'] = [];
        $tca['check']['sql']['automatically'] = 13;
        $tca['check']['sql']['type'] = 'SMALLINT';
        $tca['check']['sql']['unsigned'] = true;
        $tca['check']['sql']['notNull'] = true;
        $tca['check']['sql']['default'] = '0';
        $tca['check']['php'] = [];
        $tca['check']['php']['type'] = 'bool';
        $tca['check']['php']['default'] = 'false';
        $tca['check']['tca'] = [];
        $tca['check']['tca']['config'] = [];
        $tca['check']['tca']['config']['type'] ='check';
        $tca['check']['fluid'] = [];

        // Alias check
        $tca['bool'] = $tca['check'];

        // Alias check
        $tca['checkboxToggle'] = $tca['check'];
        $tca['checkboxToggle']['tca']['config']['renderType'] ='checkboxToggle';

        // Alias check
        $tca['checkboxLabeledToggle'] = $tca['check'];
        $tca['checkboxLabeledToggle']['tca']['config']['renderType'] ='checkboxLabeledToggle';
        $tca['checkboxLabeledToggle']['tca']['config']['items'] = [];
        $tca['checkboxLabeledToggle']['tca']['config']['items']['label'] = 'Missing label';
        $tca['checkboxLabeledToggle']['tca']['config']['items']['labelChecked'] = 'Enabled';
        $tca['checkboxLabeledToggle']['tca']['config']['items']['labelUnchecked'] = 'Disabled';

	
        // color
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Color/Index.html
        $tca['color'] = [];
        $tca['color']['t3v'] = '12';
        $tca['color']['sql'] = [];
        $tca['color']['sql']['automatically'] = 13;
        $tca['color']['sql']['type'] = 'VARCHAR';
        $tca['color']['sql']['size'] = 7;
        $tca['color']['sql']['notNull'] = true;
        $tca['color']['sql']['default'] = '';
        $tca['color']['php'] = [];
        $tca['color']['php']['type'] = 'string';
        $tca['color']['tca'] = [];
        $tca['color']['tca']['config'] = [];
        $tca['color']['tca']['config']['type'] = 'color';
        $tca['color']['fluid'] = [];

        // datetime
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Datetime/Index.html
        $tca['datetime'] = [];
        $tca['datetime']['t3v'] = '12';
        $tca['datetime']['sql'] = [];
        $tca['datetime']['sql']['automatically'] = 12;
        $tca['datetime']['sql']['type'] = 'BIGINT';
        $tca['datetime']['sql']['default'] = 0;
        $tca['datetime']['php'] = [];
        $tca['datetime']['php']['type'] = '?\\DateTime';
        $tca['datetime']['php']['default'] = 'null';
        $tca['datetime']['tca'] = [];
        $tca['datetime']['tca']['config'] = [];
        $tca['datetime']['tca']['config']['type'] = 'datetime';
        $tca['datetime']['tca']['config']['nullable'] = true;
        $tca['datetime']['tca']['config']['size'] = 20;
        $tca['datetime']['tca']['config']['default'] = 0;
        $tca['datetime']['fluid'] = [];
        $tca['datetime']['fluid']['type'] = 'datetime-local';

        // adte alias for datetime
        $tca['date'] = $tca['datetime'];
        $tca['date']['tca']['config']['format'] = 'date';
        $tca['date']['fluid']['type'] = 'date';

        // email
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Email/Index.html
        $tca['email'] = [];
        $tca['email']['t3v'] = '12';
        $tca['email']['sql'] = [];
        $tca['email']['sql']['automatically'] = 13;
        $tca['email']['sql']['type'] = 'VARCHAR';
        $tca['email']['sql']['size'] = 255;
        $tca['email']['sql']['notNull'] = true;
        $tca['email']['sql']['default'] = '';
        $tca['email']['php'] = [];
        $tca['email']['php']['type'] = 'string';
        $tca['email']['php']['default'] = '';
        $tca['email']['tca'] = [];
        $tca['email']['tca']['config'] = [];
        $tca['email']['tca']['config']['type'] = 'email';
        $tca['email']['fluid'] = [];
        $tca['email']['fluid']['f'] = 'f:link.email';
        $tca['email']['fluid']['value'] = 'email';

        // file
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/File/Index.html
        $tca['file'] = [];
        $tca['file']['t3v'] = '12';
        $tca['file']['sql'] = [];
        $tca['file']['sql']['automatically'] = 13;
        $tca['file']['sql']['type'] = 'INT';
        $tca['file']['sql']['unsigned'] = true;
        $tca['file']['sql']['notNull'] = true;
        $tca['file']['sql']['default'] = 0;
        $tca['file']['php'] = [];
        $tca['file']['php']['type'] = 'int';
        $tca['file']['php']['default'] = '0';
        $tca['file']['tca'] = [];
        $tca['file']['tca']['config'] = [];
        $tca['file']['tca']['config']['type'] = 'file';
        $tca['file']['fluid'] = [];
        $tca['file']['fluid']['f'] = 'f:image';

// ToDo
        // upload alias for file
        $tca['uploadToFile'] = $tca['file'];
        $tca['uploadToFile']['fluid']['f'] = 'f:form.upload';

        // flex
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Flex/Index.html
        $tca['flex'] = [];
        $tca['flex']['t3v'] = '12';
        $tca['flex']['sql'] = [];
        $tca['flex']['sql']['automatically'] = 13;
        $tca['felx']['sql']['type'] = 'LONGTEXT';
        $tca['felx']['sql']['default'] = 'null';
        $tca['flex']['php'] = [];
        $tca['flex']['php']['type'] = 'string';
        $tca['flex']['php']['default'] = '';
        $tca['flex']['tca'] = [];
        $tca['flex']['tca']['config'] = [];
        $tca['flex']['tca']['config']['type'] = 'flex';
        $tca['flex']['fluid'] = [];

        // folder
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Folder/Index.html
        $tca['folder'] = [];
        $tca['folder']['t3v'] = '12';
        $tca['folder']['sql'] = [];
        $tca['folder']['sql']['automatically'] = 13;
        $tca['folder']['sql']['type'] = 'LONGTEXT';
        $tca['folder']['php'] = [];
        $tca['folder']['php']['type'] = 'string';
        $tca['folder']['tca'] = [];
        $tca['folder']['tca']['config'] = [];
        $tca['folder']['tca']['config']['type'] ='folder';
        $tca['folder']['fluid'] = [];

        // group
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Group/Index.html
        $tca['group'] = [];
        $tca['group']['t3v'] = '12';
        $tca['group']['sql'] = [];
        $tca['group']['sql']['automatically'] = 13;
        $tca['group']['sql']['type'] = 'LONGTEXT';
        $tca['group']['php'] = [];
        $tca['group']['php']['type'] = 'string';
        $tca['group']['tca'] = [];
        $tca['group']['tca']['config'] = [];
        $tca['group']['tca']['config']['type'] = 'group';
        $tca['group']['fluid'] = [];

        // imageManipulation
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/ImageManipulation/Index.html
        $tca['imageManipulation'] = [];
        $tca['imageManipulation']['t3v'] = '12';
        $tca['imageManipulation']['sql'] = [];
        $tca['imageManipulation']['sql']['automatically'] = 13;
        $tca['imageManipulation']['sql']['type'] = 'LONGTEXT';
        $tca['imageManipulation']['php'] = [];
        $tca['imageManipulation']['php']['type'] = 'string';
        $tca['imageManipulation']['tca'] = [];
        $tca['imageManipulation']['tca']['config'] = [];
        $tca['imageManipulation']['tca']['config']['type'] = 'imageManipulation';
        $tca['imageManipulation']['fluid'] = [];

        // inline
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Index.html
        $tca['inline'] = [];
        $tca['inline']['t3v'] = '12';
        $tca['inline']['sql'] = [];
        $tca['inline']['sql']['automatically'] = 13;
        $tca['inline']['sql']['type'] = 'LONGTEXT';
        $tca['inline']['php'] = [];
        $tca['inline']['php']['type'] = 'string';
        $tca['inline']['tca'] = [];
        $tca['inline']['tca']['config'] = [];
        $tca['inline']['tca']['config']['type'] = 'group';
        $tca['inline']['fluid'] = [];

        // input
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Index.html
        $tca['input'] = [];
        $tca['input']['t3v'] = '12';
        $tca['input']['sql'] = [];
        $tca['input']['sql']['automatically'] = false;
        $tca['input']['sql']['type'] = 'VARCHAR';
        $tca['input']['sql']['size'] = 255;
        $tca['input']['sql']['notNull'] = true;
        $tca['input']['sql']['default'] = '';
        $tca['input']['php'] = [];
        $tca['input']['php']['type'] = 'string';
        $tca['input']['php']['default'] = '';
        $tca['input']['tca'] = [];
        $tca['input']['tca']['config'] = [];
        $tca['input']['tca']['config']['type'] = 'input';
        $tca['input']['fluid'] = [];

        // json
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Json/Index.html
        $tca['json'] = [];
        $tca['json']['t3v'] = '12';
        $tca['json']['sql'] = [];
        $tca['json']['sql']['automatically'] = 12;
        $tca['json']['sql']['type'] = 'JSON';
        $tca['json']['php'] = [];
        $tca['json']['php']['type'] = 'string';
        $tca['json']['tca'] = [];
        $tca['json']['tca']['config'] = [];
        $tca['json']['tca']['config']['type'] = 'json';
        $tca['json']['fluid'] = [];

        // language
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Language/Index.html
        $tca['language'] = [];
        $tca['language']['t3v'] = '12';
        $tca['language']['sql'] = [];
        $tca['language']['sql']['automatically'] = 13;
        $tca['language']['sql']['type'] = 'INT';
        $tca['language']['sql']['notNull'] = true;
        $tca['language']['sql']['default'] = 0;
        $tca['language']['php'] = [];
        $tca['language']['php']['type'] = 'int';
        $tca['language']['tca'] = [];
        $tca['language']['tca']['config'] = [];
        $tca['language']['tca']['config']['type'] = 'language';
        $tca['language']['fluid'] = [];

        // country alias of language
        $tca['country'] = $tca['language'];
        $tca['country']['fluid']['f'] = 'f:form.countrySelect';

        // link
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Link/Index.html
        $tca['link'] = [];
        $tca['link']['t3v'] = '12';
        $tca['link']['sql'] = [];
        $tca['link']['sql']['automatically'] = 13;
        $tca['link']['sql']['type'] = 'VARCHAR';
        $tca['link']['sql']['size'] = '2048';
        $tca['link']['sql']['notNull'] = true;
        $tca['link']['sql']['default'] = '';
        $tca['link']['php'] = [];
        $tca['link']['php']['type'] = 'string';
        $tca['link']['php']['default'] = '';
        $tca['link']['tca'] = [];
        $tca['link']['tca']['config'] = [];
        $tca['link']['tca']['config']['type'] = 'link';
        $tca['link']['fluid'] = [];

        // none
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/None/Index.html
        $tca['none'] = [];
        $tca['none']['t3v'] = '12';
        $tca['none']['noSql'] = true;
        $tca['none']['noDomain'] = true;
        $tca['none']['php'] = [];
        $tca['none']['php']['type'] = 'string';
        $tca['none']['tca'] = [];
        $tca['none']['tca']['config'] = [];
        $tca['none']['tca']['config']['type'] = 'none';
        $tca['none']['fluid'] = [];

        // map alias for none
        $tca['map'] = $tca['none'];

        // repetition alias for none
        $tca['repetition'] = $tca['none'];

        // number
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Number/Index.html
        $tca['number'] = [];
        $tca['number']['t3v'] = '12';
        $tca['number']['sql'] = [];
        $tca['number']['sql']['automatically'] = 13;
        $tca['number']['sql']['type'] = 'INT';
        $tca['number']['sql']['notNull'] = true;
        $tca['number']['sql']['default'] = 0;
        $tca['number']['php'] = [];
        $tca['number']['php']['type'] = 'int';
        $tca['number']['php']['default'] = '0';
        $tca['number']['tca'] = [];
        $tca['number']['tca']['config'] = [];
        $tca['number']['tca']['config']['type'] = 'number';
        $tca['number']['fluid'] = [];

        // decimal alias of number
        $tca['decimal'] = $tca['number'];
        $tca['decimal']['sql']['type'] = 'DECIMAL';
        $tca['decimal']['sql']['size'] = '10, 2';
        $tca['decimal']['php']['type'] = 'float';
        $tca['decimal']['tca']['config']['format'] = 'decimal';

        // passthrough
        // https://docs.typo3.org/m/typo3/reference-tca/12.4/en-us/ColumnsConfig/Type/Passthrough/Index.html
        $tca['passthrough'] = [];
        $tca['passthrough']['t3v'] = '12';
        $tca['passthrough']['sql'] = [];
        $tca['passthrough']['sql']['automatically'] = false;
        $tca['passthrough']['php'] = [];
        $tca['passthrough']['php']['type'] = 'string';
        $tca['passthrough']['tca'] = [];
        $tca['passthrough']['tca']['config'] = [];
        $tca['passthrough']['tca']['config']['type'] = 'passthrough';
        $tca['passthrough']['fluid'] = [];

        // password
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Password/Index.html
        $tca['password'] = [];
        $tca['password']['t3v'] = '12';
        $tca['password']['sql'] = [];
        $tca['password']['sql']['automatically'] = 13;
        $tca['password']['sql']['type'] = 'VARCHAR';
        $tca['password']['sql']['size'] = 255;
        $tca['password']['sql']['notNull'] = true;
        $tca['password']['sql']['default'] = '';
        $tca['password']['php'] = [];
        $tca['password']['php']['type'] = 'string';
        $tca['password']['php']['default'] = '';
        $tca['password']['tca'] = [];
        $tca['password']['tca']['config'] = [];
        $tca['password']['tca']['config']['type'] = 'password';
        $tca['password']['fluid'] = [];
        $tca['password']['fluid']['f'] = 'f:form.password';

        // radio
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Radio/Index.html
        $tca['radio'] = [];
        $tca['radio']['t3v'] = '12';
        $tca['radio']['sql'] = [];
        $tca['radio']['sql']['automatically'] = 13;
        $tca['radio']['sql']['type'] = 'VARCHAR';
        $tca['radio']['sql']['size'] = 255;
        $tca['radio']['sql']['notNull'] = true;
        $tca['radio']['sql']['default'] = '';
        $tca['radio']['php'] = [];
        $tca['radio']['php']['type'] = 'string';
        $tca['radio']['tca'] = [];
        $tca['radio']['tca']['config']['type'] = [];
        $tca['radio']['tca']['config']['type'] = 'radio';
        $tca['radio']['fluid'] = [];

        // select
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Index.html
        $tca['selectSingle'] = [];
        $tca['selectSingle']['t3v'] = '12';
        $tca['selectSingle']['sql'] = [];
        $tca['selectSingle']['sql']['automatically'] = 13;
        $tca['selectSingle']['sql']['type'] = 'INT';
        $tca['selectSingle']['sql']['unsigned'] = true;
        $tca['selectSingle']['sql']['default'] = 0;
        $tca['selectSingle']['sql']['notNull'] = true;

        $tca['selectSingle']['php'] = [];
        $tca['selectSingle']['php']['type'] = 'int';
        $tca['selectSingle']['php']['default'] = '0';
        $tca['selectSingle']['tca'] = [];
        $tca['selectSingle']['tca']['config'] = [];
        $tca['selectSingle']['tca']['config']['type'] = 'select';
        $tca['selectSingle']['tca']['config']['renderType'] = 'selectSingle';
        $tca['selectSingle']['fluid'] = [];

        $tca['selectSingleBox'] = $tca['selectSingle'];
        $tca['selectSingleBox']['tca']['config']['renderType'] = 'selectSingleBox';

        $tca['selectCheckBox'] = $tca['selectSingle'];
        $tca['selectCheckBox']['tca']['config']['renderType'] = 'selectCheckBox';

        $tca['selectMultipleSideBySide'] = $tca['selectSingle'];
        $tca['selectMultipleSideBySide']['tca']['config']['renderType'] = 'selectMultipleSideBySide';

        $tca['selectTree'] = $tca['selectSingle'];
        $tca['selectTree']['tca']['config']['renderType'] = 'selectTree';

        // slug
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Slug/Index.html
        $tca['slug'] = [];
        $tca['slug']['t3v'] = '12';
        $tca['slug']['sql'] = [];
        $tca['slug']['sql']['automatically'] = 12;
        $tca['slug']['sql']['type'] = 'VARCHAR';
        $tca['slug']['sql']['size'] = 2048;
        $tca['slug']['php'] = [];
        $tca['slug']['php']['type'] = 'string';
        $tca['slug']['php']['default'] = '';
        $tca['slug']['tca'] = [];
        $tca['slug']['tca']['config'] = [];
        $tca['slug']['tca']['config']['type'] = 'slug';
        $tca['slug']['fluid'] = [];

        // text
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Index.html
        $tca['text'] = [];
        $tca['text']['t3v'] = '12';
        $tca['text']['sql'] = [];
        $tca['text']['sql']['automatically'] = 13;
        $tca['text']['sql']['type'] = 'LONGTEXT';
        $tca['text']['php'] = [];
        $tca['text']['php']['type'] = 'string';
        $tca['text']['php']['default'] = '';
        $tca['text']['tca'] = [];
        $tca['text']['tca']['config'] = [];
        $tca['text']['tca']['config']['type'] = 'text';
        $tca['text']['fluid'] = [];

        // text Alias für RTE text
        $tca['textrte'] = $tca['text'];
        $tca['textrte']['tca']['config']['enableRichtext'] = true;

        // text Alias für t3editor 
        $tca['textt3editor'] = $tca['text'];
        $tca['textt3editor']['tca']['config']['renderType'] = 't3editor';

        // text Alias für belayoutwizard
        $tca['textbelayoutwizard'] = $tca['text'];
        $tca['textbelayoutwizard']['tca']['config']['renderType'] = 'belayoutwizard';

        // text Alias für textTable
        $tca['texttable'] = $tca['text'];
        $tca['texttable']['tca']['config']['renderType'] = 'textTable';

        // user ???
// ToDo Test / Aktiv?
        $tca['user'] = [];
        $tca['user']['t3v'] = '12';
        $tca['user']['sql'] = [];
        $tca['user']['sql']['automatically'] = false;
        $tca['user']['sql']['type'] = 'VARCHAR';
        $tca['user']['sql']['size'] = 255;
        $tca['user']['php'] = [];
        $tca['user']['php']['type'] = 'string';
        $tca['user']['tca'] = [];
        $tca['user']['tca']['config'] = [];
        $tca['user']['tca']['config']['type'] = 'user';
        $tca['user']['fluid'] = [];

        // uuid
        // https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Uuid/Index.html
        $tca['uuid'] = [];
        $tca['uuid']['t3v'] = '12';
        $tca['uuid']['sql'] = [];
        $tca['uuid']['sql']['automatically'] = 12;
        $tca['uuid']['sql']['type'] = 'VARCHAR';
        $tca['uuid']['sql']['size'] = '36';
        $tca['uuid']['sql']['notNull'] = true;
        $tca['uuid']['sql']['default'] = '';
        $tca['uuid']['php'] = [];
        $tca['uuid']['php']['type'] = 'string';
        $tca['uuid']['tca'] = [];
        $tca['uuid']['tca']['config'] = [];
        $tca['uuid']['tca']['config']['type'] = 'uuid';
        $tca['uuid']['fluid'] = [];

    }

}