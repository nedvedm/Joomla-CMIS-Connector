<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

defined('_JEXEC') or die;


class PlgButtonMn_Cmis extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return array A two element array of (imageName, textToInsert)
	 */
	public function onDisplay($name)
	{
	//nacteme parametry z content pluginu
	$plugin = JPluginHelper::getPlugin('content', 'mn_cmis');
	$content_plugin_params = new JRegistry();
	$content_plugin_params->loadString($plugin->params);
	//$content_plugin_params->get('plugin_code', 'documents');
  
    $js = "
  		function InsertAlfrescoDoc(noderef, template, order)
  		{
        switch (template)
        {
           case 'soubor':
               var templateText = '';
               var type = '';
               break;
               
           case 'slozka':
               var templateText = '';
               var type = '" . $content_plugin_params->get('folder_code') . "';
               break;
               
           case 'galerie':
               var templateText = ' " . $content_plugin_params->get('template_code') . "=thumbnail';
               var type = '" . $content_plugin_params->get('folder_code') . "';
               break;
           
           case 'thumbnail':
               var templateText = ' " . $content_plugin_params->get('template_code') . "=thumbnail';
               var type = '';
               break;
               
           case 'imgpreview':
               var templateText = ' " . $content_plugin_params->get('template_code') . "=imgpreview';
               var type = '';
               break;
               
           default: 
              var templateText = '';
              var type = '';
              break;
        }
      
        if (order == 'vzestupne') { var orderText = ' " . $content_plugin_params->get('order_code') . "=' + order; } else {orderText = '';}
        if (type) { var typeText = ' " . $content_plugin_params->get('type_code') . "='+ type; } else {var typeText = '';}
		
  			var tag = '{" . $content_plugin_params->get('plugin_code') . " ' + noderef + typeText + templateText + orderText + '}';
  			jInsertEditorText(tag, '" . $name . "');
  			jModalClose();
  		}";
  
  		$doc = JFactory::getDocument();
  		$doc->addScriptDeclaration($js);
      
      
      
	$link = 'index.php?option=com_mn_cmis&view=filelist&layout=button&tmpl=component&e_name=' . $name;
  
	$button = new JObject;
	$button->modal = true;
	$button->class = 'btn';
	$button->link  = $link;
	$button->text  = JText::_('Alfresco');
	$button->name  = 'file-add';
	$button->options = "{handler: 'iframe', size: {x: 600, y: 400}}";

	return $button;
	}
}