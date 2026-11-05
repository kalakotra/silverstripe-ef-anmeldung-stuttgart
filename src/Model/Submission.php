<?php

declare(strict_types=1);

namespace Kalakotra\EFormAnmeldungStuttgart\Model;

use app\forms\basemodel\BaseSubmission;
use mikehaertl\pdftk\Pdf;
use RuntimeException;
use setasign\Fpdi\Fpdi;

/**
 * Anmeldung Stuttgart (Meldeschein 32/42-0034) submission.
 *
 * Mapping conventions (see BaseSubmission::getArrayDataForPDF):
 *   'PDF field' => 'DbField'                       plain value
 *   'PDF field' => 'Method:DbField:Arg'            computed value (always exactly 3 parts)
 *
 * All dates on the Meldeschein are six single-character fields (_T1 _T2 _M1 _M2 _J1 _J2 = DDMMYY),
 * mapped with DateDigit:Field:<0-5>. Page 4 repeats the new address and persons 1-4 under the same
 * field names, so they are filled once. Fixed-size fonts: step fields limit the input length.
 */
class Submission extends BaseSubmission
{
    private static string $table_name = 'Kalakotra_EFormAnmeldungStuttgart_Submission';

    private static array $db = [
        // Step 1 - Neue Wohnung
        'MoveInDate' => 'Date',
        'PostalCode' => 'Varchar(5)',
        'Street' => 'Varchar(45)',
        'AddressAddition' => 'Varchar(45)',
        'PersonCount' => 'Varchar(1)',

        // Step 2 - Bisherige Wohnung
        'PreviousResidence' => 'Varchar(10)',
        'PreviousCity' => 'Varchar(45)',
        'PreviousStreet' => 'Varchar(45)',
        'LastDomesticAddress' => 'Varchar(45)',

        // Step 3 - Weitere Wohnungen / Hauptwohnung
        'HasOtherApartment' => 'Varchar(3)',
        'OtherApartmentPerson' => 'Varchar(3)',
        'OtherApartmentAddress' => 'Varchar(80)',
        'MainResidenceGroup' => 'Varchar(10)',
        'MainResidencePrevious' => 'Varchar(10)',
        'MainResidenceFuture' => 'Varchar(10)',

        // Person 1 (Form_Person1)
        'Person1LastName' => 'Varchar(40)',
        'Person1FirstNames' => 'Varchar(28)',
        'Person1Gender' => 'Varchar(1)',
        'Person1BirthDate' => 'Date',
        'Person1BirthPlace' => 'Varchar(26)',
        'Person1MaritalStatus' => 'Varchar(2)',
        'Person1MaritalSince' => 'Date',
        'Person1MarriagePlace' => 'Varchar(18)',
        'Person1Religion' => 'Varchar(4)',
        'Person1Nationality' => 'Varchar(22)',
        'Person1IdType' => 'Varchar(4)',
        'Person1IdNumber' => 'Varchar(12)',
        'Person1IdAuthority' => 'Varchar(40)',
        'Person1IdIssueDate' => 'Date',
        'Person1IdValidUntil' => 'Date',

        // Person 2 (Form_Person2)
        'Person2LastName' => 'Varchar(40)',
        'Person2FirstNames' => 'Varchar(28)',
        'Person2Gender' => 'Varchar(1)',
        'Person2BirthDate' => 'Date',
        'Person2BirthPlace' => 'Varchar(26)',
        'Person2MaritalStatus' => 'Varchar(2)',
        'Person2MaritalSince' => 'Date',
        'Person2MarriagePlace' => 'Varchar(18)',
        'Person2Religion' => 'Varchar(4)',
        'Person2Nationality' => 'Varchar(22)',
        'Person2IdType' => 'Varchar(4)',
        'Person2IdNumber' => 'Varchar(12)',
        'Person2IdAuthority' => 'Varchar(40)',
        'Person2IdIssueDate' => 'Date',
        'Person2IdValidUntil' => 'Date',

        // Person 3 (Form_Person3)
        'Person3LastName' => 'Varchar(40)',
        'Person3FirstNames' => 'Varchar(28)',
        'Person3Gender' => 'Varchar(1)',
        'Person3BirthDate' => 'Date',
        'Person3BirthPlace' => 'Varchar(26)',
        'Person3MaritalStatus' => 'Varchar(2)',
        'Person3MaritalSince' => 'Date',
        'Person3MarriagePlace' => 'Varchar(18)',
        'Person3Religion' => 'Varchar(4)',
        'Person3Nationality' => 'Varchar(22)',
        'Person3IdType' => 'Varchar(4)',
        'Person3IdNumber' => 'Varchar(12)',
        'Person3IdAuthority' => 'Varchar(40)',
        'Person3IdIssueDate' => 'Date',
        'Person3IdValidUntil' => 'Date',

        // Person 4 (Form_Person4)
        'Person4LastName' => 'Varchar(40)',
        'Person4FirstNames' => 'Varchar(28)',
        'Person4Gender' => 'Varchar(1)',
        'Person4BirthDate' => 'Date',
        'Person4BirthPlace' => 'Varchar(26)',
        'Person4MaritalStatus' => 'Varchar(2)',
        'Person4MaritalSince' => 'Date',
        'Person4MarriagePlace' => 'Varchar(18)',
        'Person4Religion' => 'Varchar(4)',
        'Person4Nationality' => 'Varchar(22)',
        'Person4IdType' => 'Varchar(4)',
        'Person4IdNumber' => 'Varchar(12)',
        'Person4IdAuthority' => 'Varchar(40)',
        'Person4IdIssueDate' => 'Date',
        'Person4IdValidUntil' => 'Date',

        // Step 8 - Anschrift am 1. September 1939
        'Address1939Applies' => 'Varchar(3)',
        'Address1939Person1' => 'Varchar(1)',
        'Address1939Text1' => 'Varchar(60)',
        'Address1939Person2' => 'Varchar(1)',
        'Address1939Text2' => 'Varchar(60)',

        // Step 9 - nicht mitgemeldete Familienangehörige
        'RelativesCount' => 'Varchar(1)',
        'Relative1Person' => 'Varchar(1)',
        'Relative1Type' => 'Varchar(2)',
        'Relative1Name' => 'Varchar(24)',
        'Relative1Gender' => 'Varchar(1)',
        'Relative1BirthDate' => 'Date',
        'Relative1Religion' => 'Varchar(4)',
        'Relative1Address' => 'Varchar(38)',
        'Relative2Person' => 'Varchar(1)',
        'Relative2Type' => 'Varchar(2)',
        'Relative2Name' => 'Varchar(24)',
        'Relative2Gender' => 'Varchar(1)',
        'Relative2BirthDate' => 'Date',
        'Relative2Religion' => 'Varchar(4)',
        'Relative2Address' => 'Varchar(38)',
        'Relative3Person' => 'Varchar(1)',
        'Relative3Type' => 'Varchar(2)',
        'Relative3Name' => 'Varchar(24)',
        'Relative3Gender' => 'Varchar(1)',
        'Relative3BirthDate' => 'Date',
        'Relative3Religion' => 'Varchar(4)',
        'Relative3Address' => 'Varchar(38)',

        // Step 10 - Amtsblatt (page 4)
        'AmtsblattTrial' => 'Varchar(3)',

        // Signature date (page 3, after the pre-printed "Stuttgart,")
    ];

