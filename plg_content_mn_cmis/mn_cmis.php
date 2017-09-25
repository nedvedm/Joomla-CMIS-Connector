<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
class plgContentMn_Cmis extends JPlugin
{
	/**
	 * Load the language file on instantiation. Note this is only available in Joomla 3.1 and higher.
	 * If you want to support 3.0 series you must override the constructor
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
 
	function onContentPrepare( $context, &$article, &$params, $page = 0 )
	{
        if (!JComponentHelper::isEnabled('com_mn_cmis', true)) {
			return JError::raiseError(JText::_('MN CMIS Component Error'), JText::_('MN CMIS Client Component is not installed on your system'));
		}
    
		// Don't run this plugin when the content is being indexed
		/**if ($context == 'com_finder.indexer') {
			return true;
		} **/
		
		/*if (!JPluginHelper::isEnabled('system', 'mn_common')) {
			return JError::raiseError(JText::_('MN Common Plugin Error'), JText::_('MN Common plugin is not installed on your system'));
		}*/
    
		JLoader::register('MnCmis', JPATH_SITE.DIRECTORY_SEPARATOR.'components/com_mn_cmis/classes/mncmis.php'); //register CMIS common class - contains all CMIS related functions (no need to load comlete mn_cmis component)
		try {
			$mncmis = new MnCmis();
		}
		catch (CmisRuntimeException $e) {
			$error_message = "DMS Chyba. Při načítání dokumentů se vyskytla neznámá chyba.";
			return "<div class=\"alert\">$error_message</div>";
		}
		
		$plugin_code = $this->params->get('plugin_code', 'documents');
		$type_code = $this->params->get('type_code', 'type');
		$folder_code = $this->params->get('folder_code', 'folder');
		$tree_code = $this->params->get('tree_code', 'tree');
		$template_code = $this->params->get('template_code', 'template');
		$order_code = $this->params->get('order_code', 'order');
		$order_asc_code = $this->params->get('order_asc_code', 'asc');
		$order_desc_code = $this->params->get('order_desc_code', 'desc');
		//$order_default = $this->params->get('order_default', 'descendent');  toto musí být na úrovni komponenty
		
		//$document = JFactory::getDocument();
		//$document->addStyleSheet("media/com_mn_cmis/css/mn_cmis.css");
		
