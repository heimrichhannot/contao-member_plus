<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package member_plus
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\MemberPlus;

define('MEMBER_MESSAGE_DANGER', 'MEMBER_DANGER');
define('MEMBER_MESSAGE_WARNING', 'MEMBER_WARNING');
define('MEMBER_MESSAGE_INFO', 'MEMBER_INFO');
define('MEMBER_MESSAGE_SUCCESS', 'MEMBER_SUCCESS');
define('MEMBER_MESSAGE_RAW', 'MEMBER_RAW');

class MemberMessage extends \Message
{
	/**
	 * Add an danger message
	 *
	 * @param string $strMessage The danger message
	 */
	public static function addDanger($strMessage)
	{
		static::add($strMessage, MEMBER_MESSAGE_DANGER);
	}


	/**
	 * Add a warning message
	 *
	 * @param string $strMessage The warning message
	 */
	public static function addWarning($strMessage)
	{
		static::add($strMessage, MEMBER_MESSAGE_WARNING);
	}


	/**
	 * Add a info message
	 *
	 * @param string $strMessage The info message
	 */
	public static function addInfo($strMessage)
	{
		static::add($strMessage, MEMBER_MESSAGE_INFO);
	}


	/**
	 * Add an success message
	 *
	 * @param string $strMessage The success message
	 */
	public static function addSuccess($strMessage)
	{
		static::add($strMessage, MEMBER_MESSAGE_SUCCESS);
	}


	/**
	 * Add a preformatted message
	 *
	 * @param string $strMessage The preformatted message
	 */
	public static function addRaw($strMessage)
	{
		static::add($strMessage, 'TL_RAW');
	}

	/**
	 * Return all messages as HTML
	 *
	 * @param boolean $blnDcLayout If true, the line breaks are different
	 * @param boolean $blnNoWrapper If true, there will be no wrapping DIV
	 *
	 * @return string The messages HTML markup
	 */
	public static function generate($blnDcLayout=false, $blnNoWrapper=false)
	{
		$strMessages = '';

		// Regular messages
		foreach (static::getTypes() as $strType)
		{
			if (!is_array($_SESSION[$strType]))
			{
				continue;
			}

			$strClass = strtolower(preg_replace('/member_/i', '', $strType));
			$_SESSION[$strType] = array_unique($_SESSION[$strType]);

			foreach ($_SESSION[$strType] as $strMessage)
			{
				if ($strType == MEMBER_MESSAGE_RAW)
				{
					$strMessages .= $strMessage;
				}
				else
				{
					$strMessages .= sprintf('<p class="alert alert-%s">%s</p>%s', $strClass, $strMessage, "\n");
				}

				unset($_SESSION[$strType]);
			}

			if (!$_POST)
			{
				$_SESSION[$strType] = array();
			}
		}

		$strMessages = trim($strMessages);

		// Wrapping container
		if (!$blnNoWrapper && $strMessages != '')
		{
			$strMessages = sprintf('%s<div class="member_message">%s%s%s</div>%s', ($blnDcLayout ? "\n\n" : "\n"), "\n", $strMessages, "\n", ($blnDcLayout ? '' : "\n"));
		}

		return $strMessages;
	}

	/**
	 * Clear all messages, or declared only
	 *
	 * @param array $arrTypes containing message valid types from getTypes that should be unset
	 */
	public static function clearMessages(array $arrTypes = array())
	{
		$arrTypes = array_intersect(static::getTypes(), $arrTypes);

		foreach (static::getTypes() as $strType)
		{
			if(!empty($arrTypes) && in_array($strType, $arrTypes))
			{
				unset($_SESSION[$strType]);
				continue;
			}

			unset($_SESSION[$strType]);
		}
	}


	/**
	 * Check if messages are present
	 *
	 * @return bool true if messages are present, otherwise false
	 */
	public static function hasMessages()
	{
		$hasMessages = false;

		foreach (static::getTypes() as $strType)
		{
			if (!is_array($_SESSION[$strType]))
			{
				continue;
			}

			$hasMessages = true;
			break;
		}

		return $hasMessages;
	}


	/**
	 * Return all available message types
	 *
	 * @return array An array of message types
	 */
	public static function getTypes()
	{
		return array(MEMBER_MESSAGE_DANGER, MEMBER_MESSAGE_WARNING, MEMBER_MESSAGE_INFO, MEMBER_MESSAGE_SUCCESS, MEMBER_MESSAGE_RAW);
	}
}