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


	protected function modifyDC()
	{
		if(!$this->objModule->disableCaptcha)
		{
			$this->addEditableField('captcha', $this->dca['fields']['captcha']);
		}
	}

	protected function onSubmitCallback(\DataContainer $dc) {

		$objMember = \MemberModel::findByPk($dc->activeRecord->id);

		$objMember->login = $this->objModule->reg_allowLogin;
		$objMember->activation = md5(uniqid(mt_rand(), true));
		$objMember->dateAdded = $this->objModel->tstamp;

		// Set default groups
		if (empty($objMember->groups))
		{
			$objMember->groups = $this->objModule->reg_groups;
		}

		// Disable account
		$objMember->disable = 1;


		if (in_array('newsletter', \ModuleLoader::getActive()))
		{
			// Make sure newsletter is an array
			if (empty($objMember->newsletter))
			{
				if ($objMember->newsletter != '')
				{
					$objMember->newsletter = array($objMember->newsletter);
				}
				else
				{
					$objMember->newsletter = array();
				}
			}
		}

		$objMember->save();

		if($this->objModule->reg_activate_plus)
		{
			$this->formHybridSendConfirmationViaEmail = true;
		}

//		$this->setReset(false); // debug
	}

	protected function prepareSubmissionData()
	{
		$arrSubmissionData = parent::prepareSubmissionData();

		$arrSubmissionData['domain'] = \Idna::decode(\Environment::get('host'));
		$arrSubmissionData['activation'] = \Idna::decode(\Environment::get('base')) . \Environment::get('request') . ((\Config::get('disableAlias') || strpos(\Environment::get('request'), '?') !== false) ? '&' : '?') . 'token=' . $this->activeRecord->activation;

		if (in_array('newsletter', \ModuleLoader::getActive()))
		{
			// Replace the wildcard
			if (!empty($this->objModel->newsletter))
			{
				$objChannels = \NewsletterChannelModel::findByIds($this->activeRecord->newsletter);

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