    private static array $field_mapping = [
        // Step 1 - Neue Wohnung
        'txtEinzug_T1' => 'DateDigit:MoveInDate:0',
        'txtEinzug_T2' => 'DateDigit:MoveInDate:1',
        'txtEinzug_M1' => 'DateDigit:MoveInDate:2',
        'txtEinzug_M2' => 'DateDigit:MoveInDate:3',
        'txtEinzug_J1' => 'DateDigit:MoveInDate:4',
        'txtEinzug_J2' => 'DateDigit:MoveInDate:5',
        'txtPLZNeueWohnung' => 'PostalCode',
        'txtAnschr1NeueWohnung' => 'Street',
        'txtAnschr2NeueWohnung' => 'AddressAddition',

        // Step 2 - Bisherige Wohnung
        'txtPLZAlteWohnung' => 'PreviousCity',
        'txtStrAlteWohnung' => 'PreviousStreet',
        'txtZuzugAlteWohnung' => 'LastDomesticAddress',

        // Step 3 - Weitere Wohnungen / Hauptwohnung
        'txtNr1' => 'PersonRef:OtherApartmentPerson:1',
        'txtAnschrWeitereWohnung' => 'OtherApartmentAddress',
        'txtNr2' => 'MainResidenceRef:OtherApartmentPerson:married',
        'txtVerheirateteBisher' => 'MainResidenceRow:MainResidencePrevious:married',
        'txtVerheirateteKuenftig' => 'MainResidenceRow:MainResidenceFuture:married',
        'txtNr3' => 'MainResidenceRef:OtherApartmentPerson:minor',
        'txtMinderjaehrigeBisher' => 'MainResidenceRow:MainResidencePrevious:minor',
        'txtMinderjaehrigeKuenftig' => 'MainResidenceRow:MainResidenceFuture:minor',
        'txtNr4' => 'MainResidenceRef:OtherApartmentPerson:other',
        'txtPersonenBisher' => 'MainResidenceRow:MainResidencePrevious:other',
        'txtPersonenKuenftig' => 'MainResidenceRow:MainResidenceFuture:other',

        // Person 1 (Form_Person1)
        'txtFamName1' => 'Person1LastName',
        'txtVorname1' => 'Person1FirstNames',
        'txtGeschl1' => 'Person1Gender',
        'txtGebDatum1_T1' => 'DateDigit:Person1BirthDate:0',
        'txtGebDatum1_T2' => 'DateDigit:Person1BirthDate:1',
        'txtGebDatum1_M1' => 'DateDigit:Person1BirthDate:2',
        'txtGebDatum1_M2' => 'DateDigit:Person1BirthDate:3',
        'txtGebDatum1_J1' => 'DateDigit:Person1BirthDate:4',
        'txtGebDatum1_J2' => 'DateDigit:Person1BirthDate:5',
        'txtGebOrt1' => 'Person1BirthPlace',
        'txtFamStand1' => 'Person1MaritalStatus',
        'txtFamStand1_T1' => 'DateDigit:Person1MaritalSince:0',
        'txtFamStand1_T2' => 'DateDigit:Person1MaritalSince:1',
        'txtFamStand1_M1' => 'DateDigit:Person1MaritalSince:2',
        'txtFamStand1_M2' => 'DateDigit:Person1MaritalSince:3',
        'txtFamStand1_J1' => 'DateDigit:Person1MaritalSince:4',
        'txtFamStand1_J2' => 'DateDigit:Person1MaritalSince:5',
        'txtOrt1' => 'Person1MarriagePlace',
        'txtReligion1' => 'PdfValue:Person1Religion:religion',
        'txtStAng1' => 'Person1Nationality',
        'txtArt1' => 'PdfValue:Person1IdType:id_type',
        'txtSerienNr1' => 'Person1IdNumber',
        'txtAusstBeh1' => 'Person1IdAuthority',
        'txtAusstDatum1_T1' => 'DateDigit:Person1IdIssueDate:0',
        'txtAusstDatum1_T2' => 'DateDigit:Person1IdIssueDate:1',
        'txtAusstDatum1_M1' => 'DateDigit:Person1IdIssueDate:2',
        'txtAusstDatum1_M2' => 'DateDigit:Person1IdIssueDate:3',
        'txtAusstDatum1_J1' => 'DateDigit:Person1IdIssueDate:4',
        'txtAusstDatum1_J2' => 'DateDigit:Person1IdIssueDate:5',
        'txtgueltig1_T1' => 'DateDigit:Person1IdValidUntil:0',
        'txtgueltig1_T2' => 'DateDigit:Person1IdValidUntil:1',
        'txtgueltig1_M1' => 'DateDigit:Person1IdValidUntil:2',
        'txtgueltig1_M2' => 'DateDigit:Person1IdValidUntil:3',
        'txtgueltig1_J1' => 'DateDigit:Person1IdValidUntil:4',
        'txtgueltig1_J2' => 'DateDigit:Person1IdValidUntil:5',

        // Person 2 (Form_Person2)
        'txtFamName2' => 'Person2LastName',
        'txtVorname2' => 'Person2FirstNames',
        'txtGeschl2' => 'Person2Gender',
        'txtGebDatum2_T1' => 'DateDigit:Person2BirthDate:0',
        'txtGebDatum2_T2' => 'DateDigit:Person2BirthDate:1',
        'txtGebDatum2_M1' => 'DateDigit:Person2BirthDate:2',
        'txtGebDatum2_M2' => 'DateDigit:Person2BirthDate:3',
        'txtGebDatum2_J1' => 'DateDigit:Person2BirthDate:4',
        'txtGebDatum2_J2' => 'DateDigit:Person2BirthDate:5',
        'txtGebOrt2' => 'Person2BirthPlace',
        'txtFamStand2' => 'Person2MaritalStatus',
        'txtFamStand2_T1' => 'DateDigit:Person2MaritalSince:0',
        'txtFamStand2_T2' => 'DateDigit:Person2MaritalSince:1',
        'txtFamStand2_M1' => 'DateDigit:Person2MaritalSince:2',
        'txtFamStand2_M2' => 'DateDigit:Person2MaritalSince:3',
        'txtFamStand2_J1' => 'DateDigit:Person2MaritalSince:4',
        'txtFamStand2_J2' => 'DateDigit:Person2MaritalSince:5',
        'txtOrt2' => 'Person2MarriagePlace',
        'txtReligion2' => 'PdfValue:Person2Religion:religion',
        'txtStAng2' => 'Person2Nationality',
        'txtArt2' => 'PdfValue:Person2IdType:id_type',
        'txtSerienNr2' => 'Person2IdNumber',
        'txtAusstBeh2' => 'Person2IdAuthority',
        'txtAusstDatum2_T1' => 'DateDigit:Person2IdIssueDate:0',
        'txtAusstDatum2_T2' => 'DateDigit:Person2IdIssueDate:1',
        'txtAusstDatum2_M1' => 'DateDigit:Person2IdIssueDate:2',
        'txtAusstDatum2_M2' => 'DateDigit:Person2IdIssueDate:3',
        'txtAusstDatum2_J1' => 'DateDigit:Person2IdIssueDate:4',
        'txtAusstDatum2_J2' => 'DateDigit:Person2IdIssueDate:5',
        'txtgueltig2_T1' => 'DateDigit:Person2IdValidUntil:0',
        'txtgueltig2_T2' => 'DateDigit:Person2IdValidUntil:1',
        'txtgueltig2_M1' => 'DateDigit:Person2IdValidUntil:2',
        'txtgueltig2_M2' => 'DateDigit:Person2IdValidUntil:3',
        'txtgueltig2_J1' => 'DateDigit:Person2IdValidUntil:4',
        'txtgueltig2_J2' => 'DateDigit:Person2IdValidUntil:5',

        // Person 3 (Form_Person3)
        'txtFamName3' => 'Person3LastName',
        'txtVorname3' => 'Person3FirstNames',
        'txtGeschl3' => 'Person3Gender',
        'txtGebDatum3_T1' => 'DateDigit:Person3BirthDate:0',
        'txtGebDatum3_T2' => 'DateDigit:Person3BirthDate:1',
        'txtGebDatum3_M1' => 'DateDigit:Person3BirthDate:2',
        'txtGebDatum3_M2' => 'DateDigit:Person3BirthDate:3',
        'txtGebDatum3_J1' => 'DateDigit:Person3BirthDate:4',
        'txtGebDatum3_J2' => 'DateDigit:Person3BirthDate:5',
        'txtGebOrt3' => 'Person3BirthPlace',
        'txtFamStand3' => 'Person3MaritalStatus',
        'txtFamStand3_T1' => 'DateDigit:Person3MaritalSince:0',
        'txtFamStand3_T2' => 'DateDigit:Person3MaritalSince:1',
        'txtFamStand3_M1' => 'DateDigit:Person3MaritalSince:2',
        'txtFamStand3_M2' => 'DateDigit:Person3MaritalSince:3',
        'txtFamStand3_J1' => 'DateDigit:Person3MaritalSince:4',
        'txtFamStand3_J2' => 'DateDigit:Person3MaritalSince:5',
        'txtOrt3' => 'Person3MarriagePlace',
        'txtReligion3' => 'PdfValue:Person3Religion:religion',
        'txtStAng3' => 'Person3Nationality',
        'txtArt3' => 'PdfValue:Person3IdType:id_type',
        'txtSerienNr3' => 'Person3IdNumber',
        'txtAusstBeh3' => 'Person3IdAuthority',
        'txtAusstDatum3_T1' => 'DateDigit:Person3IdIssueDate:0',
        'txtAusstDatum3_T2' => 'DateDigit:Person3IdIssueDate:1',
        'txtAusstDatum3_M1' => 'DateDigit:Person3IdIssueDate:2',
        'txtAusstDatum3_M2' => 'DateDigit:Person3IdIssueDate:3',
        'txtAusstDatum3_J1' => 'DateDigit:Person3IdIssueDate:4',
        'txtAusstDatum3_J2' => 'DateDigit:Person3IdIssueDate:5',
        'txtgueltig3_T1' => 'DateDigit:Person3IdValidUntil:0',
        'txtgueltig3_T2' => 'DateDigit:Person3IdValidUntil:1',
        'txtgueltig3_M1' => 'DateDigit:Person3IdValidUntil:2',
        'txtgueltig3_M2' => 'DateDigit:Person3IdValidUntil:3',
        'txtgueltig3_J1' => 'DateDigit:Person3IdValidUntil:4',
        'txtgueltig3_J2' => 'DateDigit:Person3IdValidUntil:5',

        // Person 4 (Form_Person4)
        'txtFamName4' => 'Person4LastName',
        'txtVorname4' => 'Person4FirstNames',
        'txtGeschl4' => 'Person4Gender',
        'txtGebDatum4_T1' => 'DateDigit:Person4BirthDate:0',
        'txtGebDatum4_T2' => 'DateDigit:Person4BirthDate:1',
        'txtGebDatum4_M1' => 'DateDigit:Person4BirthDate:2',
        'txtGebDatum4_M2' => 'DateDigit:Person4BirthDate:3',
        'txtGebDatum4_J1' => 'DateDigit:Person4BirthDate:4',
        'txtGebDatum4_J2' => 'DateDigit:Person4BirthDate:5',
        'txtGebOrt4' => 'Person4BirthPlace',
        'txtFamStand4' => 'Person4MaritalStatus',
        'txtFamStand4_T1' => 'DateDigit:Person4MaritalSince:0',
        'txtFamStand4_T2' => 'DateDigit:Person4MaritalSince:1',
        'txtFamStand4_M1' => 'DateDigit:Person4MaritalSince:2',
        'txtFamStand4_M2' => 'DateDigit:Person4MaritalSince:3',
        'txtFamStand4_J1' => 'DateDigit:Person4MaritalSince:4',
        'txtFamStand4_J2' => 'DateDigit:Person4MaritalSince:5',
        'txtOrt4' => 'Person4MarriagePlace',
        'txtReligion4' => 'PdfValue:Person4Religion:religion',
        'txtStAng4' => 'Person4Nationality',
        'txtArt4' => 'PdfValue:Person4IdType:id_type',
        'txtSerienNr4' => 'Person4IdNumber',
        'txtAusstBeh4' => 'Person4IdAuthority',
        'txtAusstDatum4_T1' => 'DateDigit:Person4IdIssueDate:0',
        'txtAusstDatum4_T2' => 'DateDigit:Person4IdIssueDate:1',
        'txtAusstDatum4_M1' => 'DateDigit:Person4IdIssueDate:2',
        'txtAusstDatum4_M2' => 'DateDigit:Person4IdIssueDate:3',
        'txtAusstDatum4_J1' => 'DateDigit:Person4IdIssueDate:4',
        'txtAusstDatum4_J2' => 'DateDigit:Person4IdIssueDate:5',
        'txtgueltig4_T1' => 'DateDigit:Person4IdValidUntil:0',
        'txtgueltig4_T2' => 'DateDigit:Person4IdValidUntil:1',
        'txtgueltig4_M1' => 'DateDigit:Person4IdValidUntil:2',
        'txtgueltig4_M2' => 'DateDigit:Person4IdValidUntil:3',
        'txtgueltig4_J1' => 'DateDigit:Person4IdValidUntil:4',
        'txtgueltig4_J2' => 'DateDigit:Person4IdValidUntil:5',

        // Step 8 - Anschrift am 1. September 1939
        'txtNr1FamBuch' => 'Address1939Person1',
        'txtAnschr1' => 'Address1939Text1',
        'txtNr2FamBuch' => 'Address1939Person2',
        'txtAnschr2' => 'Address1939Text2',

        // Step 9 - nicht mitgemeldete Familienangehörige
        'txtNr1E' => 'Relative1Person',
        'txtEhegatte1' => 'PdfValue:Relative1Type:relative_type',
        'txtFamName1E' => 'Relative1Name',
        'txtGeschl5' => 'Relative1Gender',
        'txtGebDatum1E_T1' => 'DateDigit:Relative1BirthDate:0',
        'txtGebDatum1E_T2' => 'DateDigit:Relative1BirthDate:1',
        'txtGebDatum1E_M1' => 'DateDigit:Relative1BirthDate:2',
        'txtGebDatum1E_M2' => 'DateDigit:Relative1BirthDate:3',
        'txtGebDatum1E_J1' => 'DateDigit:Relative1BirthDate:4',
        'txtGebDatum1E_J2' => 'DateDigit:Relative1BirthDate:5',
        'txtReli1E' => 'PdfValue:Relative1Religion:religion',
        'txtAnschr1E' => 'Relative1Address',
        'txtNr2E' => 'Relative2Person',
        'txtEhegatte2' => 'PdfValue:Relative2Type:relative_type',
        'txtFamName2E' => 'Relative2Name',
        'txtGeschl6' => 'Relative2Gender',
        'txtGebDatum2E_T1' => 'DateDigit:Relative2BirthDate:0',
        'txtGebDatum2E_T2' => 'DateDigit:Relative2BirthDate:1',
        'txtGebDatum2E_M1' => 'DateDigit:Relative2BirthDate:2',
        'txtGebDatum2E_M2' => 'DateDigit:Relative2BirthDate:3',
        'txtGebDatum2E_J1' => 'DateDigit:Relative2BirthDate:4',
        'txtGebDatum2E_J2' => 'DateDigit:Relative2BirthDate:5',
        'txtReli2E' => 'PdfValue:Relative2Religion:religion',
        'txtAnschr2E' => 'Relative2Address',
        'txtNr3E' => 'Relative3Person',
        'txtEhegatte3' => 'PdfValue:Relative3Type:relative_type',
        'txtFamName3E' => 'Relative3Name',
        'txtGeschl7' => 'Relative3Gender',
        'txtGebDatum3E_T1' => 'DateDigit:Relative3BirthDate:0',
        'txtGebDatum3E_T2' => 'DateDigit:Relative3BirthDate:1',
        'txtGebDatum3E_M1' => 'DateDigit:Relative3BirthDate:2',
        'txtGebDatum3E_M2' => 'DateDigit:Relative3BirthDate:3',
        'txtGebDatum3E_J1' => 'DateDigit:Relative3BirthDate:4',
        'txtGebDatum3E_J2' => 'DateDigit:Relative3BirthDate:5',
        'txtReli3E' => 'PdfValue:Relative3Religion:religion',
        'txtAnschr3E' => 'Relative3Address',

        // Step 10 - Amtsblatt (page 4)
        'grpZusendung' => 'StateValue:AmtsblattTrial:grpZusendung',

        // Signature date (page 3, after the pre-printed "Stuttgart,")
        'txtDatum' => 'SignatureDate:Created:d.m.Y',
    ];

