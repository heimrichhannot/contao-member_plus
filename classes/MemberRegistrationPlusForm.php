<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package member_plus
 * @author  Oliver Janke <o.janke@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\MemberPlus;


class MemberRegistrationPlusForm extends \HeimrichHannot\FormHybrid\Form
{
	protected $strTemplate = 'formhybrid_registration_plus';
	
	public function __construct($objModule, $intId = 0)
	{
		$this->strPalette = 'default';
		$this->strMethod  = FORMHYBRID_METHOD_POST;
		
		parent::__construct($objModule, $intId);
	}
	
	
	public function modifyDC(&$arrDca = null)
	{
		if (!$this->objModule->disableCaptcha) {
			$this->addEditableField('captcha', $this->dca['fields']['captcha']);
		}
		
		// HOOK: send insert ID and user data
		if (isset($GLOBALS['TL_HOOKS']['modifyDCRegistrationPlusForm']) && is_array($GLOBALS['TL_HOOKS']['modifyDCRegistrationPlusForm'])) {
			foreach ($GLOBALS['TL_HOOKS']['modifyDCRegistrationPlusForm'] as $callback) {
				$this->import($callback[0]);
				$this->{$callback[0]}->{$callback[1]}($this->dca, $this->objModule);
			}
		}
	}

//	protected function initialize() {
//		parent::initialize();
//
//		$this->objActiveRecord->username = $this->objActiveRecord->email;
//		$this->objActiveRecord->save();
//	}
	
	protected function setDefaults($arrDca = [])
	{
		parent::setDefaults();
		
		$this->objActiveRecord->login = true;
	}
	
	protected function modifyVersion($objVersion)
	{
		$objVersion->setUsername($this->objActiveRecord->email);
		$objVersion->setEditUrl('contao/main.php?do=member&act=edit&id=' . $this->objActiveRecord->id . '&rt=' . REQUEST_TOKEN);
		
		return $objVersion;
	}
	
	protected function onSubmitCallback(\DataContainer $dc)
	{
		// HOOK: send insert ID and user data
		if (isset($GLOBALS['TL_HOOKS']['preRegistration']) && is_array($GLOBALS['TL_HOOKS']['preRegistration'])) {
			foreach ($GLOBALS['TL_HOOKS']['preRegistration'] as $callback) {
				$this->import($callback[0]);
				$this->{$callback[0]}->{$callback[1]}($dc->activeRecord->id, $dc->activeRecord, $this->objModule);
			}
		}
		
		$objMember = \MemberModel::findByPk($dc->activeRecord->id);
		
		$objMember->login      = $this->objModule->reg_allowLogin;
		$objMember->activation = md5(uniqid(mt_rand(), true));
		$objMember->dateAdded  = $dc->activeRecord->tstamp;
		
		// Set default groups
		if (empty($objMember->groups)) {
			$objMember->groups = $this->objModule->reg_groups;
		}
		
		// Disable account
		$objMember->disable = 1;
		
		$objMember->save();
		
		if ($this->objModule->reg_activate_plus) {
			$this->formHybridSendConfirmationViaEmail = true;
		}
		
		// HOOK: send insert ID and user data
		if (isset($GLOBALS['TL_HOOKS']['createNewUser']) && is_array($GLOBALS['TL_HOOKS']['createNewUser'])) {
			foreach ($GLOBALS['TL_HOOKS']['createNewUser'] as $callback) {
				$this->import($callback[0]);
				$this->{$callback[0]}->{$callback[1]}($objMember->id, $objMember->row(), $this->objModule);
			}
		}

//		$this->setReset(false); // debug - stay on current page
	}
	
	protected function afterSubmitCallback(\DataContainer $dc)
	{
		if (($objTarget = \PageModel::findByPk($this->objModule->jumpTo)) !== null) {
			\Controller::redirect(\Controller::generateFrontendUrl($objTarget->row()));
		}
	}
	
	protected function prepareSubmissionData()
	{
		$arrSubmissionData = parent::prepareSubmissionData();
		
		$arrSubmissionData['domain']     = \Idna::decode(\Environment::get('host'));
		$arrSubmissionData['activation'] = \Idna::decode(\Environment::get('base')) . \Environment::get('request') . ((\Config::get('disableAlias')
																													   || strpos(
																															  \Environment::get(
																																  'request'
																															  ),
																															  '?'
																														  ) !== false) ? '&' : '?')
										   . 'token=' . $this->activeRecord->activation;
		
		if (in_array('newsletter', \ModuleLoader::getActive())) {
			// Replace the wildcard
			if (!empty($this->objModel->newsletter)) {
				$objChannels = \NewsletterChannelModel::findByIds($this->activeRecord->newsletter);
				
				if ($objChannels !== null) {
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
		if ($this->getFields()) {
			return $this->arrEditable;
		}
		
		return [];
	}
	
	protected function compile()
	{
	}
	
}


