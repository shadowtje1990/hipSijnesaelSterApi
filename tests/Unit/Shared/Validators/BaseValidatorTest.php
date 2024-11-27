<?php

declare(strict_types=1);
namespace Test\Unit\Shared\Validators;

use PHPUnit\Framework\TestCase;
use App\Shared\Validators\BaseValidator;
use App\Shared\Exceptions\ValidationException;

class BaseValidatorTest extends TestCase
{
    private TestableBaseValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new TestableBaseValidator();
    }

    public function testIsMandatoryThrowsExceptionWhenFieldIsMissing(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field requiredField is mandatory.');

        $this->validator->isMandatory([], 'requiredField');
    }

    public function testIsStringThrowsExceptionWhenFieldIsNotString(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field stringField must be a string.');

        $this->validator->isString(['stringField' => 123], 'stringField');
    }

    public function testNoIllegalCharactersThrowsExceptionWhenFieldContainsIllegalCharacters(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field specialField cannot contains illegal characters.');

        $this->validator->noIllegalCharacters(['specialField' => 'Hello@World'], 'specialField');
    }

    public function testIsFromAcceptedSetThrowsExceptionWhenFieldIsNotInSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field optionField must be from accepted set (option1 | option2)');

        $this->validator->isFromAcceptedSet(['optionField' => 'invalidOption'], 'optionField', ['option1', 'option2']);
    }

    public function testIsFromAcceptedSetWithCommaSeparatedValuesThrowsExceptionWhenValueIsNotInSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field listField has value "invalidOption", this must be from accepted set (option1 | option2)');

        $this->validator->isFromAcceptedSet(['listField' => 'option1,invalidOption'], 'listField', ['option1', 'option2'], true);
    }

    public function testIsValidDateStringThrowsExceptionWhenFieldIsInvalidDate(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field dateField must be a valid Date.');

        $this->validator->isValidDateString(['dateField' => 'not-a-date'], 'dateField');
    }

    public function testIsValidCombinationThrowsExceptionWhenFieldIsUsedWithNotAcceptedField(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field mainField cannot be used in combination with field conflictingField.');

        $this->validator->isValidCombination(
            ['mainField' => 'value', 'conflictingField' => 'value'],
            'mainField',
            [],
            ['conflictingField']
        );
    }

    public function testIsValidCombinationThrowsExceptionWithAcceptedFieldsWhenFieldIsUsedWithNotAcceptedField(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field mainField cannot be used in combination with field conflictingField.You can use this field in combination with (acceptedField1 | acceptedField2)');

        $this->validator->isValidCombination(
            ['mainField' => 'value', 'conflictingField' => 'value'],
            'mainField',
            ['acceptedField1', 'acceptedField2'],
            ['conflictingField']
        );
    }
}

class TestableBaseValidator extends BaseValidator
{
    public function isMandatory(array $input, string $fieldName): void
    {
        parent::isMandatory($input, $fieldName);
    }

    public function isString(array $input, string $fieldName): void
    {
        parent::isString($input, $fieldName);
    }

    public function noIllegalCharacters(array $input, string $fieldName): void
    {
        parent::noIllegalCharacters($input, $fieldName);
    }

    public function isFromAcceptedSet(array $input, string $fieldName, array $acceptedValues, bool $isCommaSeparated = false): void
    {
        parent::isFromAcceptedSet($input, $fieldName, $acceptedValues, $isCommaSeparated);
    }

    public function isValidDateString(array $input, string $fieldName): void
    {
        parent::isValidDateString($input, $fieldName);
    }

    public function isValidCombination(array $input, string $fieldName, array $acceptedFieldNameCombinations, array $notAcceptedFieldNameCombinations): void
    {
        parent::isValidCombination($input, $fieldName, $acceptedFieldNameCombinations, $notAcceptedFieldNameCombinations);
    }
}
