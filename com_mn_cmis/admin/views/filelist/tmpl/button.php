<?php

defined('_JEXEC') or die;

if (!$app = @include(JPATH_SITE . '/components/com_mn_cmis/views/filelist/tmpl/button.php')) {
    return;
}