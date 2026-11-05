<?php

declare(strict_types=1);

namespace Kalakotra\EFormAnmeldungStuttgart\Forms;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Page 4 "Erklärung über städtische Informationen" - optional Amtsblatt trial subscription.
 */
class Form_Step10 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 10;

    private static $next_steps = Form_Preview::class;

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Erklärung über städtische Informationen'), 4),
            LiteralField::create('Notice', '<p>' . _t(
                __CLASS__ . '.Notice',
                'Freiwillige Angabe. Die Erklärung (Seite 4 des Formulars) wird an die Landeshauptstadt Stuttgart, '
                . 'Abteilung Kommunikation, weitergeleitet und nur bei einer Antwort unterschrieben.'
            ) . '</p>'),
            DropdownField::create('AmtsblattTrial', _t(
                __CLASS__ . '.AmtsblattTrial',
                'Mit der Zusendung von vier kostenlosen Ausgaben des Stuttgarter Amtsblatts bin ich einverstanden. Das Probeabo endet danach automatisch.'
            ))
                ->setSource([
                    'yes' => _t(__CLASS__ . '.AmtsblattTrial.yes', 'ja'),
                    'no' => _t(__CLASS__ . '.AmtsblattTrial.no', 'nein'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.NO_ANSWER', 'keine Angabe') . ' --'),
        );
    }
}
