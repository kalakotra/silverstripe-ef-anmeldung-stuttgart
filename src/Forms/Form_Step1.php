<?php

declare(strict_types=1);

namespace Kalakotra\EFormAnmeldungStuttgart\Forms;

use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Neue Wohnung (Tag des Einzugs, Anschrift in Stuttgart) + number of persons to register.
 */
class Form_Step1 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 1;

    private static $next_steps = Form_Step2::class;

    /** Stuttgart postal codes: 70173 - 70629 */
    private const POSTAL_CODE_PATTERN = '/^70[1-6]\d{2}$/';

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Neue Wohnung'), 4),
            LiteralField::create('Notice', '<p class="alert alert-info">' . _t(
                __CLASS__ . '.Notice',
                'Wer eine Wohnung bezieht, muss sich innerhalb von zwei Wochen anmelden. Bringen Sie zur Meldebehörde '
                . 'die Bestätigung des Wohnungsgebers und ein gültiges Ausweisdokument jeder gemeldeten Person mit.'
            ) . '</p>'),
            DateField::create('MoveInDate', _t(__CLASS__ . '.MoveInDate', 'Tag des Einzugs')),
            TextField::create('PostalCode', _t(__CLASS__ . '.PostalCode', 'Postleitzahl (Stuttgart)'))
                ->setMaxLength(5),
            TextField::create('Street', _t(__CLASS__ . '.Street', 'Gemeindeteil, Straße, Hausnummer, -Zusatz'))
                ->setMaxLength(45),
            TextField::create('AddressAddition', _t(__CLASS__ . '.AddressAddition', 'Wohnungsnummer / Adresszusatz (z. B. 2. OG links, c/o)'))
                ->setMaxLength(45),
            DropdownField::create('PersonCount', _t(__CLASS__ . '.PersonCount', 'Wie viele Personen melden sich mit diesem Meldeschein an?'))
                ->setSource([
                    '1' => _t(__CLASS__ . '.PersonCount.1', '1 Person'),
                    '2' => _t(__CLASS__ . '.PersonCount.2', '2 Personen'),
                    '3' => _t(__CLASS__ . '.PersonCount.3', '3 Personen'),
                    '4' => _t(__CLASS__ . '.PersonCount.4', '4 Personen'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --')
                ->setDescription(_t(
                    __CLASS__ . '.PersonCountDescription',
                    'Personen derselben Familie können gemeinsam angemeldet werden (max. 4 pro Meldeschein).'
                )),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'MoveInDate',
            'PostalCode',
            'Street',
            'PersonCount',
        ]);
    }

    public function validateStep($data, $form)
    {
        $valid = $this->validateConditionalRequired($data, $form);
        $postalCode = trim((string) ($form->getData()['PostalCode'] ?? ''));

        if ($postalCode !== '' && !preg_match(self::POSTAL_CODE_PATTERN, $postalCode)) {
            $message = _t(__CLASS__ . '.PostalCodeInvalid', 'Bitte geben Sie eine gültige Stuttgarter Postleitzahl ein (70173 - 70629).');
            $form->sessionFieldError($message, 'PostalCode');
            $form->sessionMessage($message);
            $valid = false;
        }

        return $valid && parent::validateStep($data, $form);
    }
}
