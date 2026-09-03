# AGENTS.md

## Projekt

Dieses Repository enthält die TYPO3-Extension:

```
extensionbuilder_typo3
```

Hersteller:

```
ExtensionBuilder
```

PHP-Namespace:

```
ExtensionBuilder\ExtensionBuilderTypo3
```

`extensionbuilder_typo3` ist ein Verwaltungswerkzeug und Editor für TYPO3-Extensions.

Die Extension verwaltet Projekte, Vendors, Extensions und deren Konfigurationen.
Der eigentliche TYPO3-Extension-Code wird **nicht direkt in `extensionbuilder_typo3` erzeugt**.

Die Code-Erzeugung erfolgt in einer **separaten Generator-Extension**.

Der grundsätzliche Ablauf ist:

1. `extensionbuilder_typo3` verwaltet und bearbeitet Projekt-, Vendor-, Extension- und Konfigurationsdaten.
2. Diese Daten werden an die separate Generator-Extension übergeben.
3. Die Generator-Extension erzeugt daraus den TYPO3-Extension-Code.
4. Der erzeugte Code wird anschließend an den TYPO3-Editor bzw. an `extensionbuilder_typo3` zurückgegeben.
5. Der erzeugte Code kann dort angezeigt, geprüft und weiterbearbeitet werden.

`extensionbuilder_typo3` verarbeitet unter anderem:

* Projekte
* Vendors
* Extensions
* Components
* Properties
* Models
* Controller
* Backend-Module
* TCA
* Fluid
* XLF
* Content Security Policies
* Generator-Konfigurationen
* persistente Projekt-JSON-Daten
* vom Generator erzeugten Extension-Code
* Übergabe des Codes an den TYPO3-Editor

## Architektur

Die grundlegende Architektur ist:

```
extensionbuilder_typo3
        ↓
Projekt- und Konfigurationsdaten
        ↓
separate Generator-Extension
        ↓
generierter TYPO3-Extension-Code
        ↓
TYPO3-Editor / extensionbuilder_typo3
```

Wichtig:

`extensionbuilder_typo3` erzeugt selbst keinen TYPO3-Extension-Code.

Deshalb muss bei Fehlern immer unterschieden werden zwischen:

1. Quellcode von `extensionbuilder_typo3`
2. Projekt-, Vendor-, Extension- und Konfigurationsdaten
3. Übergabe der Daten an die Generator-Extension
4. Quellcode der Generator-Extension
5. Generator-Templates der Generator-Extension
6. generiertem TYPO3-Extension-Code
7. Rückgabe des generierten Codes
8. Anzeige und Bearbeitung des Codes im TYPO3-Editor

## Zielplattform

Primäres Ziel:

```
TYPO3 14.x
```

Zusätzlich:

* TYPO3 13 möglichst kompatibel halten
* TYPO3 15 bei neuen APIs berücksichtigen
* keine bereits deprecated TYPO3-APIs neu verwenden
* neue Implementierungen möglichst zukunftssicher gestalten

Primäre PHP-Version:

```
PHP 8.5
```

Es dürfen nur PHP-Sprachfeatures und PHP-Funktionen verwendet werden,
die mit der eingesetzten TYPO3-Version kompatibel sind.

## Grundregeln

* Offizielle TYPO3-Coding-Guidelines beachten.
* TYPO3 Core APIs bevorzugen.
* Keine TYPO3-APIs erfinden.
* Keine Fluid-ViewHelper erfinden.
* Keine Klassen, Methoden, Partials, Routen oder Konfigurationsschlüssel erfinden.
* Vor Änderungen immer prüfen, ob das Ziel tatsächlich existiert.
* Änderungen möglichst klein und lokal halten.
* Keine nicht angeforderten Refactorings durchführen.
* Bestehende Funktionen nicht entfernen.
* Bestehende öffentliche Klassen, Actions, Routen und Konfigurationsschlüssel nicht ohne Auftrag umbenennen.
* Bestehendes Verhalten möglichst erhalten.
* Keine rein kosmetischen Änderungen in nicht betroffenen Dateien durchführen.
* Keine kompletten Dateien neu formatieren, wenn dies für die eigentliche Änderung nicht erforderlich ist.
* Keine Sicherheitsmechanismen deaktivieren, um einen Fehler kurzfristig zu umgehen.
* Vor jeder Änderung prüfen, ob eine ähnliche Implementierung bereits im Projekt existiert.

