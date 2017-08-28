<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package member_plus
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$dc = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Palettes
 */
array_insert(
    $dc['palettes']['__selector__'],
    0,
    ['mlAddCustomDummyImages', 'mlSkipFields', 'reg_activate_plus']
); // bug? mustn't be inserted after type selector

$dc['palettes']['memberreader'] =
    '{title_legend},name,headline,type;{config_legend},mlGroups,mlTemplate,mlLoadContent;{image_legend:hide},imgSize,mlDisableImages,mlDisableDummyImages,mlAddCustomDummyImages,mlSkipFields;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$dc['palettes']['loginregistration'] =
    '{title_legend},name,headline,type;{config_legend},autologin,allowedMailDomains,showAllowedDomains;{register_legend},reg_groups,reg_allowLogin,reg_assignDir,reg_activate;{redirect_legend},jumpTo,redirectBack,redirectPermanent;{template_legend:hide},cols;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$dc['palettes']['login_registration_plus'] =
    '{title_legend},name,headline,type;
	{config_legend},formHybridDataContainer,formHybridPalette,formHybridEditable,formHybridAddEditableRequired,formHybridTemplate,formHybridCustomSubTemplates,formHybridCssClass,formHybridAddDefaultValues,disableCaptcha,bypassPasswordConfirm,newsletters;
	{account_legend},reg_groups,reg_allowLogin;
	{email_legend:hide},reg_jumpTo,formHybridSendConfirmationAsNotification,formHybridSendSubmissionAsNotification,formHybridSendSubmissionViaEmail;
	{template_legend:hide},customTpl;{redirect_legend},jumpTo, redirectLogin;
	{protected_legend:hide},protected;
	{expert_legend:hide},guests';


$dc['palettes']['registration_plus'] =
    '{title_legend},name,headline,type;
	{config_legend},formHybridDataContainer,formHybridPalette,formHybridEditable,formHybridAddEditableRequired,formHybridTemplate,formHybridCustomSubTemplates,formHybridAsync,formHybridCssClass,formHybridAddDefaultValues,disableCaptcha,newsletters;
	{account_legend},reg_groups,reg_allowLogin;
	{message_legend},formHybridSuccessMessage;
	{email_legend:hide},reg_jumpTo,formHybridSendConfirmationAsNotification,reg_activate_plus,formHybridSendSubmissionAsNotification,formHybridSendSubmissionViaEmail;
	{template_legend:hide},customTpl;{redirect_legend},jumpTo,
	{protected_legend:hide},protected;
	{expert_legend:hide},guests';


$dc['palettes']['member_messages'] =
    '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

/**
 * Subpalettes
 */
$dc['subpalettes']['mlAddCustomDummyImages'] = 'mlDummyImageMale,mlDummyImageFemale';
$dc['subpalettes']['mlSkipFields']           = 'mlFields';
$dc['subpalettes']['reg_activate_plus']      =
    'formHybridConfirmationMailRecipientField,formHybridConfirmationAvisotaMessage,formHybridConfirmationMailSender,formHybridConfirmationMailSubject,formHybridConfirmationMailText,formHybridConfirmationMailTemplate,formHybridConfirmationMailAttachment';
/**
 * Callbacks
 */
$dc['config']['onload_callback'][] = ['tl_module_member_plus', 'modifyPalette'];

/**
 * Fields
 */
