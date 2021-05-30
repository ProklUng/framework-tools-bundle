<?php

namespace Prokl\FrameworkExtensionBundle\Services\Validators;

use Symfony\Component\Validator\Constraint;

/**
 * Class PhoneValidator
 * @package Prokl\FrameworkExtensionBundle\Services\Validators
 *
 * @Annotation
 *
 * @since 03.04.2021
 */
class Phone extends Constraint
{
    /**
     * @var string $message
     */
    public $message = 'The string "{{ string }}" contains not valid phone number.';
}