<?php
namespace DmitryDulepov\Realurl\Controller;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Dmitry Dulepov (dmitry.dulepov@gmail.com)
 *  All rights reserved
 *
 *  You may not remove or change the name of the author above. See:
 *  http://www.gnu.org/licenses/gpl-faq.html#IWantCredit
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * This class provides a controller for the Backend module of RealURL.
 *
 * @author Dmitry Dulepov <dmitry.dulepov@gmail.com>
 */
abstract class BackendModuleController extends ActionController {

	/** @var int */
	protected $currentPageId = 0;

	/** @var \TYPO3\CMS\Core\Database\DatabaseConnection */
	protected $databaseConnection;

	/** @var string[] */
	protected $excludedArgments = array();

	/** @var bool */
	static private $forwardedAction = false;

	/**
	 * Forwards the request to the last active action.
	 *
	 * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
	 */
	protected function forwardToLastAction() {
		$moduleData = BackendUtility::getModuleData(
			array('controller' => '', 'action' => ''),
			array(),
			'tx_realurl_web_realurlrealurl'
		);
		if (is_array($moduleData) && $moduleData['controller'] !== '' && $moduleData['action'] !== '') {
			self::$forwardedAction = true;
			$this->forward($moduleData['action'], $moduleData['controller']);
		}
	}

	/**
	 * Makes action name from the current action method name.
	 *
	 * @return string
	 */
	protected function getActionName() {
		return substr($this->actionMethodName, 0, -6);
	}

	/**
	 * Makes controller name from the controller class name.
	 *
	 * @return mixed
	 */
	protected function getControllerName() {
		return preg_replace('/^.*\\\([^\\\]+)Controller$/', '\1', get_class($this));
	}

	/**
	 * Initializes all actions.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		$this->currentPageId = (int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('id');
		$this->databaseConnection = $GLOBALS['TYPO3_DB'];

		// Fix pagers
		$arguments = GeneralUtility::_GPmerged('tx_realurl_web_realurlrealurl');
		if ($arguments && is_array($arguments)) {
			if (isset($arguments['action']) && isset($arguments['controller'])) {
				$this->storeLastAction();
			}
			foreach ($arguments as $argumentKey => $argumentValue) {
				if ($argumentValue) {
					if (!in_array($argumentKey, $this->excludedArgments)) {
						GeneralUtility::_GETset($argumentValue, 'tx_realurl_web_realurlrealurl|' . $argumentKey);
					}
					else {
						GeneralUtility::_GETset('', 'tx_realurl_web_realurlrealurl|' . $argumentKey);
					}
				}
			}
		}
		elseif (!self::$forwardedAction) {
			$this->forwardToLastAction();
		}

		parent::initializeAction();
	}

	/**
	 * Stores information about the last action of the module.
	 */
	protected function storeLastAction() {
		BackendUtility::getModuleData(
			array('controller' => '', 'action' => ''),
			array('controller' => $this->getControllerName(), 'action' => $this->getActionName()),
			'tx_realurl_web_realurlrealurl'
		);
	}
}