$arrFields = [
    'mlGroups'               => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['mlGroups'],
        'exclude'    => true,
        'inputType'  => 'checkboxWizard',
        'foreignKey' => 'tl_member_group.name',
        'eval'       => ['mandatory' => true, 'multiple' => true],
        'sql'        => "blob NULL",
        'relation'   => ['type' => 'hasMany', 'load' => 'lazy']
    ],
    'mlTemplate'             => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['mlTemplate'],
        'default'          => 'memberlist_full',
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['tl_module_member_plus', 'getMemberlistTemplates'],
        'eval'             => ['tl_class' => 'w50'],
        'sql'              => "varchar(64) NOT NULL default ''"
    ],
    'mlImgSize'              => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['mlImgSize'],
        'exclude'   => true,
        'inputType' => 'imageSize',
        'options'   => System::getImageSizes(),
        'reference' => &$GLOBALS['TL_LANG']['MSC'],
        'eval'      => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
        'sql'       => "varchar(64) NOT NULL default ''"
    ],
    'mlLoadContent'          => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['mlLoadContent'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50 m12'],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'mlDisableImages'        => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['mlDisableImages'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50 m12'],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'mlDisableDummyImages'   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['mlDisableDummyImages'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50 m12'],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'mlAddCustomDummyImages' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['mlAddCustomDummyImages'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50 m12', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'mlDummyImageMale'       => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['mlDummyImageMale'],
        'exclude'   => true,
        'inputType' => 'fileTree',
        'eval'      => ['filesOnly' => true, 'fieldType' => 'radio', 'tl_class' => 'clr w50'],
        'sql'       => "binary(16) NULL"
    ],
    'mlDummyImageFemale'     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['mlDummyImageFemale'],
        'exclude'   => true,
        'inputType' => 'fileTree',
        'eval'      => ['filesOnly' => true, 'fieldType' => 'radio', 'tl_class' => 'w50'],
        'sql'       => "binary(16) NULL"
    ],
    'mlSkipFields'           => [
        'label'     => &$GLOBALS['TL_LANG']['tl_content']['mlSkipFields'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'mlFields'               => [
        'label'            => &$GLOBALS['TL_LANG']['tl_content']['mlFields'],
        'exclude'          => true,
        'inputType'        => 'checkbox',
        'options_callback' => ['tl_module_member_plus', 'getViewableMemberFields'],
        'eval'             => ['multiple' => true, 'tl_class' => 'clr',],
        'sql'              => "blob NULL",
    ],
    'allowedMailDomains'     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['allowedMailDomains'],
        'exclude'   => true,
        'inputType' => 'multiColumnWizard',
        'eval'      => [
            'columnFields' => [
                'domain' => [
                    'label'     => &$GLOBALS['TL_LANG']['tl_module']['allowedMailDomains']['domain'],
                    'exclude'   => true,
                    'inputType' => 'text',
                    'eval'      => ['style' => 'width: 600px'],
                ],
                'hide'   => [
                    'label'     => &$GLOBALS['TL_LANG']['tl_module']['allowedMailDomains']['hide'],
                    'exclude'   => true,
                    'inputType' => 'checkbox',
                    'eval'      => ['style' => 'width: 100px;'],
                ]
            ]
        ],
        'sql'       => "blob NULL"
    ],
    'showAllowedDomains'     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['showAllowedDomains'],
        'exclude'   => true,
        'default'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'long'],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'reg_activate_login'     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['reg_activate_login'],
        'exclude'   => true,
        'default'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'long'],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'redirectPermanent'      => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['redirectPermanent'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'long'],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'reg_activate_plus'      => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['reg_activate'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'redirectLogin'          => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['redirectLogin'],
        'exclude'    => true,
        'inputType'  => 'pageTree',
        'foreignKey' => 'tl_page.title',
        'eval'       => ['fieldType' => 'radio', 'tl_class' => 'w50 clr'],
        'sql'        => "int(10) unsigned NOT NULL default '0'",
        'relation'   => ['type' => 'hasOne', 'load' => 'lazy']
    ],
    'bypassPasswordConfirm'  => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['bypassPasswordConfirm'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50 clr'],
        'sql'       => "char(1) NOT NULL default ''"
    ],
];

$dc['fields'] = array_merge($dc['fields'], $arrFields);

class tl_module_member_plus extends \Backend
{

    /**
     * Return all news templates as array
     *
     * @return array
     */
    public function getMemberlistTemplates()
    {
        return $this->getTemplateGroup('memberlist_');
    }

    public function modifyPalette()
    {
        $objModule = \ModuleModel::findByPk(\Input::get('id'));
        $arrDc     = &$GLOBALS['TL_DCA']['tl_module'];

        // submission -> already done in formhybrid

        // confirmation
        $arrFieldsToHide = [
            'formHybridConfirmationMailSender',
            'formHybridConfirmationMailSubject',
            'formHybridConfirmationMailText',
            'formHybridConfirmationMailTemplate',
            'formHybridConfirmationMailAttachment'
        ];

        if (in_array('avisota-core', \ModuleLoader::getActive()) && $objModule->reg_activate_plus
            && $objModule->formHybridConfirmationAvisotaMessage
        ) {
            $arrDc['subpalettes']['reg_activate_plus'] = str_replace(
                $arrFieldsToHide,
                array_map(
                    function () {
                        return '';
                    },
                    $arrFieldsToHide
                ),
                $arrDc['subpalettes']['reg_activate_plus']
            );

            $arrDc['subpalettes']['reg_activate_plus'] = str_replace(
                'formHybridConfirmationAvisotaMessage',
                'formHybridConfirmationAvisotaMessage,formHybridConfirmationAvisotaSalutationGroup',
                $arrDc['subpalettes']['reg_activate_plus']
            );
        }
    }

    /**
     * Return all feViewable fields as array
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getViewableMemberFields(DataContainer $dc)
    {
        \Controller::loadDataContainer('tl_member');
        \Controller::loadLanguageFile('tl_member');

        $arrOptions = [];

        $arrFields = $GLOBALS['TL_DCA']['tl_member']['fields'];

        if (!is_array($arrFields) || empty($arrFields)) {
            return $arrOptions;
        }

        foreach ($arrFields as $strName => $arrData) {
            if (!isset($arrData['inputType'])) {
                continue;
            }

            if (!$arrData['eval']['feViewable']) {
                continue;
            }

            $arrOptions[$strName] = $arrData['label'][0];
        }

        return $arrOptions;
    }
}
