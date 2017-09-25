<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

require_once ('cmis_repository_wrapper.php'); //musi byt ve stejne slozce jako trida a pak neni nutna cesta
	
class MnCmis {

	protected $client;
	
	var $cache_query = null;
	var $cache_render = null;
	
	private $alfresco_url;
	private $debug;
	//private $query_data;
	private $render_data;
	
	//public function __construct(&$repo_url, &$repo_username, &$repo_password, &$options)
	public function __construct()
	{ 
		jimport('joomla.application.component.helper'); 	//toto pomůže získat parametr komponenty i když voláme třídu pouze z pluginu
		
		(JComponentHelper::getParams('com_mn_cmis')->get('alfresco_ssl_site')) ? $url_prefix = "https://" : $url_prefix = "http://";
		$this->alfresco_url = $url_prefix . JComponentHelper::getParams('com_mn_cmis')->get('alfresco_url');
		
		//zjistime zda je zapnuta globalni cache
		//$conf = JFactory::getConfig(); //tohle proste vraci porad 0 coz je divne - v libraries/joomla/cahce/cache.php je pouzito..
		//echo "<pre>"; print_r($conf); echo "</pre>"; echo "Caching z config: " . $conf->get('caching'); 
		$global_cache_state = JFactory::getCache('_system')->getCaching(); //tohle funguje...
		//echo "<pre>"; print_r($global_cache_state); echo "</pre>"; echo "Stav globální cache: " . $global_cache_state;
		$local_cache_query = JComponentHelper::getParams('com_mn_cmis')->get('cache_query');
		$local_cache_render = JComponentHelper::getParams('com_mn_cmis')->get('cache_render');
		//global = 1, local = 0 => 0
		//global = 1, local = 1 => 1
		//global = 1, local = 2 => 1 //use global
		//global = 0, local = 2 => 0 //use global
		//global = 0, local = 1 => 1
		
		$this->cache_query = JFactory::getCache('mn_cmis_query', 'output'); //toto je cache pouze na dotazy do Alfresca, dále je render cache - output znamená že použiju cache controller -> output, výchozí je callback, více v /libraries/joomla/cache/controller
		if(( $global_cache_state == 1 && $local_cache_query > 0 ) || ( $local_cache_query == 1 ))
		{
			$this->cache_query->setCaching(true);
			$this->cache_query->setLifeTime(JComponentHelper::getParams('com_mn_cmis')->get('cache_query_lifetime', 86400));
		} else {
			$this->cache_query->setCaching(false);
		}
		
		$this->cache_render = JFactory::getCache('mn_cmis_render', 'output'); //toto je cache pro rendery
		if(( $global_cache_state == 1 && $local_cache_render > 0 ) || ( $local_cache_render == 1 ))
		{
			$this->cache_render->setCaching(true);
			$this->cache_render->setLifeTime(JComponentHelper::getParams('com_mn_cmis')->get('cache_render_lifetime', 86400));
		} else {
			$this->cache_render->setCaching(false);
		}
		
		echo "<pre>query"; print_r($this->cache_query); echo "</pre>";
		echo "<pre>render"; print_r($this->cache_render); echo "</pre>";
		
		$this->debug = JComponentHelper::getParams('com_mn_cmis')->get('debug', 0);
		
		$repo_url = $this->alfresco_url . "/alfresco/api/-default-/public/cmis/versions/1.0/atom";
		$repo_username = JComponentHelper::getParams('com_mn_cmis')->get('alfresco_username');
		$repo_password = JComponentHelper::getParams('com_mn_cmis')->get('alfresco_password');
	 	$options = "";
		$this->client = new CMISService($repo_url, $repo_username, $repo_password, $options);
	}
	
	//metody pro manipulaci s daty z renderů
	public function add_render_data($type, $data) {
		$this->render_data[$type][] = $data;
	}
	/*public function get_render_data() { nepouzito
		return $this->render_data;
	}*/
	public function reset_render_data() {
		$this->render_data = null;
		return;
	}
	
