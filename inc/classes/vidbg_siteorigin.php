<?php

/**
 * Video Background's class to add a video background to a SiteOrigin Page Builder row
 *
 * @author Push Labs https://pushlabs.co
 * @copyright Copyright (c) Push Labs (hello@pushlabs.co)
 * @since 2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Vidbg_SiteOrigin' ) ) {
	/**
	 * SiteOrigin Page Builder Integration
	 *
	 * @package Video Background/Video Background Pro
	 * @author Push Labs
	 * @version 1.0.0
	 */
  class Vidbg_SiteOrigin {

    // Class' properties
    private $prefix;
    private $group_name;
    protected $vidbg_atts;

    public function __construct() {
      // The data prefix for the attributes we'll add to SiteOrigin Page Builder
      $this->prefix = 'vidbg_so_';

      // The SiteOrigin Page Builder Row Group
      $this->group_name = 'vidbg_so';

      // $vidbg_atts will hold our [vidbg] shortcode attributes
      $this->vidbg_atts = array();

      // Add our filters to execute our methods
      add_filter( 'siteorigin_panels_row_style_fields', array( $this, 'register_fields' ) );
      add_filter( 'siteorigin_panels_row_style_groups', array( $this, 'create_group' ), 10, 3 );
      add_filter( 'siteorigin_panels_before_row', array( $this, 'generate_shortcode_before_row' ), 10, 3 );
    }

    /**
     * Create SiteOrigin group for Video Background on row
     *
     * @since 2.7.0
     */
    public function create_group( $groups, $post_id, $args ) {
      $groups[$this->group_name] = array(
        'name' => __( 'Video Background', 'video-background' ),
        'priority' => 25,
      );
      return $groups;
    }

    /**
     * Add fields to SiteOrigin Page Builder row
     *
     * @since 2.7.0
     */
    public function register_fields( $fields ) {
      $priority = 5;

      $fields[$this->prefix . 'mp4'] = array(
        'name'        => __( 'Link to .mp4', 'video-background' ),
        'type'        => 'url',
        'description' => __( 'Please specify the link to the .mp4 file.', 'video-background' ),
        'group'       => $this->group_name,
        'priority'    => $priority++,
      );
      $fields[$this->prefix . 'webm'] = array(
        'type'        => 'url',
        'name'        => __( 'Link to .webm', 'video-background' ),
        'description' => __( 'Please specify the link to the .webm file.', 'video-background' ),
        'group'       => $this->group_name,
        'priority'    => $priority++,
      );
      $fields[$this->prefix . 'poster'] = array(
        'type'        => 'image',
        'name'        => __( 'Fallback Image', 'video-background' ),
        'description' => __( 'Please upload a fallback image.', 'video-background' ),
        'group'       => $this->group_name,
        'priority'    => $priority++,
      );
      $fields[$this->prefix . 'overlay'] = array(
        'type'        => 'checkbox',
        'name'        => __( 'Enable Overlay?', 'video-background' ),
        'description' => __( 'Add an overlay over the video. This is useful if your text isn\'t readable with a video background.', 'video-background' ),
        'group'       => $this->group_name,
        'priority'    => $priority++,
      );
      $fields[$this->prefix . 'overlay_color'] = array(
        'type'        => 'color',
        'name'        => __( 'Overlay Color', 'video-background' ),
        'description' => __( 'If overlay is enabled, a color will be used for the overlay. You can specify the color here.', 'video-background' ),
        'default'     => '#000',
        'group'       => $this->group_name,
        'priority'    => $priority++,
      );
      $fields[$this->prefix . 'overlay_alpha'] = array(
        'type'        => 'text',
        'name'        => __( 'Overlay Opacity', 'video-background' ),
        'description' => __( 'Specify the opacity of the overlay. Accepts any value between 0.00-1.00 with 0 being completely transparent and 1 being completely invisible. Ex. 0.30', 'video-background' ),
        'default'     => '0.3',
        'group'       => $this->group_name,
        'priority'    => $priority++,
      );
      $fields[$this->prefix . 'loop'] = array(
        'type'        => 'checkbox',
        'name'        => __( 'Disable Loop?', 'video-background' ),
        'description' => __( 'Turn off the loop for Video Background. Once the video is complete, it will display the last frame of the video.', 'video-background' ),
        'group'       => $this->group_name,
        'priority'    => $priority++,
      );
      $fields[$this->prefix . 'muted'] = array(
        'type'        => 'checkbox',
        'name'        => __( 'Play the Audio?', 'video-background' ),
        'description' => __( 'Enabling this will play the audio of the video.', 'video-background' ),
        'group'       => $this->group_name,
        'priority'    => $priority++,
      );

			$fields = apply_filters( 'vidbg_siteorigin_fields', $fields );

      return $fields;
    }

    /**
     * Find all attributes with the prefix and add them to the $vidbg_atts array
     *
     * @since 2.7.0
     */
    public function get_vidbg_attributes( $siteorigin_row_atts ) {

      if ( $siteorigin_row_atts === null ) {
        return;
      }

      // Run a foreach loop on the SiteOrigin Page Builder Row atts
      foreach ( $siteorigin_row_atts as $attribute_key => $attribute ) {
        // Find the attributes with the $prefix
        if ( substr( $attribute_key, 0, strlen( $this->prefix ) ) === $this->prefix ) {
          // Remove the $prefix
          $attribute_key = substr( $attribute_key, strlen( $this->prefix ) );

          // Add the attribute to the $vidbg_atts array
          // Only add attribute if it's not empty
          if ( !empty( $attribute ) ) {
            $this->vidbg_atts[$attribute_key] = $attribute;
          }
        }
      }
    }

    /**
     * Generate the shortcode and place it before the SiteOrigin row
     *
     * @since 2.7.0
     */
    public function generate_shortcode_before_row( $output, $grid_item, $grid_attributes ) {

      // Use to test the attributes gathered in $grid_item
      // var_dump( $grid_item['style'] );

      // Get the $vidbg_atts
      $this->get_vidbg_attributes( $grid_item['style'] );

      if ( array_key_exists( 'muted', $this->vidbg_atts ) ) {
        $this->vidbg_atts['muted'] = $this->vidbg_atts['muted'] === true ? 'false' : 'true';
      }

      if ( array_key_exists( 'loop', $this->vidbg_atts ) ) {
        $this->vidbg_atts['loop'] = $this->vidbg_atts['loop'] === true ? 'false' : 'true';
      }

      if ( array_key_exists( 'poster', $this->vidbg_atts ) ) {
        $poster_src_arr = wp_get_attachment_image_src( $this->vidbg_atts['poster'], 'full' );
        $this->vidbg_atts['poster'] = $poster_src_arr[0];
      }

      // Create our container selector
      $unique_class = vidbg_create_unique_ref();
      $row_class = $unique_class . '-row';
      $container_class = $unique_class . '-container';

      // Add our class to the shortcode atts array
      $this->vidbg_atts['container'] = '.' . $container_class;

      // Add our source to the shortcode atts array
      $this->vidbg_atts['source'] = 'SiteOrigin Page Builder Integration';

      // Use to test the attributes created for $vidbg_atts
      // var_dump( $this->vidbg_atts );

      // Our jQuery code to add the container class to the container so we can target the SiteOrigin row
      $add_container_to_row = "
      jQuery(function($){
        $('." . $unique_class . "').next('.panel-grid').addClass('" . $row_class . "');

        if( $('.panel-grid." . $row_class . " > .siteorigin-panels-stretch').length ) {
          $('.panel-grid." . $row_class . " > .siteorigin-panels-stretch').addClass( '" . $container_class ."' );
        } else {
          $('.panel-grid." . $row_class . "').addClass( '" . $container_class ."' );
        }
      });
      ";

      // Add our "container to row" script
      wp_add_inline_script( 'vidbg-video-background', $add_container_to_row );

      // Construct the shortcode with our attributes
      $shortcode = vidbg_construct_shortcode( $this->vidbg_atts );

      // Output the shortcode
      $output = do_shortcode( $shortcode );
      $output .= '<div class="' . $unique_class . '" style="display: none;"></div>';

      return $output;
    }

  }

  // Call the class
  $vidbg_init_siteorigin = new Vidbg_SiteOrigin();
}
