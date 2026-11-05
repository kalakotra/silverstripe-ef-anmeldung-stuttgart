<?php

declare(strict_types=1);

namespace Kalakotra\EFormAnmeldungStuttgart\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Person 3 of the Meldeschein (fields Person3*), see PersonStepTrait.
 * No $next_steps: the flow is decided in PersonStepTrait::getNextStep().
 */
class Form_Person3 extends MultiFormStep
{
    use StepTrait;
    use PersonStepTrait;

    private static $default_sort_order = 6;

    private static int $person_index = 3;

    private static array $conditional_required = [
        'Person3MaritalSince',
        'Person3MarriagePlace',
        'Person3IdNumber',
        'Person3IdAuthority',
        'Person3IdIssueDate',
        'Person3IdValidUntil',
    ];
}
