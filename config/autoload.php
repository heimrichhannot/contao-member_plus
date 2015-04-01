<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package Member_plus
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
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
	// Models
	'HeimrichHannot\MemberPlus\MemberPlusMemberModel' => 'system/modules/member_plus/models/MemberPlusMemberModel.php',

	// Modules
	'HeimrichHannot\MemberPlus\ModuleMemberReader'      => 'system/modules/member_plus/modules/ModuleMemberReader.php',
	'HeimrichHannot\MemberPlus\ModuleLoginRegistration' => 'system/modules/member_plus/modules/ModuleLoginRegistration.php',

	// Elements
	'HeimrichHannot\MemberPlus\ContentMemberlist'     => 'system/modules/member_plus/elements/ContentMemberlist.php',

	// Classes
	'HeimrichHannot\MemberPlus\MemberPlus'            => 'system/modules/member_plus/classes/MemberPlus.php',
	'HeimrichHannot\MemberPlus\Hooks'                 => 'system/modules/member_plus/classes/Hooks.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'memberlist_full'       => 'system/modules/member_plus/templates/memberlist',
	'memberlist_default'    => 'system/modules/member_plus/templates/memberlist',
	'mod_loginregistration' => 'system/modules/member_plus/templates/modules',
	'mod_memberreader'      => 'system/modules/member_plus/templates/modules',
	'ce_memberlist'         => 'system/modules/member_plus/templates/elements',
));
