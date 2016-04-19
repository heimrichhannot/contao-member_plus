<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package member_plus
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

\Controller::loadLanguageFile('tl_content');
\Controller::loadDataContainer('tl_content');

$arrDca = &$GLOBALS['TL_DCA']['tl_member'];

/**
 * Add operations to tl_member
 */
$GLOBALS['TL_DCA']['tl_member']['list']['operations']['content'] = array
(
	'label'               => &$GLOBALS['TL_LANG']['tl_member']['content'],
	'href'                => 'table=tl_content',
	'icon'                => 'article.gif'
);

/**
 * Palettes
 */

// selector
$arrDca['palettes']['__selector__'][] = 'addImage';

// title
$arrDca['palettes']['default'] = '{title_legend},headline;' . $arrDca['palettes']['default'];
// alias - must be invoked after firstname & title, otherwise not available in save_callback
$arrDca['palettes']['default'] = str_replace('lastname', 'lastname,alias', $arrDca['palettes']['default']);
// academicTitle
$arrDca['palettes']['default'] = str_replace('firstname', 'academicTitle,firstname', $arrDca['palettes']['default']);
// personal
$arrDca['palettes']['default'] = str_replace('gender', 'gender,position', $arrDca['palettes']['default']);
// address
$arrDca['palettes']['default'] = str_replace('country', 'country,addressText', $arrDca['palettes']['default']);
// image
$arrDca['palettes']['default'] = str_replace('assignDir', 'assignDir;{image_legend},addImage;', $arrDca['palettes']['default']);

/**
 * Subpalettes
 */

$arrDca['subpalettes']['addImage'] = 'singleSRC,alt,title,size,imagemargin,imageUrl,fullsize,caption,floating';

/**
 * Fields
 */

$arrFields = array
(
	'headline'      => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_member']['headline'],
		'exclude'   => true,
		'filter'    => true,
		'sorting'   => true,
		'inputType' => 'text',
		'eval'      => array('feEditable' => true, 'feViewable' => true, 'feGroup' => 'title', 'tl_class' => 'w50'),
		'sql'       => "varchar(255) NOT NULL default ''"
	),
	'alias' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_member']['alias'],
		'exclude'                 => true,
		'search'                  => true,
		'inputType'               => 'text',
		'eval'                    => array('feEditable' => false, 'feViewable' => false, 'rgxp'=>'alias', 'unique'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
		'save_callback' => array
		(
			array('tl_member_plus', 'generateAlias')
		),
		'sql'                     => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
	),
	'academicTitle' => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_member']['academicTitle'],
		'exclude'   => true,
		'filter'    => true,
		'sorting'   => true,
		'inputType' => 'text',
		'eval'      => array('feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'),
		'sql'       => "varchar(255) NOT NULL default ''"
	),
	'position'      => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_member']['position'],
		'exclude'   => true,
		'filter'    => true,
		'sorting'   => true,
		'inputType' => 'text',
		'eval'      => array('feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'),
		'sql'       => "varchar(255) NOT NULL default ''"
	),
	'addressText'   => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_member']['addressText'],
		'exclude'     => true,
		'search'      => true,
		'inputType'   => 'textarea',
		'eval'        => array('feEditable' => true, 'feViewable' => true, 'feGroup' => 'address', 'rte' => 'tinyMCE', 'tl_class' => 'clr', 'helpwizard' => true),
		'explanation' => 'insertTags',
		'sql'         => "mediumtext NULL"
	),
	'addImage'      => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_member']['addImage'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true),
		'sql'       => "char(1) NOT NULL default ''"
	),
	'singleSRC'     => $GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC'],
	'alt'           => $GLOBALS['TL_DCA']['tl_content']['fields']['alt'],
	'title'         => $GLOBALS['TL_DCA']['tl_content']['fields']['title'],
	'size'          => $GLOBALS['TL_DCA']['tl_content']['fields']['size'],
	'imagemargin'   => $GLOBALS['TL_DCA']['tl_content']['fields']['imagemargin'],
	'imageUrl'      => $GLOBALS['TL_DCA']['tl_content']['fields']['imageUrl'],
	'fullsize'      => $GLOBALS['TL_DCA']['tl_content']['fields']['fullsize'],
	'caption'       => $GLOBALS['TL_DCA']['tl_content']['fields']['caption'],
	'floating'      => $GLOBALS['TL_DCA']['tl_content']['fields']['floating'],
	'captcha' => array
	(
		'label'     => &$GLOBALS['TL_LANG']['MSC']['securityQuestion'],
		'exclude'   => true,
		'inputType' => 'captcha',
	)
);

$arrDca['fields'] = array_merge($arrDca['fields'], $arrFields);

$arrDca['fields']['firstname']['eval']['tl_class'] = 'w50 clr';

if (TL_MODE == 'BE') {
	$arrDca['fields']['email']['eval']['mandatory'] = false;
}

if(TL_MODE == 'FE')
{
	$arrDca['fields']['gender']['inputType'] = 'radio';
	$arrDca['fields']['gender']['eval']['includeBlankOption'] = false;
}

// increase activation field, otherwise MEMBER_ACTIVATION_ACTIVATED_FIELD_PREFIX will not fit in
$arrDca['fields']['activation']['sql'] = "varchar(64) NOT NULL default ''";

class tl_member_plus extends \Backend
{
	/**
	 * Auto-generate the member alias if it has not been set yet
	 * @param mixed
	 * @param \DataContainer
	 * @return string
	 * @throws \Exception
	 */
	public function generateAlias($varValue, DataContainer $objDc)
	{
		$autoAlias = false;

		// Generate alias if there is none
		if ($varValue == '')
		{
			$autoAlias = true;
            $arrTitle = \HeimrichHannot\MemberPlus\MemberPlus::getCombinedTitle($objDc->activeRecord);
			$varValue = standardize(\StringUtil::restoreBasicEntities($arrTitle));
		}

        $objAlias = \Database::getInstance()->prepare("SELECT id FROM tl_member WHERE alias=? AND id!=?")
            ->execute($varValue, $objDc->activeRecord->id);

        // Check whether the news alias exists
		if ($objAlias->numRows > 1 && !$autoAlias)
        {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

		// Add ID to alias
		if ($objAlias->numRows && $autoAlias)
		{
			$varValue .= '-' . $objDc->id;
		}

        return $varValue;
	}
}