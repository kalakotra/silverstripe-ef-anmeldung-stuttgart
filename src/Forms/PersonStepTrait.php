<?php

declare(strict_types=1);

namespace Kalakotra\EFormAnmeldungStuttgart\Forms;

use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * One registered person (Meldeschein Spalten 1-9, 11-15), used by Form_Person1 ... Form_Person4.
 *
 * Each person is its own concrete step class (multiform loads steps by class + session, so a class
 * can only exist once per session) with prefixed field names "Person{n}..." so mergeStepData() and
 * Submission::$db work without collisions. NOT implemented via subclasses: a DataList on a parent
 * step class also returns subclass records, which would let multiform load the wrong person step.
 *
 * Labels use __TRAIT__ keys (one translation set for all four persons). The preview resolves labels and
 * step titles from the field titles (see Form::resolveFieldLabel()).
 *
 * Using classes declare: private static int $person_index; private static array $conditional_required.
 */
trait PersonStepTrait
{
    public function personIndex(): int
    {
        return (int) $this->config()->get('person_index');
    }

    public function getFields(): FieldList
    {
        $p = 'Person' . $this->personIndex();
        $selectEmpty = '-- ' . _t(__TRAIT__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';

        return FieldList::create(
            HeaderField::create('IntroText', _t(__TRAIT__ . '.IntroText', 'Person {number}', ['number' => $this->personIndex()]), 4),
            TextField::create($p . 'LastName', _t(__TRAIT__ . '.LastName', 'Familienname (ggf. auch abweichender Geburtsname)'))
                ->setMaxLength(40)
                ->setDescription(_t(__TRAIT__ . '.LastNameDescription', 'z. B. Müller geb. Schmidt')),
            TextField::create($p . 'FirstNames', _t(__TRAIT__ . '.FirstNames', 'Vorname(n)'))
                ->setMaxLength(28),
            DropdownField::create($p . 'Gender', _t(__TRAIT__ . '.Gender', 'Geschlecht'))
                ->setSource([
                    'm' => _t(__TRAIT__ . '.Gender.m', 'männlich'),
                    'w' => _t(__TRAIT__ . '.Gender.w', 'weiblich'),
                    'd' => _t(__TRAIT__ . '.Gender.d', 'divers'),
                ])
                ->setEmptyString($selectEmpty),
            DateField::create($p . 'BirthDate', _t(__TRAIT__ . '.BirthDate', 'Geburtsdatum')),
            TextField::create($p . 'BirthPlace', _t(__TRAIT__ . '.BirthPlace', 'Geburtsort (Gemeinde, Kreis; falls Ausland: auch Staat)'))
                ->setMaxLength(26),

            DropdownField::create($p . 'MaritalStatus', _t(__TRAIT__ . '.MaritalStatus', 'Familienstand'))
                ->setSource([
                    'LD' => _t(__TRAIT__ . '.MaritalStatus.LD', 'ledig'),
                    'VH' => _t(__TRAIT__ . '.MaritalStatus.VH', 'verheiratet'),
                    'VW' => _t(__TRAIT__ . '.MaritalStatus.VW', 'verwitwet'),
                    'GS' => _t(__TRAIT__ . '.MaritalStatus.GS', 'geschieden'),
                    'LP' => _t(__TRAIT__ . '.MaritalStatus.LP', 'Lebenspartnerschaft'),
                    'LV' => _t(__TRAIT__ . '.MaritalStatus.LV', 'Lebenspartner verstorben'),
                    'LA' => _t(__TRAIT__ . '.MaritalStatus.LA', 'Lebenspartnerschaft aufgehoben'),
                ])
                ->setEmptyString($selectEmpty),
            Wrapper::create(
                DateField::create($p . 'MaritalSince', _t(__TRAIT__ . '.MaritalSince', 'Familienstand seit')),
                TextField::create($p . 'MarriagePlace', _t(__TRAIT__ . '.MarriagePlace', 'Ort der Eheschließung/Begründung der Lebenspartnerschaft'))
                    ->setMaxLength(18),
            )
                ->displayIf($p . 'MaritalStatus')->isNotEqualTo('LD')->end(),

            DropdownField::create($p . 'Religion', _t(__TRAIT__ . '.Religion', 'Religion (öffentlich-rechtliche Religionsgesellschaft)'))
                ->setSource([
                    'none' => _t(__TRAIT__ . '.Religion.none', 'keine / keine öffentlich-rechtliche Religionsgesellschaft'),
                    'ev' => _t(__TRAIT__ . '.Religion.ev', 'evangelisch (ev)'),
                    'rk' => _t(__TRAIT__ . '.Religion.rk', 'römisch-katholisch (rk)'),
                    'ak' => _t(__TRAIT__ . '.Religion.ak', 'alt-katholisch (ak)'),
                    'lt' => _t(__TRAIT__ . '.Religion.lt', 'evangelisch-lutherisch (lt)'),
                    'rf' => _t(__TRAIT__ . '.Religion.rf', 'evangelisch-reformiert (rf)'),
                    'fr' => _t(__TRAIT__ . '.Religion.fr', 'französisch-reformiert (fr)'),
                    'iw' => _t(__TRAIT__ . '.Religion.iw', 'israelitisch - Israelitische Religionsgemeinschaft Württembergs (iw)'),
                ])
                ->setEmptyString($selectEmpty),
            TextField::create($p . 'Nationality', _t(__TRAIT__ . '.Nationality', 'Staatsangehörigkeit(en)'))
                ->setMaxLength(22)
                ->setDescription(_t(__TRAIT__ . '.NationalityDescription', 'Bei mehreren Staatsangehörigkeiten bitte alle angeben.')),

            HeaderField::create('IdHeader', _t(__TRAIT__ . '.IdHeader', 'Personalausweis / Pass / Passersatz'), 5),
            DropdownField::create($p . 'IdType', _t(__TRAIT__ . '.IdType', 'Art des Ausweisdokuments'))
                ->setSource([
                    'PA' => _t(__TRAIT__ . '.IdType.PA', 'Personalausweis (PA)'),
                    'VP' => _t(__TRAIT__ . '.IdType.VP', 'vorläufiger Personalausweis (VP)'),
                    'RP' => _t(__TRAIT__ . '.IdType.RP', 'Reisepass (RP)'),
                    'KP' => _t(__TRAIT__ . '.IdType.KP', 'Kinderreisepass (KP)'),
                    'PE' => _t(__TRAIT__ . '.IdType.PE', 'Passersatz (PE)'),
                    'none' => _t(__TRAIT__ . '.IdType.none', 'kein Ausweisdokument (z. B. Neugeborenes)'),
                ])
                ->setEmptyString($selectEmpty),
            Wrapper::create(
                TextField::create($p . 'IdNumber', _t(__TRAIT__ . '.IdNumber', 'Seriennummer'))
                    ->setMaxLength(12),
                TextField::create($p . 'IdAuthority', _t(__TRAIT__ . '.IdAuthority', 'Ausstellungsbehörde'))
                    ->setMaxLength(40),
                DateField::create($p . 'IdIssueDate', _t(__TRAIT__ . '.IdIssueDate', 'Ausstellungsdatum')),
                DateField::create($p . 'IdValidUntil', _t(__TRAIT__ . '.IdValidUntil', 'gültig bis')),
            )
                ->displayIf($p . 'IdType')->isNotEqualTo('none')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        $p = 'Person' . $this->personIndex();

        return RequiredFieldsValidator::create([
            $p . 'LastName',
            $p . 'FirstNames',
            $p . 'Gender',
            $p . 'BirthDate',
            $p . 'BirthPlace',
            $p . 'MaritalStatus',
            $p . 'Religion',
            $p . 'Nationality',
            $p . 'IdType',
        ]);
    }

    /**
     * Next person while the number chosen in Step 1 is not reached, then the 1939 address step.
     */
    public function getNextStep()
    {
        $index = $this->personIndex();
        $count = max(1, min(4, (int) $this->valueFromStep(Form_Step1::class, 'PersonCount')));

        return $index < $count ? Form::PERSON_STEPS[$index + 1] : Form_Step8::class;
    }
}
