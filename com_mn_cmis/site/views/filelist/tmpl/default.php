<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

//$document = JFactory::getDocument();
//$document->addStyleSheet("media/com_mn_cmis/css/mn_cmis.css");

$mncmis = new MnCmis(); //vytvorime instanci tridy mncmis

$app = JFactory::getApplication();
$jinput = $app->input;
$params = $app->getParams();


if($jinput->get('format') == "raw") {
	$ajax = true;	
} else {
	$ajax = false;
}

$nodeRef = $jinput->get('nodeRef','', 'RAW');
$backButton = $jinput->get('backButton');
$type = $jinput->get('type');
$template = $jinput->get('tpl'); //template nemuze pouzivat v url - vyhrazeno pro joomla template...
$tag = $jinput->get('tag');
$order = $jinput->get('order');
$ogimage = $jinput->get('ogimage');
$folder_id = $jinput->get('folder_id');

$q = JRequest::getString( 'q' );
//$q = $jinput->get('q');
$search_form = $jinput->get('search_form');

// get the menu parameters for use
//print_r($this) ;
//$menuparams = $this->state->get("menuparams");
//$text_before = $menuparams->get("text_before");

$cmis_params = array("nodeRef" => $nodeRef,
					"template" => $template,
					"backButton" => $backButton,
					"tag" => $tag,
					"q" => $q,
					"type" => $type,
					"order" => $order,
					"ogimage" => $ogimage,
					"folder_id" => $folder_id
				);

$output = ""; //init

//print_r($cmis_params);

if($ajax == false){	//pokud neni volano ajaxem ale přimo jako komponenta nastavime vychozi parametry
	$history = array();
	$history['href'] = "index.php?option=com_mn_cmis&task=filelist&nodeRef=" . $cmis_params['nodeRef'] . "&type=" . $cmis_params['type'] . "&tpl=" . $cmis_params['template'] . "&order=" . $cmis_params['order'] . "&ogimage=" . $cmis_params['ogimage'] . "&folder_id=" . $cmis_params['folder_id'];
	$history['element'] = "mn-cmis-browser-999";
	JFactory::getDocument()->addScriptDeclaration("mn_common_history_obj.push(" . json_encode($history) . ");");
	
	$mydoc = JFactory::getDocument();
	$output .= "<h1>" . $mydoc->getTitle() . "</h1>";
	//$output .= "<div>" . $text_before . "</div>";
	if($search_form == 1) {
		$output .= "<form class=\"uk-form\"><fieldset><input type=\"text\" name=\"q\" placeholder=\"Hledat v dokumentech\"><button class=\"uk-button uk-margin-left\">Hledat</button></fieldset></form>";
		if(!empty($q)) {
			$output .= "<br>Výsledky pro hledaný dotaz <strong>" . $q . "</strong>:";
		}
		$output .= "<hr>";
	}
	$output .= "<div class=\"mn-common-history-container\" id=\"mn-cmis-browser-999\">"; //vytvorime pouze pokud neni volano ajaxem!
}

$output .= $mncmis->MnCmisQuery( $cmis_params );

if($ajax == false){
	$output .= "</div>"; //vytvorime pouze pokud neni volano ajaxem!
}

/*$dispatcher = JEventDispatcher::getInstance(); //J3 only support JEventDispatcher!
$item->text = $output;  
$item->params = clone($params);
JPluginHelper::importPlugin('content');
JPluginHelper::importPlugin('system'); 
$dispatcher->trigger('onPrepareContent', array (& $item, & $item->params, 0));*/

$output = JHtml::_('content.prepare', $output);

echo $output;