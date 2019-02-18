<?php
/**
 * Created with PhpStorm.
 * Project: tobier.de
 * User: Tobias Keller
 * Date: 22.08.2018
 * Time: 20:00
 *
 * This class filters the content of posts
 * and adds links to the wiki post type
 * if the setting is in the customizer activated
 */

$start_single_content_filter = new single_content_filter();

class single_content_filter {
	public function __construct() {
		add_filter( 'the_content', array( $this, 'add_wiki_links') );
		add_action( 'customize_register', array( $this, 'add_content_filter_to_customizer' ) );
	}

	public function add_wiki_links( $the_content ){

		if ( get_post()->post_type == 'post' && get_theme_mod('tobier_content_filter') == true OR
		     get_post()->post_type == 'tobier_wiki' && get_theme_mod('tobier_content_filter') == true) {

			$sites = $this->get_wiki_links();

			foreach ( $sites as $site ) {
				if ($site['link'] != get_the_permalink() ) {
					$title       = '/ ' . $site['title'] . ' /';
					$new_link    = ' <a class="wiki-link" href="' . $site['link'] . '" title="' . $site['title'] . '" data-tooltip="Erklärung zu \'' . $site['title'] . '\' öffnen" target="_blank">' . $site['title'] . '</a> ';
					$the_content = preg_replace( $title, $new_link, $the_content, 1 );
				}
			}

		}

		return $the_content;
	}

	public function get_wiki_links(){
		// get custom post type articles
		$query = new WP_Query(array(
			'post_type' => 'tobier_wiki',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby'   => 'title',
			'order' => 'ASC',
		));

		$sites = array();

		while ($query->have_posts()) {
			$query->the_post();
			$sites[] = array('title' => get_the_title(), 'link' => get_the_permalink() );
		}
		wp_reset_query();

		return $sites;
	}

	public function add_content_filter_to_customizer( $wp_customize ){

		$wp_customize->add_section( 'tobier_content_filter' , array(
			'title'      => __( 'Wiki Content Filter', 'tobier' ),
			'description'=> __( 'Filtert den Content und fügt Wiki Links ein', 'tobier' ),
			'priority'   => 30,
		) );

		$wp_customize->add_setting(
			'tobier_content_filter',
			array(
				'default' => 0,
				'transport' => 'refresh',
			)
		);

		$wp_customize->add_control(
			'tobier_content_filter',
			array(
				'label' => __( 'Filter aktivieren', 'tobier' ),
				'section'  => 'tobier_content_filter',
				'priority' => 10,
				'type'=> 'checkbox',
				'capability' => 'edit_theme_options',
			)
		);
	}
}