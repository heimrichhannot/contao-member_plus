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

$dc = &$GLOBALS['TL_DCA']['tl_member'];

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
$dc['palettes']['__selector__'][] = 'addImage';

// title
$dc['palettes']['default'] = '{title_legend},headline;' . $dc['palettes']['default'];
// alias - must be invoked after firstname & title, otherwise not available in save_callback
$dc['palettes']['default'] = str_replace('lastname', 'lastname,alias', $dc['palettes']['default']);
// personal
$dc['palettes']['default'] = str_replace('gender', 'gender,academicTitle,position', $dc['palettes']['default']);
// address
$dc['palettes']['default'] = str_replace('country', 'country,addressText', $dc['palettes']['default']);
// image
$dc['palettes']['default'] = str_replace('assignDir', 'assignDir;{image_legend},addImage;', $dc['palettes']['default']);

/**
 * Subpalettes
 */

$dc['subpalettes']['addImage'] = 'singleSRC,alt,title,size,imagemargin,imageUrl,fullsize,caption,floating';

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
	'singleSRC'     => array
	(
		'label'         => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
		'exclude'       => true,
		'inputType'     => 'fileTree',
		'eval'          => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'),
		'sql'           => "binary(16) NULL",
		'load_callback' => array
		(
			array('tl_content', 'setSingleSrcFlags')
		),
		'save_callback' => array
		(
			array('tl_content', 'storeFileMetaInformation')
		)
	),
	'alt'           => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['alt'],
		'exclude'   => true,
		'search'    => true,
		'inputType' => 'text',
		'eval'      => array('maxlength' => 255, 'tl_class' => 'w50'),
		'sql'       => "varchar(255) NOT NULL default ''"
	),
	'title'         => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['title'],
		'exclude'   => true,
		'search'    => true,
		'inputType' => 'text',
		'eval'      => array('maxlength' => 255, 'tl_class' => 'w50'),
		'sql'       => "varchar(255) NOT NULL default ''"
	),
	'size'          => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['size'],
		'exclude'   => true,
		'inputType' => 'imageSize',
		'options'   => System::getImageSizes(),
		'reference' => &$GLOBALS['TL_LANG']['MSC'],
		'eval'      => array('rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'),
		'sql'       => "varchar(64) NOT NULL default ''"
	),
	'imagemargin'   => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['imagemargin'],
		'exclude'   => true,
		'inputType' => 'trbl',
		'options'   => $GLOBALS['TL_CSS_UNITS'],
		'eval'      => array('includeBlankOption' => true, 'tl_class' => 'w50'),
		'sql'       => "varchar(128) NOT NULL default ''"
	),
	'imageUrl'      => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['imageUrl'],
		'exclude'   => true,
		'search'    => true,
		'inputType' => 'text',
		'eval'      => array('rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50 wizard'),
		'wizard'    => array
		(
			array('tl_content', 'pagePicker')
		),
		'sql'       => "varchar(255) NOT NULL default ''"
	),
	'fullsize'      => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['fullsize'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('tl_class' => 'w50 m12'),
		'sql'       => "char(1) NOT NULL default ''"
	),
	'caption'       => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['caption'],
		'exclude'   => true,
		'search'    => true,
		'inputType' => 'text',
		'eval'      => array('maxlength' => 255, 'allowHtml' => true, 'tl_class' => 'w50'),
		'sql'       => "varchar(255) NOT NULL default ''"
	),
	'floating'      => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_content']['floating'],
		'default'   => 'above',
		'exclude'   => true,
		'inputType' => 'radioTable',
		'options'   => array('above', 'left', 'right', 'below'),
		'eval'      => array('cols' => 4, 'tl_class' => 'w50'),
		'reference' => &$GLOBALS['TL_LANG']['MSC'],
		'sql'       => "varchar(32) NOT NULL default ''"
	),
);

$dc['fields'] = array_merge($dc['fields'], $arrFields);

// change fields only in backend
if (TL_MODE == 'BE') {
	$dc['fields']['email']['eval']['mandatory'] = false;
}

class tl_member_plus extends \Backend
{
	/**
	 * Auto-generate the member alias if it has not been set yet
	 * @param mixed
	 * @param \DataContainer
	 * @return string
	 * @throws \Exception
	 */
	public function generateAlias($varValue, DataContainer $dc)
	{
		$autoAlias = false;

		// Generate alias if there is none
		if ($varValue == '')
		{
			$autoAlias = true;
            $arrTitle = \HeimrichHannot\MemberPlus\MemberPlus::getCombinedTitle($dc->activeRecord);
			$varValue = standardize(String::restoreBasicEntities($arrTitle));
		}

        $objAlias = $this->Database->prepare("SELECT id FROM tl_member WHERE alias=? AND id!=?")
            ->execute($varValue, $dc->activeRecord->id);

        // Check whether the news alias exists
		if ($objAlias->numRows > 1 && !$autoAlias)
        {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

		// Add ID to alias
		if ($objAlias->numRows && $autoAlias)
		{
			$varValue .= '-' . $dc->id;
		}

        return $varValue;
	}
}