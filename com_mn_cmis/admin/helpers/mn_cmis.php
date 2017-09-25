<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// No direct access to this file
defined('_JEXEC') or die;
 

abstract class MnCmisHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu) 
	{
		JHtmlSidebar::addEntry(JText::_('COM_MN_CMIS_SUBMENU_STAT'),
		                         'index.php?option=com_mn_cmis&view=stat', $submenu == 'statistics');
	}
}