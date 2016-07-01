<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package member_plus
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

/**
 * Add the tl_content table to members module
 */
$GLOBALS['BE_MOD']['accounts']['member']['tables'][] = 'tl_content';

/**
 * Content elements
 */
$GLOBALS['TL_CTE']['includes']['memberlist'] = '\HeimrichHannot\MemberPlus\ContentMemberlist';

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_registration_plus'] = '\HeimrichHannot\MemberPlus\MemberRegistrationPlusForm';

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['user']['memberreader'] = '\HeimrichHannot\MemberPlus\ModuleMemberReader';
$GLOBALS['FE_MOD']['user']['loginregistration'] = '\HeimrichHannot\MemberPlus\ModuleLoginRegistration';
$GLOBALS['FE_MOD']['user']['registration_plus'] = '\HeimrichHannot\MemberPlus\ModuleRegistrationPlus';
$GLOBALS['FE_MOD']['user']['member_messages'] = '\HeimrichHannot\MemberPlus\ModuleMemberMessages';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = array('\HeimrichHannot\MemberPlus\Hooks', 'getPageIdFromUrlHook');
$GLOBALS['TL_HOOKS']['generateBreadcrumb'][] = array('\HeimrichHannot\MemberPlus\Hooks', 'generateBreadcrumbHook');
$GLOBALS['TL_HOOKS']['activateAccount'][] = array('\HeimrichHannot\MemberPlus\Hooks', 'activateAccountHook');

/**
 * Constants
 */
define('MEMBER_ACTIVATION_ACTIVATED_FIELD_PREFIX', 'ACTIVATED:');

/**
 * Notifications
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_text'][] = 'activation';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_html'][] = 'activation';