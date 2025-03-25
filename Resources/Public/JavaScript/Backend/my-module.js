import { ClassicEditor } from '@typo3/rte-ckeditor/ckeditor5';

import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic';
import { Essentials } from '@ckeditor/ckeditor5-essentials';
import { Bold, Italic } from '@ckeditor/ckeditor5-basic-styles';
import { List } from '@ckeditor/ckeditor5-list';
import { Link } from '@ckeditor/ckeditor5-link';
import { Paragraph } from '@ckeditor/ckeditor5-paragraph';
import { SourceEditing } from '@ckeditor/ckeditor5-source-editing';

//ClassicEditor.create( document.querySelector( '#editor' ), {
//	licenseKey: 'GPL',
//} ) ;


class MyModule {


  constructor() {
    let target = document.getElementById('myTextfieldId');
    const config = {
      toolbar: [ 'bold', 'italic', '|', 'bulletedList', 'numberedList', '|', 'link', '|', 'sourceEditing' ],
    }
    ClassicEditor.builtinPlugins = [
      Essentials,
      Bold,
      Italic,
      List,
      Link,
      Paragraph,
      SourceEditing
    ];
    ClassicEditor.create(target, config);
  }
}

export default new MyModule;