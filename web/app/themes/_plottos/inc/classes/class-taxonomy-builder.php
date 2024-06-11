<?php

namespace PLOTT_THEME\Inc;

class Taxonomy_Builder {
	public string $taxonomy;
	public array $object_types;
	public array $taxonomy_args;
	public array $taxonomy_labels;

	protected function __construct(string $taxonomy, array $object_types, array $args = [], array $labels = []) {
		$this->setup($taxonomy, $object_types, $args, $labels);
	}

	protected function setup(string $taxonomy, array $object_types, array $args = [], array $labels = []): void {
		$this->taxonomy = strtolower(str_replace(' ', '_', $taxonomy));
		$this->object_types = $object_types;
		$this->taxonomy_args = $args;
		$this->taxonomy_labels = $labels;

		add_action('init', [$this, 'register_taxonomy']);
	}

	public function register_taxonomy(): void {
		$singular = ucwords(str_replace('_', ' ', $this->taxonomy));
		$plural = $singular . 's';

		$labels = array_merge([
			'name'                       => _x($plural, 'taxonomy general name'),
			'singular_name'              => _x($singular, 'taxonomy singular name'),
			'search_items'               => __('Search ' . $plural),
			'popular_items'              => __('Popular ' . $plural),
			'all_items'                  => __('All ' . $plural),
			'parent_item'                => __('Parent ' . $singular),
			'parent_item_colon'          => __('Parent ' . $singular . ':'),
			'edit_item'                  => __('Edit ' . $singular),
			'update_item'                => __('Update ' . $singular),
			'add_new_item'               => __('Add New ' . $singular),
			'new_item_name'              => __('New ' . $singular . ' Name'),
			'separate_items_with_commas' => __('Separate ' . strtolower($plural) . ' with commas'),
			'add_or_remove_items'        => __('Add or remove ' . strtolower($plural)),
			'choose_from_most_used'      => __('Choose from the most used ' . strtolower($plural)),
			'not_found'                  => __('No ' . strtolower($plural) . ' found'),
			'menu_name'                  => $plural,
		], $this->taxonomy_labels);

		$args = array_merge([
			'labels'             => $labels,
			'public'             => true,
			'show_ui'            => true,
			'show_in_nav_menus'  => true,
			'show_tagcloud'      => true,
			'hierarchical'       => true,
			'show_admin_column'  => true,
			'query_var'          => true,
			'rewrite'            => ['slug' => $this->taxonomy],
		], $this->taxonomy_args);

		register_taxonomy($this->taxonomy, $this->object_types, $args);
	}

	public static function create(string $taxonomy, array $object_types, array $args = [], array $labels = []): self {
		return new self($taxonomy, $object_types, $args, $labels);
	}
}