## ExtensionBuilder-spezifische Fehleranalyse

Wenn ein Fehler im erzeugten TYPO3-Extension-Code auftritt:

1. prüfen, ob die Daten in `extensionbuilder_typo3` korrekt gespeichert sind
2. prüfen, welche Daten an die Generator-Extension übergeben werden
3. prüfen, ob die Übergabe vollständig und korrekt ist
4. prüfen, ob die Generator-Extension die Daten korrekt verarbeitet
5. prüfen, ob der Fehler in einem Generator-Template liegt
6. prüfen, ob der generierte Code bereits vor der Rückgabe fehlerhaft ist
7. prüfen, ob der Code bei der Rückgabe verändert wird
8. prüfen, ob der TYPO3-Editor den Code korrekt darstellt
9. erst danach die tatsächlich verantwortliche Extension oder Datei ändern

Wenn der Fehler in der Generator-Extension liegt, darf er nicht nur im generierten Code oder im TYPO3-Editor repariert werden.

Wenn der Fehler in `extensionbuilder_typo3` liegt, darf die Generator-Extension nicht unnötig geändert werden.

Direkte Änderungen am generierten Code dienen grundsätzlich nur zur Analyse,
wenn dieser Code beim nächsten Generierungsvorgang erneut erzeugt wird.

## Persistente Datenstrukturen

Projekt-, Vendor- und Extension-Daten können langfristig gespeichert sein.

Bestehende Schlüssel dürfen nicht ohne Rückwärtskompatibilitätsprüfung:

* umbenannt
* entfernt
* verschoben
* in ihrem Datentyp verändert
* anders verschachtelt

werden.

Besonders wichtig sind historische Schlüssel wie:

```
propertys
contentSecurityPolicys
extensionNameLagecy
extensionNameLegacy
```

Auch wenn Schreibweisen sprachlich falsch oder inkonsistent sind,
dürfen diese Schlüssel nicht stillschweigend korrigiert werden.

Vor Änderungen an Datenstrukturen prüfen:

* alte Projektdateien
* JSON-Strukturen
* Import
* Export
* Build-Prozess
* Generator-Übergabe
* Konfigurationszugriff
* Fluid-Ausgabe
* gespeicherte Extensions
* Rückwärtskompatibilität

## PHP

Neue PHP-Dateien möglichst mit:

```
declare(strict_types=1);
```

TYPO3- und PSR-Coding-Standards einhalten.

Typed Properties verwenden.

Bevorzugt:

```php
private SomeService $someService;
```

Constructor Property Promotion verwenden, wenn passend:

```php
public function __construct(
    private readonly SomeService $someService,
) {
}
```

Dependency Injection bevorzugen.

Nicht unnötig:

```php
GeneralUtility::makeInstance(SomeService::class);
```

verwenden, wenn Constructor Injection möglich ist.

`GeneralUtility::makeInstance()` ist zulässig,
wenn TYPO3-Architektur oder Laufzeitkontext dies tatsächlich erfordern.

## PHP Namespaces

Namespaces müssen der TYPO3-/PSR-Struktur entsprechen.

Für diese Extension gilt grundsätzlich:

```
ExtensionBuilder\ExtensionBuilderTypo3
```

Vor dem Hinzufügen eines `use`-Statements prüfen, ob die Klasse tatsächlich existiert.

Keine Klassen anhand eines vermuteten Namespace-Pfades erfinden.

## Extbase Controller

Controller-Actions sollen PSR-7-Responses zurückgeben.

Beispiel:

```php
public function listAction(): ResponseInterface
{
    return $this->htmlResponse();
}
```

Verwenden:

```php
use Psr\Http\Message\ResponseInterface;
```

Keine `void`-Controller-Actions verwenden,
wenn TYPO3 eine Response erwartet.

Extbase Argument Mapping bevorzugen.

Controller-Argumente und Fluid-Link-Argumente müssen zusammenpassen.

Wenn eine Action beispielsweise erwartet:

```php
public function editAction(Extension $extension): ResponseInterface
```

muss der aufrufende Link oder Redirect auch `extension` übergeben.

Bei Fehlern wie:

```
Required argument "..." is not set
```

immer prüfen:

* `f:link.action`
* `f:form`
* Redirects
* Action-Signatur
* Property Mapping
* URL-Parameter
* Controller
* Action-Namen

## ModuleTemplate

Backend-Module sollen TYPO3-Backend-APIs verwenden.

