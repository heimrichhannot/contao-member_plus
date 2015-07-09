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


class ModuleRegistrationPlus extends \ModuleRegistration
{
	protected $strTemplate = 'mod_registration_plus';

	protected $objForm;

	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate           = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### '.utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['registration_plus'][0]).' ###';
			$objTemplate->title    = $this->headline;
			$objTemplate->id       = $this->id;
			$objTemplate->link     = $this->name;
			$objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

			return $objTemplate->parse();
		}

		$this->objForm = new MemberRegistrationPlusForm($this->objModel);
		$this->editable = $this->objForm->getEditableFields();

		// Return if there are no editable fields
		if (!is_array($this->editable) || empty($this->editable)) {
			return '';
		}

		return parent::generate();
	}

	protected function compile()
	{
		// Activate account
		if (\Input::get('token') != '')
		{
			$this->activateAcount();

			return;
		}

		$this->Template->form = $this->objForm->generate();
	}

}