    /**
     * Radio group on-states: stored value -> PDF state (StateValue:DbField:PdfField).
     */
    private static array $state_map = [
        'grpZusendung' => ['yes' => 'optJa', 'no' => 'optNein'],
    ];

    /**
     * Stored value -> text printed into the PDF (PdfValue:DbField:<set>); values not listed are printed as stored.
     * A dash marks "not applicable" (Erläuterung 2).
     */
    private static array $pdf_values = [
        'religion' => ['none' => '-'],
        'id_type' => ['none' => '-'],
        'relative_type' => ['GV' => 'ges.V.'],
        'residence' => [
            'previous' => 'bisherige Wohnung',
            'other' => 'weitere Wohnung',
            'new' => 'neue Wohnung',
            'none' => '-',
        ],
    ];

    /**
     * Declarative dependency rules (see FieldConditions), mirrored by display-logic in the steps.
     * Parents before children. The PersonCount rules are whole-step conditions (no display-logic).
     */
    private static array $field_conditions = [
        [['PreviousCity', 'PreviousStreet'], ['PreviousResidence' => 'domestic'], ['PreviousResidence' => 'abroad']],
        [['LastDomesticAddress'], ['PreviousResidence' => 'abroad']],
        [['OtherApartmentPerson', 'OtherApartmentAddress', 'MainResidenceGroup', 'MainResidencePrevious', 'MainResidenceFuture'], ['HasOtherApartment' => 'yes']],
        [['Person1MaritalSince', 'Person1MarriagePlace'], ['Person1MaritalStatus' => '!LD']],
        [['Person1IdNumber', 'Person1IdAuthority', 'Person1IdIssueDate', 'Person1IdValidUntil'], ['Person1IdType' => '!none']],
        [['Person2MaritalSince', 'Person2MarriagePlace'], ['Person2MaritalStatus' => '!LD']],
        [['Person2IdNumber', 'Person2IdAuthority', 'Person2IdIssueDate', 'Person2IdValidUntil'], ['Person2IdType' => '!none']],
        [['Person3MaritalSince', 'Person3MarriagePlace'], ['Person3MaritalStatus' => '!LD']],
        [['Person3IdNumber', 'Person3IdAuthority', 'Person3IdIssueDate', 'Person3IdValidUntil'], ['Person3IdType' => '!none']],
        [['Person4MaritalSince', 'Person4MarriagePlace'], ['Person4MaritalStatus' => '!LD']],
        [['Person4IdNumber', 'Person4IdAuthority', 'Person4IdIssueDate', 'Person4IdValidUntil'], ['Person4IdType' => '!none']],
        [['Address1939Person1', 'Address1939Text1', 'Address1939Person2', 'Address1939Text2'], ['Address1939Applies' => 'yes']],
        [['Relative1Person', 'Relative1Type', 'Relative1Name', 'Relative1Gender', 'Relative1BirthDate', 'Relative1Religion', 'Relative1Address'], ['RelativesCount' => ['1', '2', '3']]],
        [['Relative2Person', 'Relative2Type', 'Relative2Name', 'Relative2Gender', 'Relative2BirthDate', 'Relative2Religion', 'Relative2Address'], ['RelativesCount' => ['2', '3']]],
        [['Relative3Person', 'Relative3Type', 'Relative3Name', 'Relative3Gender', 'Relative3BirthDate', 'Relative3Religion', 'Relative3Address'], ['RelativesCount' => ['3']]],
        [['Person2LastName', 'Person2FirstNames', 'Person2Gender', 'Person2BirthDate', 'Person2BirthPlace', 'Person2MaritalStatus', 'Person2MaritalSince', 'Person2MarriagePlace', 'Person2Religion', 'Person2Nationality', 'Person2IdType', 'Person2IdNumber', 'Person2IdAuthority', 'Person2IdIssueDate', 'Person2IdValidUntil'], ['PersonCount' => ['2', '3', '4']]],
        [['Person3LastName', 'Person3FirstNames', 'Person3Gender', 'Person3BirthDate', 'Person3BirthPlace', 'Person3MaritalStatus', 'Person3MaritalSince', 'Person3MarriagePlace', 'Person3Religion', 'Person3Nationality', 'Person3IdType', 'Person3IdNumber', 'Person3IdAuthority', 'Person3IdIssueDate', 'Person3IdValidUntil'], ['PersonCount' => ['3', '4']]],
        [['Person4LastName', 'Person4FirstNames', 'Person4Gender', 'Person4BirthDate', 'Person4BirthPlace', 'Person4MaritalStatus', 'Person4MaritalSince', 'Person4MarriagePlace', 'Person4Religion', 'Person4Nationality', 'Person4IdType', 'Person4IdNumber', 'Person4IdAuthority', 'Person4IdIssueDate', 'Person4IdValidUntil'], ['PersonCount' => ['4']]],
    ];

