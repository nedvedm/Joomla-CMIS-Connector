<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

defined('_JEXEC') or die;

/**
 * Statistics model class for Finder.
 *
 * @since  2.5
 */
class MnCmisModelStatistics extends JModelLegacy
{
	/**
	 * Method to get the component statistics
	 *
	 * @return  object  The component statistics
	 *
	 * @since   2.5
	 */
	public function getData()
	{
		// Initialise
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$data = new JObject;

		$query->select('COUNT(id)')
			->from($db->quoteName('#__mn_cmis_stat'));
		$db->setQuery($query);
		$data->id_count = $db->loadResult();

		$query->clear()
			->select('COUNT(type)')
			->from($db->quoteName('#__mn_cmis_stat'))
			->where($db->quoteName('type') . ' = "file"');
		$db->setQuery($query);
		$data->file_count = $db->loadResult();

		$query->clear()
			->select('COUNT(type)')
			->from($db->quoteName('#__mn_cmis_stat'))
			->where($db->quoteName('type') . ' = "folder"');
		$db->setQuery($query);
		$data->folder_count = $db->loadResult();
		
		$query->clear()
			->select('COUNT(type)')
			->from($db->quoteName('#__mn_cmis_stat'))
			->where($db->quoteName('type') . ' = "root_folder"');
		$db->setQuery($query);
		$data->rootfolder_count = $db->loadResult();
		
		$query->clear()
			->select('COUNT(DISTINCT noderef)')
			->from($db->quoteName('#__mn_cmis_stat'));
			
		$db->setQuery($query);
		$data->nodeRef_count = $db->loadResult();

		

		$lang  = JFactory::getLanguage();
		$plugins = JPluginHelper::getPlugin('finder');

		foreach ($plugins as $plugin)
		{
			$lang->load('plg_finder_' . $plugin->name . '.sys', JPATH_ADMINISTRATOR, null, false, true)
			|| $lang->load('plg_finder_' . $plugin->name . '.sys', JPATH_PLUGINS . '/finder/' . $plugin->name, null, false, true);
		}

		return $data;
	}
}
