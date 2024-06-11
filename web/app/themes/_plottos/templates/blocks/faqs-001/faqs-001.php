<section class="faqs-001">
	<div class="faqs-001__container container">
		<div class="faqs-001__row row">
			<div class="col-12 faqs-001__col">
				<?php foreach ($faqs as $faq) : ?>
					<div class="faqs-001__accordion">
						<div class="faqs-001__accordion-heading">
							<h3><?php echo $faq['question']; ?></h3>
						</div>
						<div class="faqs-001__accordion-content">
							<?php echo $faq['answer']; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<?php

global $schema;

$schema = array(
	'@context' => 'https://schema.org',
	'@type' => 'FAQPage',
	'mainEntity' => array()
);

foreach ($faqs as $faq) {
	$questions = array(
		'@type' => 'Question',
		'name' => $faq['question'],
		'acceptedAnswer' => array(
			'@type' => 'Answer',
			'text' => $faq['answer'],
		)
	);
	array_push($schema['mainEntity'], $questions);
}

function plott_add_schema($schema)
{
	global $schema;

	echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
}

add_action('wp_footer', 'plott_add_schema', 100);

?>
