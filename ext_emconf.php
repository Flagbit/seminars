<?php

########################################################################
# Extension Manager/Repository config file for ext "seminars".
#
# Auto generated 14-10-2011 21:55
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Seminar Manager',
	'description' => 'This extension allows you to create and manage a list of seminars, workshops, lectures, theater performances and other events, allowing front-end users to sign up. FE users also can create and edit events.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => 'cms,css_styled_content,oelib,ameos_formidable,static_info_tables,static_info_tables_taxes',
	'conflicts' => 'dbal,sourceopt',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'BackEnd,BackEndExtJs',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'be_groups,fe_groups,fe_users',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'author_company' => 'oliverklee.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.9.71',
	'_md5_values_when_last_written' => 'a:295:{s:13:"changelog.txt";s:4:"de93";s:20:"class.ext_update.php";s:4:"4e63";s:37:"class.tx_seminars_EmailSalutation.php";s:4:"320e";s:33:"class.tx_seminars_configcheck.php";s:4:"ee46";s:34:"class.tx_seminars_configgetter.php";s:4:"2fb0";s:31:"class.tx_seminars_flexForms.php";s:4:"ebb7";s:34:"class.tx_seminars_registration.php";s:4:"b309";s:41:"class.tx_seminars_registrationmanager.php";s:4:"3dcc";s:29:"class.tx_seminars_seminar.php";s:4:"a64d";s:29:"class.tx_seminars_speaker.php";s:4:"ea3f";s:29:"class.tx_seminars_tcemain.php";s:4:"d8b7";s:30:"class.tx_seminars_timeslot.php";s:4:"45f9";s:30:"class.tx_seminars_timespan.php";s:4:"0508";s:16:"ext_autoload.php";s:4:"56d5";s:21:"ext_conf_template.txt";s:4:"a043";s:12:"ext_icon.gif";s:4:"35fc";s:17:"ext_localconf.php";s:4:"2fc4";s:14:"ext_tables.php";s:4:"a3f1";s:14:"ext_tables.sql";s:4:"b5cd";s:13:"locallang.xml";s:4:"0f11";s:16:"locallang_db.xml";s:4:"671f";s:8:"todo.txt";s:4:"a2c3";s:36:"tx_seminars_modifiedSystemTables.php";s:4:"89ef";s:33:"BackEnd/AbstractEventMailForm.php";s:4:"e147";s:24:"BackEnd/AbstractList.php";s:4:"3acc";s:19:"BackEnd/BackEnd.css";s:4:"b59c";s:15:"BackEnd/CSV.php";s:4:"ee68";s:31:"BackEnd/CancelEventMailForm.php";s:4:"d6b5";s:32:"BackEnd/ConfirmEventMailForm.php";s:4:"cefe";s:22:"BackEnd/EventsList.php";s:4:"b043";s:32:"BackEnd/GeneralEventMailForm.php";s:4:"c29b";s:18:"BackEnd/Module.php";s:4:"a59d";s:26:"BackEnd/OrganizersList.php";s:4:"6098";s:29:"BackEnd/RegistrationsList.php";s:4:"43bf";s:24:"BackEnd/SpeakersList.php";s:4:"c0e9";s:16:"BackEnd/conf.php";s:4:"7ce7";s:17:"BackEnd/index.php";s:4:"efaf";s:21:"BackEnd/locallang.xml";s:4:"298f";s:25:"BackEnd/locallang_mod.xml";s:4:"0cdd";s:22:"BackEnd/moduleicon.gif";s:4:"032e";s:27:"BackEndExtJs/ClearCache.php";s:4:"e933";s:23:"BackEndExtJs/Module.php";s:4:"58c7";s:21:"BackEndExtJs/conf.php";s:4:"9c7f";s:22:"BackEndExtJs/index.php";s:4:"35aa";s:34:"BackEndExtJs/Ajax/AbstractList.php";s:4:"bc3f";s:32:"BackEndExtJs/Ajax/Dispatcher.php";s:4:"b59e";s:32:"BackEndExtJs/Ajax/EventsList.php";s:4:"6fa4";s:36:"BackEndExtJs/Ajax/OrganizersList.php";s:4:"5cb8";s:39:"BackEndExtJs/Ajax/RegistrationsList.php";s:4:"961b";s:34:"BackEndExtJs/Ajax/SpeakersList.php";s:4:"088d";s:35:"BackEndExtJs/Ajax/StateProvider.php";s:4:"de68";s:16:"Bag/Abstract.php";s:4:"45bb";s:16:"Bag/Category.php";s:4:"e3ce";s:13:"Bag/Event.php";s:4:"fdd9";s:17:"Bag/Organizer.php";s:4:"041f";s:20:"Bag/Registration.php";s:4:"a96d";s:15:"Bag/Speaker.php";s:4:"888a";s:16:"Bag/TimeSlot.php";s:4:"b78c";s:23:"BagBuilder/Abstract.php";s:4:"05a2";s:23:"BagBuilder/Category.php";s:4:"d5de";s:20:"BagBuilder/Event.php";s:4:"85f2";s:24:"BagBuilder/Organizer.php";s:4:"0e27";s:27:"BagBuilder/Registration.php";s:4:"b005";s:22:"BagBuilder/Speaker.php";s:4:"fc4e";s:41:"Configuration/FlexForms/flexforms_pi1.xml";s:4:"5b49";s:25:"Configuration/TCA/tca.php";s:4:"5134";s:38:"Configuration/TypoScript/constants.txt";s:4:"1559";s:34:"Configuration/TypoScript/setup.txt";s:4:"4609";s:25:"FrontEnd/AbstractView.php";s:4:"89ad";s:25:"FrontEnd/CategoryList.php";s:4:"0e97";s:22:"FrontEnd/Countdown.php";s:4:"321d";s:30:"FrontEnd/DefaultController.php";s:4:"a5b8";s:19:"FrontEnd/Editor.php";s:4:"a72d";s:24:"FrontEnd/EventEditor.php";s:4:"bfa3";s:26:"FrontEnd/EventHeadline.php";s:4:"d924";s:25:"FrontEnd/PublishEvent.php";s:4:"894b";s:29:"FrontEnd/RegistrationForm.php";s:4:"4e1c";s:30:"FrontEnd/RegistrationsList.php";s:4:"1bd8";s:29:"FrontEnd/RequirementsList.php";s:4:"111c";s:27:"FrontEnd/SelectorWidget.php";s:4:"c696";s:23:"FrontEnd/WizardIcon.php";s:4:"9435";s:22:"Mapper/BackEndUser.php";s:4:"5b40";s:27:"Mapper/BackEndUserGroup.php";s:4:"f0a4";s:19:"Mapper/Category.php";s:4:"2075";s:19:"Mapper/Checkbox.php";s:4:"84bb";s:16:"Mapper/Event.php";s:4:"3831";s:20:"Mapper/EventType.php";s:4:"8801";s:15:"Mapper/Food.php";s:4:"2ac0";s:23:"Mapper/FrontEndUser.php";s:4:"25a6";s:28:"Mapper/FrontEndUserGroup.php";s:4:"58ae";s:18:"Mapper/Lodging.php";s:4:"cdf5";s:20:"Mapper/Organizer.php";s:4:"7bb6";s:24:"Mapper/PaymentMethod.php";s:4:"5e1e";s:16:"Mapper/Place.php";s:4:"1e71";s:23:"Mapper/Registration.php";s:4:"e757";s:16:"Mapper/Skill.php";s:4:"56f1";s:18:"Mapper/Speaker.php";s:4:"42eb";s:22:"Mapper/TargetGroup.php";s:4:"66bd";s:19:"Mapper/TimeSlot.php";s:4:"f4c5";s:26:"Model/AbstractTimeSpan.php";s:4:"5db8";s:21:"Model/BackEndUser.php";s:4:"7bae";s:26:"Model/BackEndUserGroup.php";s:4:"a8da";s:18:"Model/Category.php";s:4:"c486";s:18:"Model/Checkbox.php";s:4:"0e1e";s:15:"Model/Event.php";s:4:"3980";s:19:"Model/EventType.php";s:4:"195f";s:14:"Model/Food.php";s:4:"da77";s:22:"Model/FrontEndUser.php";s:4:"010d";s:27:"Model/FrontEndUserGroup.php";s:4:"52bf";s:17:"Model/Lodging.php";s:4:"e377";s:19:"Model/Organizer.php";s:4:"0efd";s:23:"Model/PaymentMethod.php";s:4:"df38";s:15:"Model/Place.php";s:4:"667c";s:22:"Model/Registration.php";s:4:"695b";s:15:"Model/Skill.php";s:4:"6c24";s:17:"Model/Speaker.php";s:4:"6864";s:21:"Model/TargetGroup.php";s:4:"08c0";s:18:"Model/TimeSlot.php";s:4:"ec5e";s:21:"OldModel/Abstract.php";s:4:"4326";s:21:"OldModel/Category.php";s:4:"1afb";s:22:"OldModel/Organizer.php";s:4:"6539";s:38:"Resources/Private/CSS/thankYouMail.css";s:4:"4e2b";s:40:"Resources/Private/Language/locallang.xml";s:4:"d11e";s:54:"Resources/Private/Language/locallang_csh_fe_groups.xml";s:4:"2c9e";s:53:"Resources/Private/Language/locallang_csh_seminars.xml";s:4:"f521";s:49:"Resources/Private/Language/FrontEnd/locallang.xml";s:4:"fa56";s:51:"Resources/Private/Templates/BackEnd/EventsList.html";s:4:"118b";s:55:"Resources/Private/Templates/BackEnd/OrganizersList.html";s:4:"0211";s:58:"Resources/Private/Templates/BackEnd/RegistrationsList.html";s:4:"c503";s:53:"Resources/Private/Templates/BackEnd/SpeakersList.html";s:4:"49b5";s:53:"Resources/Private/Templates/FrontEnd/EventEditor.html";s:4:"4498";s:50:"Resources/Private/Templates/FrontEnd/FrontEnd.html";s:4:"41c5";s:60:"Resources/Private/Templates/FrontEnd/RegistrationEditor.html";s:4:"3fd5";s:44:"Resources/Private/Templates/Mail/e-mail.html";s:4:"619c";s:38:"Resources/Public/CSS/BackEnd/Print.css";s:4:"d41d";s:45:"Resources/Public/CSS/BackEndExtJs/BackEnd.css";s:4:"e32a";s:43:"Resources/Public/CSS/BackEndExtJs/Print.css";s:4:"d41d";s:42:"Resources/Public/CSS/FrontEnd/FrontEnd.css";s:4:"bf1f";s:35:"Resources/Public/Icons/Canceled.png";s:4:"4161";s:35:"Resources/Public/Icons/Category.gif";s:4:"c95b";s:35:"Resources/Public/Icons/Checkbox.gif";s:4:"f1f0";s:36:"Resources/Public/Icons/Confirmed.png";s:4:"77af";s:40:"Resources/Public/Icons/ContentWizard.gif";s:4:"5e60";s:40:"Resources/Public/Icons/EventComplete.gif";s:4:"d4db";s:43:"Resources/Public/Icons/EventComplete__h.gif";s:4:"ccf3";s:43:"Resources/Public/Icons/EventComplete__t.gif";s:4:"a5cc";s:36:"Resources/Public/Icons/EventDate.gif";s:4:"7853";s:39:"Resources/Public/Icons/EventDate__h.gif";s:4:"fd86";s:39:"Resources/Public/Icons/EventDate__t.gif";s:4:"acc7";s:37:"Resources/Public/Icons/EventTopic.gif";s:4:"e4b1";s:40:"Resources/Public/Icons/EventTopic__h.gif";s:4:"4689";s:40:"Resources/Public/Icons/EventTopic__t.gif";s:4:"e220";s:36:"Resources/Public/Icons/EventType.gif";s:4:"61a5";s:31:"Resources/Public/Icons/Food.gif";s:4:"1024";s:34:"Resources/Public/Icons/Lodging.gif";s:4:"5fdf";s:36:"Resources/Public/Icons/Organizer.gif";s:4:"1e7e";s:40:"Resources/Public/Icons/PaymentMethod.gif";s:4:"44bd";s:32:"Resources/Public/Icons/Place.gif";s:4:"2694";s:32:"Resources/Public/Icons/Price.gif";s:4:"61a5";s:32:"Resources/Public/Icons/Print.png";s:4:"2424";s:39:"Resources/Public/Icons/Registration.gif";s:4:"d892";s:42:"Resources/Public/Icons/Registration__h.gif";s:4:"5571";s:32:"Resources/Public/Icons/Skill.gif";s:4:"30a2";s:34:"Resources/Public/Icons/Speaker.gif";s:4:"ddc1";s:38:"Resources/Public/Icons/TargetGroup.gif";s:4:"b5a7";s:31:"Resources/Public/Icons/Test.gif";s:4:"bd58";s:35:"Resources/Public/Icons/TimeSlot.gif";s:4:"bb73";s:51:"Resources/Public/JavaScript/BackEndExtJs/BackEnd.js";s:4:"977d";s:57:"Resources/Public/JavaScript/BackEndExtJs/flashmessages.js";s:4:"2ffc";s:48:"Resources/Public/JavaScript/FrontEnd/FrontEnd.js";s:4:"4c94";s:33:"Service/SingleViewLinkBuilder.php";s:4:"2e13";s:42:"cli/class.tx_seminars_cli_MailNotifier.php";s:4:"c558";s:23:"cli/tx_seminars_cli.php";s:4:"965e";s:20:"doc/dutch-manual.pdf";s:4:"beed";s:21:"doc/german-manual.sxw";s:4:"c334";s:14:"doc/manual.sxw";s:4:"b59b";s:29:"pi2/class.tx_seminars_pi2.php";s:4:"b177";s:17:"pi2/locallang.xml";s:4:"ef40";s:25:"tests/ConfigCheckTest.php";s:4:"2801";s:43:"tests/BackEnd/AbstractEventMailFormTest.php";s:4:"58be";s:41:"tests/BackEnd/CancelEventMailFormTest.php";s:4:"87c3";s:42:"tests/BackEnd/ConfirmEventMailFormTest.php";s:4:"9c53";s:32:"tests/BackEnd/EventsListTest.php";s:4:"c45e";s:31:"tests/BackEnd/FlexFormsTest.php";s:4:"d905";s:42:"tests/BackEnd/GeneralEventMailFormTest.php";s:4:"45d8";s:28:"tests/BackEnd/ModuleTest.php";s:4:"26de";s:36:"tests/BackEnd/OrganizersListTest.php";s:4:"9076";s:39:"tests/BackEnd/RegistrationsListTest.php";s:4:"d5db";s:34:"tests/BackEnd/SpeakersListTest.php";s:4:"24d5";s:33:"tests/BackEndExtJs/ModuleTest.php";s:4:"7935";s:44:"tests/BackEndExtJs/Ajax/AbstractListTest.php";s:4:"c362";s:42:"tests/BackEndExtJs/Ajax/DispatcherTest.php";s:4:"b383";s:42:"tests/BackEndExtJs/Ajax/EventsListTest.php";s:4:"f2dd";s:46:"tests/BackEndExtJs/Ajax/OrganizersListTest.php";s:4:"a3b3";s:49:"tests/BackEndExtJs/Ajax/RegistrationsListTest.php";s:4:"e104";s:44:"tests/BackEndExtJs/Ajax/SpeakersListTest.php";s:4:"3c0f";s:45:"tests/BackEndExtJs/Ajax/StateProviderTest.php";s:4:"9010";s:26:"tests/Bag/AbstractTest.php";s:4:"9933";s:26:"tests/Bag/CategoryTest.php";s:4:"0ae3";s:23:"tests/Bag/EventTest.php";s:4:"5144";s:27:"tests/Bag/OrganizerTest.php";s:4:"81cf";s:25:"tests/Bag/SpeakerTest.php";s:4:"daad";s:33:"tests/BagBuilder/AbstractTest.php";s:4:"ab21";s:33:"tests/BagBuilder/CategoryTest.php";s:4:"9c91";s:30:"tests/BagBuilder/EventTest.php";s:4:"da79";s:34:"tests/BagBuilder/OrganizerTest.php";s:4:"1356";s:37:"tests/BagBuilder/RegistrationTest.php";s:4:"66ee";s:32:"tests/BagBuilder/SpeakerTest.php";s:4:"6be2";s:35:"tests/FrontEnd/CategoryListTest.php";s:4:"1acd";s:32:"tests/FrontEnd/CountdownTest.php";s:4:"ef71";s:40:"tests/FrontEnd/DefaultControllerTest.php";s:4:"fa15";s:29:"tests/FrontEnd/EditorTest.php";s:4:"7337";s:34:"tests/FrontEnd/EventEditorTest.php";s:4:"3e7b";s:36:"tests/FrontEnd/EventHeadlineTest.php";s:4:"5a22";s:35:"tests/FrontEnd/PublishEventTest.php";s:4:"b114";s:39:"tests/FrontEnd/RegistrationFormTest.php";s:4:"acd1";s:40:"tests/FrontEnd/RegistrationsListTest.php";s:4:"a05f";s:39:"tests/FrontEnd/RequirementsListTest.php";s:4:"b72d";s:37:"tests/FrontEnd/SelectorWidgetTest.php";s:4:"b15f";s:34:"tests/FrontEnd/TestingViewTest.php";s:4:"c07f";s:37:"tests/Mapper/BackEndUserGroupTest.php";s:4:"0aa3";s:32:"tests/Mapper/BackEndUserTest.php";s:4:"d927";s:29:"tests/Mapper/CategoryTest.php";s:4:"479c";s:29:"tests/Mapper/CheckboxTest.php";s:4:"b119";s:30:"tests/Mapper/EventDateTest.php";s:4:"508d";s:26:"tests/Mapper/EventTest.php";s:4:"8b7a";s:31:"tests/Mapper/EventTopicTest.php";s:4:"7c06";s:30:"tests/Mapper/EventTypeTest.php";s:4:"c6bd";s:25:"tests/Mapper/FoodTest.php";s:4:"17e3";s:38:"tests/Mapper/FrontEndUserGroupTest.php";s:4:"f665";s:33:"tests/Mapper/FrontEndUserTest.php";s:4:"878d";s:28:"tests/Mapper/LodgingTest.php";s:4:"319c";s:30:"tests/Mapper/OrganizerTest.php";s:4:"99d6";s:34:"tests/Mapper/PaymentMethodTest.php";s:4:"25e5";s:26:"tests/Mapper/PlaceTest.php";s:4:"a59e";s:33:"tests/Mapper/RegistrationTest.php";s:4:"35d5";s:32:"tests/Mapper/SingleEventTest.php";s:4:"7a1b";s:26:"tests/Mapper/SkillTest.php";s:4:"b529";s:28:"tests/Mapper/SpeakerTest.php";s:4:"045a";s:32:"tests/Mapper/TargetGroupTest.php";s:4:"98ca";s:29:"tests/Mapper/TimeSlotTest.php";s:4:"912d";s:36:"tests/Model/AbstractTimeSpanTest.php";s:4:"2af6";s:36:"tests/Model/BackEndUserGroupTest.php";s:4:"3662";s:31:"tests/Model/BackEndUserTest.php";s:4:"6a7c";s:28:"tests/Model/CategoryTest.php";s:4:"9375";s:28:"tests/Model/CheckboxTest.php";s:4:"6576";s:29:"tests/Model/EventDateTest.php";s:4:"5ce8";s:25:"tests/Model/EventTest.php";s:4:"9b16";s:30:"tests/Model/EventTopicTest.php";s:4:"8a22";s:29:"tests/Model/EventTypeTest.php";s:4:"628a";s:24:"tests/Model/FoodTest.php";s:4:"6de3";s:37:"tests/Model/FrontEndUserGroupTest.php";s:4:"ae41";s:32:"tests/Model/FrontEndUserTest.php";s:4:"4750";s:27:"tests/Model/LodgingTest.php";s:4:"6da9";s:29:"tests/Model/OrganizerTest.php";s:4:"bab7";s:33:"tests/Model/PaymentMethodTest.php";s:4:"4c25";s:25:"tests/Model/PlaceTest.php";s:4:"3320";s:32:"tests/Model/RegistrationTest.php";s:4:"98fc";s:31:"tests/Model/SingleEventTest.php";s:4:"5276";s:25:"tests/Model/SkillTest.php";s:4:"4d54";s:27:"tests/Model/SpeakerTest.php";s:4:"7b94";s:31:"tests/Model/TargetGroupTest.php";s:4:"2097";s:28:"tests/Model/TimeSlotTest.php";s:4:"b5b5";s:31:"tests/OldModel/AbstractTest.php";s:4:"c5d0";s:31:"tests/OldModel/CategoryTest.php";s:4:"0204";s:28:"tests/OldModel/EventTest.php";s:4:"7105";s:32:"tests/OldModel/OrganizerTest.php";s:4:"01b8";s:35:"tests/OldModel/RegistrationTest.php";s:4:"17ec";s:30:"tests/OldModel/SpeakerTest.php";s:4:"2021";s:31:"tests/OldModel/TimeSlotTest.php";s:4:"8cfd";s:31:"tests/OldModel/TimespanTest.php";s:4:"53f8";s:37:"tests/Service/EMailSalutationTest.php";s:4:"70fc";s:41:"tests/Service/RegistrationManagerTest.php";s:4:"11c1";s:43:"tests/Service/SingleViewLinkBuilderTest.php";s:4:"e5f8";s:30:"tests/cli/MailNotifierTest.php";s:4:"8c43";s:54:"tests/fixtures/class.tx_seminars_registrationchild.php";s:4:"e2f1";s:49:"tests/fixtures/class.tx_seminars_seminarchild.php";s:4:"f0dd";s:67:"tests/fixtures/class.tx_seminars_tests_fixtures_TestingTimeSpan.php";s:4:"5008";s:50:"tests/fixtures/class.tx_seminars_timeslotchild.php";s:4:"d6d1";s:50:"tests/fixtures/class.tx_seminars_timespanchild.php";s:4:"af1e";s:28:"tests/fixtures/locallang.xml";s:4:"182e";s:47:"tests/fixtures/BackEnd/TestingEventMailForm.php";s:4:"cc3b";s:45:"tests/fixtures/BackEndExtJs/TestingModule.php";s:4:"4c0d";s:56:"tests/fixtures/BackEndExtJs/Ajax/TestingAbstractList.php";s:4:"4a42";s:54:"tests/fixtures/BackEndExtJs/Ajax/TestingEventsList.php";s:4:"7e0e";s:58:"tests/fixtures/BackEndExtJs/Ajax/TestingOrganizersList.php";s:4:"d6f7";s:61:"tests/fixtures/BackEndExtJs/Ajax/TestingRegistrationsList.php";s:4:"9d5a";s:56:"tests/fixtures/BackEndExtJs/Ajax/TestingSpeakersList.php";s:4:"3b92";s:30:"tests/fixtures/Bag/Testing.php";s:4:"559e";s:43:"tests/fixtures/BagBuilder/BrokenTesting.php";s:4:"511b";s:37:"tests/fixtures/BagBuilder/Testing.php";s:4:"879d";s:39:"tests/fixtures/FrontEnd/TestingView.php";s:4:"93d1";s:35:"tests/fixtures/OldModel/Testing.php";s:4:"be57";s:55:"tests/fixtures/Service/TestingSingleViewLinkBuilder.php";s:4:"b26c";s:21:"tests/pi2/pi2Test.php";s:4:"3fc3";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.3.0-0.0.0',
			'cms' => '',
			'css_styled_content' => '',
			'oelib' => '0.7.61-',
			'ameos_formidable' => '1.1.0-1.9.99',
			'static_info_tables' => '2.1.0-',
			'static_info_tables_taxes' => '',
		),
		'conflicts' => array(
			'dbal' => '',
			'sourceopt' => '',
		),
		'suggests' => array(
			'onetimeaccount' => '',
			'sr_feuser_register' => '',
		),
	),
	'suggests' => array(
		'onetimeaccount' => '',
		'sr_feuser_register' => '',
	),
);

?>