<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'HeimrichHannot',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Modules
	'HeimrichHannot\MemberPlus\ModuleMemberMessages'        => 'system/modules/member_plus/modules/ModuleMemberMessages.php',
	'HeimrichHannot\MemberPlus\ModuleMemberReader'          => 'system/modules/member_plus/modules/ModuleMemberReader.php',
	'HeimrichHannot\MemberPlus\ModuleRegistrationPlus'      => 'system/modules/member_plus/modules/ModuleRegistrationPlus.php',
	'HeimrichHannot\MemberPlus\ModuleLoginRegistration'     => 'system/modules/member_plus/modules/ModuleLoginRegistration.php',

	// Elements
	'HeimrichHannot\MemberPlus\ContentMemberlist'           => 'system/modules/member_plus/elements/ContentMemberlist.php',

	// Models
	'HeimrichHannot\MemberPlus\MemberPlusMemberModel'       => 'system/modules/member_plus/models/MemberPlusMemberModel.php',

	// Classes
	'HeimrichHannot\MemberPlus\MemberPlusProtectedHomeDirs' => 'system/modules/member_plus/classes/MemberPlusProtectedHomeDirs.php',
	'HeimrichHannot\MemberPlus\MemberMessage'               => 'system/modules/member_plus/classes/MemberMessage.php',
	'HeimrichHannot\MemberPlus\Hooks'                       => 'system/modules/member_plus/classes/Hooks.php',
	'HeimrichHannot\MemberPlus\MemberPlus'                  => 'system/modules/member_plus/classes/MemberPlus.php',
	'HeimrichHannot\MemberPlus\MemberRegistrationPlusForm'  => 'system/modules/member_plus/classes/MemberRegistrationPlusForm.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_loginregistration'        => 'system/modules/member_plus/templates/modules',
	'mod_member_messages'          => 'system/modules/member_plus/templates/modules',
	'mod_memberreader'             => 'system/modules/member_plus/templates/modules',
	'mod_registration_plus'        => 'system/modules/member_plus/templates/modules',
	'memberlist_default'           => 'system/modules/member_plus/templates/memberlist',
	'memberlist_full'              => 'system/modules/member_plus/templates/memberlist',
	'ce_memberlist'                => 'system/modules/member_plus/templates/elements',
	'formhybrid_registration_plus' => 'system/modules/member_plus/templates/form',
));
