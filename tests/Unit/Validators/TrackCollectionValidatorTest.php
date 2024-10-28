<?php

declare(strict_types=1);

namespace Test\Unit\Validators;

use App\Shared\Exceptions\ValidationException;
use App\Validators\TrackCollectionValidator;
use PHPUnit\Framework\TestCase;

class TrackCollectionValidatorTest extends TestCase
{
    private TrackCollectionValidator $validator;

    protected function setUp(): void
    {
        $this->validator = $this->getMockBuilder(TrackCollectionValidator::class)
            ->onlyMethods(['isMandatory', 'isString'])
            ->getMock();
    }

    public function testValidateTrackCollectionWithValidInput()
    {
        $input = [
            'trackSearchCollection' => [
                ['artist' => 'Artist Name', 'track' => 'Track Name'],
                ['artist' => 'Another Artist', 'track' => 'Another Track'],
            ],
        ];

        $this->validator->expects($this->exactly(4))->method('isMandatory')
            ->willReturnCallback(function ($trackCollectionItem, $field) {
                $this->assertContains($field, ['artist', 'track']);
            });

        $this->validator->expects($this->exactly(4))->method('isString')
            ->willReturnCallback(function ($trackCollectionItem, $field) {
                $this->assertContains($field, ['artist', 'track']);
            });

        $this->validator->validateTrackCollection($input);
        $this->assertTrue(true);
    }

    public function testValidateTrackCollectionThrowsExceptionForMissingFields()
    {
        $input = [
            'trackSearchCollection' => [
                ['artist' => 'Artist Name'],
            ],
        ];

        $this->validator->expects($this->once())
            ->method('isMandatory')
            ->willThrowException(new ValidationException("Field 'track' is mandatory"));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Field 'track' is mandatory");

        $this->validator->validateTrackCollection($input);
    }

    public function testValidateTrackCollectionThrowsExceptionForInvalidFieldTypes()
    {
        $input = [
            'trackSearchCollection' => [
                ['artist' => 'Artist Name', 'track' => 123],
            ],
        ];

        $this->validator->expects($this->once())
            ->method('isMandatory');

        $this->validator->expects($this->once())
            ->method('isString')
            ->willThrowException(new ValidationException("Field 'track' must be a string"));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Field 'track' must be a string");

        $this->validator->validateTrackCollection($input);
    }
}
