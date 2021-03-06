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
class Tx_Seminars_Tests_Unit_Mapper_TimeSlotTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var Tx_Seminars_Mapper_TimeSlot
     */
    private $fixture;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

        $this->fixture = new Tx_Seminars_Mapper_TimeSlot();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////
    // Tests concerning find
    //////////////////////////

    /**
     * @test
     */
    public function findWithUidReturnsTimeSlotInstance()
    {
        self::assertTrue(
            $this->fixture->find(1) instanceof Tx_Seminars_Model_TimeSlot
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots', ['title' => '01.02.03 04:05']
        );

        /** @var Tx_Seminars_Model_TimeSlot $model */
        $model = $this->fixture->find($uid);
        self::assertEquals(
            '01.02.03 04:05',
            $model->getTitle()
        );
    }

    //////////////////////////////////
    // Tests regarding the speakers.
    //////////////////////////////////

    /**
     * @test
     */
    public function getSpeakersReturnsListInstance()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_timeslots');

        /** @var Tx_Seminars_Model_TimeSlot $model */
        $model = $this->fixture->find($uid);
        self::assertInstanceOf(Tx_Oelib_List::class, $model->getSpeakers());
    }

    /**
     * @test
     */
    public function getSpeakersWithOneSpeakerReturnsListOfSpeakers()
    {
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots'
        );
        $speaker = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Speaker::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_timeslots', $timeSlotUid, $speaker->getUid(), 'speakers'
        );

        /** @var Tx_Seminars_Model_TimeSlot $model */
        $model = $this->fixture->find($timeSlotUid);
        self::assertTrue(
            $model->getSpeakers()->first() instanceof Tx_Seminars_Model_Speaker
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithOneSpeakerReturnsOneSpeaker()
    {
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots'
        );
        $speaker = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Speaker::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_timeslots', $timeSlotUid, $speaker->getUid(), 'speakers'
        );

        /** @var Tx_Seminars_Model_TimeSlot $model */
        $model = $this->fixture->find($timeSlotUid);
        self::assertEquals(
            $speaker->getUid(),
            $model->getSpeakers()->getUids()
        );
    }

    ///////////////////////////////
    // Tests regarding the place.
    ///////////////////////////////

    /**
     * @test
     */
    public function getPlaceWithoutPlaceReturnsNull()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_timeslots');

        /** @var Tx_Seminars_Model_TimeSlot $model */
        $model = $this->fixture->find($uid);
        self::assertNull(
            $model->getPlace()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithPlaceReturnsPlaceInstance()
    {
        $place = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Place::class)->getNewGhost();
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots', ['place' => $place->getUid()]
        );

        /** @var Tx_Seminars_Model_TimeSlot $model */
        $model = $this->fixture->find($timeSlotUid);
        self::assertInstanceOf(Tx_Seminars_Model_Place::class, $model->getPlace());
    }

    /*
     * Tests regarding the seminar.
     */

    /**
     * @test
     */
    public function getSeminarWithoutSeminarReturnsNull()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_timeslots');

        /** @var Tx_Seminars_Model_TimeSlot $model */
        $model = $this->fixture->find($uid);
        self::assertNull(
            $model->getSeminar()
        );
    }

    /**
     * @test
     */
    public function getSeminarWithSeminarReturnsEventInstance()
    {
        $seminar = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getNewGhost();
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots', ['seminar' => $seminar->getUid()]
        );

        /** @var Tx_Seminars_Model_TimeSlot $model */
        $model = $this->fixture->find($timeSlotUid);
        self::assertTrue(
            $model->getSeminar() instanceof Tx_Seminars_Model_Event
        );
    }
}
