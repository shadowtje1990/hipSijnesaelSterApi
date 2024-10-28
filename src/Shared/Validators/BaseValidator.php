<?php declare(strict_types=1);

namespace App\Shared\Validators;

use App\Shared\Exceptions\ValidationException;

class BaseValidator
{
    /**
     * @throws ValidationException
     */
    protected function exitWithError(string $message): void
    {
        throw new ValidationException($message);
    }

    /**
     * @throws ValidationException
     */
    protected function isMandatory(array $input, string $fieldName): void
    {
        if (empty($input[$fieldName])) {
            $message = sprintf('Field %s is mandatory.', $fieldName);
            $this->exitWithError($message);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function isString(array $input, string $fieldName): void
    {
        if (!empty($input[$fieldName]) && !is_string($input[$fieldName])) {
            $message = sprintf('Field %s must be a string.', $fieldName);
            $this->exitWithError($message);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function noIllegalCharacters(array $input, string $fieldName): void
    {
        $illegalCharacters = '/[!@#$%^&*()\[\]{}]+/';

        if (!is_numeric($input[$fieldName]) && !empty($input[$fieldName]) && preg_match($illegalCharacters, $input[$fieldName]) > 0) {
            $message = sprintf('Field %s cannot contains illegal characters.', $fieldName);
            $this->exitWithError($message);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function isFromAcceptedSet(array $input, string $fieldName, array $acceptedValues, bool $isCommaSeparated = false): void
    {
        if (!isset($input[$fieldName])) {
            return;
        }

        if ($isCommaSeparated) {
            $array = explode(',', $input[$fieldName]);
            foreach ($array as $item) {
                if (!in_array($item, $acceptedValues, true)) {
                    $message = sprintf(
                        'Field %s has value "%s", this must be from accepted set (%s)',
                        $fieldName,
                        $item,
                        implode(' | ', $acceptedValues
                        )
                    );
                    $this->exitWithError($message);
                }
            }
            return;
        }

        if (!in_array($input[$fieldName], $acceptedValues, true)) {
            $message = sprintf(
                'Field %s must be from accepted set (%s)',
                $fieldName,
                implode(' | ', $acceptedValues
                )
            );
            $this->exitWithError($message);
        }
    }

    protected function isValidDateString(array $input, string $fieldName): void
    {
        if (!isset($input[$fieldName])) {
            return;
        }

        if ((bool)strtotime($input[$fieldName])) {
            return;
        }

        $message = sprintf('Field %s must be a valid Date.', $fieldName);
        $this->exitWithError($message);
    }

    /**
     * @throws ValidationException
     */
    protected function isValidCombination(array $input, string $fieldName, array $acceptedFieldNameCombinations, array $notAcceptedFieldNameCombinations)
    {
        if (!isset($input[$fieldName])) {
            return;
        }

        foreach ($notAcceptedFieldNameCombinations as $notAcceptedFieldName) {
            if(isset($input[$notAcceptedFieldName])) {
                $message = sprintf(
                    'Field %s cannot be used in combination with field %s.',
                    $fieldName,
                    $notAcceptedFieldName);

                if (!empty($acceptedFieldNameCombinations)) {
                    $message .= sprintf('You can use this field in combination with (%s)',
                        implode(' | ', $acceptedFieldNameCombinations)
                    );
                }
                $this->exitWithError($message);
            }
        }
    }
}