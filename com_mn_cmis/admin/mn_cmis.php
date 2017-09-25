<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Require helper file
JLoader::register('MnCmisHelper', JPATH_COMPONENT . '/helpers/mn_cmis.php');

// Get an instance of the controller prefixed by HelloWorld
$controller = JControllerLegacy::getInstance('MnCmis');
 
// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));
 
// Redirect if set by the controller
$controller->redirect();