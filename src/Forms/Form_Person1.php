<?php

declare(strict_types=1);

namespace Kalakotra\EFormAnmeldungStuttgart\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Person 1 of the Meldeschein (fields Person1*), see PersonStepTrait.
 * No $next_steps: the flow is decided in PersonStepTrait::getNextStep().
 */
class Form_Person1 extends MultiFormStep
{
    use StepTrait;
    use PersonStepTrait;

    private static $default_sort_order = 4;

    private static int $person_index = 1;

    private static array $conditional_required = [
        'Person1MaritalSince',
        'Person1MarriagePlace',
        'Person1IdNumber',
        'Person1IdAuthority',
        'Person1IdIssueDate',
        'Person1IdValidUntil',
    ];
}
