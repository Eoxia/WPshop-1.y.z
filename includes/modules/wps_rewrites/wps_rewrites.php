<?php if ( !defined( 'ABSPATH' ) ) exit;

// Rewrite wpshop_categories
$options = get_option( 'wpshop_catalog_categories_option', null );
$slug = !empty($options['wpshop_catalog_categories_slug']) ? $options['wpshop_catalog_categories_slug'] : WPSHOP_CATALOG_PRODUCT_NO_CATEGORY;
( empty( $options['wpshop_catalog_categories_slug'] ) || $options['wpshop_catalog_categories_slug'] == '/' ) ? new Custom_Taxonomy_Rewrite( WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, $slug, false ) : false;

// Rewrite wpshop_products
$options = get_option( 'wpshop_catalog_product_option', null );
$slug = !empty( $options['wpshop_catalog_product_slug'] ) ? $options['wpshop_catalog_product_slug'] : '/';
$categories = !empty( $options['wpshop_catalog_product_slug_with_category'] );
( empty( $options['wpshop_catalog_product_slug'] ) || $options['wpshop_catalog_product_slug'] == '/' ) ? new Custom_Post_Type_Rewrite( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, $slug, $categories ) : false;

class Custom_Taxonomy_Rewrite {
	private $post_type;
	private $taxonomy;
	private $enable_rewrites;
	private $use_slug;
	private $use_hierarchy_cat_slug;
	public function __construct( $taxonomy, $post_type, $slug = '/', $hierarchy = false ) {
		$this->post_type = $post_type;
		$this->taxonomy = $taxonomy;
		$this->use_slug = $slug;
		$this->use_hierarchy_cat_slug = $hierarchy;
		add_action( 'init', array( $this, 'rewrite' ), 11 );
		add_action( 'created_' . $this->taxonomy, array( $this, 'flush_rewrite' ) );
		add_action( 'edited_' . $this->taxonomy, array( $this, 'flush_rewrite' ) );
		add_action( 'delete_' . $this->taxonomy, array( $this, 'flush_rewrite' ) );
		add_filter( 'term_link', array( $this, 'permalink' ), 10, 3 );
	}
	public function rewrite() {
		global $wp_rewrite;
		$this->enable_rewrites = $wp_rewrite->use_trailing_slashes;
		if( $this->enable_rewrites ) {
			if( empty( $this->use_slug ) || $this->use_slug == '/' ) {
				if( $this->use_hierarchy_cat_slug ) {
					$slug = '';
					$rewrite = array();
					$terms = get_terms( $this->taxonomy, array( 'parent' => 0, 'hide_empty' => false ) );
					foreach( $terms as $term ) {
						$this->rewrite_term_hierarchy( $term, $rewrite, $slug, '/' );
					}
					foreach( $rewrite as $way ) {
						add_rewrite_rule( $way . 'feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?' . $this->taxonomy . '=$matches[1]&feed=$matches[2]', 'top' );
						add_rewrite_rule( $way . '(feed|rdf|rss|rss2|atom)/?$', 'index.php?' . $this->taxonomy . '=$matches[1]&feed=$matches[2]', 'top' );
						add_rewrite_rule( $way . 'page/?([0-9]{1,})/?$', 'index.php?' . $this->taxonomy . '=$matches[1]&paged=$matches[2]', 'top' );
						add_rewrite_rule( $way . '?$', 'index.php?' . $this->taxonomy . '=$matches[1]', 'top' );
					}
				} else {
					$terms = get_terms( $this->taxonomy, array( 'fields' => 'id=>slug', 'hide_empty' => false ) );
					foreach( $terms as $term ) {
						add_rewrite_rule( '(' . $term . ')/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?' . $this->taxonomy . '=$matches[1]&feed=$matches[2]', 'top' );
						add_rewrite_rule( '(' . $term . ')/(feed|rdf|rss|rss2|atom)/?$', 'index.php?' . $this->taxonomy . '=$matches[1]&feed=$matches[2]', 'top' );
						add_rewrite_rule( '(' . $term . ')/page/?([0-9]{1,})/?$', 'index.php?' . $this->taxonomy . '=$matches[1]&paged=$matches[2]', 'top' );
						add_rewrite_rule( '(' . $term . ')/?$', 'index.php?' . $this->taxonomy . '=$matches[1]', 'top' );
					}
				}
			} else {
				if( $this->use_hierarchy_cat_slug ) {
					$slug = '/';
					$rewrite = array();
					$terms = get_terms( $this->taxonomy, array( 'parent' => 0, 'hide_empty' => false ) );
					foreach( $terms as $term ) {
						$this->rewrite_term_hierarchy( $term, $rewrite, $slug, '/' );
					}
					foreach( $rewrite as $way ) {
						add_rewrite_rule( $this->use_slug . $way . 'feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?' . $this->taxonomy . '=$matches[1]&feed=$matches[2]', 'top' );
						add_rewrite_rule( $this->use_slug . $way . '(feed|rdf|rss|rss2|atom)/?$', 'index.php?' . $this->taxonomy . '=$matches[1]&feed=$matches[2]', 'top' );
						add_rewrite_rule( $this->use_slug . $way . 'page/?([0-9]{1,})/?$', 'index.php?' . $this->taxonomy . '=$matches[1]&paged=$matches[2]', 'top' );
						add_rewrite_rule( $this->use_slug . $way . '?$', 'index.php?' . $this->taxonomy . '=$matches[1]', 'top' );
					}
				} else {
					add_rewrite_rule( $this->use_slug . '/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?' . $this->taxonomy . '=$matches[1]&feed=$matches[2]', 'top' );
					add_rewrite_rule( $this->use_slug . '/([^/]+)/(feed|rdf|rss|rss2|atom)/?$', 'index.php?' . $this->taxonomy . '=$matches[1]&feed=$matches[2]', 'top' );
					add_rewrite_rule( $this->use_slug . '/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?' . $this->taxonomy . '=$matches[1]&paged=$matches[2]', 'top' );
					add_rewrite_rule( $this->use_slug . '/([^/]+)/?$', 'index.php?' . $this->taxonomy . '=$matches[1]', 'top' );
				}
			}
		}
	}
	private function rewrite_term_hierarchy( $parent, &$rewrite, $slug, $separator ) {
		$childs = get_terms( $this->taxonomy, array( 'parent' => $parent->term_id, 'hide_empty' => false ) );
		foreach( $childs as $child ) {
			$this->rewrite_term_hierarchy( $child, $rewrite, $slug . $parent->slug . $separator, $separator );
		}
		$rewrite[] = $slug . '(' . $parent->slug . ')' . $separator;
	}
	public function flush_rewrite() {
		if( $this->enable_rewrites ) {
			$this->rewrite();
			flush_rewrite_rules();
		}
	}
	public function permalink( $permalink, $term, $taxonomy ) {
		if( $this->enable_rewrites ) {
			if( $taxonomy === $this->taxonomy ) {
				if( empty( $this->use_slug ) || $this->use_slug == '/' ) {
					if( $this->use_hierarchy_cat_slug ) {
						$term_permalink = '';
						$this->permalink_term_hierarchy( $term, $term_permalink, $taxonomy, '/' );
						$permalink = get_site_url() . '/' . $term_permalink;
					} else {
						$permalink = get_site_url() . '/' . $term->slug;
					}
				} else {
					if( $this->use_hierarchy_cat_slug ) {
						$term_permalink = '';
						$this->permalink_term_hierarchy( $term, $term_permalink, $taxonomy, '/' );
						$permalink = get_site_url() . '/' . $this->use_slug . '/' . $term_permalink;
					} else {
						$permalink = get_site_url() . '/' . $this->use_slug . '/' . $term->slug;
					}
				}
			}
		}
		return $permalink;
	}
	private function permalink_term_hierarchy( $term, &$permalink, $taxonomy, $separator ) {
		if( $term->parent == 0 ) {
			$permalink = $term->slug . $separator . $permalink;
		} else {
			$permalink = $term->slug . $separator . $permalink;
			$term_parent = get_term( $term->parent, $taxonomy );
			$this->permalink_term_hierarchy( $term_parent, $permalink, $taxonomy, $separator );
		}
	}
}

