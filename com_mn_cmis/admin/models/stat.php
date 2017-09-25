<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 

class MnCmisModelStat extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'type',
				'name',
				'nodeRef_count'
			);
		}
 
		parent::__construct($config);
	}
	
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
 
		$query
			->select( array('*', 'COUNT(nodeRef) as nodeRef_count', 'GROUP_CONCAT(sef_url ORDER BY sef_url ASC SEPARATOR ", ") as sef_urls') )
			->from($db->quoteName('#__mn_cmis_stat'))
			->group($db->quoteName('nodeRef'));
 
 
		// Filter: like / search
		$search = $this->getState('filter.search');
 
		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('name LIKE ' . $like);
		}
 
		// Filter by type
		if ($type = $this->getState('filter.type'))
		{
			$query->where('type = ' . $db->quote($type));
		}
		
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'name');
		$orderDirn 	= $this->state->get('list.direction', 'asc');
 
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
		
		return $query;
	}
	
	/**
	 * Method to purge the index, deleting all links.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   2.5
	 * @throws  Exception on database error
	 */
	public function purge()
	{
		$db = $this->getDbo();

		// Truncate the stat table.
		$db->truncateTable('#__mn_cmis_stat');

		return true;
	}
	
}