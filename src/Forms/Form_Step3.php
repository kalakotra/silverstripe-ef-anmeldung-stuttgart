<?php

declare(strict_types=1);

namespace Kalakotra\EFormAnmeldungStuttgart\Forms;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\MultiForm\Models\MultiFormStep;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * Weitere Wohnungen im Bundesgebiet + Hauptwohnung (Erläuterung 5).
 */
class Form_Step3 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 3;

    private static $next_steps = Form_Person1::class;

    private static array $conditional_required = [
        'OtherApartmentPerson',
        'OtherApartmentAddress',
        'MainResidenceGroup',
        'MainResidencePrevious',
        'MainResidenceFuture',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Weitere Wohnungen'), 4),
            DropdownField::create('HasOtherApartment', _t(__CLASS__ . '.HasOtherApartment', 'Haben Sie neben der neuen Wohnung noch weitere Wohnungen in Deutschland?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.HasOtherApartment.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.HasOtherApartment.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DropdownField::create('OtherApartmentPerson', _t(__CLASS__ . '.OtherApartmentPerson', 'Wer hat die weitere Wohnung?'))
                    ->setSource([
                        'all' => _t(__CLASS__ . '.OtherApartmentPerson.all', 'alle angemeldeten Personen'),
                        '1' => _t(__CLASS__ . '.OtherApartmentPerson.1', 'Person 1'),
                        '2' => _t(__CLASS__ . '.OtherApartmentPerson.2', 'Person 2'),
                        '3' => _t(__CLASS__ . '.OtherApartmentPerson.3', 'Person 3'),
                        '4' => _t(__CLASS__ . '.OtherApartmentPerson.4', 'Person 4'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                TextField::create('OtherApartmentAddress', _t(__CLASS__ . '.OtherApartmentAddress', 'Weitere Wohnung: PLZ, Gemeinde, Straße, Hausnummer'))
                    ->setMaxLength(80),
                DropdownField::create('MainResidenceGroup', _t(__CLASS__ . '.MainResidenceGroup', 'Für wen gilt die Angabe zur Hauptwohnung?'))
                    ->setSource([
                        'married' => _t(__CLASS__ . '.MainResidenceGroup.married', 'Verheiratete/Lebenspartner, die nicht dauernd getrennt leben (Wohnung der Familie)'),
                        'minor' => _t(__CLASS__ . '.MainResidenceGroup.minor', 'Minderjährige (Wohnung der/des Personensorgeberechtigten)'),
                        'other' => _t(__CLASS__ . '.MainResidenceGroup.other', 'alle übrigen Personen'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                DropdownField::create('MainResidencePrevious', _t(__CLASS__ . '.MainResidencePrevious', 'Welche Wohnung wurde bisher vorwiegend benutzt?'))
                    ->setSource([
                        'previous' => _t(__CLASS__ . '.MainResidencePrevious.previous', 'die bisherige Wohnung'),
                        'other' => _t(__CLASS__ . '.MainResidencePrevious.other', 'die weitere Wohnung'),
                        'none' => _t(__CLASS__ . '.MainResidencePrevious.none', 'keine Wohnung in Deutschland'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                DropdownField::create('MainResidenceFuture', _t(__CLASS__ . '.MainResidenceFuture', 'Welche Wohnung wird künftig vorwiegend benutzt?'))
                    ->setSource([
                        'new' => _t(__CLASS__ . '.MainResidenceFuture.new', 'die neue Wohnung'),
                        'other' => _t(__CLASS__ . '.MainResidenceFuture.other', 'die weitere Wohnung'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --')
                    ->setDescription(_t(
                        __CLASS__ . '.MainResidenceFutureDescription',
                        'Hauptwohnung ist die im Laufe eines Jahres überwiegend benutzte Wohnung (Erläuterung 5).'
                    )),
            )
                ->displayIf('HasOtherApartment')->isEqualTo('yes')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'HasOtherApartment',
        ]);
    }
}
