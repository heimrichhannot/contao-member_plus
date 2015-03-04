<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package member_plus
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$dc = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Palettes
 */
$dc['palettes']['memberreader'] = '{title_legend},name,headline,type;{config_legend},mlGroups,mlTemplate,mlLoadContent;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrFields = array
(
	'mlGroups'      => array
	(
		'label'      => &$GLOBALS['TL_LANG']['tl_module']['mlGroups'],
		'exclude'    => true,
		'inputType'  => 'checkboxWizard',
		'foreignKey' => 'tl_member_group.name',
		'eval'       => array('mandatory' => true, 'multiple' => true),
		'sql'        => "blob NULL",
		'relation'   => array('type' => 'hasMany', 'load' => 'lazy')
	),
	'mlTemplate'    => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['mlTemplate'],
		'default'          => 'memberlist_full',
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => array('tl_module_member_plus', 'getMemberlistTemplates'),
		'eval'             => array('tl_class' => 'w50'),
		'sql'              => "varchar(64) NOT NULL default ''"
	),
	'mlLoadContent' => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['mlLoadContent'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('tl_class' => 'w50 m12'),
		'sql'       => "char(1) NOT NULL default ''"
	)
);

$dc['fields'] = array_merge($dc['fields'], $arrFields);

class tl_module_member_plus extends \Backend
{

	/**
	 * Return all news templates as array
	 * @return array
	 */
	public function getMemberlistTemplates()
	{
		return $this->getTemplateGroup('memberlist_');
	}
}