	//pomocna funkce pro prevod velikosti
	public function MnCmisFormatFileSize( $size ) {
		if (!$size) {
			return '(invalid file size)';
		} else {
			$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
      		if ($size == 0) { return('n/a'); } else {
      		return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]); }
		}
	}
	
	//tato funkce je volana z pluginu i komponenty a ovládá cache + spouští vše co je mimo cache (JFactory::getDocument()->add...)
	public function MnCmisQuery( $cmis_params ) {
		 
		
		if ($this->cache_query->getCaching()) {
			$cacheid = md5(serialize($cmis_params));
			$items = $this->cache_query->get($cacheid);
		}
        if (empty($items)) {
			$items = $this->MnCmisQueryExec( $cmis_params );
			//store data in the cache:
			if ($this->cache_query->getCaching()) {
				$this->cache_query->store($items, $cacheid);
			}
		}
		
		if ($this->debug == 1) { echo "<pre>"; print_r($items); echo "</pre>"; }	// debug vysledky cmis dotazu	
		
		$total_files = count((array) $items['files']->objectList);
		($cmis_params['type'] == "folder") ? $total_folders = count((array) $items['folders']->objectList) : $total_folders = 0;
		$total = $total_files + $total_folders;
		if($total_files == 0 && !empty($cmis_params['q'])) {  //chybu zobrazíme pouze pokud hledáme dle textu
			JFactory::getApplication()->enqueueMessage(JText::_('COM_MN_CMIS_NO_FILES'), 'message');
		}
		if($total == 0 ) {
			return;
		}
		
		if ($this->cache_render->getCaching()) {
			$cmis_params['cache'] = "render"; //toto pridame, abychom rozlisili cahce pro cmis dotaz a cache pro render!
			$cacheid = md5(serialize($cmis_params));
			$this->render_data = $this->cache_render->get($cacheid);
		}
        if (empty($this->render_data)) { // spustime render pokud neni v render cache
			if ($cmis_params['type'] == "folder") {
				
				//ulozime statistiky pro roor folder - pouze pokud neni v cache a pouze pokud je zapnute SEF, aby se neukladaly non-sef url
				$conf = JFactory::getConfig();
				if ($conf->get('sef') == 1) {
					$this->MnCmisUpdateStat('root_folder', $items['current_folder']->objectList[0]->properties['cmis:path'], $items['current_folder']->objectList[0]->properties['alfcmis:nodeRef']);
				}
				
				$this->MnCmisRenderFolderBefore($cmis_params); //renderujeme vše co je před položkami (složek) pokud je typ folder
				foreach ($items['folders']->objectList as $folder) // pro kazdy vyskyt zavolame funkci pro vykresleni slozky
				{
					$this->MnCmisRenderFolder($folder, $cmis_params);
				}
			}
			foreach ($items['files']->objectList as $obj) // pro kazdy vyskyt zavolame funkci pro vykresleni odkazu, nahledu atd..
			{ 
				$this->MnCmisRenderFile($obj, $cmis_params);  
			}					
			if ($cmis_params['type'] == "folder") {
				$this->MnCmisRenderFolderAfter($cmis_params); //renderujeme vše co je za položkami (složek) pokud je typ folder
			}
			
			//store data in the cache:
			if ($this->cache_render->getCaching()) {
				$this->cache_render->store($this->render_data, $cacheid);
			}
		}
		
		if ($this->debug == 1) { echo "<pre>"; htmlspecialchars(print_r($this->render_data)); echo "</pre>"; } //debug vysledky renderu
		
		$document = JFactory::getDocument();
		
		if ($cmis_params['template'] == 'json'){
			$output = array();
			foreach ($this->render_data['html'] as $html) {
				array_push($output, $html);
			}	
			$output = json_encode($output);
		} else {
			$output = "";
			foreach ($this->render_data['html'] as $html) {
				$output .= $html;
			}
		}
		
		
		if (get_class($document) == "JDocumentHTML") { //osetreni chyby pri indexovani ve finderu
			if (!empty($this->render_data['doc_add_custom_tag'])) {
				foreach ($this->render_data['doc_add_custom_tag'] as $doc_add_custom_tag) {
					$document->addCustomTag($doc_add_custom_tag);
				}
			}
		}
		if (!empty($this->render_data['doc_add_script_declaration'])) {
			foreach ($this->render_data['doc_add_script_declaration'] as $doc_add_script_declaration) {
				$document->addScriptDeclaration($doc_add_script_declaration);
			}
		}
		$this->reset_render_data();
		
		return $output;
	}
	
	public function MnCmisQueryExec( $cmis_params )
	{
		//nacteni wrapperu a nastaveni CMIS
		try {	
			//throw new Exception("Not Implemented");
			if ($cmis_params['order']) {
				$order = "ORDER BY D.cmis:name " . $cmis_params['order'];
				$folder_order = "ORDER BY cmis:name " . $cmis_params['order'];
			} else {
				$order = "ORDER BY D.cmis:name DESC";
				$folder_order = "ORDER BY cmis:name DESC";
			}	
        			
			$query_conditions = array();
			if ($cmis_params['q'] && $cmis_params['type'] == "folder") { //pokud přidáme parametr hledat musíme vždy přepnout na tree
				$cmis_params['type'] = "tree";
			}
			
			if (isset($cmis_params['nodeRef']) && $cmis_params['type'] != "folder" && $cmis_params['type'] != "tree") {
				array_push($query_conditions, "D.cmis:objectId = '" . $cmis_params['nodeRef'] . "'");
			}
			
			if (isset($cmis_params['nodeRef']) && $cmis_params['type'] == "folder") {
				array_push($query_conditions, "IN_FOLDER(D,'" . $cmis_params['nodeRef']. "')");
				$query_folders = "SELECT * FROM cmis:folder WHERE IN_FOLDER('" . $cmis_params['nodeRef']. "') AND cmis:objectTypeId = 'cmis:folder' " . $folder_order . "";
				$query_current_folder = "SELECT * FROM cmis:folder WHERE cmis:objectId = '" . $cmis_params['nodeRef'] . "'";
			}
			
			elseif (!empty($cmis_params['nodeRef']) && $cmis_params['type'] == "tree") {
				array_push($query_conditions, "IN_TREE(D,'" . $cmis_params['nodeRef'] . "')");
			}
			
			if (!empty($cmis_params['q'])) {
				array_push($query_conditions, "CONTAINS(D,'" . $cmis_params['q'] . "') OR CONTAINS(D,'cmis:name:" . $cmis_params['q'] . "')");
			}
	
			if ($cmis_params['tag']) {
				array_push($query_conditions, "CONTAINS(D,'TAG:" . $cmis_params['tag'] . "')");
			}
	
			/*if ($cmis_params[name]) {
				array_push($query_conditions, "D.cmis:name LIKE '$cmis_params[name]'"); 
			}*/
			
			$items = array(); //inicializace výstupu
			// nejdrive nacteme složky
			if (isset($query_folders)) {
				//echo $query_folders;
				$items['folders'] = $this->client->query($query_folders);
				$items['current_folder'] = $this->client->query($query_current_folder);
			}
			
			// nacteme soubory
			if(sizeof($query_conditions)) {
				$query = "SELECT D.*, S.*, T.cm:title FROM cmis:document AS D JOIN qshare:shared AS S ON D.cmis:objectId = S.cmis:objectId JOIN cm:titled AS T ON D.cmis:objectId = T.cmis:objectId WHERE " . join(" AND ", $query_conditions) . " " . $order . "";
				//echo $query;
				$items['files'] = $this->client->query($query);
			}
			
			return $items;		
		}
		
		/**catch (CmisObjectNotFoundException $e) {
			$error_message = "Nebyly nalezeny žádné dokumenty.";
		}
		catch (CmisRuntimeException $e) {
			$error_message = "DMS Chyba. Při načítání dokumentů se vyskytla neznámá chyba.";
		}
		catch (CmisInvalidArgumentException $e) {
			$error_message = "DMS Chyba. Byl zadán chybný parametr pro hledání dokumentů.";
		}   **/
		catch(Exception $e) {
			echo 'Chyba: ' .$e->getMessage();
		}
	}
	
	function MnCmisRenderFolderBefore($cmis_params)
	{
		//history api
		$output = "<div class=\"mn-common-history-container\" id=\"mn-cmis-browser-" . $cmis_params["folder_id"] . "\">"; //vytvoříme DIV který bude ohraničovat načítací prostor AJAXu, končit musí až za soubory v aktuální složce!
		$history = array();
		$history['href'] = "index.php?option=com_mn_cmis&task=filelist&nodeRef=" . $cmis_params['nodeRef'] . "&type=" . $cmis_params['type'] . "&tpl=" . $cmis_params['template'] . "&order=" . $cmis_params['order'] . "&ogimage=" . $cmis_params['ogimage'] . "&folder_id=" . $cmis_params['folder_id'];
		$history['element'] = "mn-cmis-browser-" . $cmis_params["folder_id"];
		
		$this->add_render_data('doc_add_script_declaration', "mn_common_history_obj.push(" . json_encode($history) . ");");		// history api
		
		if(isset($cmis_params['backButton'])){ //toto renderuje pouze lištu s tlačítkem zpět
			$output .= "<div class=\"nav navbar-nav\"><a href=\"#\" onclick=\"history.back();return false;\"><i class=\"icon-folder-open\"></i> Nahoru</a></div>";
		}
		if ($cmis_params['template'] == "thumbnail"){ 		//doresit - zatim styl thumbnail
			$output .= "<div class=\"uk-margin\" data-uk-margin>";
		} else { 											//doresit - zatim styl doclib
			$output .= "<ul class=\"uk-list uk-list-line\">";
		}
		
		$this->add_render_data('html', $output);
		
		return;
	}
	
	function MnCmisRenderFolder( $obj, $cmis_params)
	{
		//převedeme formát datumu
		$date_time = strtotime($obj->properties['cmis:lastModificationDate']);
		$date = Date("d. n. Y, H:i:s", $date_time);
		$nodeRef = $obj->properties['alfcmis:nodeRef'];
		$doclib_url = JURI::root() . "media/com_mn_cmis/images/folder-64.png";
		$folder_url = "index.php?option=com_mn_cmis&view=filelist&backButton=true&nodeRef=" . $nodeRef . "&type=" . $cmis_params['type'] . "&tpl=" . $cmis_params['template'] . "&order=" . $cmis_params['order'] . "&ogimage=" . $cmis_params['ogimage'] . "&folder_id=" . $cmis_params['folder_id'];
		$output  = "<li class=\"row-fluid\"><div class=\"span12\">"; //row-fluid and span12 is bootstrap css!
		$output .= "<a class=\"mn-common-history-link\" title=\"" . $obj->properties['cmis:name'] . "\" href=\"" . JRoute::_('$folder_url') . "\"><img style=\"margin: 10px; float: left;\" alt=\"nahled\" src=\"" . $doclib_url . "\"></a>";
		$output .= "<h2 class=\"uk-margin-remove\"><a class=\"mn-common-history-link\" href=\"" . $folder_url . "\">" . $obj->properties['cmis:name'] . "</a></h2>";
		/*if(isset($obj->properties['cm:title'])){
			$output .="<span>&nbsp;(" . $obj->properties['cm:title'] . ")</span>";
		}*/
		$output .= "<span class=\"box-info\">Poslední změna: " . $date . "</span></div></li>";
		$output .= "\n";
		
		$this->add_render_data('html', $output);
		
		//ulozime statistiky - pouze pokud neni v cache a pouze pokud je zapnute SEF, aby se neukladaly non-sef url
		$conf = JFactory::getConfig();
		if ($conf->get('sef') == 1) {
			$this->MnCmisUpdateStat('folder', $obj->properties['cmis:path'], $obj->properties['alfcmis:nodeRef']);
		}
		
		return;
	}	// end function
	
	function MnCmisRenderFolderAfter($cmis_params)
	{
		$output = "";
		
		if ($cmis_params['template'] == "thumbnail"){ 		//doresit - zatim styl galerie
			$output .= "</div>";
        } else { 											//doresit - zatim styl document
			$output .= "</ul>";
		}
		
		$output .= "</div>"; //konec history api container
		
		$this->add_render_data('html', $output);
		
		return;
	}
	
	function MnCmisRenderFile( $obj, $cmis_params)
	{
		if ($this->debug == 1) { echo "<pre>"; print_r($obj); echo "</pre>"; } //debug - zapina se v konfiguraci komponenty
				
		$path_parts = pathinfo($obj->properties['cmis:name']); //zjistime název a příponu
		$date_time = strtotime($obj->properties['cmis:lastModificationDate']); //převedeme formát datumu
		$date = Date("d. n. Y, H:i:s", $date_time);
		$shared_node_path = $this->alfresco_url . "/share/proxy/alfresco-noauth/api/internal/shared/node/";
		$doclib_alt_url = JURI::root()."components/com_mn_cmis/images/doclib.png";
		
		$obj->url['doclib_url'] = $shared_node_path . $obj->properties['qshare:sharedId'] . "/content/thumbnails/doclib?c=force"; //finalni url si uložíme ke každému objektu (dokumentu)
		$obj->url['imgpreview_url'] = $shared_node_path . $obj->properties['qshare:sharedId'] . "/content/thumbnails/imgpreview?c=force";
		$obj->url['content_url'] = $shared_node_path . $obj->properties['qshare:sharedId'] . "/content/". rawurlencode($obj->properties['cmis:name']) . "?c=force&a=true"; 
		$obj->url['quickshare_url'] = $this->alfresco_url . "/share/s/". $obj->properties['qshare:sharedId'];
		
		
		//ověřit zda ještě funguje zobrazení KML
		$encoded_url = str_replace('%3A',':', rawurlencode($obj->url['content_url'])); //pro google musí být 2x encoded mezery
		$path_parts['extension'] = ""; // osetreni undefined indexu
		if ($path_parts['extension'] == "kml" || $path_parts['extension'] == "kmz") {
			$read_url = "https://maps.google.com/maps?q=". $encoded_url;	//for modal previews
		} else {
			$read_url = $obj->url['quickshare_url'];
		}
		
		//Facebook opengraph image - og:image
		if ($cmis_params['ogimage'] == true){
			$this->add_render_data('doc_add_custom_tag', "<meta property=\"og:image\" content=\"" . $obj->url['imgpreview_url'] . "\" />\n");
		}
		
		// BEGIN TEMPLATE PARSE
		switch ($cmis_params['template']) {
			case 'url_webpreview':		//tento nazev dohledat v textech a bude nahrazen nasledujicim imgpreview
				$output = $obj->url['imgpreview_url'];
				break;
			case 'imgpreview_url':
				$output = $obj->url['imgpreview_url'];
				break;
			case 'doclib_url':
				$output = $obj->url['doclib_url'];
				break;
			case 'imgpreview':	//tuto později předělat na php sablonu
				$output = "<a data-uk-lightbox=\"{group:'album'}\" data-lightbox-type=\"image\" title=\"" . $obj->properties['cmis:name'] . "\" href=\"" . $obj->url['imgpreview_url'] . "\"><img alt=\"" . $obj->properties['cmis:name'] . "\" src=\"" . $obj->url['imgpreview_url'] . "\"></a>\r\n";
				break;
			case 'thumbnail':	
				$output = "<a class=\"uk-thumbnail uk-thumbnail-mini\" data-uk-lightbox=\"{group:'album'}\" data-lightbox-type=\"image\" title=\"" . $obj->properties['cmis:name'] . "\" href=\"" . $obj->url['imgpreview_url'] . "\"><img alt=\"" . $obj->properties['cmis:name'] . "\" src=\"" . $obj->url['imgpreview_url'] . "\"><div class=\"uk-thumbnail-caption\">" . $obj->properties['cmis:name'] . "</div></a>\r\n";
				break;
			case 'doprava':		//stejne jako thumbnail ale zarovnane doprava
				$output = "<a class=\"uk-float-right uk-thumbnail uk-thumbnail-mini\" data-uk-lightbox=\"{group:'album'}\" data-lightbox-type=\"image\" title=\"" . $obj->properties['cmis:name'] . "\" href=\"" . $obj->url['imgpreview_url'] . "\"><img alt=\"" . $obj->properties['cmis:name'] . "\" src=\"" . $obj->url['imgpreview_url'] . "\"></a>\r\n";
				break;
			case 'doprava':		//stejne jako thumbnail ale zarovnane doleva
				$output = "<a class=\"uk-float-left uk-thumbnail uk-thumbnail-mini\" data-uk-lightbox=\"{group:'album'}\" data-lightbox-type=\"image\" title=\"" . $obj->properties['cmis:name'] . "\" href=\"" . $obj->url['imgpreview_url'] . "\"><img alt=\"" . $obj->properties['cmis:name'] . "\" src=\"" . $obj->url['imgpreview_url'] . "\"></a>\r\n";
				break;
			case 'doclib':	
				$output  = "<li class=\"row-fluid\"><div class=\"span10\">"; //row-fluid and span12 is bootstrap css!
				$output .= "<a title=\"" . $obj->properties['cmis:name'] . "\" href=\"" . $read_url . "\">";
				$output .= "<img class=\"uk-float-left uk-margin-right\" src=\"" . $obj->url['doclib_url'] . "\" alt=\"". $obj->properties['cmis:name'] . "\" onError=\"this.onerror=null;this.src='" . $doclib_alt_url . "';\"></a>"; //pokud neni nahled zobrazi se ikona souboru (netestujeme na serveru ale az v prohlizeci javascriptem!
				$output .= "<h3 class=\"uk-margin-remove\"><a title=\"" . $obj->properties['cmis:name'] . "\" href=\"". $read_url . "\">" . $obj->properties['cmis:name'] . "</a></h3>";
				if(isset($obj->properties['cm:title'])){
					$output .="<h4 class=\"uk-margin-remove\">&nbsp;(" . $obj->properties['cm:title'] . ")</h4>";
				}
				$output .= "<span class=\"box-info\">Poslední změna: " . $date . ", velikost: " . $this->MnCmisFormatFileSize($obj->properties['cmis:contentStreamLength']) . "</span>";
				$output .= "</div>"; //end span8
				$output .= "<div class=\"span2\"><ul class=\"uk-list\">";
				$output .= "<li><a title=\"Stáhnout\" href=\"" . $obj->url['content_url'] . "\"><i class=\"icon-download\"></i> Stáhnout</a></li>";
				$output .= "<li><a title=\"Číst\" href=\"". $read_url . "\"><i class=\"icon-eye-open\"></i> Číst</a></li>";  // a class=\"colorbox-iframe\
				$output .= "<li><a data-uk-lightbox=\"{group:'album'}\" data-lightbox-type=\"image\" title=\"" . $obj->properties['cmis:name'] . "\" href=\"". $obj->url['imgpreview_url'] . "\"><i class=\"uk-icon-bolt\"></i> Náhled</a></li>"; 
				$output .= "</ul></div></li>\n";
			break;
			case 'list':	//prime zobrazeni pro newslist
				$output = "<a class=\"uk-thumbnail uk-margin-right uk-float-left\" data-uk-modal title=\"" . $obj->properties['cmis:name'] . "\" href=\"#" . $obj->properties['qshare:sharedId'] . "\"><img alt=\"" . $obj->properties['cmis:name'] . "\" width=\"100\" height=\"100\" src=\"" . $obj->url['doclib_url'] . "\"></a><div id=\"" . $obj->properties['qshare:sharedId'] . "\" class=\"uk-modal\"><div class=\"uk-modal-dialog uk-modal-dialog-frameless\"><a class=\"uk-modal-close uk-close uk-close-alt\"></a><img alt=\"nahled\" src=\"" . $obj->url['imgpreview_url'] . "\"></div></div>";
				break;
			case 'rss': 	// TODO! zde vytvořit plnohodnotnou šablonu pro RSS s použitím Feedwriter
				
				break;
			case 'json':	//pro ws a další, například používá seblod šablona rss
			    $output = array();
				$output['cmis:name'] = $obj->properties['cmis:name'];
				$output['qshare:sharedId'] = $obj->properties['qshare:sharedId'];
				$output['cmis:contentStreamLength'] = $obj->properties['cmis:contentStreamLength'];
				$output['cmis:lastModificationDate'] = $obj->properties['cmis:lastModificationDate'];
				$output['quickshare_url'] = $obj->url['quickshare_url'];
				$output['content_url'] = $obj->url['content_url'];
				$output['imgpreview_url'] = $obj->url['imgpreview_url'];
				$output['doclib_url'] = $obj->url['doclib_url'];
				break;
			
			default:		//pokud neni nic z vyse uvedeneho, budeme volat php šablony (dodelat)
				echo "default cmis template";
		}
		
		//ulozime statistiky - pouze pokud neni v cache a pouze pokud je zapnute SEF, aby se neukladaly non-sef url
		$conf = JFactory::getConfig();
		if ($conf->get('sef') == 1) {
			$this->MnCmisUpdateStat('file', $obj->properties['cmis:name'], $obj->properties['alfcmis:nodeRef']);
		}
		
		$this->add_render_data('html', $output);
		
		return;
		// END TEMPLATE PARSE
	}
	
	function MnCmisUpdateStat($type, $name, $curr_nodeRef) //musime vzit noderef z aktualniho objektu, jinak bere nodeRef složky v pripadě složek a stromu
	{
		$url = JUri::getInstance();
		$sef_url = $url->getPath();
		
		$id = md5(serialize($sef_url . $curr_nodeRef));
		
		$db = JFactory::getDbo();
		$query = "INSERT IGNORE INTO #__mn_cmis_stat (id,noderef,type,name, sef_url) VALUES ('" .$id . "','" . $curr_nodeRef . "','" . $type . "','" . $name . "','" . $sef_url . "')"; //nepotrebujeme nic aktualizovat ON DUPLICATE KEY UPDATE, ignore resi existujici zaznamy... 
		$db->setQuery($query);
		$db->execute();
	}	
}

/*
$obj->properties['qshare:sharedId']
$obj->properties['alfcmis:nodeRef']
$obj->properties['cmis:name']
$obj->properties['cm:title']
$obj->properties['cmis:contentStreamLength'],*/