<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
 /**
 * Default View
 *
 * @since  0.0.1
 */
class MnCmisViewDefault extends JViewLegacy
{
	/**
	 * Display the Hello World view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null)
	{
		// Get data from the model
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
 
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
 
			return false;
		}
 
		// Set the toolbar
		$this->addToolBar();
		
		// Set the submenu
		MnCmisHelper::addSubmenu('statistics');
		$this->sidebar = JHtmlSidebar::render();
		// Display the template
		parent::display($tpl);
	}
 
	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_MN_CMIS_MANAGER_DEFAULT'));
		JToolBarHelper::addNew('mn_cmis.add');
		JToolBarHelper::editList('mn_cmis.edit');
		JToolBarHelper::deleteList('', 'mn_cmis.delete');
		JToolBarHelper::preferences('com_mn_cmis');
	}
}