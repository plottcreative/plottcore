<section class="image-content-001">
	<div class="image-content-001__container container">
		<div
			class="image-content-001__row row align-items-center<?php if ($image_position === 'right') echo ' flex-lg-row-reverse'; ?>">
			<div class="image-content-001__col col-lg-6">
				<div class="image-content-001__image" style="--aspect-ratio: 16/9">
					<?php echo  \PLOTT_THEME\Inc\Images::get_instance()->img_srcset($image); ?>
				</div>
			</div>
			<div class="image-content-001__col col-lg-6">
				<div class="image-content-001__content-container">
					<?php if ($heading) : ?>
						<h2 class="image-content-001__heading"><?php echo $heading; ?></h2>
					<?php endif; ?>
					<?php if ($content) : ?>
						<div class="image-content-001__content"><?php echo $content; ?></div>
					<?php endif; ?>
					<?php if ($page_link): \PLOTT_THEME\Inc\Links::get_instance()->render($page_link, 'btn btn-sm btn-primary'); endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
