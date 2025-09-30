<?php

declare(strict_types=1);

namespace ThingyValidator;

/**
 * Represents the type of validation result
 *
 * @package ThingyValidator
 */
enum ValidationResultType: string
{
    /**
     * Validation passed successfully
     */
    case Success = 'success';

    /**
     * Validation failed
     */
    case Failure = 'failure';

    /**
     * Validation passed with warnings
     */
    case Warning = 'warning';
}
