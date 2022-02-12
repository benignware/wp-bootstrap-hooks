<?php


add_action( 'customize_register', function($wp_customize) {
  $wp_customize->add_section(
    'options',
    array(
      'title'      => __( 'Theme Options', 'twentytwenty' ),
      'priority'   => 40,
      'capability' => 'edit_theme_options',
    )
  );

  // Blog content
  $wp_customize->add_setting(
    'blog_content',
    array(
      'capability'        => 'edit_theme_options',
      'default'           => 'full',
    )
  );

  $wp_customize->add_control(
    'blog_content',
    array(
      'type'     => 'radio',
      'section'  => 'options',
      'priority' => 10,
      'label'    => __( 'On archive pages, posts show:', 'twentytwenty' ),
      'choices'  => array(
        'full'    => __( 'Full text', 'twentytwenty' ),
        'summary' => __( 'Summary', 'twentytwenty' ),
      ),
    )
  );

  // Blog sidebar
  $wp_customize->add_setting(
    'blog_sidebar',
    array(
      'capability'        => 'edit_theme_options',
      'default'           => 'full',
    )
  );
  
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'blog_sidebar',
      array(
        'label'     => __( 'On archive pages, show sidebar', 'twentytwenty' ),
        'section'   => 'options',
        'settings'  => 'blog_sidebar',
        'type'      => 'checkbox',
      )
    )
  );
});