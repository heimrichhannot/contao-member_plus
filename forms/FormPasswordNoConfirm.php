<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 04.08.17
 * Time: 15:10
 */

namespace HeimrichHannot;

class FormPasswordNoConfirm extends \Widget
{
	protected $blnSubmitInput = true;
	
	protected $strTemplate = 'form_password_noConfirm';
	
	protected function validator($varInput)
	{
		$this->blnSubmitInput = false;
		
		if (!strlen($varInput) && (strlen($this->varValue) || !$this->mandatory))
		{
			return '';
		}
		
		if (utf8_strlen($varInput) < \Config::get('minPasswordLength'))
		{
			$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['passwordLength'], \Config::get('minPasswordLength')));
		}
		
		$varInput = parent::validator($varInput);
		
		if (!$this->hasErrors())
		{
			$this->blnSubmitInput = true;
			
			return \Encryption::hash($varInput);
		}
		
		return '';
	}
	
	public function generate()
	{
		return sprintf('<input type="password" name="%s" id="ctrl_%s" class="text password%s" value=""%s%s',
								$this->strName,
								$this->strId,
			(($this->strClass != '') ? ' ' . $this->strClass : ''),
								$this->getAttributes(),
								$this->strTagEnding) . $this->addSubmit();
	}
}