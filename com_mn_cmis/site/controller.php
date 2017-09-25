<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
//jimport('joomla.application.component.controller'); ???
 
class mn_cmisController extends JControllerLegacy
{
	public function filelist() 
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set("view", 'filelist');	//timto neni potreba zadavat argument view do url (staci task)		
		parent::display();
	}
}