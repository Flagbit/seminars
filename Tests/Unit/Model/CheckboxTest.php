<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Model_CheckboxTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_Model_Checkbox
     */
    private $fixture;

    protected function setUp()
    {
        $this->fixture = new Tx_Seminars_Model_Checkbox();
    }

    /**
     * @test
     */
    public function setTitleWithEmptyTitleThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The parameter $title must not be empty.'
        );

        $this->fixture->setTitle('');
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->fixture->setTitle('I agree with the T&C.');

        self::assertEquals(
            'I agree with the T&C.',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->fixture->setData(['title' => 'I agree with the T&C.']);

        self::assertEquals(
            'I agree with the T&C.',
            $this->fixture->getTitle()
        );
    }

    /////////////////////////////////////
    // Tests regarding the description.
    /////////////////////////////////////

    /**
     * @test
     */
    public function getDescriptionWithoutDescriptionReturnsAnEmptyString()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionWithDescriptionReturnsDescription()
    {
        $this->fixture->setData(['description' => 'I agree with the T&C.']);

        self::assertEquals(
            'I agree with the T&C.',
            $this->fixture->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionSetsDescription()
    {
        $this->fixture->setDescription('I agree with the T&C.');

        self::assertEquals(
            'I agree with the T&C.',
            $this->fixture->getDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithoutDescriptionReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithDescriptionReturnsTrue()
    {
        $this->fixture->setDescription('I agree with the T&C.');

        self::assertTrue(
            $this->fixture->hasDescription()
        );
    }
}
