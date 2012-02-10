<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2011 Niels Pardon (mail@niels-pardon.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the tx_seminars_seminar class in the "seminars" extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_OldModel_EventTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_seminarchild
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var integer
	 */
	private $beginDate;
	/**
	 * @var integer
	 */
	private $unregistrationDeadline;
	/**
	 * @var integer
	 */
	private $now;

	/**
	 * @var tx_seminars_FrontEnd_DefaultController
	 */
	private $pi1;

	public function setUp() {
		$GLOBALS['LANG']->includeLLFile(
			t3lib_extMgm::extPath('seminars') . 'locallang.xml'
		);
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->now = $GLOBALS['SIM_EXEC_TIME'];
		$this->beginDate = ($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->unregistrationDeadline = ($this->now + tx_oelib_Time::SECONDS_PER_WEEK);

		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'deadline_unregistration' => $this->unregistrationDeadline,
				'attendees_min' => 5,
				'attendees_max' => 10,
				'object_type' => 0,
				'queue_size' => 0,
				'needs_registration' => 1,
			)
		);

		$this->fixture = new tx_seminars_seminarchild(
			$uid,
			array(
				'dateFormatYMD' => '%d.%m.%Y',
				'timeFormat' => '%H:%M',
				'showTimeOfUnregistrationDeadline' => 0,
				'unregistrationDeadlineDaysBeforeBeginDate' => 0,
			)
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		if ($this->pi1) {
			$this->pi1->__destruct();
		}
		$this->fixture->__destruct();
		tx_seminars_registrationmanager::purgeInstance();
		unset($this->fixture, $this->pi1, $this->testingFramework);
	}


	//////////////////////
	// Utility functions
	//////////////////////

	/**
	 * Creates a fake front end and a pi1 instance in $this->pi1.
	 *
	 * @param integer UID of the detail view page
	 */
	private function createPi1($detailPageUid = 0) {
		$this->testingFramework->createFakeFrontEnd();

		$this->pi1 = new tx_seminars_FrontEnd_DefaultController();
		$this->pi1->init(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'detailPID' => $detailPageUid,
			)
		);
		$this->pi1->getTemplateCode();
	}

	/**
	 * Inserts a place record into the database and creates a relation to it
	 * from the fixture.
	 *
	 * @param array data of the place to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addPlaceRelation(array $placeData = array()) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites', $placeData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm',
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfPlaces(
			$this->fixture->getNumberOfPlaces() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a target group record into the database and creates a relation to
	 * it from the fixture.
	 *
	 * @param array data of the target group to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addTargetGroupRelation(array $targetGroupData = array()) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups', $targetGroupData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_target_groups_mm',
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfTargetGroups(
			$this->fixture->getNumberOfTargetGroups() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a payment method record into the database and creates a relation
	 * to it from the fixture.
	 *
	 * @param array data of the payment method to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addPaymentMethodRelation(
		array $paymentMethodData = array()
	) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', $paymentMethodData
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm', $this->fixture->getUid(),
			$uid
		);
		$this->fixture->setNumberOfPaymentMethods(
			$this->fixture->getNumberOfPaymentMethods() + 1
		);

		return $uid;
	}

	/**
	 * Inserts an organizer record into the database and creates a relation to
	 * it from the fixture as a organizing partner.
	 *
	 * @param array data of the organizer to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addOrganizingPartnerRelation(
		array $organizerData = array()
	) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', $organizerData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizing_partners_mm',
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfOrganizingPartners(
			$this->fixture->getNumberOfOrganizingPartners() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a category record into the database and creates a relation to it
	 * from the fixture.
	 *
	 * @param array data of the category to add, may be empty
	 * @param integer the sorting index of the category to add, must be >= 0
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addCategoryRelation(
		array $categoryData = array(), $sorting = 0
	) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_categories', $categoryData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm',
			$this->fixture->getUid(), $uid, $sorting
		);
		$this->fixture->setNumberOfCategories(
			$this->fixture->getNumberOfCategories() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a organizer record into the database and creates a relation to it
	 * from the fixture.
	 *
	 * @param array data of the organizer to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addOrganizerRelation(array $organizerData = array()) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', $organizerData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm',
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfOrganizers(
			$this->fixture->getNumberOfOrganizers() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a speaker record into the database and creates a relation to it
	 * from the fixture.
	 *
	 * @param array data of the speaker to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addSpeakerRelation($speakerData) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', $speakerData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_speakers_mm',
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfSpeakers(
			$this->fixture->getNumberOfSpeakers() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a speaker record into the database and creates a relation to it
	 * from the fixture as partner.
	 *
	 * @param array data of the speaker to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addPartnerRelation($speakerData) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', $speakerData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_speakers_mm_partners',
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfPartners(
			$this->fixture->getNumberOfPartners() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a speaker record into the database and creates a relation to it
	 * from the fixture as tutor.
	 *
	 * @param array data of the speaker to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addTutorRelation($speakerData) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', $speakerData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_speakers_mm_tutors',
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfTutors(
			$this->fixture->getNumberOfTutors() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a speaker record into the database and creates a relation to it
	 * from the fixture as leader.
	 *
	 * @param array data of the speaker to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addLeaderRelation($speakerData) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', $speakerData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_speakers_mm_leaders',
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfLeaders(
			$this->fixture->getNumberOfLeaders() + 1
		);

		return $uid;
	}

	/**
	 * Inserts an event type record into the database and creates a relation to
	 * it from the fixture.
	 *
	 * @param array data of the event type to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addEventTypeRelation($eventTypeData) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_event_types', $eventTypeData
		);

		$this->fixture->setEventType($uid);

		return $uid;
	}


	////////////////////////////////////
	// Tests for the utility functions
	////////////////////////////////////

	public function testCreatePi1CreatesFakeFrontEnd() {
		$GLOBALS['TSFE'] = NULL;

		$this->createPi1();

		$this->assertNotNull(
			$GLOBALS['TSFE']
		);
	}

	public function testCreatePi1CreatesPi1Instance() {
		$this->pi1 = NULL;

		$this->createPi1();

		$this->assertTrue(
			$this->pi1 instanceof tx_seminars_FrontEnd_DefaultController
		);
	}

	public function testAddPlaceRelationReturnsUid() {
		$uid = $this->addPlaceRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddPlaceRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addPlaceRelation(array()),
			$this->addPlaceRelation(array())
		);
	}

	public function testAddPlaceRelationIncreasesTheNumberOfPlaces() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfPlaces()
		);

		$this->addPlaceRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfPlaces()
		);

		$this->addPlaceRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfPlaces()
		);
	}

	public function testAddPlaceRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_place_mm',
				'uid_local=' . $this->fixture->getUid()
			)
		);

		$this->addPlaceRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_place_mm',
				'uid_local=' . $this->fixture->getUid()
			)
		);

		$this->addPlaceRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_place_mm',
				'uid_local=' . $this->fixture->getUid()
			)
		);
	}

	public function testAddCategoryRelationReturnsUid() {
		$uid = $this->addCategoryRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddCategoryRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addCategoryRelation(array()),
			$this->addCategoryRelation(array())
		);
	}

	public function testAddCategoryRelationIncreasesTheNumberOfCategories() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfCategories()
		);

		$this->addCategoryRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfCategories()
		);

		$this->addCategoryRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfCategories()
		);
	}

	public function testAddCategoryRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_categories_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addCategoryRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_categories_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addCategoryRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_categories_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddCategoryRelationCanSetSortingInRelationTable() {
		$this->addCategoryRelation(array(), 42);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_categories_mm',
				'uid_local=' . $this->fixture->getUid() . ' AND sorting=42'
			)
		);
	}

	public function testAddTargetGroupRelationReturnsUid() {
		$this->assertTrue(
			$this->addTargetGroupRelation(array()) > 0
		);
	}

	public function testAddTargetGroupRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addTargetGroupRelation(array()),
			$this->addTargetGroupRelation(array())
		);
	}

	public function testAddTargetGroupRelationIncreasesTheNumberOfTargetGroups() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfTargetGroups()
		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfTargetGroups()
		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfTargetGroups()
		);
	}

	public function testAddTargetGroupRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_target_groups_mm',
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_target_groups_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_target_groups_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddPaymentMethodRelationReturnsUid() {
		$uid = $this->addPaymentMethodRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddPaymentMethodRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addPaymentMethodRelation(array()),
			$this->addPaymentMethodRelation(array())
		);
	}

	public function testAddPaymentMethodRelationIncreasesTheNumberOfPaymentMethods() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfPaymentMethods()
		);

		$this->addPaymentMethodRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfPaymentMethods()
		);

		$this->addPaymentMethodRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfPaymentMethods()
		);
	}

	public function testAddOrganizingPartnerRelationReturnsUid() {
		$uid = $this->addOrganizingPartnerRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddOrganizingPartnerRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addOrganizingPartnerRelation(array()),
			$this->addOrganizingPartnerRelation(array())
		);
	}

	public function testAddOrganizingPartnerRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_organizing_partners_mm',
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addOrganizingPartnerRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_organizing_partners_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addOrganizingPartnerRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_organizing_partners_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddOrganizerRelationReturnsUid() {
		$uid = $this->addOrganizerRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddOrganizerRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addOrganizerRelation(array()),
			$this->addOrganizerRelation(array())
		);
	}

	public function testAddOrganizerRelationIncreasesTheNumberOfOrganizers() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfOrganizers()
		);

		$this->addOrganizerRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfOrganizers()
		);

		$this->addOrganizerRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfOrganizers()
		);
	}

	public function testAddSpeakerRelationReturnsUid() {
		$uid = $this->addSpeakerRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddSpeakerRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addSpeakerRelation(array()),
			$this->addSpeakerRelation(array())
		);
	}

	public function testAddSpeakerRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm',
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addSpeakerRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addSpeakerRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddPartnerRelationReturnsUid() {
		$uid = $this->addPartnerRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddPartnerRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addPartnerRelation(array()),
			$this->addPartnerRelation(array())
		);
	}

	public function testAddPartnerRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_partners',
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addPartnerRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_partners',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addPartnerRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_partners',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddTutorRelationReturnsUid() {
		$uid = $this->addTutorRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddTutorRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addTutorRelation(array()),
			$this->addTutorRelation(array())
		);
	}

	public function testAddTutorRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_tutors',
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addTutorRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_tutors',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addTutorRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_tutors',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddLeaderRelationReturnsUid() {
		$uid = $this->addLeaderRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddLeaderRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addLeaderRelation(array()),
			$this->addLeaderRelation(array())
		);
	}

	public function testAddLeaderRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_leaders',
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addLeaderRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_leaders',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addLeaderRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_leaders',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddEventTypeRelationReturnsUid() {
		$uid = $this->addEventTypeRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddEventTypeRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addLeaderRelation(array()),
			$this->addLeaderRelation(array())
		);
	}


	///////////////////////////////////////
	// Tests for some basic functionality
	///////////////////////////////////////

	public function testIsOk() {
		$this->assertTrue(
			$this->fixture->isOk()
		);
	}


	//////////////////////////////
	// Tests concerning getTitle
	//////////////////////////////

	/**
	 * @test
	 */
	public function getTitleForSingleEventReturnsTitle() {
		$this->assertSame(
			'a test event',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleForTopicReturnsTitle() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'title' => 'a test topic',
			)
		);
		$topic = new tx_seminars_seminar($topicRecordUid);

		$this->assertSame(
			'a test topic',
			$topic->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleForDateReturnsTopicTitle() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'title' => 'a test topic',
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
				'title' => 'a test date',
			)
		);
		$date = new tx_seminars_seminar($dateRecordUid);

		$this->assertSame(
			'a test topic',
			$date->getTitle()
		);
	}


	/////////////////////////////////////////////////////////
	// Tests regarding the ability to register for an event
	/////////////////////////////////////////////////////////

	public function testCanSomebodyRegisterIsTrueForEventWithFutureDate() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		$this->assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsTrueForEventWithFutureDateAndRegistrationWithoutDateActivated() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		$this->assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsFalseForPastEvent() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsFalseForPastEventWithRegistrationWithoutDateActivated() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsFalseForCurrentlyRunningEvent() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsFalseForCurrentlyRunningEventWithRegistrationWithoutDateActivated() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsFalseForEventWithoutDate() {
		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsTrueForEventWithoutDateAndRegistrationWithoutDateActivated() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function test_CanSomebodyRegister_ForEventWithUnlimitedVacanvies_IsTrue() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		$this->fixture->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function test_CanSomebodyRegister_ForCancelledEvent_ReturnsFalse() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function test_CanSomebodyRegister_ForEventWithoutNeedeRegistration_ReturnsFalse() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setNeedsRegistration(FALSE);

		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function test_CanSomebodyRegister_ForFullyBookedEvent_ReturnsFalse() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setNumberOfAttendances(10);

		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function test_CanSomebodyRegister_ForEventWithRegistrationQueueAndNoRegularVacancies_ReturnsTrue() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setNumberOfAttendances(10);
		$this->fixture->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function test_CanSomebodyRegister_ForEventWithRegistrationQueueAndRegularVacancies_ReturnsTrue() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setNumberOfAttendances(5);
		$this->fixture->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function test_CanSomebodyRegister_ForEventWithRegistrationBeginInFuture_ReturnsFalse() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setRegistrationBeginDate(
			$GLOBALS['SIM_EXEC_TIME'] + 20
		);

		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function test_CanSomebodyRegister_ForEventWithRegistrationBeginInPast_ReturnsTrue() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setRegistrationBeginDate(
			$GLOBALS['SIM_EXEC_TIME'] - 20
		);

		$this->assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function test_CanSomebodyRegister_ForEventWithoutRegistrationBegin_ReturnsTrue() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setRegistrationBeginDate(0);

		$this->assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}


	////////////////////////////////////////////////
	// Tests concerning canSomebodyRegisterMessage
	////////////////////////////////////////////////


	public function test_CanSomebodyRegisterMessage_EventWithFutureDate_ReturnsEmptyString() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

		$this->assertEquals(
			'',
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForPastEvent_ReturnsSeminarRegistrationClosedMessage() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);

		$this->assertEquals(
			$this->fixture->translate('message_seminarRegistrationIsClosed'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForPastEventWithRegistrationWithoutDateActivated_ReturnsRegistrationDeadlineOverMessage() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);

		$this->assertEquals(
			$this->fixture->translate('message_seminarRegistrationIsClosed'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForCurrentlyRunningEvent_ReturnsSeminarRegistrationClosesMessage() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

		$this->assertEquals(
			$this->fixture->translate('message_seminarRegistrationIsClosed'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForCurrentlyRunningEventWithRegistrationWithoutDateActivated_ReturnsSeminarRegistrationClosesMessage() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

		$this->assertEquals(
			$this->fixture->translate('message_seminarRegistrationIsClosed'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForEventWithoutDate_ReturnsNoDateMessage() {
		$this->assertEquals(
			$this->fixture->translate('message_noDate'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForEventWithoutDateAndRegistrationWithoutDateActivated_ReturnsEmptyString() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);
		$this->fixture->setBeginDate(0);
		$this->fixture->setRegistrationDeadline(0);

		$this->assertEquals(
			'',
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForEventWithUnlimitedVacanvies_ReturnsEmptyString() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		$this->fixture->setUnlimitedVacancies();

		$this->assertEquals(
			'',
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForCancelledEvent_ReturnsSeminarCancelledMessage() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		$this->assertEquals(
			$this->fixture->translate('message_seminarCancelled'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForEventWithoutNeedeRegistration_ReturnsNoRegistrationNecessaryMessage() {
		$this->fixture->setNeedsRegistration(FALSE);

		$this->assertEquals(
			$this->fixture->translate('message_noRegistrationNecessary'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForFullyBookedEvent_ReturnsNoVacanciesMessage() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setNumberOfAttendances(10);

		$this->assertEquals(
			$this->fixture->translate('message_noVacancies'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForFullyBookedEventWithRegistrationQueue_ReturnsEmptyString() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setNumberOfAttendances(10);
		$this->fixture->setRegistrationQueue(TRUE);

		$this->assertEquals(
			'',
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForEventWithRegistrationBeginInFuture_ReturnsRegistrationOpensOnMessage() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setRegistrationBeginDate(
			$GLOBALS['SIM_EXEC_TIME'] + 20
		);

		$this->assertEquals(
			sprintf(
				$this->fixture->translate('message_registrationOpensOn'),
				$this->fixture->getRegistrationBegin()
			),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForEventWithRegistrationBeginInPast_ReturnsEmptyString() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setRegistrationBeginDate(
			$GLOBALS['SIM_EXEC_TIME'] - 20
		);

		$this->assertEquals(
			'',
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	public function test_CanSomebodyRegisterMessage_ForEventWithoutRegistrationBegin_ReturnsEmptyString() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setRegistrationBeginDate(0);

		$this->assertEquals(
			'',
			$this->fixture->canSomebodyRegisterMessage()
		);
	}


	/////////////////////////////////////////////
	// Tests regarding the language of an event
	/////////////////////////////////////////////

	public function testGetLanguageFromIsoCodeWithValidLanguage() {
		$this->assertEquals(
			'Deutsch',
			$this->fixture->getLanguageNameFromIsoCode('de')
		);
	}

	public function testGetLanguageFromIsoCodeWithInvalidLanguage() {
		$this->assertEquals(
			'',
			$this->fixture->getLanguageNameFromIsoCode('xy')
		);
	}

	public function testGetLanguageFromIsoCodeWithVeryInvalidLanguage() {
		$this->assertEquals(
			'',
			$this->fixture->getLanguageNameFromIsoCode('foobar')
		);
	}

	public function testGetLanguageFromIsoCodeWithEmptyLanguage() {
		$this->assertEquals(
			'',
			$this->fixture->getLanguageNameFromIsoCode('')
		);
	}

	public function testHasLanguageWithLanguageReturnsTrue() {
		$this->fixture->setLanguage('de');
		$this->assertTrue(
			$this->fixture->hasLanguage()
		);
	}

	public function testHasLanguageWithNoLanguageReturnsFalse() {
		$this->fixture->setLanguage('');
		$this->assertFalse(
			$this->fixture->hasLanguage()
		);
	}

	public function testGetLanguageNameWithDefaultLanguageOnSingleEvent() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'Deutsch',
			$this->fixture->getLanguageName()
		);
	}

	public function testGetLanguageNameWithValidLanguageOnSingleEvent() {
		$this->fixture->setLanguage('en');
		$this->assertEquals(
			'English',
			$this->fixture->getLanguageName()
		);
	}

	public function testGetLanguageNameWithInvalidLanguageOnSingleEvent() {
		$this->fixture->setLanguage('xy');
		$this->assertEquals(
			'',
			$this->fixture->getLanguageName()
		);
	}

	public function testGetLanguageNameWithNoLanguageOnSingleEvent() {
		$this->fixture->setLanguage('');
		$this->assertEquals(
			'',
			$this->fixture->getLanguageName()
		);
	}

	public function testGetLanguageNameOnDateRecord() {
		// This was an issue with bug #1518 and #1517.
		// The method getLanguage() needs to return the language from the date
		// record instead of the topic record.
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('language' => 'de')
		);

		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
				'language' => 'it'
			)
		);

		$seminar = new tx_seminars_seminar($dateRecordUid);

		$this->assertEquals(
			'Italiano',
			$seminar->getLanguageName()
		);

		$seminar->__destruct();
	}

	public function testGetLanguageOnSingleRecordThatWasADateRecord() {
		// This test comes from bug 1518 and covers the following situation:
		// We have an event record that has the topic field set as it was a
		// date record. But then it was switched to be a single event record.
		// In that case, the language from the single event record must be
		// returned, not the one from the referenced topic record.

		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('language' => 'de')
		);

		$singleRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'topic' => $topicRecordUid,
				'language' => 'it'
			)
		);

		$seminar = new tx_seminars_seminar($singleRecordUid);

		$this->assertEquals(
			'Italiano',
			$seminar->getLanguageName()
		);

		$seminar->__destruct();
	}


	////////////////////////////////////////////////
	// Tests regarding the date fields of an event
	////////////////////////////////////////////////

	public function testGetBeginDateAsTimestampIsInitiallyZero() {
		$this->assertEquals(
			0,
			$this->fixture->getBeginDateAsTimestamp()
		);
	}

	public function testGetBeginDateAsTimestamp() {
		$this->fixture->setBeginDate($this->beginDate);
		$this->assertEquals(
			$this->beginDate,
			$this->fixture->getBeginDateAsTimestamp()
		);
	}

	public function testHasBeginDateIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasBeginDate()
		);
	}

	public function testHasBeginDate() {
		$this->fixture->setBeginDate($this->beginDate);
		$this->assertTrue(
			$this->fixture->hasBeginDate()
		);
	}

	public function testGetEndDateAsTimestampIsInitiallyZero() {
		$this->assertEquals(
			0,
			$this->fixture->getEndDateAsTimestamp()
		);
	}

	public function testGetEndDateAsTimestamp () {
		$this->fixture->setEndDate($this->beginDate);
		$this->assertEquals(
			$this->beginDate,
			$this->fixture->getEndDateAsTimestamp()
		);
	}

	public function testHasEndDateIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasEndDate()
		);
	}

	public function testHasEndDate () {
		$this->fixture->setEndDate($this->beginDate);
		$this->assertTrue(
			$this->fixture->hasEndDate()
		);
	}


	//////////////////////////////////////
	// Tests regarding the registration.
	//////////////////////////////////////

	public function test_NeedsRegistration_forNeedsRegistrationTrue_ReturnsTrue() {
		$this->fixture->setNeedsRegistration(TRUE);

		$this->assertTrue(
			$this->fixture->needsRegistration()
		);
	}

	public function test_NeedsRegistration_forNeedsRegistrationFalse_ReturnsFalse() {
		$this->fixture->setNeedsRegistration(FALSE);

		$this->assertFalse(
			$this->fixture->needsRegistration()
		);
	}


	///////////////////////////////////////////
	// Tests concerning hasUnlimitedVacancies
	///////////////////////////////////////////

	public function test_HasUnlimitedVacancies_ForNeedsRegistrationTrueAndMaxAttendeesZero_ReturnsTrue() {
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(0);

		$this->assertTrue(
			$this->fixture->hasUnlimitedVacancies()
		);
	}

	public function test_HasUnlimitedVacancies_ForNeedsRegistrationTrueAndMaxAttendeesOne_ReturnsFalse() {
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(1);

		$this->assertFalse(
			$this->fixture->hasUnlimitedVacancies()
		);
	}

	public function test_HasUnlimitedVacancies_ForNeedsRegistrationFalseAndMaxAttendeesZero_ReturnsFalse() {
		$this->fixture->setNeedsRegistration(FALSE);
		$this->fixture->setAttendancesMax(0);

		$this->assertFalse(
			$this->fixture->hasUnlimitedVacancies()
		);
	}

	public function test_HasUnlimitedVacancies_ForNeedsRegistrationFalseAndMaxAttendeesOne_ReturnsFalse() {
		$this->fixture->setNeedsRegistration(FALSE);
		$this->fixture->setAttendancesMax(1);

		$this->assertFalse(
			$this->fixture->hasUnlimitedVacancies()
		);
	}


	////////////////////////////
	// Tests concerning isFull
	////////////////////////////

	public function test_IsFull_ForUnlimitedVacanciesAndZeroAttendances_ReturnsFalse() {
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setNumberOfAttendances(0);

		$this->assertFalse(
			$this->fixture->isFull()
		);
	}

	public function test_IsFull_ForUnlimitedVacanciesAndOneAttendance_ReturnsFalse() {
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setNumberOfAttendances(1);

		$this->assertFalse(
			$this->fixture->isFull()
		);
	}

	public function test_IsFull_ForOneVacancyAndNoAttendances_ReturnsFalse() {
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setNumberOfAttendances(0);

		$this->assertFalse(
			$this->fixture->isFull()
		);
	}

	public function test_IsFull_ForOneVacancyAndOneAttendance_ReturnsTrue() {
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setNumberOfAttendances(1);

		$this->assertTrue(
			$this->fixture->isFull()
		);
	}

	public function test_IsFull_ForTwoVacanciesAndOneAttendance_ReturnsFalse() {
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(2);
		$this->fixture->setNumberOfAttendances(1);

		$this->assertFalse(
			$this->fixture->isFull()
		);
	}

	public function test_IsFull_ForTwoVacanciesAndTwoAttendances_ReturnsTrue() {
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(2);
		$this->fixture->setNumberOfAttendances(2);

		$this->assertTrue(
			$this->fixture->isFull()
		);
	}


	/////////////////////////////////////////////////////
	// Tests regarding the unregistration and the queue
	/////////////////////////////////////////////////////

	public function testGetUnregistrationDeadlineAsTimestampForNonZero() {
		$this->fixture->setUnregistrationDeadline($this->unregistrationDeadline);

		$this->assertEquals(
			$this->unregistrationDeadline,
			$this->fixture->getUnregistrationDeadlineAsTimestamp()
		);
	}

	public function testGetUnregistrationDeadlineAsTimestampForZero() {
		$this->fixture->setUnregistrationDeadline(0);

		$this->assertEquals(
			0,
			$this->fixture->getUnregistrationDeadlineAsTimestamp()
		);
	}

	public function testGetUnregistrationDeadlineWithoutTimeForNonZero() {
		$this->fixture->setUnregistrationDeadline(1893488400);

		$this->assertEquals(
			'01.01.2030',
			$this->fixture->getUnregistrationDeadline()
		);
	}

	public function testGetNonUnregistrationDeadlineWithTimeForZero() {
		$this->fixture->setUnregistrationDeadline(1893488400);
		$this->fixture->setShowTimeOfUnregistrationDeadline(1);

		$this->assertEquals(
			'01.01.2030 10:00',
			$this->fixture->getUnregistrationDeadline()
		);
	}

	public function testGetUnregistrationDeadlineIsEmptyForZero() {
		$this->fixture->setUnregistrationDeadline(0);

		$this->assertEquals(
			'',
			$this->fixture->getUnregistrationDeadline()
		);
	}

	public function testHasUnregistrationDeadlineIsTrueForNonZeroDeadline() {
		$this->fixture->setUnregistrationDeadline($this->unregistrationDeadline);

		$this->assertTrue(
			$this->fixture->hasUnregistrationDeadline()
		);
	}

	public function testHasUnregistrationDeadlineIsFalseForZeroDeadline() {
		$this->fixture->setUnregistrationDeadline(0);

		$this->assertFalse(
			$this->fixture->hasUnregistrationDeadline()
		);
	}


	////////////////////////////////////////////////
	// Tests concerning isUnregistrationPossible()
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithoutDeadlineReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setAttendancesMax(10);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithNoBeginDateAndNoDeadlineReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate(0);
		$this->fixture->setAttendancesMax(10);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithGlobalDeadlineInFutureReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithGlobalDeadlineInPastReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(5);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_DAY);
		$this->fixture->setAttendancesMax(10);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithoutBeginDateAndWithGlobalDeadlineReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate(0);
		$this->fixture->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithFutureEventDeadlineReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithPastEventDeadlineReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(
			$this->now - tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setAttendancesMax(10);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithoutBeginDateAndWithFutureEventDeadlineReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate(0);
		$this->fixture->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithoutBeginDateAndWithPastEventDeadlineReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(
			$this->now - tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate(0);
		$this->fixture->setAttendancesMax(10);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithBothDeadlinesInFutureReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithBothDeadlinesInPastReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(2);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setUnregistrationDeadline(
			$this->now - tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_DAY);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithoutBeginDateAndWithBothDeadlinesInFutureReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate(0);
		$this->fixture->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithoutBeginDateAndWithBothDeadlinesInPastReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setBeginDate(0);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setUnregistrationDeadline(
			$this->now - tx_oelib_Time::SECONDS_PER_DAY
		);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithPassedEventUnregistrationDeadlineReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setBeginDate($this->now + 2 * tx_oelib_Time::SECONDS_PER_DAY);
		$this->fixture->setUnregistrationDeadline(
			$this->now - tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setAttendancesMax(10);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithNonZeroAttendancesMaxReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setAttendancesMax(10);
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleForNeedsRegistrationFalseReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setNeedsRegistration(FALSE);
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleForEventWithEmptyWaitingListAndAllowUnregistrationWithEmptyWaitingListReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setAttendancesMax(10);
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setRegistrationQueue(TRUE);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}


	////////////////////////////////////////////////////////////////////////
	// Tests concerning getUnregistrationDeadlineFromModelAndConfiguration
	////////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForNoBeginDateAndNoUnregistrationDeadlineReturnsZero() {
		$this->fixture->setBeginDate(0);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setGlobalUnregistrationDeadline(0);

		$this->assertEquals(
			0,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForNoBeginDateAndUnregistrationDeadlineSetInEventReturnsUnregistrationDeadline() {
		$this->fixture->setBeginDate(0);
		$this->fixture->setUnregistrationDeadline($this->now);
		$this->fixture->setGlobalUnregistrationDeadline(0);

		$this->assertEquals(
			$this->now,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForNoBeginDateAndUnregistrationDeadlinInEventAndUnregistrationDeadlineSetInConfigurationReturnsZero() {
		$this->fixture->setBeginDate(0);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setGlobalUnregistrationDeadline($this->now);

		$this->assertEquals(
			0,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForUnregistrationDeadlineSetInEventReturnsThisDeadline() {
		$this->fixture->setBeginDate(($this->now + tx_oelib_Time::SECONDS_PER_WEEK));
		$this->fixture->setUnregistrationDeadline($this->now);
		$this->fixture->setGlobalUnregistrationDeadline(0);

		$this->assertEquals(
			$this->now,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForNoUnregistrationDeadlineSetInEventAndNoDeadlineConfigurationSetReturnsZero() {
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setGlobalUnregistrationDeadline(0);

		$this->assertEquals(
			0,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForNoUnregistrationDeadlineSetInEventAndDeadlineConfigurationSetReturnsCalculatedDeadline() {
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setGlobalUnregistrationDeadline(1);

		$this->assertEquals(
			$this->now + tx_oelib_Time::SECONDS_PER_WEEK - tx_oelib_Time::SECONDS_PER_DAY,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForUnregistrationDeadlinesSetInEventAndConfigurationReturnsEventsDeadline() {
		$this->fixture->setBeginDate(($this->now + tx_oelib_Time::SECONDS_PER_WEEK));
		$this->fixture->setUnregistrationDeadline($this->now);
		$this->fixture->setGlobalUnregistrationDeadline(1);

		$this->assertEquals(
			$this->now,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}


	//////////////////////////////////////////
	// Tests concerning hasRegistrationQueue
	//////////////////////////////////////////

	public function test_hasRegistrationQueue_WithQueue_ReturnsTrue() {
		$this->fixture->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->hasRegistrationQueue()
		);
	}

	public function test_hasRegistrationQueue_WithoutQueue_ReturnsFalse() {
			$this->fixture->setRegistrationQueue(FALSE);

		$this->assertFalse(
			$this->fixture->hasRegistrationQueue()
		);
	}


	///////////////////////////////////////////////////////
	// Tests concerning getAttendancesOnRegistrationQueue
	///////////////////////////////////////////////////////

	public function testGetAttendancesOnRegistrationQueueIsInitiallyZero() {
		$this->assertEquals(
			0,
			$this->fixture->getAttendancesOnRegistrationQueue()
		);
	}

	public function testGetAttendancesOnRegistrationQueueForNonEmptyRegistrationQueue() {
		$this->fixture->setNumberOfAttendancesOnQueue(4);
		$this->assertEquals(
			4,
			$this->fixture->getAttendancesOnRegistrationQueue()
		);
	}

	public function testHasAttendancesOnRegistrationQueueIsFalseForNoRegistrations() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueue(FALSE);
		$this->fixture->setNumberOfAttendances(0);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		$this->assertFalse(
			$this->fixture->hasAttendancesOnRegistrationQueue()
		);
	}

	public function testHasAttendancesOnRegistrationQueueIsFalseForRegularRegistrationsOnly() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueue(FALSE);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		$this->assertFalse(
			$this->fixture->hasAttendancesOnRegistrationQueue()
		);
	}

	public function testHasAttendancesOnRegistrationQueueIsTrueForQueueRegistrations() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueue(TRUE);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(1);

		$this->assertTrue(
			$this->fixture->hasAttendancesOnRegistrationQueue()
		);
	}

	public function testIsUnregistrationPossibleIsTrueWithNonEmptyQueueByDefault() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueue(TRUE);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(1);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->now + (6*tx_oelib_Time::SECONDS_PER_DAY))
		);
		$this->fixture->setBeginDate(($this->now + tx_oelib_Time::SECONDS_PER_WEEK));

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testIsUnregistrationPossibleIsFalseWithEmptyQueueByDefault() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueue(TRUE);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->now + (6*tx_oelib_Time::SECONDS_PER_DAY))
		);
		$this->fixture->setBeginDate(($this->now + tx_oelib_Time::SECONDS_PER_WEEK));

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testIsUnregistrationPossibleIsTrueWithEmptyQueueIfAllowedByConfiguration() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueue(TRUE);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->now + (6*tx_oelib_Time::SECONDS_PER_DAY))
		);
		$this->fixture->setBeginDate(($this->now + tx_oelib_Time::SECONDS_PER_WEEK));

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}


	///////////////////////////////////////////////////////////
	// Tests regarding the country field of the place records
	///////////////////////////////////////////////////////////

	public function testGetPlacesWithCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);

		$this->assertEquals(
			array('ch'),
			$this->fixture->getPlacesWithCountry()
		);
	}

	public function testGetPlacesWithCountryWithNoCountry() {
		$this->addPlaceRelation(
			array(
				'country' => ''
			)
		);

		$this->assertEquals(
			array(),
			$this->fixture->getPlacesWithCountry()
		);
	}

	public function testGetPlacesWithCountryWithInvalidCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'xy'
			)
		);

		$this->assertEquals(
			array('xy'),
			$this->fixture->getPlacesWithCountry()
		);
	}

	public function testGetPlacesWithCountryWithNoPlace() {
		$this->assertEquals(
			array(),
			$this->fixture->getPlacesWithCountry()
		);
	}

	public function testGetPlacesWithCountryWithDeletedPlace() {
		$this->addPlaceRelation(
			array(
				'country' => 'at',
				'deleted' => 1
			)
		);

		$this->assertEquals(
			array(),
			$this->fixture->getPlacesWithCountry()
		);
	}

	public function testGetPlacesWithCountryWithMultipleCountries() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);
		$this->addPlaceRelation(
			array(
				'country' => 'de'
			)
		);

		$this->assertEquals(
			array('ch', 'de'),
			$this->fixture->getPlacesWithCountry()
		);
	}

	public function testHasCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);

		$this->assertTrue(
			$this->fixture->hasCountry()
		);
	}

	public function testHasCountryWithNoCountry() {
		$this->addPlaceRelation(
			array(
				'country' => ''
			)
		);

		$this->assertFalse(
			$this->fixture->hasCountry()
		);
	}

	public function testHasCountryWithInvalicCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'xy'
			)
		);

		// We expect a TRUE even if the country code is invalid! See function's
		// comment on this.
		$this->assertTrue(
			$this->fixture->hasCountry()
		);
	}

	public function testHasCountryWithNoPlace() {
		$this->assertFalse(
			$this->fixture->hasCountry()
		);
	}

	public function testHasCountryWithMultipleCountries() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);
		$this->addPlaceRelation(
			array(
				'country' => 'de'
			)
		);

		$this->assertTrue(
			$this->fixture->hasCountry()
		);
	}

	public function testGetCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);

		$this->assertEquals(
			'Schweiz',
			$this->fixture->getCountry()
		);
	}

	public function testGetCountryWithNoCountry() {
		$this->addPlaceRelation(
			array(
				'country' => ''
			)
		);

		$this->assertEquals(
			'',
			$this->fixture->getCountry()
		);
	}

	public function testGetCountryWithInvalidCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'xy'
			)
		);

		$this->assertEquals(
			'',
			$this->fixture->getCountry()
		);
	}

	public function testGetCountryWithMultipleCountries() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);
		$this->addPlaceRelation(
			array(
				'country' => 'de'
			)
		);

		$this->assertEquals(
			'Schweiz, Deutschland',
			$this->fixture->getCountry()
		);
	}

	public function testGetCountryWithNoPlace() {
		$this->assertEquals(
			'',
			$this->fixture->getCountry()
		);
	}

	public function testGetCountryNameFromIsoCode() {
		$this->assertEquals(
			'Schweiz',
			$this->fixture->getCountryNameFromIsoCode('ch')
		);

		$this->assertEquals(
			'',
			$this->fixture->getCountryNameFromIsoCode('xy')
		);

		$this->assertEquals(
			'',
			$this->fixture->getCountryNameFromIsoCode('')
		);
	}

	public function testGetRelatedMmRecordUidsWithNoPlace() {
		$this->assertEquals(
			array(),
			$this->fixture->getRelatedMmRecordUids('tx_seminars_seminars_place_mm')
		);
	}

	public function testGetRelatedMmRecordUidsWithOnePlace() {
		$uid = $this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);

		$this->assertEquals(
			array($uid),
			$this->fixture->getRelatedMmRecordUids('tx_seminars_seminars_place_mm')
		);
	}

	public function testGetRelatedMmRecordUidsWithTwoPlaces() {
		$uid1 = $this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);
		$uid2 = $this->addPlaceRelation(
			array(
				'country' => 'de'
			)
		);

		$result = $this->fixture->getRelatedMmRecordUids(
			'tx_seminars_seminars_place_mm'
		);
		sort($result);
		$this->assertEquals(
			array($uid1, $uid2),
			$result
		);
	}


	//////////////////////////////////////
	// Tests regarding the target groups
	//////////////////////////////////////

	public function testHasTargetGroupsIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasTargetGroups()
		);
	}

	public function testHasTargetGroups() {
		$this->addTargetGroupRelation(array());

		$this->assertTrue(
			$this->fixture->hasTargetGroups()
		);
	}

	public function testGetTargetGroupNamesWithNoTargetGroup() {
		$this->assertEquals(
			'',
			$this->fixture->getTargetGroupNames()
		);
	}

	public function testGetTargetGroupNamesWithSingleTargetGroup() {
		$title = 'TEST target group 1';
		$this->addTargetGroupRelation(array('title' => $title));

		$this->assertEquals(
			$title,
			$this->fixture->getTargetGroupNames()
		);
	}

	public function testGetTargetGroupNamesWithMultipleTargetGroups() {
		$titleTargetGroup1 = 'TEST target group 1';
		$this->addTargetGroupRelation(array('title' => $titleTargetGroup1));

		$titleTargetGroup2 = 'TEST target group 2';
		$this->addTargetGroupRelation(array('title' => $titleTargetGroup2));

		$this->assertEquals(
			$titleTargetGroup1.', '.$titleTargetGroup2,
			$this->fixture->getTargetGroupNames()
		);
	}

	public function testGetTargetGroupsAsArrayWithNoTargetGroups() {
		$this->assertEquals(
			array(),
			$this->fixture->getTargetGroupsAsArray()
		);
	}

	public function testGetTargetGroupsAsArrayWithSingleTargetGroup() {
		$title = 'TEST target group 1';
		$this->addTargetGroupRelation(array('title' => $title));

		$this->assertEquals(
			array($title),
			$this->fixture->getTargetGroupsAsArray()
		);
	}

	public function testGetTargetGroupsAsArrayWithMultipleTargetGroups() {
		$titleTargetGroup1 = 'TEST target group 1';
		$this->addTargetGroupRelation(array('title' => $titleTargetGroup1));

		$titleTargetGroup2 = 'TEST target group 2';
		$this->addTargetGroupRelation(array('title' => $titleTargetGroup2));

		$this->assertEquals(
			array($titleTargetGroup1, $titleTargetGroup2),
			$this->fixture->getTargetGroupsAsArray()
		);
	}


	////////////////////////////////////////
	// Tests regarding the payment methods
	////////////////////////////////////////

	public function testHasPaymentMethodsReturnsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasPaymentMethods()
		);
	}

	public function testCanHaveOnePaymentMethod() {
		$this->addPaymentMethodRelation(array());

		$this->assertTrue(
			$this->fixture->hasPaymentMethods()
		);
	}

	public function testGetPaymentMethodsPlainWithNoPaymentMethodReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getPaymentMethodsPlain()
		);
	}

	public function testGetPaymentMethodsPlainWithSinglePaymentMethodReturnsASinglePaymentMethod() {
		$title = 'Test title';
		$this->addPaymentMethodRelation(array('title' => $title));

		$this->assertContains(
			$title,
			$this->fixture->getPaymentMethodsPlain()
		);
	}

	public function testGetPaymentMethodsPlainWithMultiplePaymentMethodsReturnsMultiplePaymentMethods() {
		$firstTitle = 'Payment Method 1';
		$secondTitle = 'Payment Method 2';
		$this->addPaymentMethodRelation(array('title' => $firstTitle));
		$this->addPaymentMethodRelation(array('title' => $secondTitle));

		$this->assertContains(
			$firstTitle,
			$this->fixture->getPaymentMethodsPlain()
		);
		$this->assertContains(
			$secondTitle,
			$this->fixture->getPaymentMethodsPlain()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsWithoutPaymentMethodsReturnsAnEmptyArray() {
		$this->assertEquals(
			array(),
			$this->fixture->getPaymentMethods()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsWithOnePaymentMethodReturnsOnePaymentMethod() {
		$this->addPaymentMethodRelation(array('title' => 'Payment Method'));

		$this->assertEquals(
			array('Payment Method'),
			$this->fixture->getPaymentMethods()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsWithTwoPaymentMethodsReturnsTwoPaymentMethods() {
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 1'));
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 2'));

		$this->assertEquals(
			array('Payment Method 1', 'Payment Method 2'),
			$this->fixture->getPaymentMethods()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsWithTwoPaymentMethodsReturnsTwoPaymentMethodsSorted() {
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 2'));
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 1'));

		$this->assertEquals(
			array('Payment Method 2', 'Payment Method 1'),
			$this->fixture->getPaymentMethods()
		);
	}


	/////////////////////////////////////////////////
	// Tests concerning getPaymentMethodsPlainShort
	/////////////////////////////////////////////////

	public function testGetPaymentMethodsPlainShortWithNoPaymentMethodReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}

	public function testGetPaymentMethodsPlainShortWithSinglePaymentMethodReturnsASinglePaymentMethod() {
		$title = 'Test title';
		$this->addPaymentMethodRelation(array('title' => $title));

		$this->assertContains(
			$title,
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}

	public function testGetPaymentMethodsPlainShortWithMultiplePaymentMethodsReturnsMultiplePaymentMethods() {
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 1'));
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 2'));

		$this->assertContains(
			'Payment Method 1',
			$this->fixture->getPaymentMethodsPlainShort()
		);
		$this->assertContains(
			'Payment Method 2',
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}

	public function testGetPaymentMethodsPlainShortSeparatesMultiplePaymentMethodsWithLineFeeds() {
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 1'));
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 2'));

		$this->assertContains(
			'Payment Method 1' . LF . 'Payment Method 2',
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}

	public function testGetPaymentMethodsPlainShortDoesNotSeparateMultiplePaymentMethodsWithCarriageReturnsAndLineFeeds() {
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 1'));
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 2'));

		$this->assertNotContains(
			'Payment Method 1' . CRLF . 'Payment Method 2',
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}


	/////////////////////////////////////////////////
	// Tests concerning getSinglePaymentMethodPlain
	/////////////////////////////////////////////////

	public function testGetSinglePaymentMethodPlainWithInvalidPaymentMethodUidReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getSinglePaymentMethodPlain(0)
		);
	}

	public function testGetSinglePaymentMethodPlainWithValidPaymentMethodUidReturnsTheTitleOfThePaymentMethod() {
		$title = 'Test payment method';
		$uid = $this->addPaymentMethodRelation(array('title' => $title));

		$this->assertContains(
			$title,
			$this->fixture->getSinglePaymentMethodPlain($uid)
		);
	}

	public function testGetSinglePaymentMethodPlainWithNonExistentPaymentMethodUidReturnsAnEmptyString() {
		$uid = $this->addPaymentMethodRelation(array());

		$this->assertEquals(
			'',
			$this->fixture->getSinglePaymentMethodPlain($uid + 1)
		);
	}

	public function testGetSinglePaymentMethodShortWithInvalidPaymentMethodUidReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getSinglePaymentMethodShort(0)
		);
	}

	public function testGetSinglePaymentMethodShortWithValidPaymentMethodUidReturnsTheTitleOfThePaymentMethod() {
		$title = 'Test payment method';
		$uid = $this->addPaymentMethodRelation(array('title' => $title));

		$this->assertContains(
			$title,
			$this->fixture->getSinglePaymentMethodShort($uid)
		);
	}

	public function testGetSinglePaymentMethodShortWithNonExistentPaymentMethodUidReturnsAnEmptyString() {
		$uid = $this->addPaymentMethodRelation(array());

		$this->assertEquals(
			'',
			$this->fixture->getSinglePaymentMethodShort($uid + 1)
		);
	}


	///////////////////////////////////
	// Tests regarding the event type
	///////////////////////////////////

	public function testSetEventTypeThrowsExceptionForNegativeArgument() {
		$this->setExpectedException(
			'Exception', '$eventType must be >= 0.'
		);

		$this->fixture->setEventType(-1);
	}

	public function testSetEventTypeIsAllowedWithZero() {
		$this->fixture->setEventType(0);
	}

	public function testSetEventTypeIsAllowedWithPositiveInteger() {
		$this->fixture->setEventType(1);
	}

	public function testHasEventTypeInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasEventType()
		);
	}

	public function testHasEventTypeReturnsTrueIfSingleEventHasNonZeroEventType() {
		$this->fixture->setEventType(
			$this->testingFramework->createRecord('tx_seminars_event_types')
		);

		$this->assertTrue(
			$this->fixture->hasEventType()
		);
	}

	public function testGetEventTypeReturnsEmptyStringForSingleEventWithoutType() {
		$this->assertEquals(
			'',
			$this->fixture->getEventType()
		);
	}

	public function testGetEventTypeReturnsTitleOfRelatedEventTypeForSingleEvent() {
		$this->fixture->setEventType(
			$this->testingFramework->createRecord(
				'tx_seminars_event_types', array('title' => 'foo type')
			)
		);

		$this->assertEquals(
			'foo type',
			$this->fixture->getEventType()
		);
	}

	public function testGetEventTypeForDateRecordReturnsTitleOfEventTypeFromTopicRecord() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'event_type' => $this->testingFramework->createRecord(
					'tx_seminars_event_types', array('title' => 'foo type')
				),
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
			)
		);
		$seminar = new tx_seminars_seminar($dateRecordUid);

		$this->assertEquals(
			'foo type',
			$seminar->getEventType()
		);
	}

	public function testGetEventTypeForTopicRecordReturnsTitleOfRelatedEventType() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'event_type' => $this->testingFramework->createRecord(
					'tx_seminars_event_types', array('title' => 'foo type')
				),
			)
		);
		$seminar = new tx_seminars_seminar($topicRecordUid);

		$this->assertEquals(
			'foo type',
			$seminar->getEventType()
		);
	}

	public function testGetEventTypeUidReturnsUidFromTopicRecord() {
		// This test comes from bug #1515.
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'event_type' => 99999
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
				'event_type' => 199999
			)
		);
		$seminar = new tx_seminars_seminar($dateRecordUid);

		$this->assertEquals(
			99999,
			$seminar->getEventTypeUid()
		);
	}

	public function testGetEventTypeUidInitiallyReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getEventTypeUid()
		);
	}

	public function testGetEventTypeUidWithEventTypeReturnsEventTypeUid() {
		$eventTypeUid = $this->addEventTypeRelation(array());
		$this->assertEquals(
			$eventTypeUid,
			$this->fixture->getEventTypeUid()
		);
	}


	////////////////////////////////////////////
	// Tests regarding the organizing partners
	////////////////////////////////////////////

	public function testHasOrganizingPartnersReturnsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasOrganizingPartners()
		);
	}

	public function testCanHaveOneOrganizingPartner() {
		$this->addOrganizingPartnerRelation(array());

		$this->assertTrue(
			$this->fixture->hasOrganizingPartners()
		);
	}

	public function testGetNumberOfOrganizingPartnersWithNoOrganizingPartnerReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfOrganizingPartners()
		);
	}

	public function testGetNumberOfOrganizingPartnersWithSingleOrganizingPartnerReturnsOne() {
		$this->addOrganizingPartnerRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfOrganizingPartners()
		);
	}

	public function testGetNumberOfOrganizingPartnersWithMultipleOrganizingPartnersReturnsTwo() {
		$this->addOrganizingPartnerRelation(array());
		$this->addOrganizingPartnerRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfOrganizingPartners()
		);
	}


	///////////////////////////////////
	// Tests regarding the categories
	///////////////////////////////////

	public function testInitiallyHasNoCategories() {
		$this->assertFalse(
			$this->fixture->hasCategories()
		);
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfCategories()
		);
		$this->assertEquals(
			array(),
			$this->fixture->getCategories()
		);
	}

	public function testGetCategoriesCanReturnOneCategory() {
		$categoryUid = $this->addCategoryRelation(array('title' => 'Test'));

		$this->assertTrue(
			$this->fixture->hasCategories()
		);
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfCategories()
		);
		$this->assertEquals(
			array($categoryUid => array('title' => 'Test', 'icon' => '')),
			$this->fixture->getCategories()
		);
	}

	public function testCanHaveTwoCategories() {
		$categoryUid1 = $this->addCategoryRelation(array('title' => 'Test 1'));
		$categoryUid2 = $this->addCategoryRelation(array('title' => 'Test 2'));

		$this->assertTrue(
			$this->fixture->hasCategories()
		);
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfCategories()
		);

		$categories = $this->fixture->getCategories();

		$this->assertEquals(
			2,
			count($categories)
		);
		$this->assertEquals(
			'Test 1',
			$categories[$categoryUid1]['title']
		);
		$this->assertEquals(
			'Test 2',
			$categories[$categoryUid2]['title']
		);
	}

	public function testGetCategoriesReturnsIconOfCategory() {
		$categoryUid = $this->addCategoryRelation(
			array(
				'title' => 'Test 1',
				'icon' => 'foo.gif',
			)
		);

		$categories = $this->fixture->getCategories();

		$this->assertEquals(
			'foo.gif',
			$categories[$categoryUid]['icon']
		);
	}

	public function testGetCategoriesReturnsCategoriesOrderedBySorting() {
		$categoryUid1 = $this->addCategoryRelation(array('title' => 'Test 1'), 2);
		$categoryUid2 = $this->addCategoryRelation(array('title' => 'Test 2'), 1);

		$this->assertTrue(
			$this->fixture->hasCategories()
		);

		$this->assertEquals(
			array(
				$categoryUid2 => array('title' => 'Test 2', 'icon' => ''),
				$categoryUid1 => array('title' => 'Test 1', 'icon' => ''),
			),
			$this->fixture->getCategories()
		);
	}


	///////////////////////////////////
	// Tests regarding the time slots
	///////////////////////////////////

	public function testGetTimeslotsAsArrayWithMarkersReturnsArraySortedByDate() {
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'seminar' => $this->fixture->getUid(),
				'begin_date' => 200,
				'room' => 'Room1'
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'seminar' => $this->fixture->getUid(),
				'begin_date' => 100,
				'room' => 'Room2'
			)
		);

		$timeSlotsWithMarkers = $this->fixture->getTimeslotsAsArrayWithMarkers();
		$this->assertEquals(
			$timeSlotsWithMarkers[0]['room'],
			'Room2'
		);
		$this->assertEquals(
			$timeSlotsWithMarkers[1]['room'],
			'Room1'
		);
	}


	///////////////////////////////////
	// Tests regarding the organizers
	///////////////////////////////////

	public function testHasOrganizersReturnsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasOrganizers()
		);
	}

	public function testHasOrganizersReturnsFalseForStringInOrganizersField() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'organizers' => 'foo',
			)
		);
		$fixture = new tx_seminars_seminarchild($eventUid);
		$hasOrganizers = $fixture->hasOrganizers();
		$fixture->__destruct();

		$this->assertFalse(
			$hasOrganizers
		);
	}

	public function testCanHaveOneOrganizer() {
		$this->addOrganizerRelation(array());

		$this->assertTrue(
			$this->fixture->hasOrganizers()
		);
	}

	public function testGetNumberOfOrganizersWithNoOrganizerReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfOrganizers()
		);
	}

	public function testGetNumberOfOrganizersWithSingleOrganizerReturnsOne() {
		$this->addOrganizerRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfOrganizers()
		);
	}

	public function testGetNumberOfOrganizersWithMultipleOrganizersReturnsTwo() {
		$this->addOrganizerRelation(array());
		$this->addOrganizerRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfOrganizers()
		);
	}


	///////////////////////////////////
	// Tests concerning getOrganizers
	///////////////////////////////////

	public function testGetOrganizersWithNoOrganizersReturnsEmptyString() {
		$this->createPi1();

		$this->assertEquals(
			'',
			$this->fixture->getOrganizers($this->pi1)
		);
	}

	public function testGetOrganizersForOneOrganizerReturnsOrganizerName() {
		$this->createPi1();
		$this->addOrganizerRelation(array('title' => 'foo'));

		$this->assertContains(
			'foo',
			$this->fixture->getOrganizers($this->pi1)
		);
	}

	public function testGetOrganizersForOneOrganizerWithHomepageReturnsOrganizerLinkedToOrganizersHomepage() {
		$this->createPi1();
		$this->addOrganizerRelation(
			array(
				'title' => 'foo',
				'homepage' => 'www.bar.com',
			)
		);

		$this->assertContains(
			'<a href="http://www.bar.com',
			$this->fixture->getOrganizers($this->pi1)
		);
	}

	public function testGetOrganizersWithTwoOrganizersReturnsBothOrganizerNames() {
		$this->createPi1();
		$this->addOrganizerRelation(array('title' => 'foo'));
		$this->addOrganizerRelation(array('title' => 'bar'));

		$organizers = $this->fixture->getOrganizers($this->pi1);

		$this->assertContains(
			'foo',
			$organizers
		);
		$this->assertContains(
			'bar',
			$organizers
		);
	}


	//////////////////////////////////////
	// Tests concerning getOrganizersRaw
	//////////////////////////////////////

	public function testGetOrganizersRawWithNoOrganizersReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getOrganizersRaw()
		);
	}

	public function testGetOrganizersRawWithSingleOrganizerWithoutHomepageReturnsSingleOrganizer() {
		$organizer = array(
			'title' => 'test organizer 1',
			'homepage' => ''
		);
		$this->addOrganizerRelation($organizer);
		$this->assertEquals(
			$organizer['title'],
			$this->fixture->getOrganizersRaw()
		);
	}

	public function testGetOrganizersRawWithSingleOrganizerWithHomepageReturnsSingleOrganizerWithHomepage() {
		$organizer = array(
			'title' => 'test organizer 1',
			'homepage' => 'test homepage 1'
		);
		$this->addOrganizerRelation($organizer);
		$this->assertEquals(
			$organizer['title'].', '.$organizer['homepage'],
			$this->fixture->getOrganizersRaw()
		);
	}

	public function testGetOrganizersRawForTwoOrganizersWithoutHomepageReturnsTwoOrganizers() {
		$this->addOrganizerRelation(
			array('title' => 'test organizer 1','homepage' => '')
		);
		$this->addOrganizerRelation(
			array('title' => 'test organizer 2','homepage' => '')
		);

		$this->assertContains(
			'test organizer 1',
			$this->fixture->getOrganizersRaw()
		);
		$this->assertContains(
			'test organizer 2',
			$this->fixture->getOrganizersRaw()
		);
	}

	public function testGetOrganizersRawForTwoOrganizersWithHomepageReturnsTwoOrganizersWithHomepage() {
		$this->addOrganizerRelation(
			array(
				'title' => 'test organizer 1',
				'homepage' => 'test homepage 1',
			)
		);
		$this->addOrganizerRelation(
			array(
				'title' => 'test organizer 2',
				'homepage' => 'test homepage 2'
			)
		);

		$this->assertContains(
			'test homepage 1',
			$this->fixture->getOrganizersRaw()
		);
		$this->assertContains(
			'test homepage 2',
			$this->fixture->getOrganizersRaw()
		);
	}

	public function testGetOrganizersRawSeparatesMultipleOrganizersWithLineFeeds() {
		$this->addOrganizerRelation(array('title' => 'test organizer 1'));
		$this->addOrganizerRelation(array('title' => 'test organizer 2'));

		$this->assertContains(
			'test organizer 1' . LF . 'test organizer 2',
			$this->fixture->getOrganizersRaw()
		);
	}

	public function testGetOrganizersRawDoesNotSeparateMultipleOrganizersWithCarriageReturnsAndLineFeeds() {
		$this->addOrganizerRelation(array('title' => 'test organizer 1'));
		$this->addOrganizerRelation(array('title' => 'test organizer 2'));

		$this->assertNotContains(
			'test organizer 1' . CRLF . 'test organizer 2',
			$this->fixture->getOrganizersRaw()
		);
	}


	///////////////////////////////////////////////
	// Tests concerning getOrganizersNameAndEmail
	///////////////////////////////////////////////

	public function testGetOrganizersNameAndEmailWithNoOrganizersReturnsEmptyString() {
		$this->assertEquals(
			array(),
			$this->fixture->getOrganizersNameAndEmail()
		);
	}

	public function testGetOrganizersNameAndEmailWithSingleOrganizerReturnsSingleOrganizer() {
		$organizer = array(
			'title' => 'test organizer',
			'email' => 'test@organizer.org'
		);
		$this->addOrganizerRelation($organizer);
		$this->assertEquals(
			array('"'.$organizer['title'].'" <'.$organizer['email'].'>'),
			$this->fixture->getOrganizersNameAndEmail()
		);
	}

	public function testGetOrganizersNameAndEmailWithMultipleOrganizersReturnsTwoOrganizers() {
		$firstOrganizer = array(
			'title' => 'test organizer 1',
			'email' => 'test1@organizer.org'
		);
		$secondOrganizer = array(
			'title' => 'test organizer 2',
			'email' => 'test2@organizer.org'
		);
		$this->addOrganizerRelation($firstOrganizer);
		$this->addOrganizerRelation($secondOrganizer);
		$this->assertEquals(
			array(
				'"'.$firstOrganizer['title'].'" <'.$firstOrganizer['email'].'>',
				'"'.$secondOrganizer['title'].'" <'.$secondOrganizer['email'].'>'
			),
			$this->fixture->getOrganizersNameAndEmail()
		);
	}

	public function testGetOrganizersEmailWithNoOrganizersReturnsEmptyString() {
		$this->assertEquals(
			array(),
			$this->fixture->getOrganizersEmail()
		);
	}

	public function testGetOrganizersEmailWithSingleOrganizerReturnsSingleOrganizer() {
		$organizer = array('email' => 'test@organizer.org');
		$this->addOrganizerRelation($organizer);
		$this->assertEquals(
			array($organizer['email']),
			$this->fixture->getOrganizersEmail()
		);
	}

	public function testGetOrganizersEmailWithMultipleOrganizersReturnsTwoOrganizers() {
		$firstOrganizer = array('email' => 'test1@organizer.org');
		$secondOrganizer = array('email' => 'test2@organizer.org');
		$this->addOrganizerRelation($firstOrganizer);
		$this->addOrganizerRelation($secondOrganizer);
		$this->assertEquals(
			array($firstOrganizer['email'], $secondOrganizer['email']),
			$this->fixture->getOrganizersEmail()
		);
	}


	/////////////////////////////////////////
	// Tests concerning getOrganizersFooter
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getOrganizersFootersWithNoOrganizersReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			$this->fixture->getOrganizersFooter()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersFootersWithSingleOrganizerReturnsSingleOrganizerFooter() {
		$organizer = array('email_footer' => 'test email footer');
		$this->addOrganizerRelation($organizer);
		$this->assertEquals(
			array($organizer['email_footer']),
			$this->fixture->getOrganizersFooter()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersFootersWithMultipleOrganizersReturnsTwoOrganizerFooters() {
		$firstOrganizer = array('email_footer' => 'test email footer');
		$secondOrganizer = array('email_footer' => 'test email footer');
		$this->addOrganizerRelation($firstOrganizer);
		$this->addOrganizerRelation($secondOrganizer);
		$this->assertEquals(
			array(
				$firstOrganizer['email_footer'],
				$secondOrganizer['email_footer']
			),
			$this->fixture->getOrganizersFooter()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersFootersWithSingleOrganizerWithoutEMailFooterReturnsEmptyArray() {
		$this->addOrganizerRelation();

		$this->assertEquals(
			array(),
			$this->fixture->getOrganizersFooter()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersFootersWithTwoOrganizersOneWithFooterOneWithoutrReturnsOnlyTheNonEmptyFooter() {
		$secondOrganizer = array('email_footer' => 'test email footer');
		$this->addOrganizerRelation();
		$this->addOrganizerRelation($secondOrganizer);
		$this->assertEquals(
			array($secondOrganizer['email_footer']),
			$this->fixture->getOrganizersFooter()
		);
	}


	///////////////////////////////////////
	// Tests concerning getAttendancesPid
	///////////////////////////////////////

	public function testGetAttendancesPidWithNoOrganizerReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getAttendancesPid()
		);
	}

	public function testGetAttendancesPidWithSingleOrganizerReturnsPid() {
		$this->addOrganizerRelation(array('attendances_pid' => 99));
		$this->assertEquals(
			99,
			$this->fixture->getAttendancesPid()
		);
	}

	public function testGetAttendancesPidWithMultipleOrganizerReturnsFirstPid() {
		$this->addOrganizerRelation(array('attendances_pid' => 99));
		$this->addOrganizerRelation(array('attendances_pid' => 66));
		$this->assertEquals(
			99,
			$this->fixture->getAttendancesPid()
		);
	}


	///////////////////////////////////////
	// Tests regarding getOrganizerBag().
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getOrganizerBagWithoutOrganizersThrowsException() {
		$this->setExpectedException(
			'Exception', 'There are no organizers related to this event.'
		);

		$this->fixture->getOrganizerBag();
	}

	/**
	 * @test
	 */
	public function getOrganizerBagWithOrganizerReturnsOrganizerBag() {
		$this->addOrganizerRelation();

		$this->assertTrue(
			$this->fixture->getOrganizerBag() instanceof tx_seminars_Bag_Organizer
		);
	}


	/////////////////////////////////
	// Tests regarding the speakers
	/////////////////////////////////

	public function testGetNumberOfSpeakersWithNoSpeakerReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfSpeakers()
		);
	}

	public function testGetNumberOfSpeakersWithSingleSpeakerReturnsOne() {
		$this->addSpeakerRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfSpeakers()
		);
	}

	public function testGetNumberOfSpeakersWithMultipleSpeakersReturnsTwo() {
		$this->addSpeakerRelation(array());
		$this->addSpeakerRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfSpeakers()
		);
	}

	public function testGetNumberOfPartnersWithNoPartnerReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfPartners()
		);
	}

	public function testGetNumberOfPartnersWithSinglePartnerReturnsOne() {
		$this->addPartnerRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfPartners()
		);
	}

	public function testGetNumberOfPartnersWithMultiplePartnersReturnsTwo() {
		$this->addPartnerRelation(array());
		$this->addPartnerRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfPartners()
		);
	}

	public function testGetNumberOfTutorsWithNoTutorReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfTutors()
		);
	}

	public function testGetNumberOfTutorsWithSingleTutorReturnsOne() {
		$this->addTutorRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfTutors()
		);
	}

	public function testGetNumberOfTutorsWithMultipleTutorsReturnsTwo() {
		$this->addTutorRelation(array());
		$this->addTutorRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfTutors()
		);
	}

	public function testGetNumberOfLeadersWithNoLeaderReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfLeaders()
		);
	}

	public function testGetNumberOfLeadersWithSingleLeaderReturnsOne() {
		$this->addLeaderRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfLeaders()
		);
	}

	public function testGetNumberOfLeadersWithMultipleLeadersReturnsTwo() {
		$this->addLeaderRelation(array());
		$this->addLeaderRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfLeaders()
		);
	}

	public function testHasSpeakersOfTypeIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasSpeakersOfType('speakers')
		);
		$this->assertFalse(
			$this->fixture->hasSpeakersOfType('partners')
		);
		$this->assertFalse(
			$this->fixture->hasSpeakersOfType('tutors')
		);
		$this->assertFalse(
			$this->fixture->hasSpeakersOfType('leaders')
		);
	}

	public function testHasSpeakersOfTypeWithSingleSpeakerOfTypeReturnsTrue() {
		$this->addSpeakerRelation(array());
		$this->assertTrue(
			$this->fixture->hasSpeakersOfType('speakers')
		);

		$this->addPartnerRelation(array());
		$this->assertTrue(
			$this->fixture->hasSpeakersOfType('partners')
		);

		$this->addTutorRelation(array());
		$this->assertTrue(
			$this->fixture->hasSpeakersOfType('tutors')
		);

		$this->addLeaderRelation(array());
		$this->assertTrue(
			$this->fixture->hasSpeakersOfType('leaders')
		);
	}

	public function testHasSpeakersIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasSpeakers()
		);
	}

	public function testCanHaveOneSpeaker() {
		$this->addSpeakerRelation(array());
		$this->assertTrue(
			$this->fixture->hasSpeakers()
		);
	}

	public function testHasPartnersIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasPartners()
		);
	}

	public function testCanHaveOnePartner() {
		$this->addPartnerRelation(array());
		$this->assertTrue(
			$this->fixture->hasPartners()
		);
	}

	public function testHasTutorsIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasTutors()
		);
	}

	public function testCanHaveOneTutor() {
		$this->addTutorRelation(array());
		$this->assertTrue(
			$this->fixture->hasTutors()
		);
	}

	public function testHasLeadersIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasLeaders()
		);
	}

	public function testCanHaveOneLeader() {
		$this->addLeaderRelation(array());
		$this->assertTrue(
			$this->fixture->hasLeaders()
		);
	}


	///////////////////////////////////////////////////
	// Tests concerning getSpeakersWithDescriptionRaw
	///////////////////////////////////////////////////

	public function testGetSpeakersWithDescriptionRawWithNoSpeakersReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawReturnsTitleOfSpeaker() {
		$this->addSpeakerRelation(array('title' => 'test speaker'));

		$this->assertContains(
			'test speaker',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawForSpeakerWithOrganizationReturnsSpeakerWithOrganization() {
		$this->addSpeakerRelation(array('organization' => 'test organization'));

		$this->assertContains(
			'test organization',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawForSpeakerWithHomepageReturnsSpeakerWithHomepage() {
		$this->addSpeakerRelation(array('homepage' => 'test homepage'));

		$this->assertContains(
			'test homepage',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawForSpeakerWithOrganizationAndHomepageReturnsSpeakerWithOrganizationAndHomepage() {
		$this->addSpeakerRelation(
			array(
				'organization' => 'test organization',
				'homepage' => 'test homepage',
			)
		);

		$this->assertRegExp(
			'/test organization.*test homepage/',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawForSpeakerWithDescriptionReturnsSpeakerWithDescription() {
		$this->addSpeakerRelation(array('description' => 'test description'));

		$this->assertContains(
			'test description',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawForSpeakerWithOrganizationAndDescriptionReturnsOrganizationAndDescription() {
		$this->addSpeakerRelation(
			array(
				'organization' => 'foo',
				'description' => 'bar',
			)
		);
		$this->assertRegExp(
			'/foo.*bar/s',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawForSpeakerWithHomepageAndDescriptionReturnsHomepageAndDescription() {
		$this->addSpeakerRelation(
			array(
				'homepage' => 'test homepage',
				'description' =>  'test description',
			)
		);

		$this->assertRegExp(
			'/test homepage.*test description/s',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawForTwoSpeakersReturnsTwoSpeakers() {
		$this->addSpeakerRelation(array('title' => 'test speaker 1'));
		$this->addSpeakerRelation(array('title' => 'test speaker 2'));

		$this->assertContains(
			'test speaker 1',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
		$this->assertContains(
			'test speaker 2',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawForTwoSpeakersWithOrganizationReturnsTwoSpeakersWithOrganization() {
		$this->addSpeakerRelation(
			array('organization' => 'test organization 1')
		);
		$this->addSpeakerRelation(
			array('organization' => 'test organization 2')
		);

		$this->assertContains(
			'test organization 1',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
		$this->assertContains(
			'test organization 2',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawOnlyReturnsSpeakersOfGivenType() {
		$this->addSpeakerRelation(array('title' => 'test speaker'));
		$this->addPartnerRelation(array('title' => 'test partner'));

		$this->assertNotContains(
			'test partner',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawCanReturnSpeakersOfTypePartner() {
		$this->addPartnerRelation(array('title' => 'test partner'));

		$this->assertContains(
			'test partner',
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);
	}

	public function testGetSpeakersWithDescriptionRawCanReturnSpeakersOfTypeLeaders() {
		$this->addLeaderRelation(array('title' => 'test leader'));

		$this->assertContains(
			'test leader',
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawCanReturnSpeakersOfTypeTutors() {
		$this->addTutorRelation(array('title' => 'test tutor'));

		$this->assertContains(
			'test tutor',
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);
	}

	public function testGetSpeakersWithDescriptionRawSeparatesMultipleSpeakersWithLineFeeds() {
		$this->addSpeakerRelation(array('title' => 'foo'));
		$this->addSpeakerRelation(array('title' => 'bar'));

		$this->assertContains(
			'foo' . LF . 'bar',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawDoesNotSeparateMultipleSpeakersWithCarriageReturnsAndLineFeeds() {
		$this->addSpeakerRelation(array('title' => 'foo'));
		$this->addSpeakerRelation(array('title' => 'bar'));

		$this->assertNotContains(
			'foo' . CRLF . 'bar',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawDoesNotSeparateSpeakersDescriptionAndTitleWithCarriageReturnsAndLineFeeds() {
		$this->addSpeakerRelation(
			array(
				'title' => 'foo',
				'description' => 'bar'
			)
		);

		$this->assertNotRegExp(
			'/foo'. CRLF . 'bar/',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	public function testGetSpeakersWithDescriptionRawSeparatesSpeakersDescriptionAndTitleWithLineFeeds() {
		$this->addSpeakerRelation(
			array(
				'title' => 'foo',
				'description' => 'bar'
			)
		);

		$this->assertRegExp(
			'/foo'. LF . 'bar/',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}


	//////////////////////////////////////
	// Tests concerning getSpeakersShort
	//////////////////////////////////////

	public function testGetSpeakersShortWithNoSpeakersReturnsAnEmptyString() {
		$this->createPi1();

		$this->assertEquals(
			'',
			$this->fixture->getSpeakersShort($this->pi1, 'speakers')
		);
		$this->assertEquals(
			'',
			$this->fixture->getSpeakersShort($this->pi1, 'partners')
		);
		$this->assertEquals(
			'',
			$this->fixture->getSpeakersShort($this->pi1, 'tutors')
		);
		$this->assertEquals(
			'',
			$this->fixture->getSpeakersShort($this->pi1, 'leaders')
		);
	}

	public function testGetSpeakersShortWithSingleSpeakersReturnsSingleSpeaker() {
		$this->createPi1();
		$speaker = array('title' => 'test speaker');

		$this->addSpeakerRelation($speaker);
		$this->assertEquals(
			$speaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'speakers')
		);

		$this->addPartnerRelation($speaker);
		$this->assertEquals(
			$speaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'partners')
		);

		$this->addTutorRelation($speaker);
		$this->assertEquals(
			$speaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'tutors')
		);

		$this->addLeaderRelation($speaker);
		$this->assertEquals(
			$speaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'leaders')
		);
	}

	public function testGetSpeakersShortWithMultipleSpeakersReturnsTwoSpeakers() {
		$firstSpeaker = array('title' => 'test speaker 1');
		$secondSpeaker = array('title' => 'test speaker 2');

		$this->addSpeakerRelation($firstSpeaker);
		$this->addSpeakerRelation($secondSpeaker);
		$this->createPi1();
		$this->assertEquals(
			$firstSpeaker['title'].', '.$secondSpeaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'speakers')
		);

		$this->addPartnerRelation($firstSpeaker);
		$this->addPartnerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$secondSpeaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'partners')
		);

		$this->addTutorRelation($firstSpeaker);
		$this->addTutorRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$secondSpeaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'tutors')
		);

		$this->addLeaderRelation($firstSpeaker);
		$this->addLeaderRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$secondSpeaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'leaders')
		);
	}

	public function testGetSpeakersShortReturnsSpeakerLinkedToSpeakerHomepage() {
		$speakerWithLink = array(
			'title' => 'test speaker',
			'homepage' => 'http://www.foo.com',
		);
		$this->addSpeakerRelation($speakerWithLink);
		$this->createPi1();

		$this->assertRegExp(
			'/href="http:\/\/www.foo.com".*>test speaker/',
			$this->fixture->getSpeakersShort($this->pi1, 'speakers')
		);
	}

	public function testGetSpeakersForSpeakerWithoutHomepageReturnsSpeakerNameWithoutLinkTag() {
		$speaker = array(
			'title' => 'test speaker',
		);

		$this->addSpeakerRelation($speaker);
		$this->createPi1();

		$shortSpeakerOutput
			= $this->fixture->getSpeakersShort($this->pi1, 'speakers');

		$this->assertContains(
			'test speaker',
			$shortSpeakerOutput
		);
		$this->assertNotContains(
			'<a',
			$shortSpeakerOutput
		);
	}


	////////////////////////////////////////
	// Test concerning the collision check
	////////////////////////////////////////

	public function testEventsWithTheExactSameDateCollide() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser();

		$begin = $GLOBALS['SIM_EXEC_TIME'];
		$end = $begin + 1000;

		$this->fixture->setBeginDate($begin);
		$this->fixture->setEndDate($end);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => $begin,
				'end_date' => $end
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $frontEndUserUid
			)
		);

		$this->assertTrue(
			$this->fixture->isUserBlocked($frontEndUserUid)
		);
	}

	public function testCollidingEventsDoNotCollideIfCollisionSkipIsEnabledForAllEvents() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser();

		$begin = $GLOBALS['SIM_EXEC_TIME'];
		$end = $begin + 1000;

		$this->fixture->setBeginDate($begin);
		$this->fixture->setEndDate($end);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => $begin,
				'end_date' => $end,
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $frontEndUserUid,
			)
		);

		$this->fixture->setConfigurationValue(
			'skipRegistrationCollisionCheck', TRUE
		);

		$this->assertFalse(
			$this->fixture->isUserBlocked($frontEndUserUid)
		);
	}

	public function testCollidingEventsDoNoCollideIfCollisionSkipIsEnabledForThisEvent() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser();

		$begin = $GLOBALS['SIM_EXEC_TIME'];
		$end = $begin + 1000;

		$this->fixture->setBeginDate($begin);
		$this->fixture->setEndDate($end);
		$this->fixture->setSkipCollisionCheck(TRUE);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => $begin,
				'end_date' => $end
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $frontEndUserUid
			)
		);

		$this->assertFalse(
			$this->fixture->isUserBlocked($frontEndUserUid)
		);
	}

	public function testCollidingEventsDoNoCollideIfCollisionSkipIsEnabledForAnotherEvent() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser();

		$begin = $GLOBALS['SIM_EXEC_TIME'];
		$end = $begin + 1000;

		$this->fixture->setBeginDate($begin);
		$this->fixture->setEndDate($end);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => $begin,
				'end_date' => $end,
				'skip_collision_check' => 1
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $frontEndUserUid
			)
		);

		$this->assertFalse(
			$this->fixture->isUserBlocked($frontEndUserUid)
		);
	}


	////////////////////////
	// Tests for the icons
	////////////////////////

	public function testUsesCorrectIconForSingleEvent() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_COMPLETE);

		$this->assertContains(
			'EventComplete.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForTopic() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_TOPIC);

		$this->assertContains(
			'EventTopic.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForDateRecord() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_DATE);

		$this->assertContains(
			'EventDate.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForHiddenSingleEvent() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_COMPLETE);
		$this->fixture->setHidden(TRUE);

		$this->assertContains(
			'EventComplete__h.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForHiddenTopic() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_TOPIC);
		$this->fixture->setHidden(TRUE);

		$this->assertContains(
			'EventTopic__h.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForHiddenDate() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_DATE);
		$this->fixture->setHidden(TRUE);

		$this->assertContains(
			'EventDate__h.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForVisibleTimedSingleEvent() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_COMPLETE);
		$this->fixture->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

		$this->assertContains(
			'EventComplete.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForVisibleTimedTopic() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_TOPIC);
		$this->fixture->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

		$this->assertContains(
			'EventTopic.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForVisibleTimedDate() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_DATE);
		$this->fixture->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

		$this->assertContains(
			'EventDate.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForExpiredSingleEvent() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_COMPLETE);
		$this->fixture->setRecordEndTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

		$this->assertContains(
			'EventComplete__t.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForExpiredTimedTopic() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_TOPIC);
		$this->fixture->setRecordEndTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

		$this->assertContains(
			'EventTopic__t.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForExpiredTimedDate() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_DATE);
		$this->fixture->setRecordEndTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

		$this->assertContains(
			'EventDate__t.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForStillInvisibleTimedSingleEvent() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_COMPLETE);
		$this->fixture->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] + 1000);

		$this->assertRegExp(
			'/EventComplete__tf?\./',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForStillInvisibleTimedTopic() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_TOPIC);
		$this->fixture->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] + 1000);

		$this->assertRegExp(
			'/EventTopic__tf?\./',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForStillInvisibleTimedDate() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_DATE);
		$this->fixture->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] + 1000);

		$this->assertRegExp(
			'/EventDate__tf?\./',
			$this->fixture->getRecordIcon()
		);
	}


	/////////////////////////////////////
	// Tests for hasSeparateDetailsPage
	/////////////////////////////////////

	public function testHasSeparateDetailsPageIsFalseByDefault() {
		$this->assertFalse(
			$this->fixture->hasSeparateDetailsPage()
		);
	}

	public function testHasSeparateDetailsPageReturnsTrueForInternalSeparateDetailsPage() {
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'details_page' => $detailsPageUid
			)
		);
		$event = new tx_seminars_seminarchild($eventUid);

		$this->assertTrue(
			$event->hasSeparateDetailsPage()
		);

		$event->__destruct();
	}

	public function testHasSeparateDetailsPageReturnsTrueForExternalSeparateDetailsPage() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'details_page' => 'www.test.com'
			)
		);
		$event = new tx_seminars_seminarchild($eventUid);

		$this->assertTrue(
			$event->hasSeparateDetailsPage()
		);

		$event->__destruct();
	}


	/////////////////////////////
	// Tests for getDetailsPage
	/////////////////////////////

	/**
	 * @test
	 */
	public function getDetailsPageForNoSeparateDetailsPageSetReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function getDetailsPageForInternalSeparateDetailsPageSetReturnsThisPage() {
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'details_page' => $detailsPageUid,
			)
		);
		$event = new tx_seminars_seminarchild($eventUid);

		$this->assertEquals(
			$detailsPageUid,
			$event->getDetailsPage()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function getDetailsPageForExternalSeparateDetailsPageSetReturnsThisPage() {
		$externalUrl = 'www.test.com';
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'details_page' => $externalUrl,
			)
		);
		$event = new tx_seminars_seminarchild($eventUid);

		$this->assertEquals(
			$externalUrl,
			$event->getDetailsPage()
		);

		$event->__destruct();
	}


	/////////////////////////////////////////
	// Tests concerning getPlaceWithDetails
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPlaceWithDetailsReturnsWillBeAnnouncedForNoPlace() {
		$this->createPi1();
		$this->assertContains(
			$this->fixture->translate('message_willBeAnnounced'),
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsTitleOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('title' => 'a place'));

		$this->assertContains(
			'a place',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsTitleOfAllRelatedPlaces() {
		$this->createPi1();
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		$this->assertContains(
			'a place',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
		$this->assertContains(
			'another place',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsAddressOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(
			array('title' => 'a place', 'address' => 'a street')
		);

		$this->assertContains(
			'a street',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsForNonEmptyZipAndCityContainsZip() {
		$this->createPi1();
		$this->addPlaceRelation(
			array('title' => 'a place', 'zip' => '12345', 'city' => 'Hamm')
		);

		$this->assertContains(
			'12345',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsForNonEmptyZipAndEmptyCityNotContainsZip() {
		$this->createPi1();
		$this->addPlaceRelation(
			array('title' => 'a place', 'zip' => '12345', 'city' => '')
		);

		$this->assertNotContains(
			'12345',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsCityOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('title' => 'a place', 'city' => 'Emden'));

		$this->assertContains(
			'Emden',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsCountryOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('title' => 'a place', 'country' => 'de'));

		$this->assertContains(
			'Deutschland',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsHomepageLinkOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('homepage' => 'www.test.com'));

		$this->assertContains(
			' href="http://www.test.com',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsDirectionsOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('directions' => 'Turn right.'));

		$this->assertContains(
			'Turn right.',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}


	////////////////////////////////////////////
	// Tests concerning getPlaceWithDetailsRaw
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawReturnsWillBeAnnouncedForNoPlace() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertContains(
			$this->fixture->translate('message_willBeAnnounced'),
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsTitleOfOnePlace() {
		$this->addPlaceRelation(array('title' => 'a place'));

		$this->assertContains(
			'a place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsTitleOfAllRelatedPlaces() {
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		$this->assertContains(
			'a place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
		$this->assertContains(
			'another place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsAddressOfOnePlace() {
		$this->addPlaceRelation(
			array('title' => 'a place', 'address' => 'a street')
		);

		$this->assertContains(
			'a street',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsCityOfOnePlace() {
		$this->addPlaceRelation(array('title' => 'a place', 'city' => 'Emden'));

		$this->assertContains(
			'Emden',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsCountryOfOnePlace() {
		$this->addPlaceRelation(array('title' => 'a place', 'country' => 'de'));

		$this->assertContains(
			'Deutschland',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsHomepageUrlOfOnePlace() {
		$this->addPlaceRelation(array('homepage' => 'www.test.com'));

		$this->assertContains(
			'www.test.com',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsDirectionsOfOnePlace() {
		$this->addPlaceRelation(array('directions' => 'Turn right.'));

		$this->assertContains(
			'Turn right.',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawSeparatesMultiplePlacesWithLineFeeds() {
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		$this->assertContains(
			'another place' . LF . 'a place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawDoesNotSeparateMultiplePlacesWithCarriageReturnsAndLineFeeds() {
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		$this->assertNotContains(
			'another place' . CRLF . 'a place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}


	////////////////////////////
	// Tests for getPlaceShort
	////////////////////////////

	public function testGetPlaceShortReturnsWillBeAnnouncedForNoPlaces() {
		$this->assertEquals(
			$this->fixture->translate('message_willBeAnnounced'),
			$this->fixture->getPlaceShort()
		);
	}

	public function testGetPlaceShortReturnsPlaceNameForOnePlace() {
		$this->addPlaceRelation(array('title' => 'a place'));

		$this->assertEquals(
			'a place',
			$this->fixture->getPlaceShort()
		);
	}

	public function testGetPlaceShortReturnsPlaceNamesWithCommaForTwoPlaces() {
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		$this->assertContains(
			'a place',
			$this->fixture->getPlaceShort()
		);
		$this->assertContains(
			', ',
			$this->fixture->getPlaceShort()
		);
		$this->assertContains(
			'another place',
			$this->fixture->getPlaceShort()
		);
	}


	///////////////////////////////
	// Tests concerning getPlaces
	///////////////////////////////

	public function test_getPlacesForEventWithNoPlaces_ReturnsEmptyList() {
		$this->assertTrue(
			$this->fixture->getPlaces() instanceof tx_oelib_List
		);
	}

	public function test_getPlaces_ForSeminarWithOnePlaces_ReturnsListWithPlaceModel() {
		$this->addPlaceRelation();

		$this->assertTrue(
			$this->fixture->getPlaces()->first() instanceof tx_seminars_Model_place
		);
	}

	public function test_getPlaces_ForSeminarWithOnePlaces_ReturnsListWithOnePlace() {
		$this->addPlaceRelation();

		$this->assertEquals(
			1,
			$this->fixture->getPlaces()->count()
		);
	}


	/////////////////////////////
	// Tests for attached files
	/////////////////////////////

	public function testHasAttachedFilesInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasAttachedFiles()
		);
	}

	public function testHasAttachedFilesWithOneAttachedFileReturnsTrue() {
		$this->fixture->setAttachedFiles('test.file');

		$this->assertTrue(
			$this->fixture->hasAttachedFiles()
		);
	}

	public function testHasAttachedFilesWithTwoAttachedFilesReturnsTrue() {
		$this->fixture->setAttachedFiles('test.file,test_02.file');

		$this->assertTrue(
			$this->fixture->hasAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function hasAttachedFilesForDateWithoutFilesAndTopicWithOneFileReturnsTrue() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'attached_files' => 'test.file',
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'attached_files' => '',
				'topic' => $topicRecordUid,
			)
		);
		$eventDate = new tx_seminars_seminar($dateRecordUid);

		$this->assertTrue(
			$eventDate->hasAttachedFiles()
		);

		$eventDate->__destruct();
	}

	/**
	 * @test
	 */
	public function hasAttachedFilesForDateWithoutFilesAndTopicWithoutFilesReturnsFalse() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'attached_files' => '',
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'attached_files' => '',
				'topic' => $topicRecordUid,
			)
		);
		$eventDate = new tx_seminars_seminar($dateRecordUid);

		$this->assertFalse(
			$eventDate->hasAttachedFiles()
		);

		$eventDate->__destruct();
	}

	public function testGetAttachedFilesInitiallyReturnsAnEmptyArray() {
		$this->createPi1();

		$this->assertEquals(
			array(),
			$this->fixture->getAttachedFiles($this->pi1)
		);
	}

	public function testGetAttachedFilesWithOneSetAttachedFileReturnsAttachedFileAsArrayWithCorrectFileSize() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertContains(
			'uploads/tx_seminars/' . $dummyFileName,
			$attachedFiles[0]['name']
		);

		$this->assertEquals(
			t3lib_div::formatSize(filesize($dummyFile)),
			$attachedFiles[0]['size']
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesForDateWithFileAndTopicWithoutFileReturnsFileFromDate() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'attached_files' => '',
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'attached_files' => $dummyFileName,
				'topic' => $topicRecordUid,
			)
		);
		$eventDate = new tx_seminars_seminar($dateRecordUid);

		$attachedFiles = $eventDate->getAttachedFiles($this->pi1);

		$this->assertContains(
			$dummyFileName,
			$attachedFiles[0]['name']
		);

		$eventDate->__destruct();
	}

	/**
	 * @test
	 */
	public function getAttachedFilesForDateWithoutFileAndTopicWithFileReturnsFileFromTopic() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'attached_files' => $dummyFileName,
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'attached_files' => '',
				'topic' => $topicRecordUid,
			)
		);
		$eventDate = new tx_seminars_seminar($dateRecordUid);

		$attachedFiles = $eventDate->getAttachedFiles($this->pi1);

		$this->assertContains(
			$dummyFileName,
			$attachedFiles[0]['name']
		);

		$eventDate->__destruct();
	}

	/**
	 * @test
	 */
	public function getAttachedFilesForDateWithFileAndTopicWithFileReturnsFilesFromTopicAndThenDate() {
		$this->createPi1();

		$topicDummyFile = $this->testingFramework->createDummyFile();
		$topicDummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($topicDummyFile);
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'attached_files' => $topicDummyFileName,
			)
		);

		$dateDummyFile = $this->testingFramework->createDummyFile();
		$dateDummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dateDummyFile);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'attached_files' => $dateDummyFileName,
				'topic' => $topicRecordUid,
			)
		);
		$eventDate = new tx_seminars_seminar($dateRecordUid);

		$attachedFiles = $eventDate->getAttachedFiles($this->pi1);

		$this->assertContains(
			$topicDummyFileName,
			$attachedFiles[0]['name']
		);
		$this->assertContains(
			$dateDummyFileName,
			$attachedFiles[1]['name']
		);

		$eventDate->__destruct();
	}

	public function testGetAttachedFilesWithTwoSetAttachedFilesReturnsAttachedFilesAsArrayWithCorrectFileSize() {
		$this->createPi1();
		$dummyFile1 = $this->testingFramework->createDummyFile();
		$dummyFileName1 =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile1);
		$dummyFile2 = $this->testingFramework->createDummyFile();
		$dummyFileName2 =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile2);
		$this->fixture->setAttachedFiles($dummyFileName1 . ',' . $dummyFileName2);

		t3lib_div::writeFile($dummyFile2, 'Test');

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertContains(
			'uploads/tx_seminars/' . $dummyFileName1,
			$attachedFiles[0]['name']
		);

		$this->assertEquals(
			t3lib_div::formatSize(filesize($dummyFile1)),
			$attachedFiles[0]['size']
		);

		$this->assertContains(
			'uploads/tx_seminars/' . $dummyFileName2,
			$attachedFiles[1]['name']
		);

		$this->assertEquals(
			t3lib_div::formatSize(filesize($dummyFile2)),
			$attachedFiles[1]['size']
		);
	}

	public function testGetAttachedFilesWithAttachedFileWithFileEndingReturnsFileType() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertEquals(
			'txt',
			$attachedFiles[0]['type']
		);
	}

	public function testGetAttachedFilesWithAttachedFileWithoutFileEndingReturnsFileTypeNone() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile('test');
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertEquals(
			'none',
			$attachedFiles[0]['type']
		);
	}

	public function testGetAttachedFilesWithAttachedFileWithDotInFileNameReturnsCorrectFileType() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile('test.test.txt');
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertEquals(
			'txt',
			$attachedFiles[0]['type']
		);
	}

	public function testGetAttachedFilesWithAttachedFileWithFileNameStartingWithADotReturnsFileType() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile('.txt');
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertEquals(
			'txt',
			$attachedFiles[0]['type']
		);
	}

	public function testGetAttachedFilesWithAttachedFileWithFileNameEndingWithADotReturnsFileTypeNone() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile('test.');
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertEquals(
			'none',
			$attachedFiles[0]['type']
		);
	}


	///////////////////////////////////
	// Tests concerning isOwnerFeUser
	///////////////////////////////////

	public function testIsOwnerFeUserForNoOwnerReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isOwnerFeUser()
		);
	}

	public function testIsOwnerFeUserForLoggedInUserOtherThanOwnerReturnsFalse() {
		$this->testingFramework->createFakeFrontEnd();
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setOwnerUid($userUid + 1);

		$this->assertFalse(
			$this->fixture->isOwnerFeUser()
		);
	}

	public function testIsOwnerFeUserForLoggedInUserOtherThanOwnerReturnsTrue() {
		$this->testingFramework->createFakeFrontEnd();
		$ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setOwnerUid($ownerUid);

		$this->assertTrue(
			$this->fixture->isOwnerFeUser()
		);
	}


	//////////////////////////////
	// Tests concerning getOwner
	//////////////////////////////

	public function testGetOwnerForExistingOwnerReturnsFrontEndUserInstance() {
		$this->testingFramework->createFakeFrontEnd();
		$ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setOwnerUid($ownerUid);

		$this->assertTrue(
			$this->fixture->getOwner() instanceof tx_oelib_Model_FrontEndUser
		);
	}

	public function testGetOwnerForExistingOwnerReturnsUserWithOwnersUid() {
		$this->testingFramework->createFakeFrontEnd();
		$ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setOwnerUid($ownerUid);

		$this->assertEquals(
			$ownerUid,
			$this->fixture->getOwner()->getUid()
		);
	}

	public function testGetOwnerForNoOwnerReturnsNull() {
		$this->assertNull(
			$this->fixture->getOwner()
		);
	}


	//////////////////////////////
	// Tests concerning hasOwner
	//////////////////////////////

	public function testHasOwnerForExistingOwnerReturnsTrue() {
		$this->testingFramework->createFakeFrontEnd();
		$ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setOwnerUid($ownerUid);

		$this->assertTrue(
			$this->fixture->hasOwner()
		);
	}

	public function testHasOwnerForNoOwnerReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasOwner()
		);
	}


	////////////////////////////////////////
	// Tests concerning getVacanciesString
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getVacanciesStringForCanceledEventWithVacanciesReturnsEmptyString() {
		$this->fixture->setConfigurationValue('showVacanciesThreshold', 10);
		$this->fixture->setAttendancesMax(5);
		$this->fixture->setNumberOfAttendances(0);
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		$this->assertEquals(
			'',
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringWithoutRegistrationNeededReturnsEmptyString() {
		$this->fixture->setConfigurationValue('showVacanciesThreshold', 10);
		$this->fixture->setNeedsRegistration(FALSE);

		$this->assertEquals(
			'',
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringForNonZeroVacanciesBelowThresholdReturnsNumberOfVacancies() {
		$this->fixture->setConfigurationValue('showVacanciesThreshold', 10);
		$this->fixture->setAttendancesMax(5);
		$this->fixture->setNumberOfAttendances(0);

		$this->assertEquals(
			'5',
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringForNoVancanciesReturnsFullyBooked() {
		$this->fixture->setConfigurationValue('showVacanciesThreshold', 10);
		$this->fixture->setAttendancesMax(5);
		$this->fixture->setNumberOfAttendances(5);

		$this->assertEquals(
			$this->fixture->translate('message_fullyBooked'),
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringForVacanciesGreaterThanThresholdReturnsEnough() {
		$this->fixture->setConfigurationValue('showVacanciesThreshold', 10);
		$this->fixture->setAttendancesMax(42);
		$this->fixture->setNumberOfAttendances(0);

		$this->assertEquals(
			$this->fixture->translate('message_enough'),
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringForVacanciesEqualToThresholdReturnsEnough() {
		$this->fixture->setConfigurationValue('showVacanciesThreshold', 42);
		$this->fixture->setAttendancesMax(42);
		$this->fixture->setNumberOfAttendances(0);

		$this->assertEquals(
			$this->fixture->translate('message_enough'),
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringForUnlimitedVacanciesAndZeroRegistrationsReturnsEnough() {
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setNumberOfAttendances(0);

		$this->assertEquals(
			$this->fixture->translate('message_enough'),
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringForUnlimitedVacanciesAndOneRegistrationReturnsEnough() {
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setNumberOfAttendances(1);

		$this->assertEquals(
			$this->fixture->translate('message_enough'),
			$this->fixture->getVacanciesString()
		);
	}


	///////////////////////////////////////////////////////
	// Tests concerning updatePlaceRelationsFromTimeSlots
	///////////////////////////////////////////////////////

	public function testUpdatePlaceRelationsForSeminarWithoutPlacesRelatesPlaceFromTimeslotToSeminar() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'my house')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'place' => $placeUid,
				'seminar' => $this->fixture->getUid(),
			)
		);
		$this->fixture->setNumberOfTimeSlots(1);

		$this->assertEquals(
			1,
			$this->fixture->updatePlaceRelationsFromTimeSlots()
		);
	}

	public function testUpdatePlaceRelationsForTwoTimeslotsWithPlacesReturnsTwo() {
		$placeUid1 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'my house')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'place' => $placeUid1,
				'seminar' => $this->fixture->getUid(),
			)
		);
		$placeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'your house')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'place' => $placeUid2,
				'seminar' => $this->fixture->getUid(),
			)
		);
		$this->fixture->setNumberOfTimeSlots(2);

		$this->assertEquals(
			2,
			$this->fixture->updatePlaceRelationsFromTimeSlots()
		);
	}

	public function testUpdatePlaceRelationsForSeminarWithoutPlacesCanRelateTwoPlacesFromTimeslotsToSeminar() {
		$placeUid1 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'my house')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'place' => $placeUid1,
				'seminar' => $this->fixture->getUid(),
			)
		);
		$placeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'your house')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'place' => $placeUid2,
				'seminar' => $this->fixture->getUid(),
			)
		);
		$this->fixture->setNumberOfTimeSlots(2);
		$this->fixture->setNumberOfPlaces(2);
		$this->fixture->updatePlaceRelationsFromTimeSlots();

		$this->assertContains(
			'my house',
			$this->fixture->getPlaceShort()
		);
		$this->assertContains(
			'your house',
			$this->fixture->getPlaceShort()
		);
	}

	public function testUpdatePlaceRelationsOverwritesSeminarPlaceWithNonEmptyPlaceFromTimeslot() {
		$this->addPlaceRelation(array('title' => 'your house'));

		$placeUidInTimeSlot = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'my house')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'place' => $placeUidInTimeSlot,
				'seminar' => $this->fixture->getUid(),
			)
		);
		$this->fixture->setNumberOfTimeSlots(1);

		$this->fixture->updatePlaceRelationsFromTimeSlots();

		$this->assertEquals(
			'my house',
			$this->fixture->getPlaceShort()
		);
	}

	public function testUpdatePlaceRelationsForSeminarWithOnePlaceAndTimeSlotWithNoPlaceReturnsOne() {
		$this->addPlaceRelation(array('title' => 'your house'));

		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array('seminar' => $this->fixture->getUid())
		);
		$this->fixture->setNumberOfTimeSlots(1);

		$this->assertEquals(
			1,
			$this->fixture->updatePlaceRelationsFromTimeSlots()
		);
	}

	public function testUpdatePlaceRelationsForTimeSlotsWithNoPlaceNotOverwritesSeminarPlace() {
		$this->addPlaceRelation(array('title' => 'your house'));

		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array('seminar' => $this->fixture->getUid())
		);
		$this->fixture->setNumberOfTimeSlots(1);

		$this->assertEquals(
			'your house',
			$this->fixture->getPlaceShort()
		);
	}


	////////////////////////////////////
	// Tests for the getImage function
	////////////////////////////////////

	public function testGetImageForNonEmptyImageReturnsImageFileName() {
		$this->fixture->setImage('foo.gif');

		$this->assertEquals(
			'foo.gif',
			$this->fixture->getImage()
		);
	}

	public function testGetImageForEmptyImageReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getImage()
		);
	}


	////////////////////////////////////
	// Tests for the hasImage function
	////////////////////////////////////

	public function testHasImageForNonEmptyImageReturnsTrue() {
		$this->fixture->setImage('foo.gif');

		$this->assertTrue(
			$this->fixture->hasImage()
		);
	}

	public function testHasImageForEmptyImageReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasImage()
		);
	}


	//////////////////////////////////////////
	// Tests for getLanguageKeySuffixForType
	//////////////////////////////////////////

	public function testGetLanguageKeySuffixForTypeReturnsSpeakerType() {
		$this->addLeaderRelation(array());

		$this->assertContains(
			'leaders_',
			$this->fixture->getLanguageKeySuffixForType('leaders')
		);
	}

	public function testGetLanguageKeySuffixForTypeForMaleSpeakerReturnsMaleMarkerPart() {
		$this->addLeaderRelation(
			array('gender' => tx_seminars_speaker::GENDER_MALE)
		);

		$this->assertContains(
			'_male',
			$this->fixture->getLanguageKeySuffixForType('leaders')
		);
	}

	public function testGetLanguageKeySuffixForTypeForFemaleSpeakerReturnsFemaleMarkerPart() {
		$this->addLeaderRelation(
			array('gender' => tx_seminars_speaker::GENDER_FEMALE)
		);

		$this->assertContains(
			'_female',
			$this->fixture->getLanguageKeySuffixForType('leaders')
		);
	}

	public function testGetLanguageKeySuffixForTypeForSingleSpeakerWithoutGenderReturnsUnknownMarkerPart() {
		$this->addLeaderRelation(
			array('gender' => tx_seminars_speaker::GENDER_UNKNOWN)
		);

		$this->assertContains(
			'_unknown',
			$this->fixture->getLanguageKeySuffixForType('leaders')
		);
	}

	public function testGetLanguageKeySuffixForTypeForSingleSpeakerReturnsSingleMarkerPart() {
		$this->addSpeakerRelation(array());

		$this->assertContains(
			'_single_',
			$this->fixture->getLanguageKeySuffixForType('speakers')
		);
	}

	public function testGetLanguageKeySuffixForTypeForMultipleSpeakersWithoutGenderReturnsSpeakerType() {
		$this->addSpeakerRelation(array());
		$this->addSpeakerRelation(array());

		$this->assertContains(
			'speakers',
			$this->fixture->getLanguageKeySuffixForType('speakers')
		);
	}

	public function testGetLanguageKeySuffixForTypeForMultipleMaleSpeakerReturnsMultipleAndMaleMarkerPart() {
		$this->addSpeakerRelation(
			array('gender' => tx_seminars_speaker::GENDER_MALE)
		);
		$this->addSpeakerRelation(
			array('gender' => tx_seminars_speaker::GENDER_MALE)
		);

		$this->assertContains(
			'_multiple_male',
			$this->fixture->getLanguageKeySuffixForType('speakers')
		);
	}

	public function testGetLanguageKeySuffixForTypeForMultipleFemaleSpeakerReturnsMultipleAndFemaleMarkerPart() {
		$this->addSpeakerRelation(
			array('gender' => tx_seminars_speaker::GENDER_FEMALE)
		);
		$this->addSpeakerRelation(
			array('gender' => tx_seminars_speaker::GENDER_FEMALE)
		);

		$this->assertContains(
			'_multiple_female',
			$this->fixture->getLanguageKeySuffixForType('speakers')
		);
	}

	public function testGetLanguageKeySuffixForTypeForMultipleSpeakersWithMixedGendersReturnsSpeakerType() {
		$this->addSpeakerRelation(
			array('gender' => tx_seminars_speaker::GENDER_MALE)
		);
		$this->addSpeakerRelation(
			array('gender' => tx_seminars_speaker::GENDER_FEMALE)
		);

		$this->assertContains(
			'speakers',
			$this->fixture->getLanguageKeySuffixForType('speakers')
		);
	}

	public function testGetLanguageKeySuffixForTypeForOneSpeakerWithoutGenderAndOneWithGenderReturnsSpeakerType() {
		$this->addLeaderRelation(
			array('gender' => tx_seminars_speaker::GENDER_UNKNOWN)
		);
		$this->addLeaderRelation(
			array('gender' => tx_seminars_speaker::GENDER_MALE)
		);

		$this->assertContains(
			'leaders',
			$this->fixture->getLanguageKeySuffixForType('leaders')
		);
	}

	public function testGetLanguageKeySuffixForTypeForSingleMaleTutorReturnsCorrespondingMarkerPart() {
		$this->addTutorRelation(
			array('gender' => tx_seminars_speaker::GENDER_MALE)
		);

		$this->assertEquals(
			'tutors_single_male',
			$this->fixture->getLanguageKeySuffixForType('tutors')
		);
	}


	/////////////////////////////////////
	// Tests concerning hasRequirements
	/////////////////////////////////////

	public function testHasRequirementsForTopicWithoutRequirementsReturnsFalse() {
		$topic = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
					'requirements' => 0,
				)
			)
		);

		$this->assertFalse(
			$topic->hasRequirements()
		);

		$topic->__destruct();
	}

	public function testHasRequirementsForDateOfTopicWithoutRequirementsReturnsFalse() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 0,
			)
		);
		$date = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		$this->assertFalse(
			$date->hasRequirements()
		);

		$date->__destruct();
	}

	public function testHasRequirementsForTopicWithOneRequirementReturnsTrue() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid, 'requirements'
		);
		$topic = new tx_seminars_seminarchild($topicUid);

		$this->assertTrue(
			$topic->hasRequirements()
		);

		$topic->__destruct();
	}

	public function testHasRequirementsForDateOfTopicWithOneRequirementReturnsTrue() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid, 'requirements'
		);
		$date = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		$this->assertTrue(
			$date->hasRequirements()
		);

		$date->__destruct();
	}

	public function testHasRequirementsForTopicWithTwoRequirementsReturnsTrue() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredTopicUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid1, 'requirements'
		);
		$requiredTopicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid2, 'requirements'
		);
		$topic = new tx_seminars_seminarchild($topicUid);

		$this->assertTrue(
			$topic->hasRequirements()
		);

		$topic->__destruct();
	}


	/////////////////////////////////////
	// Tests concerning hasDependencies
	/////////////////////////////////////

	public function testHasDependenciesForTopicWithoutDependenciesReturnsFalse() {
		$topic = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
					'dependencies' => 0,
				)
			)
		);

		$this->assertFalse(
			$topic->hasDependencies()
		);

		$topic->__destruct();
	}

	public function testHasDependenciesForDateOfTopicWithoutDependenciesReturnsFalse() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 0,
			)
		);
		$date = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		$this->assertFalse(
			$date->hasDependencies()
		);

		$date->__destruct();
	}

	public function testHasDependenciesForTopicWithOneDependencyReturnsTrue() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 1,
			)
		);
		$dependentTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid, $topicUid
		);
		$topic = new tx_seminars_seminarchild($topicUid);

		$this->assertTrue(
			$topic->hasDependencies()
		);

		$topic->__destruct();
	}

	public function testHasDependenciesForDateOfTopicWithOneDependencyReturnsTrue() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 1,
			)
		);
		$dependentTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid, $topicUid
		);
		$date = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		$this->assertTrue(
			$date->hasDependencies()
		);

		$date->__destruct();
	}

	public function testHasDependenciesForTopicWithTwoDependenciesReturnsTrue() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 2,
			)
		);
		$dependentTopicUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid1, $topicUid
		);
		$dependentTopicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid2, $topicUid
		);
		$topic = new tx_seminars_seminarchild($topicUid);

		$result = $topic->hasDependencies();
		$topic->__destruct();

		$this->assertTrue(
			$result
		);
	}


	/////////////////////////////////////
	// Tests concerning getRequirements
	/////////////////////////////////////

	public function testGetRequirementsReturnsSeminarBag() {
		$this->assertTrue(
			$this->fixture->getRequirements() instanceof tx_seminars_Bag_Event
		);
	}

	public function testGetRequirementsForNoRequirementsReturnsEmptyBag() {
		$this->assertTrue(
			$this->fixture->getRequirements()->isEmpty()
		);
	}

	public function testGetRequirementsForOneRequirementReturnsBagWithOneTopic() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid, 'requirements'
		);
		$topic = new tx_seminars_seminarchild($topicUid);

		$result = $topic->getRequirements();
		$topic->__destruct();

		$this->assertEquals(
			1,
			$result->count()
		);
		$this->assertEquals(
			$requiredTopicUid,
			$result->current()->getUid()
		);

		$result->__destruct();
	}

	public function testGetRequirementsForDateOfTopicWithOneRequirementReturnsBagWithOneTopic() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid, 'requirements'
		);
		$date = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		$result = $date->getRequirements();
		$date->__destruct();

		$this->assertEquals(
			1,
			$result->count()
		);
		$this->assertEquals(
			$requiredTopicUid,
			$result->current()->getUid()
		);

		$result->__destruct();
	}

	public function testGetRequirementsForTwoRequirementsReturnsBagWithTwoItems() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredTopicUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid1, 'requirements'
		);
		$requiredTopicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid2, 'requirements'
		);
		$topic = new tx_seminars_seminarchild($topicUid);
		$requirements = $topic->getRequirements();
		$topic->__destruct();

		$this->assertEquals(
			2,
			$requirements->count()
		);

		$requirements->__destruct();
	}


	/////////////////////////////////////
	// Tests concerning getDependencies
	/////////////////////////////////////

	public function testGetDependenciesReturnsSeminarBag() {
		$this->assertTrue(
			$this->fixture->getDependencies() instanceof tx_seminars_Bag_Event
		);
	}

	public function testGetDependenciesForNoDependenciesReturnsEmptyBag() {
		$this->assertTrue(
			$this->fixture->getDependencies()->isEmpty()
		);
	}

	public function testGetDependenciesForOneDependencyReturnsBagWithOneTopic() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 1,
			)
		);
		$dependentTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid, $topicUid
		);
		$topic = new tx_seminars_seminarchild($topicUid);

		$result = $topic->getDependencies();
		$topic->__destruct();

		$this->assertEquals(
			1,
			$result->count()
		);
		$this->assertEquals(
			$dependentTopicUid,
			$result->current()->getUid()
		);

		$result->__destruct();
	}

	public function testGetDependenciesForDateOfTopicWithOneDependencyReturnsBagWithOneTopic() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 1,
			)
		);
		$dependentTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid, $topicUid
		);
		$date = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		$result = $date->getDependencies();
		$date->__destruct();

		$this->assertEquals(
			1,
			$result->count()
		);
		$this->assertEquals(
			$dependentTopicUid,
			$result->current()->getUid()
		);

		$result->__destruct();
	}

	public function testGetDependenciesForTwoDependenciesReturnsBagWithTwoItems() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 2,
			)
		);
		$dependentTopicUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid1, $topicUid
		);
		$dependentTopicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid2, $topicUid
		);
		$topic = new tx_seminars_seminarchild($topicUid);
		$dependencies = $topic->getDependencies();
		$topic->__destruct();

		$this->assertEquals(
			2,
			$dependencies->count()
		);

		$dependencies->__destruct();
	}


	/////////////////////////////////
	// Tests concerning isConfirmed
	/////////////////////////////////

	public function testIsConfirmedForStatusPlannedReturnsFalse() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_PLANNED);

		$this->assertFalse(
			$this->fixture->isConfirmed()
		);
	}

	public function testIsConfirmedForStatusConfirmedReturnsTrue() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CONFIRMED);

		$this->assertTrue(
			$this->fixture->isConfirmed()
		);
	}

	public function testIsConfirmedForStatusCanceledReturnsFalse() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		$this->assertFalse(
			$this->fixture->isConfirmed()
		);
	}


	////////////////////////////////
	// Tests concerning isCanceled
	////////////////////////////////

	public function testIsCanceledForPlannedEventReturnsFalse() {
	$this->fixture->setStatus(tx_seminars_seminar::STATUS_PLANNED);

	$this->assertFalse(
			$this->fixture->isCanceled()
		);
	}

	public function testIsCanceledForCanceledEventReturnsTrue() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		$this->assertTrue(
			$this->fixture->isCanceled()
		);
	}

	public function testIsCanceledForConfirmedEventReturnsFalse() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CONFIRMED);

		$this->assertFalse(
			$this->fixture->isCanceled()
		);
	}

	/////////////////////////////////
	// Tests concerning isPlanned
	/////////////////////////////////

	public function testIsPlannedForStatusPlannedReturnsTrue() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_PLANNED);

		$this->assertTrue(
			$this->fixture->isPlanned()
		);
	}

	public function testiIsPlannedForStatusConfirmedReturnsFalse() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CONFIRMED);

		$this->assertFalse(
			$this->fixture->isPlanned()
		);
	}

	public function testIsPlannedForStatusCanceledReturnsFalse() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		$this->assertFalse(
			$this->fixture->isPlanned()
		);
	}


	////////////////////////////////////////////////////////
	// Tests concerning setEventTakesPlaceReminderSentFlag
	////////////////////////////////////////////////////////

	public function testSetEventTakesPlaceReminderSentFlagSetsFlagToTrue() {
		$this->fixture->setEventTakesPlaceReminderSentFlag();

		$this->assertTrue(
			$this->fixture->getRecordPropertyBoolean(
				'event_takes_place_reminder_sent'
			)
		);
	}


	////////////////////////////////////////////////////////////
	// Tests concerning setCancelationDeadlineReminderSentFlag
	////////////////////////////////////////////////////////////

	public function testSetCancelationDeadlineReminderSentFlagToTrue() {
		$this->fixture->setCancelationDeadlineReminderSentFlag();

		$this->assertTrue(
			$this->fixture->getRecordPropertyBoolean(
				'cancelation_deadline_reminder_sent'
			)
		);
	}


	////////////////////////////////////////////
	// Tests concerning getCancelationDeadline
	////////////////////////////////////////////

	public function testGetCancelationDeadlineForEventWithoutSpeakerReturnsBeginDateOfEvent() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);

		$this->assertEquals(
			$this->fixture->getBeginDateAsTimestamp(),
			$this->fixture->getCancelationDeadline()
		);
	}

	public function testGetCancelationDeadlineForEventWithSpeakerWithoutCancelationPeriodReturnsBeginDateOfEvent() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
		$this->addSpeakerRelation(array('cancelation_period' => 0));

		$this->assertEquals(
			$this->fixture->getBeginDateAsTimestamp(),
			$this->fixture->getCancelationDeadline()
		);
	}

	public function testGetCancelationDeadlineForEventWithTwoSpeakersWithoutCancelationPeriodReturnsBeginDateOfEvent() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
		$this->addSpeakerRelation(array('cancelation_period' => 0));
		$this->addSpeakerRelation(array('cancelation_period' => 0));

		$this->assertEquals(
			$this->fixture->getBeginDateAsTimestamp(),
			$this->fixture->getCancelationDeadline()
		);
	}

	public function testGetCancelationDeadlineForEventWithOneSpeakersWithCancelationPeriodReturnsBeginDateMinusCancelationPeriod() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
		$this->addSpeakerRelation(array('cancelation_period' => 1));

		$this->assertEquals(
			$GLOBALS['SIM_EXEC_TIME'] - tx_seminars_timespan::SECONDS_PER_DAY,
			$this->fixture->getCancelationDeadline()
		);
	}

	public function testGetCancelationDeadlineForEventWithTwoSpeakersWithCancelationPeriodsReturnsBeginDateMinusBiggestCancelationPeriod() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
		$this->addSpeakerRelation(array('cancelation_period' => 21));
		$this->addSpeakerRelation(array('cancelation_period' => 42));

		$this->assertEquals(
			$GLOBALS['SIM_EXEC_TIME']
				- (42 * tx_seminars_timespan::SECONDS_PER_DAY),
			$this->fixture->getCancelationDeadline()
		);
	}

	public function testGetCancelationDeadlineForEventWithoutBeginDateThrowsException() {
		$this->fixture->setBeginDate(0);

		$this->setExpectedException(
			'Exception',
			'The event has no begin date. Please call ' .
				'this function only if the event has a begin date.'
		);

		$this->fixture->getCancelationDeadline();
	}


	////////////////////////////////////////
	// Tests concerning the license expiry
	///////////////////////////////////////

	public function testHasExpiryForNoExpiryReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasExpiry()
		);
	}

	public function testHasExpiryForNonZeroExpiryReturnsTrue() {
		$this->fixture->setExpiry(42);

		$this->assertTrue(
			$this->fixture->hasExpiry()
		);
	}

	public function testGetExpiryForNoExpiryReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getExpiry()
		);
	}

	public function testGetExpiryForNonZeroExpiryReturnsFormattedDate() {
		$this->fixture->setExpiry(mktime(0, 0, 0, 12, 31, 2000));

		$this->assertEquals(
			'31.12.2000',
			$this->fixture->getExpiry()
		);
	}


	////////////////////////////////////
	// Tests concerning setDescription
	////////////////////////////////////

	// TODO: Add an rte_css function to the fake front end to be able to run
	// the function pi_RTEcssText


	//////////////////////////////////
	// Tests concerning getEventData
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getEventDataReturnsFormattedUnregistrationDeadline() {
		$this->fixture->setUnregistrationDeadline(1893488400);
		$this->fixture->setShowTimeOfUnregistrationDeadline(0);
		$this->assertEquals(
			'01.01.2030',
			$this->fixture->getEventData('deadline_unregistration')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForShowTimeOfUnregistrationDeadlineTrueReturnsFormattedUnregistrationDeadlineWithTime() {
		$this->fixture->setUnregistrationDeadline(1893488400);
		$this->fixture->setShowTimeOfUnregistrationDeadline(1);

		$this->assertEquals(
			'01.01.2030 10:00',
			$this->fixture->getEventData('deadline_unregistration')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForUnregistrationDeadlineZeroReturnsEmptyString () {
		$this->fixture->setUnregistrationDeadline(0);
		$this->assertEquals(
			'',
			$this->fixture->getEventData('deadline_unregistration')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForEventWithMultipleLodgingsSeparatesLodgingsWithLineFeeds() {
		$lodgingUid1 = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('title' => 'foo')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_lodgings_mm',
			$this->fixture->getUid(), $lodgingUid1
		);

		$lodgingUid2 = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('title' => 'bar')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_lodgings_mm',
			$this->fixture->getUid(), $lodgingUid2
		);

		$this->fixture->setNumberOfLodgings(2);

		$this->assertContains(
			'foo' . LF . 'bar',
			$this->fixture->getEventData('lodgings')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForEventWithMultipleLodgingsDoesNotSeparateLodgingsWithCarriageReturnsAndLineFeeds() {
		$lodgingUid1 = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('title' => 'foo')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_lodgings_mm',
			$this->fixture->getUid(), $lodgingUid1
		);

		$lodgingUid2 = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('title' => 'bar')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_lodgings_mm',
			$this->fixture->getUid(), $lodgingUid2
		);

		$this->fixture->setNumberOfLodgings(2);

		$this->assertNotContains(
			'foo' . CRLF . 'bar',
			$this->fixture->getEventData('lodgings')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataDataWithCarriageReturnAndLinefeedGetsConvertedToLineFeedOnly() {
		$this->fixture->setDescription('foo'. CRLF . 'bar');

		$this->assertContains(
			'foo' . LF . 'bar',
			$this->fixture->getEventData('description')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataDataWithTwoAdjacentLineFeedsReturnsStringWithOnlyOneLineFeed() {
		$this->fixture->setDescription('foo'. LF . LF . 'bar');

		$this->assertContains(
			'foo' . LF . 'bar',
			$this->fixture->getEventData('description')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataDataWithThreeAdjacentLineFeedsReturnsStringWithOnlyOneLineFeed() {
		$this->fixture->setDescription('foo'. LF . LF .  LF . 'bar');

		$this->assertContains(
			'foo' . LF . 'bar',
			$this->fixture->getEventData('description')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataDataWithFourAdjacentLineFeedsReturnsStringWithOnlyOneLineFeed() {
		$this->fixture->setDescription('foo'. LF . LF .  LF . LF . 'bar');

		$this->assertContains(
			'foo' . LF . 'bar',
			$this->fixture->getEventData('description')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForEventWithDateUsesHyphenAsDateSeparator() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY);

		$this->assertContains(
			'-',
			$this->fixture->getEventData('date')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForEventWithTimeUsesHyphenAsTimeSeparator() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

		$this->assertContains(
			'-',
			$this->fixture->getEventData('time')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataSeparatesPlacePartsByCommaAndSpace() {
		$place = array(
			'title' => 'Hotel Ibis',
			'homepage' => '',
			'address' => 'Kaiser-Karl-Ring 91',
			'city' => 'Bonn',
			'country' => '',
			'directions' => '',
		);

		$fixture = $this->getMock(
			'tx_seminars_seminar', array('getPlacesAsArray', 'hasPlace')
		);
		$fixture->expects($this->any())->method('getPlacesAsArray')
			->will($this->returnValue(array($place)));
		$fixture->expects($this->any())->method('hasPlace')
			->will($this->returnValue(TRUE));

		$this->assertEquals(
			'Hotel Ibis, Kaiser-Karl-Ring 91, Bonn',
			$fixture->getEventData('place')
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getEventDataSeparatesTwoPlacesByLineFeed() {
		$place1 = array(
			'title' => 'Hotel Ibis',
			'homepage' => '',
			'address' => '',
			'city' => '',
			'country' => '',
			'directions' => '',
		);
		$place2 = array(
			'title' => 'Wasserwerk',
			'homepage' => '',
			'address' => '',
			'city' => '',
			'country' => '',
			'directions' => '',
		);

		$fixture = $this->getMock(
			'tx_seminars_seminar', array('getPlacesAsArray', 'hasPlace')
		);
		$fixture->expects($this->any())->method('getPlacesAsArray')
			->will($this->returnValue(array($place1, $place2)));
		$fixture->expects($this->any())->method('hasPlace')
			->will($this->returnValue(TRUE));

		$this->assertEquals(
			'Hotel Ibis' . LF . 'Wasserwerk',
			$fixture->getEventData('place')
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getEventDataForPlaceWithoutZipContainsTitleAndAddressAndCity() {
		$place = array(
			'title' => 'Hotel Ibis',
			'address' => 'Kaiser-Karl-Ring 91',
			'zip' => '',
			'city' => 'Bonn',
		);

		$fixture = $this->getMock(
			'tx_seminars_seminar', array('getPlacesAsArray', 'hasPlace')
		);
		$fixture->expects($this->any())->method('getPlacesAsArray')
			->will($this->returnValue(array($place)));
		$fixture->expects($this->any())->method('hasPlace')
			->will($this->returnValue(TRUE));

		$this->assertEquals(
			'Hotel Ibis, Kaiser-Karl-Ring 91, Bonn',
			$fixture->getEventData('place')
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getEventDataForPlaceWithZipContainsTitleAndAddressAndZipAndCity() {
		$place = array(
			'title' => 'Hotel Ibis',
			'address' => 'Kaiser-Karl-Ring 91',
			'zip' => '53111',
			'city' => 'Bonn',
		);

		$fixture = $this->getMock(
			'tx_seminars_seminar', array('getPlacesAsArray', 'hasPlace')
		);
		$fixture->expects($this->any())->method('getPlacesAsArray')
			->will($this->returnValue(array($place)));
		$fixture->expects($this->any())->method('hasPlace')
			->will($this->returnValue(TRUE));

		$this->assertEquals(
			'Hotel Ibis, Kaiser-Karl-Ring 91, 53111 Bonn',
			$fixture->getEventData('place')
		);

		$fixture->__destruct();
	}


	///////////////////////////////////////
	// Tests concerning dumpSeminarValues
	///////////////////////////////////////

	public function test_dumpSeminarValues_ForTitleGiven_ReturnsTitle() {
		$this->assertContains(
			$this->fixture->getTitle(),
			$this->fixture->dumpSeminarValues('title')
		);
	}

	public function test_dumpSeminarValues_ForTitleGiven_ReturnsLabelForTitle() {
		$this->assertContains(
			$this->fixture->translate('label_title'),
			$this->fixture->dumpSeminarValues('title')
		);
	}

	public function test_dumpSeminarValues_ForTitleGiven_ReturnsTitleWithLineFeedAtEndOfLine() {
		$this->assertRegexp(
			'/\n$/',
			$this->fixture->dumpSeminarValues('title')
		);
	}

	public function test_dumpSeminarValues_ForTitleAndDescriptionGiven_ReturnsTitleAndDescription() {
		$this->fixture->setDescription('foo bar');

		$this->assertRegexp(
			'/.*' . $this->fixture->getTitle() . '.*\n.*' .
				$this->fixture->getRecordPropertyString('description') .'/',
			$this->fixture->dumpSeminarValues('title,description')
		);
	}

	public function test_dumpSeminarValues_ForEventWithoutDescriptionAndDescriptionGiven_ReturnsDescriptionLabelWithColonsAndLineFeed() {
		$this->fixture->setDescription('');

		$this->assertEquals(
			$this->fixture->translate('label_description') . ':' . LF,
			$this->fixture->dumpSeminarValues('description')
		);
	}

	public function test_dumpSeminarValues_ForEventWithNoVacanciesAndVacanciesGiven_ReturnsVacanciesLabelWithNumber() {
		$this->fixture->setNumberOfAttendances(2);
		$this->fixture->setAttendancesMax(2);
		$this->fixture->setNeedsRegistration(TRUE);

		$this->assertEquals(
			$this->fixture->translate('label_vacancies') . ': 0' . LF ,
			$this->fixture->dumpSeminarValues('vacancies')
		);
	}

	public function test_dumpSeminarValues_ForEventWithOneVacancyAndVacanciesGiven_ReturnsNumberOfVacancies() {
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setAttendancesMax(2);
		$this->fixture->setNeedsRegistration(TRUE);

		$this->assertEquals(
			$this->fixture->translate('label_vacancies') . ': 1' . LF ,
			$this->fixture->dumpSeminarValues('vacancies')
		);
	}

	public function test_dumpSeminarValues_ForEventWithUnlimitedVacanciesAndVacanciesGiven_ReturnsVacanciesUnlimitedString() {
		$this->fixture->setUnlimitedVacancies();

		$this->assertEquals(
			$this->fixture->translate('label_vacancies') . ': ' .
				$this->fixture->translate('label_unlimited') . LF ,
			$this->fixture->dumpSeminarValues('vacancies')
		);
	}


	////////////////////////////////////////////////
	// Tests regarding the registration begin date
	////////////////////////////////////////////////

	public function test_hasRegistrationBegin_ForNoRegistrationBegin_ReturnsFalse() {
		$this->fixture->setRegistrationBeginDate(0);

		$this->assertFalse(
			$this->fixture->hasRegistrationBegin()
		);
	}

	public function test_hasRegistrationBegin_ForEventWithRegistrationBegin_ReturnsTrue() {
		$this->fixture->setRegistrationBeginDate(42);

		$this->assertTrue(
			$this->fixture->hasRegistrationBegin()
		);
	}

	public function test_getRegistrationBeginAsUnixTimestamp_ForEventWithoutRegistrationBegin_ReturnsZero() {
		$this->fixture->setRegistrationBeginDate(0);

		$this->assertEquals(
			0,
			$this->fixture->getRegistrationBeginAsUnixTimestamp()
		);
	}

	public function test_getRegistrationBeginAsUnixTimestamp_ForEventWithRegistrationBegin_ReturnsRegistrationBeginAsUnixTimestamp() {
		$this->fixture->setRegistrationBeginDate(42);

		$this->assertEquals(
			42,
			$this->fixture->getRegistrationBeginAsUnixTimestamp()
		);
	}

	public function test_getRegistrationBegin_ForEventWithoutRegistrationBegin_ReturnsEmptyString() {
		$this->fixture->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
		$this->fixture->setConfigurationValue('timeFormat', '%H:%M');

		$this->fixture->setRegistrationBeginDate(0);

		$this->assertEquals(
			'',
			$this->fixture->getRegistrationBegin()
		);
	}

	public function test_getRegistrationBegin_ForEventWithRegistrationBegin_ReturnsFormattedRegistrationBegin() {
		$this->fixture->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
		$this->fixture->setConfigurationValue('timeFormat', '%H:%M');

		$this->fixture->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME']);

		$this->assertEquals(
			strftime('%d.%m.%Y %H:%M', $GLOBALS['SIM_EXEC_TIME']),
			$this->fixture->getRegistrationBegin()
		);
	}


	/////////////////////////////////////
	// Tests regarding the description.
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getDescriptionWithoutDescriptionReturnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function setDescriptionSetsDescription() {
		$this->fixture->setDescription('this is a great event.');

		$this->assertEquals(
			'this is a great event.',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionWithoutDescriptionReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionWithDescriptionReturnsTrue() {
		$this->fixture->setDescription('this is a great event.');

		$this->assertTrue(
			$this->fixture->hasDescription()
		);
	}


	////////////////////////////////////////////////
	// Tests regarding the additional information.
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAdditionalInformationWithoutAdditionalInformationReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function setAdditionalInformationSetsAdditionalInformation() {
		$this->fixture->setAdditionalInformation('this is good to know');

		$this->assertEquals(
			'this is good to know',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalInformationWithoutAdditionalInformationReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalInformationWithAdditionalInformationReturnsTrue() {
		$this->fixture->setAdditionalInformation('this is good to know');

		$this->assertTrue(
			$this->fixture->hasAdditionalInformation()
		);
	}


	///////////////////////////////////////////////////////
	// Tests concerning getLatestPossibleRegistrationTime
	///////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getLatestPossibleRegistrationTimeForEventWithoutAnyDatesReturnsZero() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'needs_registration' => 1,
				'deadline_registration' => 0,
				'begin_date' => 0,
				'end_date' => 0,
			)
		);
		$fixture = new tx_seminars_seminarchild($uid, array());

		$this->assertEquals(
			0,
			$fixture->getLatestPossibleRegistrationTime()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getLatestPossibleRegistrationTimeForEventWithBeginDateReturnsBeginDate() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'needs_registration' => 1,
				'deadline_registration' => 0,
				'begin_date' => $this->now,
				'end_date' => 0,
			)
		);
		$fixture = new tx_seminars_seminarchild($uid, array());

		$this->assertEquals(
			$this->now,
			$fixture->getLatestPossibleRegistrationTime()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getLatestPossibleRegistrationTimeForEventWithBeginDateAndRegistrationDeadlineReturnsRegistrationDeadline() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'needs_registration' => 1,
				'deadline_registration' => $this->now,
				'begin_date' => $this->now + 1000,
				'end_date' => 0,
			)
		);
		$fixture = new tx_seminars_seminarchild($uid, array());

		$this->assertEquals(
			$this->now,
			$fixture->getLatestPossibleRegistrationTime()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getLatestPossibleRegistrationTimeForEventWithBeginAndEndDateAndRegistrationForStartedEventsAllowedReturnsEndDate() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'needs_registration' => 1,
				'deadline_registration' => 0,
				'begin_date' => $this->now,
				'end_date' => $this->now + 1000,
			)
		);
		$fixture = new tx_seminars_seminarchild(
			$uid, array('allowRegistrationForStartedEvents' => 1)
		);

		$this->assertEquals(
			$this->now + 1000,
			$fixture->getLatestPossibleRegistrationTime()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getLatestPossibleRegistrationTimeForEventWithBeginDateAndRegistrationDeadlineAndRegistrationForStartedEventsAllowedReturnsRegistrationDeadline() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'needs_registration' => 1,
				'deadline_registration' => $this->now - 1000,
				'begin_date' => $this->now,
				'end_date' => $this->now + 1000,
			)
		);
		$fixture = new tx_seminars_seminarchild(
			$uid, array('allowRegistrationForStartedEvents' => 1)
		);

		$this->assertEquals(
			$this->now - 1000,
			$fixture->getLatestPossibleRegistrationTime()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getLatestPossibleRegistrationTimeForEventWithBeginDateAndWithoutEndDateAndRegistrationForStartedEventsAllowedReturnsBeginDate() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'needs_registration' => 1,
				'deadline_registration' => 0,
				'begin_date' => $this->now,
				'end_date' => 0,
			)
		);
		$fixture = new tx_seminars_seminarchild(
			$uid, array('allowRegistrationForStartedEvents' => 1)
		);

		$this->assertEquals(
			$this->now,
			$fixture->getLatestPossibleRegistrationTime()
		);

		$fixture->__destruct();
	}


	/////////////////////////////////////////////
	// Tests concerning hasOfflineRegistrations
	/////////////////////////////////////////////

	public function test_hasOfflineRegistrations_ForEventWithoutOfflineRegistrations_ReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasOfflineRegistrations()
		);
	}

	public function test_hasOfflineRegistrations_ForEventWithTwoOfflineRegistrations_ReturnsTrue() {
		$this->fixture->setOfflineRegistrationNumber(2);

		$this->assertTrue(
			$this->fixture->hasOfflineRegistrations()
		);
	}


	/////////////////////////////////////////////
	// Tests concerning getOfflineRegistrations
	/////////////////////////////////////////////

	public function test_getOfflineRegistrations_ForEventWithoutOfflineRegistrations_ReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getOfflineRegistrations()
		);
	}

	public function test_getOfflineRegistrations_ForEventWithTwoOfflineRegistrations_ReturnsTwo() {
		$this->fixture->setOfflineRegistrationNumber(2);

		$this->assertEquals(
			2,
			$this->fixture->getOfflineRegistrations()
		);
	}


	/////////////////////////////////////////
	// Tests concerning calculateStatistics
	/////////////////////////////////////////

	public function test_calculateStatistics_ForEventWithOfflineRegistrationsAndRegularRegistrations_CalculatesCumulatedAttendeeNumber() {
		$this->fixture->setOfflineRegistrationNumber(1);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->fixture->getUid(),
			)
		);

		$this->fixture->calculateStatistics();

		$this->assertEquals(
			2,
			$this->fixture->getAttendances()
		);
	}

	public function test_calculateStatistics_ForEventWithOnePaidRegistration_SetsOnePaidAttendance() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->fixture->getUid(),
				'datepaid' => $GLOBALS['SIM_EXEC_TIME'],
			)
		);

		$this->fixture->calculateStatistics();

		$this->assertEquals(
			1,
			$this->fixture->getAttendancesPaid()
		);
	}

	public function test_calculateStatistics_ForEventWithTwoAttendeesOnQueue_SetsTwoAttendanceOnQueue() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->fixture->getUid(),
				'registration_queue' => 1,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->fixture->getUid(),
				'registration_queue' => 1,
			)
		);

		$this->fixture->calculateStatistics();

		$this->assertEquals(
			2,
			$this->fixture->getAttendancesOnRegistrationQueue()
		);
	}

	public function test_calculateStatistics_ForEventWithOneOfflineRegistration_SetsAttendancesToOne() {
		$this->fixture->setOfflineRegistrationNumber(1);

		$this->fixture->calculateStatistics();

		$this->assertEquals(
			1,
			$this->fixture->getAttendances()
		);
	}


	/////////////////////////////////////
	// Tests concerning getTopicInteger
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getTopicIntegerForSingleEventReturnsDataFromRecord() {
		$this->fixture->setRecordPropertyInteger('credit_points', 42);

		$this->assertEquals(
			42,
			$this->fixture->getTopicInteger('credit_points')
		);
	}

	/**
	 * @test
	 */
	public function getTopicIntegerForDateReturnsDataFromTopic() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'credit_points' => 42,
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
			)
		);

		$date = new tx_seminars_seminarchild($dateRecordUid);

		$this->assertEquals(
			42,
			$date->getTopicInteger('credit_points')
		);

		$date->__destruct();
	}


	/////////////////////////////////////
	// Tests concerning hasTopicInteger
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function hasTopicIntegerForSingleEventForZeroReturnsFalse() {
		$this->fixture->setRecordPropertyInteger('credit_points', 0);

		$this->assertFalse(
			$this->fixture->hasTopicInteger('credit_points')
		);
	}

	/**
	 * @test
	 */
	public function hasTopicIntegerForSingleEventForPositiveIntegerReturnsFalse() {
		$this->fixture->setRecordPropertyInteger('credit_points', 1);

		$this->assertTrue(
			$this->fixture->hasTopicInteger('credit_points')
		);
	}

	/**
	 * @test
	 */
	public function hasTopicIntegerForSingleEventForNegativeIntegerReturnsFalse() {
		$this->fixture->setRecordPropertyInteger('credit_points', -1);

		$this->assertTrue(
			$this->fixture->hasTopicInteger('credit_points')
		);
	}

	/**
	 * @test
	 */
	public function hasTopicIntegerForDateForZeroInTopicReturnsFalse() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'credit_points' => 0,
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
			)
		);

		$date = new tx_seminars_seminarchild($dateRecordUid);

		$this->assertFalse(
			$date->hasTopicInteger('credit_points')
		);

		$date->__destruct();
	}

	/**
	 * @test
	 */
	public function hasTopicIntegerForDateForPositiveIntegerInTopicReturnsTrue() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'credit_points' => 1,
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
			)
		);

		$date = new tx_seminars_seminarchild($dateRecordUid);

		$this->assertTrue(
			$date->hasTopicInteger('credit_points')
		);

		$date->__destruct();
	}


	///////////////////////////////////////////
	// Tests concerning the publication state
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPublicationHashReturnsPublicationHash() {
		$this->fixture->setRecordPropertyString(
			'publication_hash',
			'5318761asdf35as5sad35asd35asd'
		);

		$this->assertEquals(
			'5318761asdf35as5sad35asd35asd',
			$this->fixture->getPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function setPublicationHashSetsPublicationHash() {
		$this->fixture->setPublicationHash('5318761asdf35as5sad35asd35asd');

		$this->assertEquals(
			'5318761asdf35as5sad35asd35asd',
			$this->fixture->getPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function isPublishedForEventWithoutPublicationHashIsTrue() {
		$this->fixture->setPublicationHash('');

		$this->assertTrue(
			$this->fixture->isPublished()
		);
	}

	/**
	 * @test
	 */
	public function isPublishedForEventWithPublicationHashIsFalse() {
		$this->fixture->setPublicationHash('5318761asdf35as5sad35asd35asd');

		$this->assertFalse(
			$this->fixture->isPublished()
		);
	}


	//////////////////////////////////////////////
	// Tests concerning canViewRegistrationsList
	//////////////////////////////////////////////

	/**
	 * Data provider for testing the canViewRegistrationsList function
	 * with default access and access only for attendees and managers.
	 *
	 * @return array test data for canViewRegistrationsList with each row
	 *               having the following elements:
	 *               [expected] boolean: expected value (TRUE or FALSE)
	 *               [loggedIn] boolean: whether a user is logged in
	 *               [isRegistered] boolean: whether the logged-in user is
	 *                              registered for that event
	 *               [isVip] boolean: whether the logged-in user is a VIP
	 *                                that event
	 *               [whichPlugin] string: value for that parameter
	 *               [registrationsListPID] integer: value for that parameter
	 *               [registrationsVipListPID] integer: value for that parameter
	 *
	 * @see canViewRegistrationsListWithNeedsRegistrationAndDefaultAccess
	 * @see canViewRegistrationsListWithNeedsRegistrationAndAttendeesManagersAccess
	 */
	public function canViewRegistrationsListDataProvider() {
		return array(
			'seminarListWithNothingElse' => array(
				'expected' => FALSE,
				'loggedIn' => FALSE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'seminarListLoggedInWithListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsRegisteredWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsRegisteredWithoutListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsVipWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 1,
			),
			'seminarListIsVipWithoutListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'myEventsIsRegisteredWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'my_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myEventsIsVipWithListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'my_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myVipEventsIsRegisteredWithListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'my_vip_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myVipEventsIsVipWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'my_vip_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'listRegistrationsIsRegistered' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'list_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listRegistrationsIsVip' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'list_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listVipRegistrationsIsRegistered' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'list_vip_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listVipRegistrationsIsVip' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'list_vip_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider canViewRegistrationsListDataProvider
	 */
	public function canViewRegistrationsListWithNeedsRegistrationAndDefaultAccess(
		$expected, $loggedIn, $isRegistered, $isVip,
		$whichPlugin, $registrationsListPID, $registrationsVipListPID
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'isUserRegistered', 'isUserVip'),
			array(),
			'',
			FALSE
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())->method('isUserRegistered')
			->will($this->returnValue($isRegistered));
		$fixture->expects($this->any())->method('isUserVip')
			->will($this->returnValue($isVip));

		if ($loggedIn) {
			$this->testingFramework->createFakeFrontEnd();
			$this->testingFramework->createAndLoginFrontEndUser();
		}

		$this->assertEquals(
			$expected,
			$fixture->canViewRegistrationsList(
				$whichPlugin, $registrationsListPID, $registrationsVipListPID
			)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 *
	 * @dataProvider canViewRegistrationsListDataProvider
	 */
	public function canViewRegistrationsListWithNeedsRegistrationAndAttendeesManagersAccess(
		$expected, $loggedIn, $isRegistered, $isVip,
		$whichPlugin, $registrationsListPID, $registrationsVipListPID
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'isUserRegistered', 'isUserVip'),
			array(),
			'',
			FALSE
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())->method('isUserRegistered')
			->will($this->returnValue($isRegistered));
		$fixture->expects($this->any())->method('isUserVip')
			->will($this->returnValue($isVip));

		if ($loggedIn) {
			$this->testingFramework->createFakeFrontEnd();
			$this->testingFramework->createAndLoginFrontEndUser();
		}

		$this->assertEquals(
			$expected,
			$fixture->canViewRegistrationsList(
				$whichPlugin, $registrationsListPID, $registrationsVipListPID,
				0, 'attendees_and_managers'
			)
		);

		$fixture->__destruct();
	}

	/**
	 * Data provider for the canViewRegistrationsForCsvExportListDataProvider
	 * test.
	 *
	 * @return array test data for canViewRegistrationsList with each row
	 *               having the following elements:
	 *               [expected] boolean: expected value (TRUE or FALSE)
	 *               [loggedIn] boolean: whether a user is logged in
	 *               [isVip] boolean: whether the logged-in user is a VIP
	 *                                that event
	 *               [allowCsvExportForVips] boolean: that configuration value
	 *
	 * @see canViewRegistrationsListForCsvExport
	 */
	public function canViewRegistrationsForCsvExportListDataProvider() {
		return array(
			'notLoggedInButCsvExportAllowed' => array(
				'expected' => FALSE,
				'loggedIn' => FALSE,
				'isVip' => FALSE,
				'allowCsvExportForVips' => TRUE,
			),
			'loggedInAndCsvExportAllowedButNoVip' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isVip' => FALSE,
				'allowCsvExportForVips' => TRUE,
			),
			'loggedInAndCsvExportAllowedAndVip' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isVip' => TRUE,
				'allowCsvExportForVips' => TRUE,
			),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider canViewRegistrationsForCsvExportListDataProvider
	 */
	public function canViewRegistrationsListForCsvExport(
		$expected, $loggedIn, $isVip, $allowCsvExportForVips
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'isUserVip'),
			array(),
			'',
			FALSE
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())->method('isUserVip')
			->will($this->returnValue($isVip));
		$fixture->init(
			array('allowCsvExportForVips' => $allowCsvExportForVips)
		);

		if ($loggedIn) {
			$this->testingFramework->createFakeFrontEnd();
			$this->testingFramework->createAndLoginFrontEndUser();
		}

		$this->assertEquals(
			$expected,
			$fixture->canViewRegistrationsList('csv_export')
		);

		$fixture->__destruct();
	}

	/**
	 * Data provider for testing the canViewRegistrationsList function
	 * with login access.
	 *
	 * @return array test data for canViewRegistrationsList with each row
	 *               having the following elements:
	 *               [expected] boolean: expected value (TRUE or FALSE)
	 *               [loggedIn] boolean: whether a user is logged in
	 *               [isRegistered] boolean: whether the logged-in user is
	 *                              registered for that event
	 *               [isVip] boolean: whether the logged-in user is a VIP
	 *                                that event
	 *               [whichPlugin] string: value for that parameter
	 *               [registrationsListPID] integer: value for that parameter
	 *               [registrationsVipListPID] integer: value for that parameter
	 *
	 * @see canViewRegistrationsListWithNeedsRegistrationAndLoginAccess
	 */
	public function canViewRegistrationsListDataProviderForLoggedIn() {
		return array(
			'seminarListWithNothingElse' => array(
				'expected' => FALSE,
				'loggedIn' => FALSE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'seminarListLoggedInWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsRegisteredWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsRegisteredWithoutListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsVipWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsVipWithVipListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 1,
			),
			'seminarListIsVipWithoutListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'myEventsIsRegisteredWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'my_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myEventsIsVipWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'my_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myVipEventsIsRegisteredWithListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'my_vip_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myVipEventsIsVipWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'my_vip_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'listRegistrationsIsRegistered' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'list_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listRegistrationsIsVip' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'list_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listVipRegistrationsIsRegistered' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'list_vip_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listVipRegistrationsIsVip' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'list_vip_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider canViewRegistrationsListDataProviderForLoggedIn
	 */
	public function canViewRegistrationsListWithNeedsRegistrationAndLoginAccess(
		$expected, $loggedIn, $isRegistered, $isVip,
		$whichPlugin, $registrationsListPID, $registrationsVipListPID
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'isUserRegistered', 'isUserVip'),
			array(),
			'',
			FALSE
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())->method('isUserRegistered')
			->will($this->returnValue($isRegistered));
		$fixture->expects($this->any())->method('isUserVip')
			->will($this->returnValue($isVip));

		if ($loggedIn) {
			$this->testingFramework->createFakeFrontEnd();
			$this->testingFramework->createAndLoginFrontEndUser();
		}

		$this->assertEquals(
			$expected,
			$fixture->canViewRegistrationsList(
				$whichPlugin, $registrationsListPID, $registrationsVipListPID,
				0, 'login'
			)
		);

		$fixture->__destruct();
	}

	/**
	 * Data provider for testing the canViewRegistrationsList function
	 * with world access.
	 *
	 * @return array test data for canViewRegistrationsList with each row
	 *               having the following elements:
	 *               [expected] boolean: expected value (TRUE or FALSE)
	 *               [loggedIn] boolean: whether a user is logged in
	 *               [isRegistered] boolean: whether the logged-in user is
	 *                              registered for that event
	 *               [isVip] boolean: whether the logged-in user is a VIP
	 *                                that event
	 *               [whichPlugin] string: value for that parameter
	 *               [registrationsListPID] integer: value for that parameter
	 *               [registrationsVipListPID] integer: value for that parameter
	 *
	 * @see canViewRegistrationsListWithNeedsRegistrationAndWorldAccess
	 */
	public function canViewRegistrationsListDataProviderForWorld() {
		return array(
			'seminarListWithNothingElse' => array(
				'expected' => FALSE,
				'loggedIn' => FALSE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'seminarListWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => FALSE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListLoggedInWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsRegisteredWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsRegisteredWithoutListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsVipWithListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 1,
			),
			'seminarListIsVipWithoutListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'myEventsIsRegisteredWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'my_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myEventsIsVipWithVipListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'my_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myVipEventsIsRegisteredWithVipListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'my_vip_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myVipEventsIsVipWithVipListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'my_vip_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'listRegistrationsIsRegistered' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'list_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listRegistrationsIsVip' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'list_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listVipRegistrationsIsRegistered' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'list_vip_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listVipRegistrationsIsVip' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'list_vip_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider canViewRegistrationsListDataProviderForWorld
	 */
	public function canViewRegistrationsListWithNeedsRegistrationAndWorldAccess(
		$expected, $loggedIn, $isRegistered, $isVip,
		$whichPlugin, $registrationsListPID, $registrationsVipListPID
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'isUserRegistered', 'isUserVip'),
			array(),
			'',
			FALSE
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())->method('isUserRegistered')
			->will($this->returnValue($isRegistered));
		$fixture->expects($this->any())->method('isUserVip')
			->will($this->returnValue($isVip));

		if ($loggedIn) {
			$this->testingFramework->createFakeFrontEnd();
			$this->testingFramework->createAndLoginFrontEndUser();
		}

		$this->assertEquals(
			$expected,
			$fixture->canViewRegistrationsList(
				$whichPlugin, $registrationsListPID, $registrationsVipListPID,
				0, 'world'
			)
		);

		$fixture->__destruct();
	}


	/////////////////////////////////////////////////////
	// Tests concerning canViewRegistrationsListMessage
	/////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageWithoutNeededRegistrationReturnsNoRegistrationMessage() {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration')
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(FALSE));
		$fixture->init();

		$this->assertEquals(
			$fixture->translate('message_noRegistrationNecessary'),
			$fixture->canViewRegistrationsListMessage('list_registrations')
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageForListAndNoLoginAndAttendeesAccessReturnsPleaseLoginMessage() {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration')
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->init();

		$this->assertEquals(
			$fixture->translate('message_notLoggedIn'),
			$fixture->canViewRegistrationsListMessage(
				'list_registrations', 'attendees_and_managers'
			)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageForListAndNoLoginAndLoginAccessReturnsPleaseLoginMessage() {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration')
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->init();

		$this->assertEquals(
			$fixture->translate('message_notLoggedIn'),
			$fixture->canViewRegistrationsListMessage(
				'list_registrations', 'login'
			)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageForListAndNoLoginAndWorldAccessReturnsEmptyString() {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration')
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->init();

		$this->assertEquals(
			'',
			$fixture->canViewRegistrationsListMessage(
				'list_registrations', 'world'
			)
		);

		$fixture->__destruct();
	}

	/**
	 * Data provider that returns all possible access level codes for the
	 * FE registration lists.
	 *
	 * @return array the possible access levels, will not be empty
	 *
	 * @see canViewRegistrationsListMessageForVipListAndNoLoginReturnsPleaseLoginMessage
	 */
	public function registrationListAccessLevelsDataProvider() {
		return array(
			'attendeesAndManagers' => array('attendees_and_managers'),
			'login' => array('login'),
			'world' => array('world'),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider registrationListAccessLevelsDataProvider
	 */
	public function canViewRegistrationsListMessageForVipListAndNoLoginReturnsPleaseLoginMessage(
		$accessLevel
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration')
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->init();

		$this->assertEquals(
			$fixture->translate('message_notLoggedIn'),
			$fixture->canViewRegistrationsListMessage(
				'list_vip_registrations', $accessLevel
			)
		);

		$fixture->__destruct();
	}

	/**
	 * Data provider that returns all possible parameter combinations for
	 * canViewRegistrationsList as called from canViewRegistrationsListMessage.
	 *
	 * @return array the possible parameter combinations, will not be empty
	 *
	 * @see canViewRegistrationsListMessageWithLoginRoutesParameters
	 */
	public function registrationListParametersDataProvider() {
		return array(
			'attendeesAndManagers' => array(
				'list_registrations', 'attendees_and_managers'
			),
			'login' => array(
				'list_registrations', 'login'
			),
			'world' => array(
				'list_registrations', 'world'
			),
			'attendeesAndManagersVip' => array(
				'list_vip_registrations', 'attendees_and_managers'
			),
			'loginVip' => array(
				'list_vip_registrations', 'login'
			),
			'worldVip' => array(
				'list_vip_registrations', 'world'
			),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider registrationListParametersDataProvider
	 */
	public function canViewRegistrationsListMessageWithLoginRoutesParameters(
		$whichPlugin, $accessLevel
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'canViewRegistrationsList')
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->once())->method('canViewRegistrationsList')
			->with($whichPlugin, $accessLevel)
			->will($this->returnValue(TRUE));

		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		$fixture->canViewRegistrationsListMessage($whichPlugin, $accessLevel);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageWithLoginAndAccessGrantedReturnsEmptyString() {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'canViewRegistrationsList')
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())->method('canViewRegistrationsList')
			->will($this->returnValue(TRUE));

		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertEquals(
			'',
			$fixture->canViewRegistrationsListMessage(
				'list_registrations', 'world'
			)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageWithoutLoginAndAccessGrantedReturnsEmptyString() {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'canViewRegistrationsList')
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())->method('canViewRegistrationsList')
			->will($this->returnValue(TRUE));

		$this->assertEquals(
			'',
			$fixture->canViewRegistrationsListMessage(
				'list_registrations', 'world'
			)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageWithLoginAndAccessDeniedReturnsAccessDeniedMessage() {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'canViewRegistrationsList')
		);
		$fixture->expects($this->any())->method('needsRegistration')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->any())->method('canViewRegistrationsList')
			->will($this->returnValue(FALSE));

		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertEquals(
			$fixture->translate('message_accessDenied'),
			$fixture->canViewRegistrationsListMessage(
				'list_registrations', 'world'
			)
		);

		$fixture->__destruct();
	}


	/////////////////////////////////
	// Tests concerning hasAnyPrice
	/////////////////////////////////

	/**
	 * Data provider for hasAnyPriceWithDataProvider.
	 *
	 * @return array two-dimensional array with the following inner keys:
	 *               [expectedHasAnyPrice] the expected return value of hasAnyPrice
	 *               [hasPriceRegular] the return value of that function
	 *               [hasPriceSpecial] the return value of that function
	 *               [earlyBirdApplies] the return value of that function
	 *               [hasEarlyBirdPriceRegular] the return value of that function
	 *               [hasEarlyBirdPriceSpecial] the return value of that function
	 *               [hasPriceRegularBoard] the return value of that function
	 *               [hasPriceSpecialBoard] the return value of that function

	 * @see hasAnyPriceWithDataProvider
	 */
	public function hasAnyPriceDataProvider() {
		return array(
			'noPriceAtAll' => array(
				'expectedHasAnyPrice' => FALSE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'regularPrice' => array(
				'expectedHasAnyPrice' => TRUE,
				'hasPriceRegular' => TRUE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'specialPrice' => array(
				'expectedHasAnyPrice' => TRUE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => TRUE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'regularEarlyBirdApplies' => array(
				'expectedHasAnyPrice' => TRUE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => TRUE,
				'hasEarlyBirdPriceRegular' => TRUE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'regularEarlyBirdNotApplies' => array(
				'expectedHasAnyPrice' => FALSE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => TRUE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'specialEarlyBirdApplies' => array(
				'expectedHasAnyPrice' => TRUE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => TRUE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => TRUE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'specialEarlyBirdNotApplies' => array(
				'expectedHasAnyPrice' => FALSE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => TRUE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'regularBoard' => array(
				'expectedHasAnyPrice' => TRUE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => TRUE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'specialBoard' => array(
				'expectedHasAnyPrice' => TRUE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => TRUE,
			),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider hasAnyPriceDataProvider
	 *
	 * @param boolean $expectedHasAnyPrice
	 *        the expected return value of hasAnyPrice
	 * @param boolean $hasPriceRegular the return value of hasPriceRegular
	 * @param boolean $hasPriceSpecial the return value of hasPriceRegular
	 * @param boolean $earlyBirdApplies the return value of earlyBirdApplies
	 * @param boolean $hasEarlyBirdPriceRegular the return value of earlyBirdApplies
	 * @param boolean $hasEarlyBirdPriceSpecial
	 *        the return value of hasEarlyBirdPriceSpecial
	 * @param boolean $hasPriceRegularBoard
	 *        the return value of hasPriceRegularBoard
	 * @param boolean $hasPriceSpecialBoard
	 *        the return value of hasPriceSpecialBoard
	 */
	public function hasAnyPriceWithDataProvider(
		$expectedHasAnyPrice, $hasPriceRegular, $hasPriceSpecial,
		$earlyBirdApplies, $hasEarlyBirdPriceRegular, $hasEarlyBirdPriceSpecial,
		$hasPriceRegularBoard, $hasPriceSpecialBoard
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array(
				'hasPriceRegular', 'hasPriceSpecial', 'earlyBirdApplies',
				'hasEarlyBirdPriceRegular', 'hasEarlyBirdPriceSpecial',
				'hasPriceRegularBoard', 'hasPriceSpecialBoard'
			)
		);

		$fixture->expects($this->any())->method('hasPriceRegular')
			->will($this->returnValue($hasPriceRegular));
		$fixture->expects($this->any())->method('hasPriceSpecial')
			->will($this->returnValue($hasPriceSpecial));
		$fixture->expects($this->any())->method('earlyBirdApplies')
			->will($this->returnValue($earlyBirdApplies));
		$fixture->expects($this->any())->method('hasEarlyBirdPriceRegular')
			->will($this->returnValue($hasEarlyBirdPriceRegular));
		$fixture->expects($this->any())->method('hasEarlyBirdPriceSpecial')
			->will($this->returnValue($hasEarlyBirdPriceSpecial));
		$fixture->expects($this->any())->method('hasPriceRegularBoard')
			->will($this->returnValue($hasPriceRegularBoard));
		$fixture->expects($this->any())->method('hasPriceSpecialBoard')
			->will($this->returnValue($hasPriceSpecialBoard));

		$this->assertEquals(
			$expectedHasAnyPrice,
			$fixture->hasAnyPrice()
		);
	}
}
?>