Monaco Editor v0.55.1 - lokaler vs-Ordner für TYPO3 Extension

Kopiere den Ordner:
Resources/Public/JavaScript/Contrib/Monaco/vs

in deine TYPO3-Extension unter:
Resources/Public/JavaScript/Contrib/Monaco/vs

Wichtig:
- Den kompletten Ordner vs kopieren, nicht nur loader.js.
- CDN-Verweise wie cdn.jsdelivr.net entfernen.
- In Fluid z.B. verwenden:
  data-monaco-loader-url="{f:uri.resource(path: 'JavaScript/Contrib/Monaco/vs/loader.js')}"

Quelle: npm package monaco-editor@0.55.1
