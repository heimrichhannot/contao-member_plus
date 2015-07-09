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

		$this->objModel->save();

		$dc->activeRecord = $this->objModel;

		if($this->objModule->reg_activate_plus)
		{
			$arrTokenData['domain'] = \Idna::decode(\Environment::get('host'));
			$arrTokenData['link'] = \Idna::decode(\Environment::get('base')) . \Environment::get('request') . ((\Config::get('disableAlias') || strpos(\Environment::get('request'), '?') !== false) ? '&' : '?') . 'token=' . $arrData['activation'];
			$this->formHybridSendConfirmationViaEmail = true;
		}
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