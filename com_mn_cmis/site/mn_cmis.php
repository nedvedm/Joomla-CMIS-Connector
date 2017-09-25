<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JLoader::register('MnCmis', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'classes/mncmis.php'); //register CMIS common class - contains all CMIS related functions

jimport('joomla.application.component.controller');
$controller = JControllerLegacy::getInstance('mn_cmis');
$jinput = JFactory::getApplication()->input;
$controller->execute($jinput->get->get('task'));
$controller->redirect();
?>
