# Anmeldung Stuttgart (Meldeschein)

Form module for the e-formulare.de PDF filler (`kalakotra/pdf-editor`).
Fills the registration form of the **Landeshauptstadt Stuttgart, Amt für öffentliche Ordnung**
(*Anmeldung*, form no. `32/42-0034-2602InV`, 4 pages, 205 fields) for up to 4 persons and stamps the signature(s).

Requires the pdf-editor app base classes (`app\forms\basemodel\*`, `app\models\FormSubmission`).

## Installation

pdf-editor `composer.json`:

```json
"repositories": [
    { "type": "git", "url": "https://github.com/kalakotra/silverstripe-ef-anmeldung-stuttgart" }
],
"require": {
    "kalakotra/silverstripe-ef-anmeldung-stuttgart": "^1.0"
}
```

```bash
composer update kalakotra/silverstripe-ef-anmeldung-stuttgart
vendor/bin/sake db:build --flush
```

`db:build` is mandatory: `DBClassName` is an enum, without it the new step classes load as the base
`MultiFormStep` ("Please define at least one $next_steps").

## CMS setup (PDFMultiFormPage)

| Tab | Setting |
|---|---|
| PDF | `PDF File` = original fillable `32-0034-anmeldung.pdf` (Stadt Stuttgart, 4 pages) |
| Form | `Form Class Name` = **Anmeldung Stuttgart (Meldeschein)** |
| Customising | `SignaturePositionX/Y` – leave empty; default X 155 mm / Y 276 mm on the Meldeschein page |

Only one PDF slot (`PDFFile`) is used. `Custom Field Mapping` is supported as usual.

## Flow

| Step | Content | Branching |
|---|---|---|
| 1 | Neue Wohnung: Tag des Einzugs, PLZ (Stuttgart 70173–70629), Anschrift, number of persons 1–4 | |
| 2 | Bisherige Wohnung (Erläuterung 4) | address only for "given up" / "abroad"; last German address only for "abroad" |
| 3 | Weitere Wohnungen + Hauptwohnung (Erläuterung 5) | details only if "yes" |
| Person 1–4 | Spalten 1–9, 11–15 | Person n → n+1 while n < number of persons, then step 8 |
| 8 | Anschrift am 1. September 1939 (Erläuterung 7) | rows only if "yes" |
| 9 | Ehegatten/LP, Kinder < 18, Eltern/ges. Vertreter not registered (max. 3) | rows by count |
| 10 | Amtsblatt Probeabo (page 4, optional) | page 4 signed only if answered |
| Preview | E-Mail, Unterschrift, AGB | |

## PDF specifics

- All dates are six single-character boxes `DDMMYY` (2-digit year) → `DateDigit:Field:0..5`.
- Text fields use a fixed font size: every text input has a `maxLength` matching the PDF column width.
- The form font is WinAnsi: characters outside Windows-1252 (ć, č, đ, ş, ł, ț …) would be dropped by pdftk,
  so `Submission::getArrayDataForPDF()` transliterates them (`Hadžić` → `Hadžic`, `Đorđe` → `Dorde`; š/ž/ä/ö/ü/ß stay).
- Page 4 repeats the new address and persons 1–4 under the same field names (filled once).
- The flattened push buttons "Drucken" / "Alle Feldinhalte löschen" are covered by a white box (`$mask_areas`).
- Signatures: Meldeschein (page −2) always, Amtsblatt declaration (page −1) only if answered.
  Negative page numbers count from the end, so a PDF without the two explanation pages works too.
- Religion codes: `ev rk ak lt rf fr iw`, none → `-`; ID types `PA VP RP KP PE`, none → `-`.

## How it works

- **Person steps** `Form_Person1..4` are separate classes sharing `PersonStepTrait` (prefixed fields
  `Person{n}*`, labels `_t(__TRAIT__ . '.X')`). Not subclasses: a DataList on a parent step class also returns
  subclass records, so multiform could load the wrong person step. `Form` resolves preview labels and step titles
  from the field titles.
- **`Submission::$field_mapping`** – `'PDF field' => 'DbField'` or `'PDF field' => 'Method:DbField:Arg'`.
- **`Submission::$field_conditions`** – declarative dependency rules, mirrored by display-logic; used for step
  cleanup, conditional required validation and `onBeforeWrite()` cleanup (incl. persons beyond the chosen count).
- **`Form::getActiveSteps()`** – submission and preview only use steps on the current path.

## Translations

`lang/*.yml`: ar, bg, bs, cs, de, en, es, fr, it, pl, ro, ru, tr, uk. Official German terms
(Meldebehörde, Wohnungsgeberbestätigung, Hauptwohnung, Amtsblatt, document/religion codes) are kept in parentheses.

## License

BSD-3-Clause, see [LICENSE](LICENSE).