Bevorzugen:

* ModuleTemplate
* ModuleTemplateFactory
* PageRenderer
* ButtonBar
* Breadcrumbs
* TYPO3 Backend Routing
* TYPO3 Icon API

Backend-HTML nicht unnötig selbst nachbauen,
wenn TYPO3 bereits eine passende API anbietet.

## Fluid

Fluid muss mit TYPO3 14 kompatibel sein.

Templates können folgende Standard-Namespaces verwenden:

```html
<html
    xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
    xmlns:be="http://typo3.org/ns/TYPO3/CMS/Backend/ViewHelpers"
    xmlns:core="http://typo3.org/ns/TYPO3/CMS/Core/ViewHelpers"
    data-namespace-typo3-fluid="true"
>
```

Wichtig:

`be:` gehört zu:

```
TYPO3/CMS/Backend/ViewHelpers
```

nicht zu:

```
TYPO3/CMS/Core/ViewHelpers
```

Partials benötigen keine zusätzliche `<html>`-Namespace-Hülle.

Layouts können Namespaces enthalten,
wenn dort entsprechende ViewHelper verwendet werden.

## Fluid-Ausdrücke

Direkte Variablen verwenden.

Bevorzugt:

```html
arguments="{0: extensionName}"
```

Nicht unnötig:

```html
arguments="{0: '{extensionName}'}"
```

Bei Action-Links Argumente direkt übergeben:

```html
arguments="{
    extension: extension,
    currentProject: currentProject
}"
```

Variablen nicht unnötig als Strings verpacken.

## Fluid Partials

Vor jeder Verwendung prüfen,
ob das Partial tatsächlich existiert.

Beispiel:

```html
<f:render partial="ComponentAdd" arguments="{_all}" />
```

erfordert:

```
Resources/Private/Partials/ComponentAdd.html
```

Singular und Plural exakt beachten.

Beispiel:

```
ComponentAdd.html
```

ist nicht:

```
ComponentsAdd.html
```

Keine fehlenden Partials voraussetzen.

## Fluid `arguments="{_all}"`

`arguments="{_all}"` nur verwenden,
wenn das Partial tatsächlich alle Variablen benötigt.

Für wiederverwendbare Partials bevorzugen:

```html
<f:render
    partial="ProjectList"
    arguments="{
        projectData: projectData,
        configuration: configuration
    }"
/>
```

Explizite Argumente machen Abhängigkeiten nachvollziehbarer.

Bestehende `{_all}`-Verwendung nicht ohne Grund komplett umbauen.

## Fluid Debugging

Temporär erlaubt:

```html
<f:debug>{variable}</f:debug>
```

oder:

```html
<f:debug inline="1">{variable}</f:debug>
```

Keine großen `{_all}`-Dumps dauerhaft in Templates stehen lassen.

Debug-Ausgaben nach Abschluss der Analyse entfernen,
wenn sie nicht ausdrücklich dauerhaft benötigt werden.

## PHP Debugging

Temporär erlaubt:

```php
debug($variable);
```

Debug-Ausgaben nach Abschluss entfernen.

Keine sensiblen Daten dumpen:

* Passwörter
* Tokens
* API-Keys
* Secrets
* Session-Daten
* Zugangsdaten

## Extbase Variable Dump

Wenn der TYPO3 Extbase Variable Dump unformatiert oder ohne die gewohnte Darstellung erscheint,
zuerst die Content Security Policy prüfen.

Besonders wichtig:

```php
SourceKeyword::nonceProxy
```

für Styles.

## Content Security Policy

Die Extension muss mit aktivierter TYPO3 Content Security Policy funktionieren.

CSP niemals als schnelle Fehlerbehebung global deaktivieren.

`unsafe-inline` nicht als Standardlösung verwenden.

TYPO3-Nonce-Unterstützung berücksichtigen.

Für TYPO3-generierte Inline-Skripte und Inline-Styles:

```php
SourceKeyword::nonceProxy
```

verwenden, wenn erforderlich.

Besonders prüfen:

```php
Directive::ScriptSrc
Directive::ScriptSrcElem
Directive::StyleSrc
Directive::StyleSrcElem
```

Beispiel:

```php
new Mutation(
    MutationMode::Set,
    Directive::StyleSrc,
    SourceKeyword::self,
    SourceKeyword::nonceProxy,
),
```

und:

```php
new Mutation(
    MutationMode::Set,
    Directive::StyleSrcElem,
    SourceKeyword::self,
    SourceKeyword::nonceProxy,
),
```

