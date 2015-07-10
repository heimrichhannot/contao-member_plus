<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package member_plus
 * @author Oliver Janke <o.janke@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\MemberPlus;


class MemberRegistrationPlusForm extends \HeimrichHannot\FormHybrid\Form
{
	protected $strTemplate = 'formhybrid_registration_plus';

	public function __construct($objModule)
	{
		$this->strPalette = 'default';
		$this->strMethod = FORMHYBRID_METHOD_POST;

		if(($objTarget = \PageModel::findByPk($objModule->jumpTo)) !== null)
		{
			$this->strAction = \Controller::generateFrontendUrl($objTarget->row());
		}

		parent::__construct($objModule);
	}

	protected function onSubmitCallback(\DataContainer $dc) {
		$this->objModel->login = $this->objModule->reg_allowLogin;
		$this->objModel->activation = md5(uniqid(mt_rand(), true));
		$this->objModel->dateAdded = $this->objModel->tstamp;

		// Set default groups
		if (empty($this->objModel->groups))
		{
			$this->objModel->groups = $this->objModule->reg_groups;
		}

		// Disable account
		$this->objModel->disable = 1;


		if (in_array('newsletter', \ModuleLoader::getActive()))
		{
			// Make sure newsletter is an array
			if (empty($this->objModel->newsletter))
			{
				if ($this->objModel->newsletter != '')
				{
					$this->objModel->newsletter = array($this->objModel->newsletter);
				}
				else
				{
					$this->objModel->newsletter = array();
				}
			}
		}

		$this->objModel->save();

		$dc->activeRecord = $this->objModel;

		if($this->objModule->reg_activate_plus)
		{
			$this->formHybridSendConfirmationViaEmail = true;
		}

		$this->clearInputs();
	}

	protected function prepareSubmissionData()
	{
		$arrSubmissionData = parent::prepareSubmissionData();

		$arrSubmissionData['domain'] = \Idna::decode(\Environment::get('host'));
		$arrSubmissionData['activation'] = \Idna::decode(\Environment::get('base')) . \Environment::get('request') . ((\Config::get('disableAlias') || strpos(\Environment::get('request'), '?') !== false) ? '&' : '?') . 'token=' . $this->objModel->activation;

		if (in_array('newsletter', \ModuleLoader::getActive()))
		{
			// Replace the wildcard
			if (!empty($this->objModel->newsletter))
			{
				$objChannels = \NewsletterChannelModel::findByIds($this->objModel->newsletter);

				if ($objChannels !== null)
				{
					$arrSubmissionData['channels'] = implode("\n", $objChannels->fetchEach('title'));
				}
			}

		}

		// Backwards compatibility
		$arrSubmissionData['channel'] = $arrSubmissionData['channels'];


		return $arrSubmissionData;
	}

	public function getEditableFields()
	{
		if($this->getFields())
		{
			return $this->arrEditable;
		}

		return array();
	}

	protected function compile() {}

}