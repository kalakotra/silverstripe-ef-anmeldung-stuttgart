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
 * Bisherige Wohnung (Erläuterung 4): only a dwelling that is given up, or a foreign one.
 */
class Form_Step2 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 2;

    private static $next_steps = Form_Step3::class;

    private static array $conditional_required = [
        'PreviousCity',
        'PreviousStreet',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Bisherige Wohnung'), 4),
            DropdownField::create('PreviousResidence', _t(__CLASS__ . '.PreviousResidence', 'Wo haben Sie bisher gewohnt?'))
                ->setSource([
                    'domestic' => _t(__CLASS__ . '.PreviousResidence.domestic', 'In Deutschland - die Wohnung wird aufgegeben'),
                    'abroad' => _t(__CLASS__ . '.PreviousResidence.abroad', 'Im Ausland (Zuzug aus dem Ausland)'),
                    'kept' => _t(__CLASS__ . '.PreviousResidence.kept', 'In Deutschland - die Wohnung wird beibehalten'),
                    'none' => _t(__CLASS__ . '.PreviousResidence.none', 'Keine bisherige Wohnung (z. B. Neugeborenes)'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --')
                ->setDescription(_t(
                    __CLASS__ . '.PreviousResidenceDescription',
                    'Eine beibehaltene Wohnung in Deutschland wird nicht als bisherige Wohnung eingetragen, '
                    . 'sondern im nächsten Schritt als weitere Wohnung angegeben.'
                )),
            Wrapper::create(
                TextField::create('PreviousCity', _t(__CLASS__ . '.PreviousCity', 'PLZ, Gemeinde, Bundesland (bei Zuzug aus dem Ausland: Staat)'))
                    ->setMaxLength(45),
                TextField::create('PreviousStreet', _t(__CLASS__ . '.PreviousStreet', 'Straße, Hausnummer, -Zusatz, Wohnungsnummer'))
                    ->setMaxLength(45),
                Wrapper::create(
                    TextField::create('LastDomesticAddress', _t(__CLASS__ . '.LastDomesticAddress', 'Letzte Anschrift in Deutschland (falls vorhanden)'))
                        ->setMaxLength(45),
                )
                    ->displayIf('PreviousResidence')->isEqualTo('abroad')->end(),
            )
                ->displayIf('PreviousResidence')->isEqualTo('domestic')
                ->orIf('PreviousResidence')->isEqualTo('abroad')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'PreviousResidence',
        ]);
    }
}