Keine TYPO3 Debugger-CSS als Workaround nachbauen,
bevor CSP und Nonce-Konfiguration geprüft wurden.

## JavaScript

Moderne ES-Module verwenden.

Keine Inline-Skripte.

Keine Inline-Eventhandler verwenden:

```html
onclick=""
onchange=""
onkeyup=""
```

Stattdessen `data-*`-Attribute und EventListener verwenden.

Beispiel:

```html
<button type="button" data-action="delete">
    Löschen
</button>
```

JavaScript:

```javascript
document.addEventListener('click', (event) => {
    const target = event.target.closest('[data-action="delete"]');

    if (!target) {
        return;
    }

    // Aktion
});
```

TYPO3 JavaScript Module API verwenden,
wenn JavaScript im Backend geladen wird.

## PageRenderer

JavaScript und CSS bevorzugt über TYPO3 laden.

Fluid-seitig kann beispielsweise verwendet werden:

```html
<f:be.pageRenderer
    includeJavaScriptModules="{0: '@extensionbuilder/extensionbuildertypo3/ExtensionInfo.js'}"
/>
```

Keine externen Assets unnötig direkt mit `<script>` einbinden.

## CSS

CSS bevorzugt unter:

```
Resources/Public/Css/
```

ablegen.

Inline-Styles möglichst vermeiden:

```html
style="..."
```

Besser:

```html
class="eb-example"
```

und:

```css
.eb-example {
    ...
}
```

Extension-CSS möglichst auf einen eigenen Container begrenzen.

Beispiel:

```css
#extensionbuilder-module .eb-component {
    ...
}
```

Globale Selektoren vermeiden:

```css
* {}
a {}
button {}
table {}
td {}
th {}
```

wenn sie TYPO3-Backend-Komponenten beeinflussen können.

Besonders vorsichtig sein bei:

```css
word-break
overflow-wrap
white-space
display
position
overflow
```

Globale Änderungen können TYPO3-Core-Komponenten und Debug-Ausgaben beeinflussen.

## TYPO3 Backend CSS

TYPO3 Backend Styles nicht unnötig überschreiben.

Besonders vorsichtig bei:

* Buttons
* Tabellen
* Dropdowns
* Icons
* Module Header
* Debugger
* Form Controls
* Bootstrap Utility Classes
* TYPO3 Core Komponenten

## Icons

TYPO3 Icon API verwenden.

Fluid:

```html
<core:icon identifier="actions-open" size="small" />
```

Typische Identifier:

```
actions-open
actions-delete
actions-add
actions-refresh
```

Vor Verwendung eines unbekannten Icon-Identifier prüfen,
ob dieser in TYPO3 tatsächlich existiert.

Keine TYPO3-Core-SVGs unnötig kopieren.

## Action Buttons

Für mehrere Aktionen bevorzugt:

```html
<div class="btn-group btn-group-sm" role="group">
```

verwenden.

Edit- und Delete-Button müssen innerhalb derselben Button-Gruppe liegen,
wenn sie gemeinsam dargestellt werden sollen.

Beispiel:

```html
<td class="text-end">
    <div class="btn-group btn-group-sm" role="group">
        ...
    </div>
</td>
```

Action-Icons müssen sauber vertikal ausgerichtet sein.

## Tabellen

Tabellen logisch konsistent halten.

Die Anzahl der logischen Spalten in:

```html
<thead>
```

und:

```html
<tbody>
```

muss übereinstimmen.

Keine leeren `<th>`-Spalten nur für optischen Abstand hinzufügen,
wenn darunter keine entsprechende Datenzelle existiert.

Action-Spalten beispielsweise:

```html
<th scope="col" class="text-end">
    Aktion
</th>
```

und:

```html
<td class="text-end">
    ...
</td>
```

Edit/Delete müssen unter der Aktionsüberschrift stehen.

## Übersetzungen

Benutzersichtbare Texte möglichst über XLF-Dateien ausgeben.

Pfad:

```
Resources/Private/Language/
```

Fluid:

```html
<f:translate key="..." />
```

Vorhandene LLL-Strukturen nicht ohne Grund umbauen.

Direkte Texte sind zulässig,
wenn es sich um rein technische oder temporäre Entwicklerausgaben handelt.

## XLF

Bei neuen Übersetzungsschlüsseln prüfen:

