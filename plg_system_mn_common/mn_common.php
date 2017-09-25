<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
class plgSystemMn_Common extends JPlugin
{
	protected $autoloadLanguage = true;

	function onAfterRoute()
	{
		$document = JFactory::getDocument();
		
		/*
		$doctype = $document->getType(); //nasledujici zjistuje zda vystup neni např. raw (mozna se bude hodit, zatím vypnuto)
		// Only render for HTML output
		if ( $doctype !== 'html' ) { return; }
		*/
				
		JHtml::_('bootstrap.framework'); //load jQuery and bootstrap first
		//$document->addScript("media/plg_mn_common/touchswipe/jquery.touchSwipe.min.js");
		$document->addScript("media/plg_mn_common/modernizr.js");
		$document->addScript("media/plg_mn_common/mn_common.js");

		return true;
	} //end function	
}