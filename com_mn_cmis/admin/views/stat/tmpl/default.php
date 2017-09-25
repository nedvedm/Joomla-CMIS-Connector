<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
 
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>

<form action="index.php?option=com_mn_cmis&view=stat" method="post" id="adminForm" name="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
		
		<table class="table table-striped table-hover">
			<thead>
			<tr>
				<th width="1%"><?php echo JText::_('COM_MN_CMIS_STAT_NUM'); ?></th>
				<th width="2%">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th width="50%">
					<?php echo JHtml::_('searchtools.sort', 'COM_MN_CMIS_STAT_NAME', 'name', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('searchtools.sort', 'COM_MN_CMIS_STAT_COUNT', 'nodeRef_count', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('searchtools.sort', 'COM_MN_CMIS_STAT_TYPE', 'type', $listDirn, $listOrder); ?>
				</th>
				<th width="40%">
					<?php echo JText::_('COM_MN_CMIS_STAT_URL'); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="5">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php if (!empty($this->items)) : ?>
					<?php foreach ($this->items as $i => $row) :
						//$link = JRoute::_('index.php?option=com_mn_cmis&task=stat.detail&id=' . $row->id);
						$link = $this->alfresco_url . "/share/page/document-details?nodeRef=" . $row->noderef;
					?>
						<tr>
							<td><?php echo $this->pagination->getRowOffset($i); ?></td>
							<td>
								<?php echo JHtml::_('grid.id', $i, $row->id); ?>
							</td>
							<td>
								<a href="<?php echo $link; ?>" target="_blank" title="<?php echo JText::_('COM_MN_CMIS_EDIT_MN_CMIS'); ?>">
									<?php echo $row->name; ?>
								</a>
							</td>
							<td align="center">
								<p class="badge ">
								<?php echo $row->nodeRef_count; ?>
								</p>
							</td>
							<td align="center">
								<?php echo $row->type; ?>
							</td>
							<td align="center">
								<ul style="list-style: none;">
								<?php
									foreach (explode(",",$row->sef_urls) as $url) {
										echo "<li><a target=\"_blank\" href=\"" . $url . "\">" . $url . "</a></li>";
									}
								?>
								</ul>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<?php endif; ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>	
