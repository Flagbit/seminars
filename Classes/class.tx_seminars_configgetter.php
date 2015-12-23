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
 * This class provides a way to access config values from plugin.tx_seminars to classes within FrontEnd/.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_configgetter extends tx_oelib_templatehelper {
	/** Same as class name */
	public $prefixId = 'tx_seminars_configgetter';

	/**
	 * faking $this->scriptRelPath so the locallang.xml file is found
	 *
	 * @var string
	 */
	public $scriptRelPath = 'locallang.xml';

	/**
	 * @var string the extension key
	 */
	public $extKey = 'seminars';

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->init();
	}
}