* Schlüssel existiert
* korrekter Dateipfad
* korrekte Sprache
* keine Dubletten
* bestehende Namensstruktur verwenden

Keine XLF-Schlüssel erfinden,
ohne die entsprechende Datei anzulegen oder zu prüfen.

## TCA

TYPO3-14-konforme TCA-Syntax verwenden.

Bei TCA-Änderungen prüfen:

* `type`
* `config`
* `renderType`
* `items`
* `eval`
* `required`
* `default`
* `foreign_table`
* `MM`
* `palettes`
* `types`
* `columnsOverrides`

Keine deprecated TCA-Konfiguration neu einbauen.

## Domain Models

Bei Änderungen an Properties alle verbundenen Stellen prüfen:

* Domain Model
* Repository
* TCA
* `ext_tables.sql`
* Fluid
* XLF
* Property Mapping
* Type Converter
* Generator-Konfiguration
* Projekt-JSON
* Generator-Übergabe

## TypeConverter

TypeConverter nur verwenden,
wenn Extbase Property Mapping dies tatsächlich erfordert.

TYPO3-14-kompatible Registrierung verwenden.

Keine alten oder deprecated TypeConverter-Registrierungen neu einbauen.

Bei Fehlern prüfen:

* Source Types
* Target Type
* Priority
* `canConvertFrom()`
* `convertFrom()`
* Property Mapping Configuration

## Datenbank

Schemaänderungen nicht isoliert durchführen.

Bei Änderungen prüfen:

* `ext_tables.sql`
* TCA
* Model
* Repository
* Projekt-Konfiguration
* Generator
* Auswirkungen auf bestehende Installationen

Keine Felder stillschweigend entfernen.

## Backend-Routen

Bestehende:

* Controller
* Action-Namen
* Module-Identifier
* Routen
* Parameter

nicht ohne Auftrag ändern.

Bei Links immer prüfen,
welche Parameter die Ziel-Action benötigt.

## Request Tokens / CSRF

Zustandsändernde Aktionen müssen geschützt sein.

TYPO3 Request Token Mechanismen verwenden,
wenn die jeweilige Action dies erfordert.

Sicherheitsprüfungen niemals entfernen,
nur um einen Request kurzfristig funktionsfähig zu machen.

## HTML-Ausgabe

HTML semantisch korrekt halten.

Keine fehlerhaften Verschachtelungen.

Besonders prüfen:

* `<div>`
* `<table>`
* `<thead>`
* `<tbody>`
* `<tr>`
* `<td>`
* `<th>`
* `<form>`
* `<button>`
* `<a>`

Fluid-Tags müssen korrekt geöffnet und geschlossen sein.

## `f:format.raw`

Nur verwenden,
wenn der Inhalt vollständig vertrauenswürdig ist.

Bei möglicherweise unsicherem HTML TYPO3-Sanitizing verwenden.

Keine Benutzerinhalte ungefiltert ausgeben.

## Generator-Übergabe

Bei Daten, die an die Generator-Extension übergeben werden, besonders prüfen:

* erwartete Schlüssel
* Datentypen
* leere Werte
* boolesche Werte
* Arrays
* verschachtelte Strukturen
* Versionsinformationen
* Extension-Namen
* Vendor-Namen
* Namespaces
* Pfade
* Konfigurationswerte

Die Struktur darf nicht ohne Prüfung verändert werden,
weil die Generator-Extension von dieser Datenstruktur abhängig sein kann.

## Generierter Code

Wenn generierter Code geprüft wird, besonders beachten:

* PHP-Namespace
* `use`-Statements
* PHP-Syntax
* Klassennamen
* Dateinamen
* Dateipfade
* Controller
* Domain Models
* Fluid
* Partials
* TCA
* XLF
* Composer
* `ext_emconf.php`
* Services.yaml
* Routes
* JavaScript-Module
* CSP

## TYPO3 Editor

Wenn erzeugter Code im TYPO3-Editor falsch angezeigt wird,
nicht automatisch davon ausgehen, dass der Generator fehlerhaft ist.

Prüfen:

1. Inhalt direkt nach der Generierung
2. Rückgabe des Codes
3. Übertragung
4. mögliche Encodings
5. HTML-Escaping
6. Editor-Ausgabe
7. Speicherung
8. erneutes Laden

Der Editor darf den generierten Code nicht unbeabsichtigt verändern.

## Kompatibilität

