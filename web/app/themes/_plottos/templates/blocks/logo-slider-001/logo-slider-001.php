<div id="clients" style="margin-top: 40px">
	<ul id="clients-list" class="clearfix">
		<?php foreach ($logos as $logo): ?>
			<li>
				<?php echo \PLOTT_THEME\Inc\Images::get_instance()->img_srcset($logo, 'srcset-image'); ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div><!-- @end #clients -->