    /**
     * Signatures in mm from the top-left page corner. Negative page = counted from the end
     * (-2 = Meldeschein, -1 = Erklärung Amtsblatt), so the module also works with a PDF without
     * the two explanation pages. PDFMultiFormPage.SignaturePositionX/Y override the "main" position.
     */
    private static array $signature_positions = [
        'main' => ['page' => -2, 'x' => 155.0, 'y' => 276.0, 'width' => 35.0],
        'amtsblatt' => ['page' => -1, 'x' => 20.0, 'y' => 187.0, 'width' => 35.0],
    ];

    /**
     * White boxes drawn over flattened push buttons ("Drucken", "Alle Feldinhalte löschen"), mm.
     */
    private static array $mask_areas = [
        ['page' => -1, 'x' => 12.5, 'y' => 209.0, 'width' => 71.5, 'height' => 7.5],
    ];

    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        $rules = (array) $this->config()->get('field_conditions');
        $data = [];
        foreach (FieldConditions::referencedFields($rules) as $field) {
            $data[$field] = $this->getField($field);
        }

        $cleaned = FieldConditions::apply($data, $rules);
        foreach ($cleaned as $field => $value) {
            if ($value === null && $data[$field] !== null) {
                $this->setField($field, null);
            }
        }
    }

    /**
     * Characters outside Windows-1252 that are common in names/places of DACH, BiH, HR, RS, TR, PL, CZ, RO users,
     * mapped to the closest WinAnsi character (diacritic removed, ICAO 9303 style).
     */
    private const PDF_TRANSLITERATION = [
        'Ć' => 'C', 'ć' => 'c', 'Č' => 'C', 'č' => 'c', 'Đ' => 'D', 'đ' => 'd', 'Ď' => 'D', 'ď' => 'd',
        'Ğ' => 'G', 'ğ' => 'g', 'İ' => 'I', 'ı' => 'i', 'Ş' => 'S', 'ş' => 's', 'Ș' => 'S', 'ș' => 's',
        'Ț' => 'T', 'ț' => 't', 'Ţ' => 'T', 'ţ' => 't', 'Ă' => 'A', 'ă' => 'a', 'Ą' => 'A', 'ą' => 'a',
        'Ę' => 'E', 'ę' => 'e', 'Ě' => 'E', 'ě' => 'e', 'Ł' => 'L', 'ł' => 'l', 'Ń' => 'N', 'ń' => 'n',
        'Ň' => 'N', 'ň' => 'n', 'Ř' => 'R', 'ř' => 'r', 'Ś' => 'S', 'ś' => 's', 'Ť' => 'T', 'ť' => 't',
        'Ů' => 'U', 'ů' => 'u', 'Ź' => 'Z', 'ź' => 'z', 'Ż' => 'Z', 'ż' => 'z', 'Ő' => 'O', 'ő' => 'o',
        'Ű' => 'U', 'ű' => 'u', 'Ľ' => 'L', 'ľ' => 'l', 'Ĺ' => 'L', 'ĺ' => 'l', 'Ŕ' => 'R', 'ŕ' => 'r',
        'Ā' => 'A', 'ā' => 'a', 'Ē' => 'E', 'ē' => 'e', 'Ī' => 'I', 'ī' => 'i', 'Ū' => 'U', 'ū' => 'u',
        'Ė' => 'E', 'ė' => 'e', 'Į' => 'I', 'į' => 'i', 'Ų' => 'U', 'ų' => 'u', 'Ģ' => 'G', 'ģ' => 'g',
        'Ķ' => 'K', 'ķ' => 'k', 'Ļ' => 'L', 'ļ' => 'l', 'Ņ' => 'N', 'ņ' => 'n', 'Ə' => 'A', 'ə' => 'a',
        '‐' => '-', '‑' => '-', '‒' => '-', '―' => '-', '″' => '"', '′' => "'",
    ];

    /**
     * The form fields use a WinAnsi font: characters outside Windows-1252 (ć, č, đ, ş, ł ...) would be
     * dropped from the flattened PDF ("Hadžić" -> "Hadži"), so they are transliterated before filling.
     *
     * @return array<string, mixed>
     */
    public function getArrayDataForPDF(): array
    {
        return array_map(
            static fn ($value) => is_string($value) ? self::toWinAnsi($value) : $value,
            parent::getArrayDataForPDF()
        );
    }

    public static function toWinAnsi(string $value): string
    {
        $value = strtr($value, self::PDF_TRANSLITERATION);

        // anything else outside Windows-1252 (e.g. Cyrillic): best-effort transliteration, never silent loss
        return (string) preg_replace_callback('/[^\x{0000}-\x{00FF}€‚ƒ„…†‡ˆ‰Š‹ŒŽ‘’“”•–—˜™š›œžŸ]/u', static function (array $m): string {
            $converted = function_exists('transliterator_transliterate')
                ? transliterator_transliterate('Any-Latin; Latin-ASCII', $m[0])
                : @iconv('UTF-8', 'ASCII//TRANSLIT', $m[0]);

            return is_string($converted) && $converted !== '' ? $converted : '?';
        }, $value);
    }

    // ---------------------------------------------------------------------------------------------
    // Mapping helpers (signature fixed by BaseSubmission::getArrayDataForPDF: method($field, $arg))
    // ---------------------------------------------------------------------------------------------

    /** Radio group: stored value -> PDF on-state of $pdfField (via $state_map). */
    public function StateValue(string $fieldName, string $pdfField): string
    {
        $map = (array) ($this->config()->get('state_map')[$pdfField] ?? []);
        $value = (string) ($this->getField($fieldName) ?? '');

        return (string) ($map[$value] ?? '');
    }

    /** One character of a date in DDMMYY form (single-character boxes on the Meldeschein). */
    public function DateDigit(string $fieldName, string $index): string
    {
        $value = (string) ($this->getField($fieldName) ?? '');
        $timestamp = $value !== '' ? strtotime($value) : false;
        if ($timestamp === false) {
            return '';
        }

        return substr(date('dmy', $timestamp), (int) $index, 1);
    }

    /** Stored value -> printed text from $pdf_values[$set]. */
    public function PdfValue(string $fieldName, string $set): string
    {
        $value = (string) ($this->getField($fieldName) ?? '');
        if ($value === '') {
            return '';
        }

        $map = (array) ($this->config()->get('pdf_values')[$set] ?? []);

        return (string) ($map[$value] ?? $value);
    }

    /** "zu lfd. Nr.": person number 1-4, empty for "all persons". */
    public function PersonRef(string $fieldName, string $unused = ''): string
    {
        $value = (string) ($this->getField($fieldName) ?? '');

        return ctype_digit($value) ? $value : '';
    }

    /** "zu lfd. Nr." of the Hauptwohnung row that matches the chosen group. */
    public function MainResidenceRef(string $fieldName, string $group): string
    {
        return $this->MainResidenceGroup === $group ? $this->PersonRef($fieldName) : '';
    }

    /** bisher / künftig of the Hauptwohnung row that matches the chosen group. */
    public function MainResidenceRow(string $fieldName, string $group): string
    {
        return $this->MainResidenceGroup === $group ? $this->PdfValue($fieldName, 'residence') : '';
    }

    /** Date of signing ("Ort, Datum" - the place "Stuttgart," is pre-printed). */
    public function SignatureDate(string $fieldName, string $format): string
    {
        $value = (string) ($this->getField($fieldName) ?? '');
        $timestamp = $value !== '' ? strtotime($value) : false;

        return date($format, $timestamp ?: time());
    }

    // ---------------------------------------------------------------------------------------------
    // PDF generation
    // ---------------------------------------------------------------------------------------------

    public function generateFilledPDF(): Pdf
    {
        $basePdfPath = getcwd() . '/assets/' . $this->FormSubmission()->getBasePDFLink('PDFFile');

        $pdf = new Pdf($basePdfPath);
        $pdf->fillForm($this->getArrayDataForPDF())
            ->needAppearances()
            ->flatten();

        $tmpFilled = sys_get_temp_dir() . '/anmeldung_stuttgart_filled_' . uniqid('', true) . '.pdf';
        if (!$pdf->saveAs($tmpFilled)) {
            throw new RuntimeException('pdftk error: ' . $pdf->getError());
        }

        $signature = trim((string) $this->Signature);
        $tmpSignature = $signature !== '' ? $this->writeSignatureImage($signature) : null;

        try {
            $outputPath = $this->postProcess($tmpFilled, $tmpSignature);
        } finally {
            if ($tmpSignature !== null) {
                @unlink($tmpSignature);
            }
            @unlink($tmpFilled);
        }

        return new Pdf($outputPath);
    }

    /**
     * Masks the flattened push buttons and stamps the signature(s):
     * always on the Meldeschein, on the Amtsblatt declaration only if it was answered.
     */
    private function postProcess(string $sourcePdf, ?string $signaturePng): string
    {
        $fpdi = new Fpdi();
        $fpdi->SetAutoPageBreak(false);
        $fpdi->SetFillColor(255, 255, 255);
        $pageCount = $fpdi->setSourceFile($sourcePdf);

        $stamps = $signaturePng !== null ? $this->signatureStamps($pageCount) : [];
        $masks = [];
        foreach ((array) $this->config()->get('mask_areas') as $area) {
            $masks[$this->resolvePage((int) $area['page'], $pageCount)][] = $area;
        }

        for ($i = 1; $i <= $pageCount; $i++) {
            $template = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($template);

            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $fpdi->useTemplate($template, 0, 0, $size['width'], $size['height']);

            foreach ($masks[$i] ?? [] as $area) {
                $fpdi->Rect((float) $area['x'], (float) $area['y'], (float) $area['width'], (float) $area['height'], 'F');
            }

            foreach ($stamps[$i] ?? [] as $position) {
                $fpdi->Image($signaturePng, $position['x'], $position['y'], $position['width'], 0, 'PNG');
            }
        }

        $outputPath = sys_get_temp_dir() . '/anmeldung_stuttgart_' . uniqid('', true) . '.pdf';
        $fpdi->Output('F', $outputPath);

        return $outputPath;
    }

    /**
     * @return array<int, array<int, array{x: float, y: float, width: float}>> page => positions
     */
    private function signatureStamps(int $pageCount): array
    {
        $positions = (array) $this->config()->get('signature_positions');
        $holder = $this->getMyHolderPage();
        $stamps = [];

        foreach ($positions as $key => $position) {
            if ($key === 'amtsblatt' && !in_array($this->AmtsblattTrial, ['yes', 'no'], true)) {
                continue;
            }

            $x = (float) $position['x'];
            $y = (float) $position['y'];
            if ($key === 'main') {
                $x = (float) ($holder?->SignaturePositionX ?: $x);
                $y = (float) ($holder?->SignaturePositionY ?: $y);
            }

            $page = $this->resolvePage((int) $position['page'], $pageCount);
            $stamps[$page][] = ['x' => $x, 'y' => $y, 'width' => (float) $position['width']];
        }

        return $stamps;
    }

    private function resolvePage(int $page, int $pageCount): int
    {
        return $page < 0 ? $pageCount + 1 + $page : $page;
    }

    /**
     * jSignature delivers a PNG data URI; base64 SVG is supported as fallback (needs Imagick).
     */
    private function writeSignatureImage(string $signature): string
    {
        $path = sys_get_temp_dir() . '/anmeldung_stuttgart_sig_' . uniqid('', true) . '.png';

        if (preg_match('#^data:image/png;base64,(.+)$#i', $signature, $matches)) {
            $png = base64_decode($matches[1], true);
            if ($png === false || file_put_contents($path, $png) === false) {
                throw new RuntimeException('Invalid PNG signature payload.');
            }

            return $path;
        }

        $svgBase64 = preg_match('#^data:image/svg\+xml(?:;charset=[^;]+)?;base64,(.+)$#i', $signature, $matches)
            ? $matches[1]
            : $signature;
        $svg = base64_decode($svgBase64, true);
        if ($svg === false) {
            throw new RuntimeException('Invalid signature payload.');
        }

        $imagick = new \Imagick();
        $imagick->readImageBlob($svg);
        $imagick->setBackgroundColor(new \ImagickPixel('white'));
        $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
        $imagick->setImageFormat('png');
        $imagick->writeImage($path);
        $imagick->clear();

        return $path;
    }
}
