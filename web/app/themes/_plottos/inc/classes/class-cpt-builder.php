<?php

namespace PLOTT_THEME\Inc;

class CPT_Builder {

	public string $post_type_name;
	public array $post_type_args;
	public array $post_type_labels;
	public string $icon;

	protected function __construct(string $name, string $icon, array $args = [], array $labels = []) {
		$this->setup($name, $icon, $args, $labels);
	}

	protected function setup(string $name, string $icon, array $args = [], array $labels = []): void {
		$this->icon = $icon;
		$this->post_type_name = strtolower(str_replace(' ', '-', $name));
		$this->post_type_args = $args;
		$this->post_type_labels = $labels;

		add_action('init', [$this, 'register_post_type']);
	}


	public function register_post_type(): void {
		$name = ucwords(str_replace('-', ' ', $this->post_type_name));
		$plural = $name . 's';
		if (str_ends_with($name, 'y')) {
			$name = substr($name, 0, -1);
			$plural = $name . 'ies';
		}

		$labels = array_merge([
			'name'                  => _x($plural, 'post type general name'),
			'singular_name'         => _x($name, 'post type singular name'),
			'add_new'               => _x('Add New', strtolower($name)),
			'add_new_item'          => __('Add New ' . $name),
			'edit_item'             => __('Edit ' . $name),
			'new_item'              => __('New ' . $name),
			'all_items'             => __('All ' . $plural),
			'view_item'             => __('View ' . $name),
			'search_items'          => __('Search ' . $plural),
			'not_found'             => __('No ' . strtolower($plural) . ' found'),
			'not_found_in_trash'    => __('No ' . strtolower($plural) . ' found in Trash'),
			'parent_item_colon'     => '',
			'menu_name'             => $plural
		], $this->post_type_labels);

		$args = array_merge([
			'label'                 => $plural,
			'labels'                => $labels,
			'public'                => true,
			'show_ui'               => true,
			'supports'              => ['title', 'editor', 'thumbnail'],
			'show_in_nav_menus'     => true,
			'_builtin'              => false,
			'exclude_from_search'   => false,
			'menu_icon'             => $this->icon,
		], $this->post_type_args);

		register_post_type($this->post_type_name, $args);
	}

	public static function create(string $name, string $icon, array $args = [], array $labels = []): self {
		return new self($name, $icon, $args, $labels);
	}
}