		//obrazky prozatim ponechany pro zpetnou kompatibilitu
		$regex_one		= '/{(' . $plugin_code . '|obrazky)\s*(.[^}]*)/';			//vyhleda vyskyt {dokumenty libovolne atributy} v textu do [0] ulozi cely text pluginu, do [1] ulozi vsechny atributy za sebou 
		$regex_all		= '/{(' . $plugin_code . '|obrazky)\s*.*?}/'; 				//vyhleda vyskyt {dokumenty libovolne atributy} v textu do [0] ulozi cely text pluginu
		$matches 		= array();
	    $count_matches	= preg_match_all($regex_all, $article->text, $matches, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER); //spocitame celkovy pocet pluginu v textu - ulozi se postupne do pole
		if ($count_matches != 0) {
			$folder_id = 0; //pocitani slozek pro histori api - musime pred jako parametr pro tridu
			for($i = 0; $i < $count_matches; $i++) {
            	$parts = array(); 													//sem ulozime jednotlive atributy pluginu
				$match = $matches[0][$i][0];									 	//postupne iterujeme pres jednotlive vyskyty pluginu v textu a do $match ulozime text aktualniho pluginu
				preg_match($regex_one, $match, $parts);					 			//do $parts[0] ulozime text celeho pluginu vcetne atributu, do $parts[1] inforamci zda se jedna o obrazky nebo dokumenty a do $parts[2] vsechny atributy pluginu
				preg_match ( "#nodeRef=([^\s]+)#", $parts[2], $nodeRef );			//v atributech pluginu hledame odpovidajici atribut a jeho hodnotu ulozime do $sid[1] a do $sid[0] se ulozi nazev atributu
				preg_match ( "#tag\s*=\s*\"\s*(.*?)\s*\"#s", $parts[2], $tag );
				preg_match ( "#". $template_code . "=([^\s]+)#", $parts[2], $template ); 		//pripustne atributy: doleva, doprava, imgpreview atp.
				preg_match ( "#q\s*=\s*\"\s*(.*?)\s*\"#s", $parts[2], $q );
				preg_match ( "#". $order_code . "=([^\s]+)#", $parts[2], $order ); 
				preg_match ( "#". $type_code . "=([^\s]+)#", $parts[2], $type ); 
				preg_match ( "#ogimage\s*=\s*\"\s*(.*?)\s*\"#s", $parts[2], $ogimage );
				
				//složka, strom nebo soubor?
				if (isset($template[1])) { //osetreni zpetne kompatibility kdyz byl styl=slozka...
					if($template[1] == "slozka"){
						$type[1] = "slozka"; //nastavime aktualni nazev z konfigurace (slozka)
						$template[1] = null;
					}
				}
				if (isset($template[1])) { //osetreni zpetne kompatibility kdyz byl styl=strom...
					if($template[1] == "strom"){
						$type[1] = "strom";
						$template[1] = null;
					}
				}
				if ($parts[1] == "obrazky" & empty($template[1])) { //osetreni zpetne kompatibility
					$template[1] = "thumbnail"; //toto bude vychozi sablona pro obrazky
				}
				
				$cmis_params = array();
				
				$cmis_params["folder_id"] = $folder_id; //predame poradove cislo slozky pro history api
				isset($nodeRef[1]) ? $cmis_params["nodeRef"] = $nodeRef[1] : $cmis_params["nodeRef"] = null;
				isset($tag[1]) ? $cmis_params["tag"] = $tag[1] : $cmis_params["tag"] = null;
				isset($q[1]) ? $cmis_params["q"] = $q[1] : $cmis_params["q"] = null;
				isset($template[1]) ? $cmis_params["template"] = $template[1] : $cmis_params["template"] = "doclib"; //kdyz neni zadana sablona, bude vychozi doclib
				
				$cmis_params['type'] = "file"; //nastavime vychozi
				//soubor, slozka, tree
				if(!empty($type[1])){
					if($type[1] == $folder_code) { $cmis_params['type'] = "folder"; }
					if($type[1] == $tree_code) { $cmis_params['type'] = "tree"; }
				} 
				
				isset($ogimage[1]) ? $cmis_params["ogimage"] = $ogimage[1] : $cmis_params["ogimage"] = false;
				
				// Razeni
				if(!empty($order[1])){     //toto resi chybu undefined offset - kdy neni definovano seradit - dodlat i u ostatnich..
					if($order[1] == $order_asc_code){   		// "vzestupne"
						$cmis_params["order"] = "ASC";
					} elseif($order[1] == $order_desc_code){   	// "vzestupne" { 
						$cmis_params["order"] = "DESC"; 
					} else {
						$cmis_params["order"] = null; 			//pokud je hodnota razeni v pluginu zadane, ale chybně oproti konfiguraci, nastavime vychozi razeni
					}
				} else {
					$cmis_params["order"] = null; 			//pokud neni zadana hodnota razeni, nastavime vychozi razeni
				}
				
				// zde zjistit, že když neni žádný parametr, tak přiřadit default tag dle joomla article id
				if (!$cmis_params['nodeRef'] & !$cmis_params['q'] & !$cmis_params['tag']) {
					$cmis_params['tag'] = "jid" . $row->id;
				}
				
				$output = $mncmis->MnCmisQuery( $cmis_params );
				
				$folder_id++;
				
				$article->text = preg_replace($regex_all, $output, $article->text, 1);
				unset($parts,$cmis_params,$nodeRef,$template,$tag,$ogimage,$q,$type); //vymažeme pole a proměnné ať se nám nepřenáší do další iterace!
			} //end for	
		} //end if		
		
		return true;
	} //end function	
}

?>