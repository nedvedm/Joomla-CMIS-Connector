<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');
 

class MnCmisController extends JControllerLegacy
{
	/**
	 * display task
	 *
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// set default view if not set
		$input = JFactory::getApplication()->input;
		$input->set('view', $input->getCmd('view', 'stat'));
 
		// call parent behavior
		parent::display($cachable, $urlparams);
 
		
		
	}
}