Bestehende Extensions und gespeicherte Projekte dürfen durch kleine Codeänderungen
nicht unnötig inkompatibel werden.

Wenn eine Änderung potenziell inkompatibel ist,
muss ausdrücklich darauf hingewiesen werden.

## Benennung

Neue Namen sollen korrekt und konsistent sein.

Bestehende historische Namen nicht automatisch korrigieren.

Bei neuen Klassen TYPO3-/PSR-Namenskonventionen verwenden.

## Dateipfade

TYPO3-Standardstruktur beibehalten:

```
Classes/
Configuration/
Resources/Private/
Resources/Public/
Documentation/
```

Fluid:

```
Resources/Private/Templates/
Resources/Private/Layouts/
Resources/Private/Partials/
```

Sprachen:

```
Resources/Private/Language/
```

## Vor jeder Änderung

Vor Änderungen:

1. Ziel-Datei öffnen
2. relevante Klasse oder Funktion vollständig ansehen
3. Aufrufer suchen
4. abhängige Fluid-Dateien suchen
5. ähnliche Implementierungen im Projekt prüfen
6. TYPO3-Version berücksichtigen
7. prüfen, ob Daten an die Generator-Extension übergeben werden
8. prüfen, ob die Änderung Auswirkungen auf persistente Daten hat

## Fehlerbehebung

Bei Fehlern nicht nur Symptome behandeln.

Vorgehen:

1. Fehler reproduzierbaren Kontext identifizieren
2. tatsächliche Ursache bestimmen
3. Datenfluss prüfen
4. Übergabe zur Generator-Extension prüfen
5. generierten Code prüfen
6. Editor-Rückgabe prüfen
7. kleinstmögliche Änderung umsetzen
8. angrenzende Stellen prüfen
9. Rückwärtskompatibilität prüfen

## Ausgabe bei Code-Reviews

Bei einer Prüfung konkrete Angaben machen:

* Datei
* Funktion oder Bereich
* Problem
* Ursache
* konkrete Änderung

Nicht nur allgemein schreiben:

```
"nicht TYPO3-konform"
```

sondern genau erklären,
welcher Teil nicht TYPO3-konform ist und wie er korrigiert werden soll.

## Änderungen für den Benutzer

Wenn der Benutzer nur die Änderungen sehen möchte:

* keine komplette Datei ausgeben
* nur die geänderten Bereiche zeigen
* möglichst Vorher/Nachher oder Diff verwenden

Wenn ausdrücklich die komplette Funktion verlangt wird,
die vollständige Funktion ausgeben.

Wenn ausdrücklich die komplette Datei verlangt wird,
die vollständige Datei ausgeben.

## Keine Annahmen

Nie annehmen, dass etwas existiert.

Vor Verwendung suchen nach:

* Partial
* ViewHelper
* Klasse
* Methode
* Icon-Identifier
* Route
* XLF-Key
* TCA-Feld
* JSON-Key
* Generator-Konfiguration
* Controller-Action

## Abschlussprüfung

Vor Abschluss einer Änderung prüfen, soweit relevant:

* PHP-Syntax
* Namespaces
* `use`-Statements
* TYPO3 APIs
* Deprecated APIs
* Fluid-Syntax
* ViewHelper-Namen
* ViewHelper-Argumente
* Partials vorhanden
* Controller-Argumente
* Action Links
* Tabellenstruktur
* CSP
* Nonces
* JavaScript
* CSS-Scope
* XLF-Schlüssel
* TCA
* Datenbank
* Projekt-JSON
* Generator-Übergabe
* generierter Code
* Editor-Rückgabe
* Rückwärtskompatibilität

## Nicht machen

Nicht:

* TYPO3 APIs erfinden
* Fluid ViewHelper erfinden
* Partials erfinden
* Dateien annehmen, ohne sie zu prüfen
* CSP global abschalten
* `unsafe-inline` als Standardlösung verwenden
* deprecated TYPO3 APIs neu einbauen
* persistente JSON-Schlüssel ungeprüft umbenennen
* Generator-Datenstrukturen ungeprüft verändern
* generierten Code als alleinige Fehlerquelle annehmen
* bestehende Features ohne Auftrag entfernen
* unnötige Refactorings durchführen
* nicht betroffenen Code neu formatieren
* Debug-Ausgaben dauerhaft stehen lassen
* Sicherheitsprüfungen für eine schnelle Lösung deaktivieren
* Rückwärtskompatibilität ohne ausdrücklichen Auftrag brechen
