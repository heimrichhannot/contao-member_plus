<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package member_plus
 * @description Adds avisota functionality and offers to change the change password page
 * @author Dennis Patzer <d.patzer@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\MemberPlus;


use HeimrichHannot\FormHybrid\AvisotaHelper;

class ModulePassword extends \ModulePassword
{

	protected function sendPasswordLink($objMember)
	{
		$confirmationId = md5(uniqid(mt_rand(), true));

		// Store the confirmation ID
		$objMember = \MemberModel::findByPk($objMember->id);
		$objMember->activation = $confirmationId;
		$objMember->save();

		// Prepare the simple token data
		$arrData = $objMember->row();
		$arrData['domain'] = \Idna::decode(\Environment::get('host'));

		// Check whether there is a jumpTo page
		$arrData['link'] = \Idna::decode(\Environment::get('base')) . \Environment::get('request') . ((\Config::get('disableAlias') || strpos(\Environment::get('request'), '?') !== false) ? '&' : '?') . 'token=' . $confirmationId;

		if (($objJumpTo = $this->objModel->getRelated('changePasswordJumpTo')) !== null)
		{
			$arrData['link'] = \Idna::decode(\Environment::get('base')) . \Controller::generateFrontendUrl($objJumpTo->row(), '?token=' . $confirmationId);
		}

		// Send e-mail
		if ($this->avisotaMessage)
		{
			AvisotaHelper::sendAvisotaEMail($this->avisotaMessage, $objMember->email, $arrData, $this->avisotaSalutationGroup, AvisotaHelper::RECIPIENT_MODE_USE_MEMBER_DATA);
		}
		else
		{
			$objEmail = new \Email();

			$objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
			$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
			$objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['passwordSubject'], \Idna::decode(\Environment::get('host')));
			$objEmail->text = \StringUtil::parseSimpleTokens($this->reg_password, $arrData);
			$objEmail->sendTo($objMember->email);
		}

		$this->log('A new password has been requested for user ID ' . $objMember->id . ' (' . $objMember->email . ')', __METHOD__, TL_ACCESS);

		// Check whether there is a jumpTo page
		if (($objJumpTo = $this->objModel->getRelated('jumpTo')) !== null)
		{
			$this->jumpToOrReload($objJumpTo->row());
		}

		$this->reload();
	}

}
