<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

\Controller::loadLanguageFile('tl_fieldpalette');
\Controller::loadDataContainer('tl_fieldpalette');
\Controller::loadDataContainer('tl_member');

$GLOBALS['TL_DCA']['tl_member_address'] = $GLOBALS['TL_DCA']['tl_fieldpalette'];
$dca                                    = &$GLOBALS['TL_DCA']['tl_member_address'];

$fields = [
    'company'     => $GLOBALS['TL_DCA']['tl_member']['fields']['company'],
    'phone'       => $GLOBALS['TL_DCA']['tl_member']['fields']['phone'],
    'fax'         => $GLOBALS['TL_DCA']['tl_member']['fields']['fax'],
    'street'      => $GLOBALS['TL_DCA']['tl_member']['fields']['street'],
    'street2'     => $GLOBALS['TL_DCA']['tl_member']['fields']['street2'],
    'postal'      => $GLOBALS['TL_DCA']['tl_member']['fields']['postal'],
    'city'        => $GLOBALS['TL_DCA']['tl_member']['fields']['city'],
    'state'       => $GLOBALS['TL_DCA']['tl_member']['fields']['state'],
    'country'     => $GLOBALS['TL_DCA']['tl_member']['fields']['country'],
    'addressText' => $GLOBALS['TL_DCA']['tl_member']['fields']['addressText'],
];

$dca['fields'] = array_merge($dca['fields'], $fields);