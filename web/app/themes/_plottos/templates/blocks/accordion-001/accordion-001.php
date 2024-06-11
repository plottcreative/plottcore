<section class="accordion-001">
	<div class="accordion-001__container container">
		<div class="accordion-001__row row">
			<div class="col-12 accordion-001__col">
				<?php foreach ($items as $item) : ?>
					<div class="accordion-001__accordion">
						<div class="accordion-001__accordion-heading">
							<h3><?php echo $item['heading']; ?></h3>
						</div>
						<div class="accordion-001__accordion-content">
							<?php echo $item['content']; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