class Custom_Post_Type_Rewrite {
	private $post_type;
	private $taxonomy;
	private $enable_rewrites;
	private $use_slug;
	private $use_cat_slug;
	private $use_hierarchy_cat_slug;
	public function __construct( $post_type, $taxonomy, $slug = '/', $categories = false ) {
		$this->post_type = $post_type;
		$this->taxonomy = $taxonomy;
		$this->use_slug = $slug;
		if( is_bool( $categories ) ) {
			$this->use_cat_slug = $categories;
			$this->use_hierarchy_cat_slug = false;
		} elseif( $categories == 'hierarchy' ) {
			$this->use_cat_slug = true;
			$this->use_hierarchy_cat_slug = true;
		}
		add_action( 'init', array( $this, 'rewrite' ), 11 );
		add_action( 'created_' . $this->taxonomy, array( $this, 'flush_rewrite' ) );
		add_action( 'edited_' . $this->taxonomy, array( $this, 'flush_rewrite' ) );
		add_action( 'delete_' . $this->taxonomy, array( $this, 'flush_rewrite' ) );
		add_filter( 'post_type_link', array( $this, 'permalink' ), 10, 3 );
		add_action( 'pre_get_posts', array( $this, 'query' ) );
	}
	public function rewrite() {
		global $wp_rewrite;
		$this->enable_rewrites = $wp_rewrite->use_trailing_slashes;
		if( $this->enable_rewrites ) {
			if( empty( $this->use_slug ) || $this->use_slug == '/' ) {
				if( $this->use_cat_slug ) {
					if( $this->use_hierarchy_cat_slug ) {
						add_rewrite_tag('%taxonomies%', '(.*)');
						$slug = '(';
						$rewrite = array();
						$terms = get_terms( $this->taxonomy, array( 'parent' => 0, 'hide_empty' => false ) );
						foreach( $terms as $term ) {
							$this->rewrite_term_hierarchy( $term, $rewrite, $slug, '/' );
						}
						foreach( array_reverse( $rewrite ) as $way ) {
							add_rewrite_rule( $way . ')([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]&feed=$matches[3]', 'top' );
							add_rewrite_rule( $way . ')([^/]+)/(feed|rdf|rss|rss2|atom)/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]&feed=$matches[3]', 'top' );
							add_rewrite_rule( $way . ')([^/]+)/page/?([0-9]{1,})/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]&paged=$matches[3]', 'top' );
							add_rewrite_rule( $way . ')([^/]+)/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]', 'top' );
						}
					} else {
						add_rewrite_tag('%taxonomies%', '(.*)');
						$terms = get_terms( $this->taxonomy, array( 'fields' => 'id=>slug', 'hide_empty' => false ) );
						foreach( $terms as $term ) {
							add_rewrite_rule( '(' . $term . ')/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]&feed=$matches[3]', 'top' );
							add_rewrite_rule( '(' . $term . ')/([^/]+)/(feed|rdf|rss|rss2|atom)/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]&feed=$matches[3]', 'top' );
							add_rewrite_rule( '(' . $term . ')/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]&paged=$matches[3]', 'top' );
							add_rewrite_rule( '(' . $term . ')/([^/]+)/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]', 'top' );
						}
					}
				} /*else {
					Only in the query func
				} */
			} else {
				if( $this->use_cat_slug ) {
					if( $this->use_hierarchy_cat_slug ) {
						add_rewrite_tag('%taxonomies%', '(.*)');
						$slug = '/(';
						$rewrite = array();
						$terms = get_terms( $this->taxonomy, array( 'parent' => 0, 'hide_empty' => false ) );
						foreach( $terms as $term ) {
							$this->rewrite_term_hierarchy( $term, $rewrite, $slug, '/' );
						}
						foreach( array_reverse( $rewrite ) as $way ) {
							add_rewrite_rule( $this->use_slug . $way . ')([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]&feed=$matches[3]', 'top' );
							add_rewrite_rule( $this->use_slug . $way . ')([^/]+)/(feed|rdf|rss|rss2|atom)/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]&feed=$matches[3]', 'top' );
							add_rewrite_rule( $this->use_slug . $way . ')([^/]+)/page/?([0-9]{1,})/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]&paged=$matches[3]', 'top' );
							add_rewrite_rule( $this->use_slug . $way . ')([^/]+)/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]', 'top' );
						}
					} else {
						add_rewrite_tag('%taxonomies%', '(.*)');
						$terms = get_terms( $this->taxonomy, array( 'fields' => 'id=>slug', 'hide_empty' => false ) );
						foreach( $terms as $term ) {
							add_rewrite_rule( $this->use_slug . '/(' . $term . ')/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]&feed=$matches[3]', 'top' );
							add_rewrite_rule( $this->use_slug . '/(' . $term . ')/([^/]+)/(feed|rdf|rss|rss2|atom)/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]&feed=$matches[3]', 'top' );
							add_rewrite_rule( $this->use_slug . '/(' . $term . ')/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]&paged=$matches[3]', 'top' );
							add_rewrite_rule( $this->use_slug . '/(' . $term . ')/([^/]+)/?$', 'index.php?taxonomies=$matches[1]&' . $this->post_type . '=$matches[2]', 'top' );
						}
					}
				} else {
					add_rewrite_rule( $this->use_slug . '/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?' . $this->post_type . '=$matches[1]&feed=$matches[2]', 'top' );
					add_rewrite_rule( $this->use_slug . '/([^/]+)/(feed|rdf|rss|rss2|atom)/?$', 'index.php?' . $this->post_type . '=$matches[1]&feed=$matches[2]', 'top' );
					add_rewrite_rule( $this->use_slug . '/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?' . $this->post_type . '=$matches[1]&paged=$matches[2]', 'top' );
					add_rewrite_rule( $this->use_slug . '/([^/]+)/?$', 'index.php?' . $this->post_type . '=$matches[1]', 'top' );
					//'rewrite' => array( 'slug' => $this->post_type ) || true
				}
			}
		}
	}
	private function rewrite_term_hierarchy( $parent, &$rewrite, $slug, $separator ) {
		$rewrite[] = $slug . $parent->slug . $separator;
		$childs = get_terms( $this->taxonomy, array( 'parent' => $parent->term_id, 'hide_empty' => false ) );
		foreach( $childs as $child ) {
			$this->rewrite_term_hierarchy( $child, $rewrite, $slug . $parent->slug . $separator, $separator );
		}
	}
	public function flush_rewrite() {
		if( $this->enable_rewrites ) {
			$this->rewrite();
			flush_rewrite_rules();
		}
	}
	public function permalink( $permalink, $post, $leavename ) {
		if( $this->enable_rewrites ) {
			if( $post->post_type == $this->post_type ) {
				if( empty( $this->use_slug ) || $this->use_slug == '/' ) {
					if( $this->use_cat_slug ) {
						if( $this->use_hierarchy_cat_slug ) {
							$terms = get_the_terms( $post->ID, $this->taxonomy );
							if( !empty( $terms ) ) {
								$term = $terms[0];
								$post_permalink = '';
								$this->permalink_term_hierarchy( $term, $post_permalink, $this->taxonomy, '/' );
								$permalink = get_site_url() . '/' . $post_permalink . $post->post_name . '/';
							}
						} else {
							$terms = get_the_terms( $post->ID, $this->taxonomy );
							if( !empty( $terms ) ) {
								$term = $terms[0];
								$permalink = get_site_url() . '/' . $term->slug . '/' . $post->post_name . '/';
							}
						}
					} else {
						$permalink = get_site_url() . '/' . $post->post_name . '/';
					}
				} else {
					if( $this->use_cat_slug ) {
						if( $this->use_hierarchy_cat_slug ) {
							$terms = get_the_terms( $post->ID, $this->taxonomy );
							if( !empty( $terms ) ) {
								$term = $terms[0];
								$post_permalink = '';
								$this->permalink_term_hierarchy( $term, $post_permalink, $this->taxonomy, '/' );
								$permalink = get_site_url() . '/' . $this->use_slug . '/' . $post_permalink . $post->post_name . '/';
							}
						} else {
							$terms = get_the_terms( $post->ID, $this->taxonomy );
							if( !empty( $terms ) ) {
								$term = $terms[0];
								$permalink = get_site_url() . '/' . $this->use_slug . '/' . $term->slug . '/' . $post->post_name . '/';
							}
						}
					} else {
						$permalink = get_site_url() . '/' . $this->use_slug . '/' . $post->post_name . '/';
					}
				}
			}
		}
		return $permalink;
	}
	private function permalink_term_hierarchy( $term, &$permalink, $taxonomy, $separator ) {
		if( $term->parent == 0 ) {
			$permalink = $term->slug . $separator . $permalink;
		} else {
			$permalink = $term->slug . $separator . $permalink;
			$term_parent = get_term( $term->parent, $taxonomy );
			$this->permalink_term_hierarchy( $term_parent, $permalink, $taxonomy, $separator );
		}
	}
	public function query( $query ) {
		if( $this->enable_rewrites ) {
			if ( empty( $this->use_slug ) || $this->use_slug == '/' && !$this->use_cat_slug ) {
				if ( !is_admin() && $query->is_main_query() && !isset( $query->page ) && count( $query->query ) == 2 ) {
					$query->set('post_type', array( 'page', 'post', $this->post_type ) );
				}
			}
		}
		return $query;
	}
}
