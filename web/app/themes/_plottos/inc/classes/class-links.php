<?php

/**
 * PLOTT Link Builder
 */

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class Links
{

	use Singleton;

	protected function __construct()
	{
		$this->setup_hooks();
	}

	protected function setup_hooks(): void
	{

	}

	function render($link, $classes = 'btn', $type = 'a'): void
	{
		$linkTarget = $link['target'] ? $link['target'] : '_self';
		echo '
	<' . $type . ' href="' . $link['url'] . '"class="' . $classes . '" title="' . $link['title'] . '" role="link" target="' . $linkTarget . '">' . $link['title'] . '</' . $type . '>';
	}


}
