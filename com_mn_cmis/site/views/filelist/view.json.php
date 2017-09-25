<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class mn_cmisViewFileList extends JViewLegacy
{
    function display($tpl = null)
    {
        $this->setLayout('json_layout');
        parent::display($tpl);
    }
}

