<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

$document = JFactory::getDocument();
$document->setMimeEncoding('application/json');

$data = array();

$mncmis = new MnCmis(); //vytvorime instanci tridy mncmis

$jinput = JFactory::getApplication()->input;

$nodeRef = $jinput->get('nodeRef','', 'RAW');
$backButton = $jinput->get('backButton');
$type = $jinput->get('type');     /*nastavit na tree kdyz slozka*/
if ($type == "folder" || $type == "tree") {
	$type = "tree";       //v JSON zobrazime vzdy cely strom
} else {
	$type = "file";
}

$tag = $jinput->get('tag');
$order = $jinput->get('order');

$q = JRequest::getString( 'q' );

$search_form = $jinput->get('search_form');

$cmis_params = array("nodeRef" => $nodeRef,
					"type" => $type,
					"backButton" => $backButton,
					"tag" => $tag,
					"q" => $q,
					"template" => 'json',
					"order" => $order,
					"ogimage" => false
				);
        
		
$output = $mncmis->MnCmisQuery( $cmis_params );

$output = JHtml::_('content.prepare', $output);
echo $output;

/*
ob_clean();
echo json_encode(array(
		array(
				'data' => $data,
				'messages' => $lists
		)
));  */

JFactory::getApplication()->close();