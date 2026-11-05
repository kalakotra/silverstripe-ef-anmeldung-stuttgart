<?php

declare(strict_types=1);

namespace Kalakotra\EFormAnmeldungStuttgart\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Person 2 of the Meldeschein (fields Person2*), see PersonStepTrait.
 * No $next_steps: the flow is decided in PersonStepTrait::getNextStep().
 */
class Form_Person2 extends MultiFormStep
{
    use StepTrait;
    use PersonStepTrait;

    private static $default_sort_order = 5;

    private static int $person_index = 2;

    private static array $conditional_required = [
        'Person2MaritalSince',
        'Person2MarriagePlace',
        'Person2IdNumber',
        'Person2IdAuthority',
        'Person2IdIssueDate',
        'Person2IdValidUntil',
    ];
}
