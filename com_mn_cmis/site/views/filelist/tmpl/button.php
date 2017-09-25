<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

defined('_JEXEC') or die;

$e_name = addslashes( $_GET['e_name'] );

$script  = 'function insertDocument() {' . "\n\t";
$script .= 'var codeInsert = document.getElementById("codeInsert").value;' . "\n\t";

$script .= "var template = jQuery('input[name=\"template\"]:checked').val(); " . "\n\t";
$script .= "var order = jQuery('input[name=\"order\"]:checked').val(); " . "\n\t";

$script .= 'if (window.parent) window.parent.InsertAlfrescoDoc(codeInsert, template, order);' . "\n\t";
$script .= '}' . "\n";


$doc = JFactory::getDocument();
$doc->addScriptDeclaration($script);

?>
<form class="form">
  <h3>Vložit dokument z Alfresca</h3>
  <fieldset>
    <input type="text" id="codeInsert" name="codeInsert" class="form-control" placeholder="Zadejte nodeRef"/>

    
    <!-- template -->
    <div class="form-group">
      <label class="col-md-4 control-label" for="template">Vyberte způsob zobrazení</label>
      <div class="col-md-4">
      <div class="radio">
        <label for="template-0">
          <input type="radio" name="template" id="template-0" value="soubor" checked="checked">
          Dokument
        </label>
    	</div>
      <div class="radio">
        <label for="template-1">
          <input type="radio" name="template" id="template-1" value="slozka">
          Složka dokumentů
        </label>
    	</div>
      <div class="radio">
        <label for="template-2">
          <input type="radio" name="template" id="template-2" value="galerie">
          Galerie obrázků (složka)
        </label>
    	</div>
      <div class="radio">
        <label for="template-3">
          <input type="radio" name="template" id="template-3" value="thumbnail">
          Jeden obrázek - malá velikost
        </label>
    	</div>
      <div class="radio">
        <label for="template-4">
          <input type="radio" name="template" id="template-4" value="imgpreview">
          Jeden obrázek - střední velikost
        </label>
    	</div>
      </div>
    </div>
    
    
    
    <!-- order -->
    <div class="form-group">
      <label class="col-md-4 control-label" for="order">Pokud se jedná o složku, jak se májí seřadit dokumenty ve složce?</label>
      <div class="col-md-4">
      <div class="radio">
        <label for="order-0">
          <input type="radio" name="order" id="order-0" value="sestupne" checked="checked">
          Sestupně
        </label>
    	</div>
      <div class="radio">
        <label for="order-1">
          <input type="radio" name="order" id="order-1" value="vzestupne">
          Vzestupně
        </label>
    	</div>
      </div>
    </div>
    
    </fieldset>
  
  
  <button onclick="insertDocument();" class="btn btn-primary"><?php echo JText::_('Vložit'); ?></button>
</form>