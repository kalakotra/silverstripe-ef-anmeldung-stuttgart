<?php

declare(strict_types=1);

namespace Kalakotra\EFormAnmeldungStuttgart\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Person 4 of the Meldeschein (fields Person4*), see PersonStepTrait.
 * No $next_steps: the flow is decided in PersonStepTrait::getNextStep().
 */
class Form_Person4 extends MultiFormStep
{
    use StepTrait;
    use PersonStepTrait;

    private static $default_sort_order = 7;

    private static int $person_index = 4;

    private static array $conditional_required = [
        'Person4MaritalSince',
        'Person4MarriagePlace',
        'Person4IdNumber',
        'Person4IdAuthority',
        'Person4IdIssueDate',
        'Person4IdValidUntil',
    ];
}
