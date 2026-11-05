<?php

declare(strict_types=1);

namespace Kalakotra\EFormAnmeldungStuttgart\Forms;

use app\forms\basemodel\BaseMultiForm;
use app\models\FormSubmission;
use Kalakotra\EFormAnmeldungStuttgart\Model\Submission;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\SingleSelectField;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Anmeldung bei der Meldebehörde Stuttgart (Meldeschein 32/42-0034).
 */
class Form extends BaseMultiForm
{
    private static $start_step = Form_Step1::class;

    /** Person steps by person number (Meldeschein "lfd. Nr."). */
    public const PERSON_STEPS = [
        1 => Form_Person1::class,
        2 => Form_Person2::class,
        3 => Form_Person3::class,
        4 => Form_Person4::class,
    ];

    public static function getFormName(): string
    {
        return 'Anmeldung Stuttgart (Meldeschein)';
    }

    public function finish($data, $form)
    {
        if (parent::finish($data, $form) === false) {
            return false;
        }

        $allData = $this->mergeStepData($this->getActiveSteps());
        if ($allData === []) {
            return $this->controller->redirectBack();
        }

        try {
            $submission = Submission::create();
            $submission->update($allData);
            $submission->write();

            $formSubmission = FormSubmission::create();
            $formSubmission->Email = $submission->ConfirmationEmail;
            $formSubmission->SubmissionID = $submission->ID;
            $formSubmission->Status = 0; // pending
            $formSubmission->ParentPageID = $this->controller->data()->ID;
            $formSubmission->write();

            $this->controller->getRequest()->getSession()->set('FormSubmissionID', $formSubmission->ID);
        } catch (\Throwable $e) {
            Injector::inst()->get(LoggerInterface::class)->error(
                'Anmeldung Stuttgart submission failed: ' . $e->getMessage(),
                ['exception' => $e]
            );
            $form->sessionError(_t(__CLASS__ . '.SAVE_ERROR', 'Es gab ein Problem beim Speichern Ihrer Daten. Bitte versuchen Sie es erneut.'));

            return $this->controller->redirectBack();
        }

        return $this->controller->redirect($this->controller->Link('/finished'));
    }

    /**
     * Preview only shows steps on the currently active path
     * (e.g. "Person 3" disappears when the user went back and reduced the number of persons).
     */
    public function PreviewSteps(): ArrayList
    {
        $activeClasses = array_map(
            static fn (MultiFormStep $step): string => (string) $step->ClassName,
            $this->getActiveSteps()
        );

        return parent::PreviewSteps()->filterByCallback(
            static fn ($item): bool => in_array((string) $item->ClassName, $activeClasses, true)
        );
    }

    /**
     * Type-aware preview values based on Submission::$db (the base implementation detects dates by
     * regex-scanning step sources for "DateField::create('Name'", which misses helper-built fields).
     */
    public function formatPreviewValue(string $stepClass, string $key, $value, ?MultiFormStep $step = null): string
    {
        $type = Submission::config()->get('db')[$key] ?? null;

        if ($type === 'Date' && is_string($value) && $value !== '') {
            return $this->DateFormatting($value);
        }

        if ($type === 'Boolean') {
            if ($value === null || $value === '') {
                return ''; // cleared because hidden -> skipped in preview
            }

            return $value
                ? _t(__CLASS__ . '.BOOLEAN_YES', 'Ja')
                : _t(__CLASS__ . '.BOOLEAN_NO', 'Nein');
        }

        // option label straight from the field source: translated if a lang file exists,
        // German default otherwise (base only resolves options that exist in a lang file)
        if (is_scalar($value) && $value !== '') {
            $label = $this->optionSources($stepClass, $step)[$key][(string) $value] ?? null;
            if ($label !== null) {
                return (string) $label;
            }
        }

        return parent::formatPreviewValue($stepClass, $key, $value, $step);
    }

    /**
     * Label from the step's own field title. Person steps share __TRAIT__ translation keys and use
     * prefixed field names, so the base lookup "<StepClass>.<Field>" finds nothing for them.
     */
    public function resolveFieldLabel(string $stepClass, string $fieldKey): string
    {
        $field = $this->stepFieldList($stepClass)?->dataFieldByName($fieldKey);
        $title = $field instanceof FormField ? trim(strip_tags((string) $field->Title())) : '';

        return $title !== '' ? $title : parent::resolveFieldLabel($stepClass, $fieldKey);
    }

    /**
     * Step title from the "IntroText" header field (person steps: "Person 1" ... "Person 4").
     */
    public function resolveStepTitle(MultiFormStep $step): string
    {
        foreach ($this->flattenFields($this->stepFieldList((string) $step->ClassName, $step)) as $field) {
            if ($field->getName() === 'IntroText') {
                $title = trim(strip_tags((string) $field->Title()));
                if ($title !== '') {
                    return $title;
                }
            }
        }

        return parent::resolveStepTitle($step);
    }

    /** @var array<string, FieldList|null> */
    private array $stepFieldCache = [];

    private function stepFieldList(string $stepClass, ?MultiFormStep $step = null): ?FieldList
    {
        if (!array_key_exists($stepClass, $this->stepFieldCache)) {
            $instance = $step ?? (is_a($stepClass, MultiFormStep::class, true) ? $stepClass::singleton() : null);
            $this->stepFieldCache[$stepClass] = $instance ? $instance->getFields() : null;
        }

        return $this->stepFieldCache[$stepClass];
    }

    /**
     * @return array<int, FormField>
     */
    private function flattenFields(?FieldList $fields): array
    {
        if ($fields === null) {
            return [];
        }

        // flattenFields() includes composite children and non-data fields (headers)
        return method_exists($fields, 'flattenFields') ? iterator_to_array($fields->flattenFields(), false) : [];
    }

    /** @var array<string, array<string, array<string, string>>> */
    private array $optionSourceCache = [];

    /**
     * @return array<string, array<string, string>> field name => [value => label]
     */
    private function optionSources(string $stepClass, ?MultiFormStep $step): array
    {
        if (!isset($this->optionSourceCache[$stepClass])) {
            $this->optionSourceCache[$stepClass] = [];
            $fields = $this->stepFieldList($stepClass, $step);

            if ($fields) {
                // getDataFields() replaces the deprecated dataFields() in SS 6.2
                $dataFields = method_exists($fields, 'getDataFields') ? $fields->getDataFields() : $fields->dataFields();

                foreach ($dataFields as $name => $field) {
                    if ($field instanceof SingleSelectField) {
                        $this->optionSourceCache[$stepClass][$name] = array_map('strval', $field->getSource());
                    }
                }
            }
        }

        return $this->optionSourceCache[$stepClass];
    }

    /**
     * Walks the step chain from the start step using each step's getNextStep(),
     * so branches the user abandoned (but which still exist in the session) are excluded.
     *
     * @return array<int, MultiFormStep>
     */
    public function getActiveSteps(): array
    {
        $stepsByClass = [];
        $steps = MultiFormStep::get()->filter('SessionID', $this->getMultiFormSession()->ID);

        foreach ($steps as $step) {
            $step->setForm($this);
            $stepsByClass[$step->ClassName] = $step;
        }

        $active = [];
        $class = $this->config()->get('start_step');

        while (is_string($class) && isset($stepsByClass[$class]) && !isset($active[$class])) {
            $step = $stepsByClass[$class];
            $active[$class] = $step;

            if ($step->isFinalStep()) {
                break;
            }

            $class = $step->getNextStep();
        }

        return array_values($active);
    }
}
