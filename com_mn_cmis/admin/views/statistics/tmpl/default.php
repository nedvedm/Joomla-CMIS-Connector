<?php
/**
 * @package com_mn_cmis
 * @author Martin Nedved
 * @copyright (C) 2013 Martin Nedved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

defined('_JEXEC') or die;
?>
<h3>
	<?php echo JText::_('COM_MN_CMIS') ?>
</h3>

<div class="row-fluid">
	<div class="span6">
		<p class="tab-description"><?php echo JText::sprintf('COM_MN_CMIS_STATISTICS_STATS_DESCRIPTION', number_format($this->data->term_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR')), number_format($this->data->link_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR')), number_format($this->data->taxonomy_node_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR')), number_format($this->data->taxonomy_branch_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR'))); ?></p>
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th>
						<?php echo JText::_('COM_MN_CMIS_STATISTICS_LINK_TYPE_HEADING');?>
					</th>
					<th>
						<?php echo JText::_('COM_MN_CMIS_STATISTICS_LINK_TYPE_COUNT');?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<strong><?php echo JText::_('COM_MN_CMIS_STATISTICS_NODEREF_TOTAL'); ?></strong>
					</td>
					<td>
						<span class="badge badge-info"><?php echo number_format($this->data->nodeRef_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR'));?></span>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo JText::_('COM_MN_CMIS_STATISTICS_FILE_TOTAL'); ?></strong>
					</td>
					<td>
						<span class="badge badge-info"><?php echo number_format($this->data->file_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR'));?></span>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo JText::_('COM_MN_CMIS_STATISTICS_FOLDER_TOTAL'); ?></strong>
					</td>
					<td>
						<span class="badge badge-info"><?php echo number_format($this->data->folder_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR'));?></span>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo JText::_('COM_MN_CMIS_STATISTICS_ROOTFOLDER_TOTAL'); ?></strong>
					</td>
					<td>
						<span class="badge badge-info"><?php echo number_format($this->data->rootfolder_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR'));?></span>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo JText::_('COM_MN_CMIS_STATISTICS_TOTAL'); ?></strong>
					</td>
					<td>
						<span class="badge badge-info"><?php echo number_format($this->data->id_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR')); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
