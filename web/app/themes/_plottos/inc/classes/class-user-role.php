<?php

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class User_Role
{

	use Singleton;

	protected function __construct()
	{
		$this->setup_hooks();
	}

	protected function setup_hooks(): void
	{

	}

	function userCaps() : void
	{
		$roleObject = get_role( 'editor' );
		if (!$roleObject->has_cap( 'edit_theme_options' ) ) {
			$roleObject->add_cap( 'edit_theme_options' );
		}
	}

}
