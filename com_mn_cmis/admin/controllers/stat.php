<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

defined('_JEXEC') or die;

/**
 * Index controller class for Finder.
 *
 * @since  2.5
 */
class MnCmisControllerStat extends JControllerAdmin
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   2.5
	 */
	public function getModel($name = 'Stat', $prefix = 'MnCmisModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Method to purge all indexed links from the database.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function purge()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Remove the script time limit.
		@set_time_limit(0);

		$model = $this->getModel('Stat', 'MnCmisModel');

		// Attempt to purge the index.
		$return = $model->purge();

		if (!$return)
		{
			$message = JText::_('COM_MN_CMIS_STAT_PURGE_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_mn_cmis&view=stat', $message);

			return false;
		}
		else
		{
			$message = JText::_('COM_MN_CMIS_STAT_PURGE_SUCCESS');
			$this->setRedirect('index.php?option=com_mn_cmis&view=stat', $message);

			return true;
		}
	}
}
