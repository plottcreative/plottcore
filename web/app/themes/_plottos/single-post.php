<?php get_header(); ?>
<article class="article">
	<section class="article-page">
		<section class="article-page__header">
			<div class="container">
				<div class="row justify-content-center">
					<div class="col-xl-9">
					<h1 class="article-page__header-heading"><?php echo the_title(); ?></h1>
					</div>
				</div>
			</div>
		</section>
		<section class="article-page__body">
			<div class="container">
				<div class="row justify-content-center">
					<div class="col-xl-9">
						<div class="article-page__body-content">
							<?php echo the_content(); ?>
						</div>
					</div>
				</div>
			</div>
		</section>
	</section>
</article>
<?php get_footer(); ?>


