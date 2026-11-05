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
 * Anschrift am 1. September 1939 (Spalte 10, Erläuterung 7) - two rows on the Meldeschein.
 */
class Form_Step8 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 8;

    private static $next_steps = Form_Step9::class;

    private static array $conditional_required = [
        'Address1939Person1',
        'Address1939Text1',
    ];

    public function getFields(): FieldList
    {
        $persons = [
            '1' => _t(__CLASS__ . '.Person.1', 'Person 1'),
            '2' => _t(__CLASS__ . '.Person.2', 'Person 2'),
            '3' => _t(__CLASS__ . '.Person.3', 'Person 3'),
            '4' => _t(__CLASS__ . '.Person.4', 'Person 4'),
        ];
        $selectEmpty = '-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';

        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Anschrift am 1. September 1939'), 4),
            DropdownField::create('Address1939Applies', _t(
                __CLASS__ . '.Address1939Applies',
                'Stammt eine der angemeldeten Personen aus den in § 1 Abs. 2 Nr. 3 Bundesvertriebenengesetz bezeichneten Gebieten?'
            ))
                ->setSource([
                    'no' => _t(__CLASS__ . '.Address1939Applies.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.Address1939Applies.yes', 'ja'),
                ])
                ->setEmptyString($selectEmpty)
                ->setDescription(_t(
                    __CLASS__ . '.Address1939AppliesDescription',
                    'Ehemals unter fremder Verwaltung stehende deutsche Ostgebiete, Danzig, Estland, Lettland, Litauen, '
                    . 'ehemalige Sowjetunion, Polen, Tschechische Republik, Slowakische Republik, Ungarn, Rumänien, '
                    . 'Bulgarien, ehemaliges Jugoslawien, Albanien, China. In allen anderen Fällen „nein“ wählen.'
                )),
            Wrapper::create(
                DropdownField::create('Address1939Person1', _t(__CLASS__ . '.Address1939Person1', 'Für Person'))
                    ->setSource($persons)
                    ->setEmptyString($selectEmpty),
                TextField::create('Address1939Text1', _t(__CLASS__ . '.Address1939Text1', 'Anschrift am 1. September 1939'))
                    ->setMaxLength(60),
                DropdownField::create('Address1939Person2', _t(__CLASS__ . '.Address1939Person2', 'Für weitere Person (optional)'))
                    ->setSource($persons)
                    ->setEmptyString($selectEmpty),
                TextField::create('Address1939Text2', _t(__CLASS__ . '.Address1939Text2', 'Anschrift am 1. September 1939 (weitere Person)'))
                    ->setMaxLength(60),
            )
                ->displayIf('Address1939Applies')->isEqualTo('yes')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'Address1939Applies',
        ]);
    }

    /**
     * The optional second row needs both the person and the address.
     */
    public function validateStep($data, $form)
    {
        $valid = $this->validateConditionalRequired($data, $form);
        $values = $form->getData();

        if (($values['Address1939Applies'] ?? '') === 'yes') {
            $person = trim((string) ($values['Address1939Person2'] ?? ''));
            $text = trim((string) ($values['Address1939Text2'] ?? ''));

            if (($person === '') !== ($text === '')) {
                $message = _t(__CLASS__ . '.SecondRowIncomplete', 'Bitte geben Sie für die weitere Person sowohl die Person als auch die Anschrift an.');
                $form->sessionFieldError($message, $person === '' ? 'Address1939Person2' : 'Address1939Text2');
                $form->sessionMessage($message);
                $valid = false;
            }
        }

        return $valid && parent::validateStep($data, $form);
    }
}
