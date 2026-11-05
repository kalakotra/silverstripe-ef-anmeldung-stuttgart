<?php

declare(strict_types=1);

namespace Kalakotra\EFormAnmeldungStuttgart\Forms;

use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\MultiForm\Models\MultiFormStep;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * Ehegatten/Lebenspartner, Kinder unter 18 und Eltern/gesetzliche Vertreter of the registered persons
 * who are NOT registered on this Meldeschein (bottom table on page 3, max. 3 rows).
 */
class Form_Step9 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 9;

    private static $next_steps = Form_Step10::class;

    private static array $conditional_required = [
        'Relative1Person', 'Relative1Type', 'Relative1Name', 'Relative1Gender', 'Relative1BirthDate', 'Relative1Address',
        'Relative2Person', 'Relative2Type', 'Relative2Name', 'Relative2Gender', 'Relative2BirthDate', 'Relative2Address',
        'Relative3Person', 'Relative3Type', 'Relative3Name', 'Relative3Gender', 'Relative3BirthDate', 'Relative3Address',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Familienangehörige, die nicht mit angemeldet werden'), 4),
            DropdownField::create('RelativesCount', _t(
                __CLASS__ . '.RelativesCount',
                'Wie viele Ehegatten/Lebenspartner, Kinder unter 18 Jahren oder Eltern/gesetzliche Vertreter der angemeldeten '
                . 'Personen werden NICHT auf diesem Meldeschein angemeldet?'
            ))
                ->setSource([
                    '0' => _t(__CLASS__ . '.RelativesCount.0', 'keine'),
                    '1' => _t(__CLASS__ . '.RelativesCount.1', '1 Person'),
                    '2' => _t(__CLASS__ . '.RelativesCount.2', '2 Personen'),
                    '3' => _t(__CLASS__ . '.RelativesCount.3', '3 Personen'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --')
                ->setDescription(_t(
                    __CLASS__ . '.RelativesCountDescription',
                    'Bei Verwitweten auch der frühere Ehegatte/Lebenspartner. Nicht angeben, wer auf einem eigenen Meldeschein gemeldet wird.'
                )),
            Wrapper::create($this->relativeRow(1))
                ->displayIf('RelativesCount')->isEqualTo('1')
                ->orIf('RelativesCount')->isEqualTo('2')
                ->orIf('RelativesCount')->isEqualTo('3')->end(),
            Wrapper::create($this->relativeRow(2))
                ->displayIf('RelativesCount')->isEqualTo('2')
                ->orIf('RelativesCount')->isEqualTo('3')->end(),
            Wrapper::create($this->relativeRow(3))
                ->displayIf('RelativesCount')->isEqualTo('3')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'RelativesCount',
        ]);
    }

    /**
     * Fields of one table row; field names are suffixed, translation keys are literal and shared by all rows.
     */
    private function relativeRow(int $row): CompositeField
    {
        $r = 'Relative' . $row;
        $selectEmpty = '-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';

        return CompositeField::create(
            HeaderField::create($r . 'Header', _t(__CLASS__ . '.RelativeHeader', 'Familienangehörige/r {number}', ['number' => $row]), 5),
            DropdownField::create($r . 'Person', _t(__CLASS__ . '.RelativePerson', 'Gehört zu Person'))
                ->setSource([
                    '1' => _t(__CLASS__ . '.RelativePerson.1', 'Person 1'),
                    '2' => _t(__CLASS__ . '.RelativePerson.2', 'Person 2'),
                    '3' => _t(__CLASS__ . '.RelativePerson.3', 'Person 3'),
                    '4' => _t(__CLASS__ . '.RelativePerson.4', 'Person 4'),
                ])
                ->setEmptyString($selectEmpty),
            DropdownField::create($r . 'Type', _t(__CLASS__ . '.RelativeType', 'Verhältnis'))
                ->setSource([
                    'E' => _t(__CLASS__ . '.RelativeType.E', 'Ehegatte/Ehegattin (E)'),
                    'LP' => _t(__CLASS__ . '.RelativeType.LP', 'Lebenspartner/in (LP)'),
                    'K' => _t(__CLASS__ . '.RelativeType.K', 'Kind unter 18 Jahren (K)'),
                    'GV' => _t(__CLASS__ . '.RelativeType.GV', 'Elternteil / gesetzliche/r Vertreter/in (ges. Vertr.)'),
                ])
                ->setEmptyString($selectEmpty),
            TextField::create($r . 'Name', _t(__CLASS__ . '.RelativeName', 'Familienname, Vorname(n)'))
                ->setMaxLength(24),
            DropdownField::create($r . 'Gender', _t(__CLASS__ . '.RelativeGender', 'Geschlecht'))
                ->setSource([
                    'm' => _t(__CLASS__ . '.RelativeGender.m', 'männlich'),
                    'w' => _t(__CLASS__ . '.RelativeGender.w', 'weiblich'),
                    'd' => _t(__CLASS__ . '.RelativeGender.d', 'divers'),
                ])
                ->setEmptyString($selectEmpty),
            DateField::create($r . 'BirthDate', _t(__CLASS__ . '.RelativeBirthDate', 'Geburtsdatum')),
            DropdownField::create($r . 'Religion', _t(__CLASS__ . '.RelativeReligion', 'Religion (öffentlich-rechtliche Religionsgesellschaft, optional)'))
                ->setSource([
                    'none' => _t(__CLASS__ . '.RelativeReligion.none', 'keine / keine öffentlich-rechtliche Religionsgesellschaft'),
                    'ev' => _t(__CLASS__ . '.RelativeReligion.ev', 'evangelisch (ev)'),
                    'rk' => _t(__CLASS__ . '.RelativeReligion.rk', 'römisch-katholisch (rk)'),
                    'ak' => _t(__CLASS__ . '.RelativeReligion.ak', 'alt-katholisch (ak)'),
                    'lt' => _t(__CLASS__ . '.RelativeReligion.lt', 'evangelisch-lutherisch (lt)'),
                    'rf' => _t(__CLASS__ . '.RelativeReligion.rf', 'evangelisch-reformiert (rf)'),
                    'fr' => _t(__CLASS__ . '.RelativeReligion.fr', 'französisch-reformiert (fr)'),
                    'iw' => _t(__CLASS__ . '.RelativeReligion.iw', 'israelitisch - Israelitische Religionsgemeinschaft Württembergs (iw)'),
                ])
                ->setEmptyString($selectEmpty),
            TextField::create($r . 'Address', _t(__CLASS__ . '.RelativeAddress', 'PLZ, Gemeinde, Straße, Hausnummer'))
                ->setMaxLength(38),
        );
    }
}
