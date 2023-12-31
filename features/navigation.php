<?php

/**
 * Nav Menu Args
 */
function wp_bootstrap_nav_menu_args($args) {
  if (!current_theme_supports('bootstrap')) {
    return $args;
  }

  $options = wp_bootstrap_options();
  $menu_class = isset($args['menu_class']) ? $args['menu_class'] : '';
  $is_default_menu_class = $menu_class === 'menu' ? true : false;

  $args['menu_class'].= ' nav';

  $navbar_locations = array(
    'primary', 'top'
  );

  // Apply .navbar-nav automatically to primary menu if no custom class has been set
  if ($is_default_menu_class && isset($args['theme_location']) && in_array($args['theme_location'], $navbar_locations)) {
    $args['menu_class'] = $args['menu_class'] . " navbar-nav";
  }

  if (empty($args['walker'])) {
    $args['fallback_cb'] = 'wp_bootstrap_navwalker::fallback';
    $args['walker'] = new wp_bootstrap_navwalker($options);
  }
  return $args;
}

add_filter( 'wp_nav_menu_args', 'wp_bootstrap_nav_menu_args', 10 );
add_action( 'wp_enqueue_scripts', function() {
  if (!current_theme_supports('bootstrap')) {
    return;
  }

  $options = wp_bootstrap_options();

  if (!isset($options['caret']) || !$options['caret']) {
    return;
  }

  wp_register_style( 'bootstrap-hooks-hide-pseudo-caret', '', [], '', true );
  wp_enqueue_style( 'bootstrap-hooks-hide-pseudo-caret' );
  wp_add_inline_style( 'bootstrap-hooks-hide-pseudo-caret', <<<EOT
<style>
  .menu-item.nav-item .dropdown-toggle::after {
    display: none
  }
</style>
EOT);
} );

add_action( 'wp_enqueue_scripts', function() {
  if (!current_theme_supports('bootstrap')) {
    return;
  }

  $options = wp_bootstrap_options();

  $caret_class = isset($options['caret_class']) ? $options['caret_class'] : '';

  wp_register_script( 'bootstrap-hooks-dropdown-links', '', [], '', true );
  wp_enqueue_script( 'bootstrap-hooks-dropdown-links' );
  wp_add_inline_script( 'bootstrap-hooks-dropdown-links', <<<EOT
  (() => {
    const caretSelector = '$caret_class';
    const handler = (e) => {
      console.log('HELLO CLICK');
      const target = event.target.closest('a[href].dropdown-toggle');
  
      if (!target) {
        return;
      }
  
      if (target.href.startsWith('#') || target.href.startsWith('javascript:')) {
        return;
      }
  
      const isOpen = !!target.classList.contains('show');
  
      if (isOpen) {
        return;
      }

      const isCaret = caretSelector && !!event.target.closest(caretSelector) || (() => {
        const after = getComputedStyle(target, ":after");
      
        if (after) {
          const w = Math.max(Number(after.getPropertyValue("width").slice(0, -2)), 16);
          const h = target.offsetHeight;
          const x = target.offsetWidth - w;
          const y = 0;
          const ex = e.layerX;
          const ey = e.layerY;
          
          if (ex > x && ex < x + w && ey > y && ey < y + h) {
            return true;
          }
        }
  
        return false;
      })();
  
      if (isCaret) {
        return false;
      }
  
      const hasText = [...target.childNodes].some(node => node.nodeType === 3);
  
      if (!hasText && event.target !== target || hasText && event.target === target) {
        window.location.href = target.href;
      }
    }
    window.addEventListener('click', handler);
  })();
EOT);
} );

// add_filter( 'nav_menu_item_title', function($title) {
//   return '<span>' . $title . '</span>';
// }, 2);

/**
 * Class Name: wp_bootstrap_navwalker
 * GitHub URI: https://github.com/twittem/wp-bootstrap-navwalker
 * Description: A custom WordPress nav walker class to implement the Bootstrap 3 navigation style in a custom theme using the WordPress built in menu manager.
 * Version: 2.0.4
 * Author: Based On Edward McIntyre - @twittem, Edited by Rafael Nowrotek - @benignware
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

class wp_bootstrap_navwalker extends Walker_Nav_Menu {

  private $options;

  public function __construct ($options = array()) {
    $this->options = $options;
  }

  /**
   * @see Walker::start_lvl()
   * @since 3.0.0
   *
   * @param string $output Passed by reference. Used to append additional content.
   * @param int $depth Depth of page. Used for padding.
   */
  public function start_lvl( &$output, $depth = 0, $args = array() ) {
    $sub_menu_tag = $this->options['sub_menu_tag'];
    $sub_menu_class = $this->options['sub_menu_class'];

    if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
      $t = '';
      $n = '';
    } else {
      $t = "\t";
      $n = "\n";
    }
    $indent = str_repeat( $t, $depth );

    // Default class.
    $classes = array( 'sub-menu', $sub_menu_class );

    /**
     * Filters the CSS class(es) applied to a menu list element.
     *
     * @since 4.8.0
     *
     * @param string[] $classes Array of the CSS classes that are applied to the menu `<ul>` element.
     * @param stdClass $args    An object of `wp_nav_menu()` arguments.
     * @param int      $depth   Depth of menu item. Used for padding.
     */
    $class_names = implode( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
    $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

    $output .= "{$n}{$indent}<$sub_menu_tag$class_names>{$n}";
  }

  /**
   * @see Walker::start_el()
   * @since 3.0.0
   *
   * @param string $output Passed by reference. Used to append additional content.
   * @param object $item Menu item data object.
   * @param int $depth Depth of menu item. Used for padding.
   * @param int $current_page Menu item ID.
   * @param object $args
   */
  public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
    $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
    $menu_order = $item->menu_order;

    $menu_item_class = $this->options['menu_item_class'];
    $menu_item_link_class = $this->options['menu_item_link_class'];
    $menu_item_link_active_class = $this->options['menu_item_link_active_class'];

    $divider_class = $this->options['divider_class'];

    $sub_menu_tag = $this->options['sub_menu_tag'];
    $sub_menu_header_class = $this->options['sub_menu_header_class'];
    $sub_menu_item_tag = $this->options['sub_menu_item_tag'];
    $sub_menu_item_class = $this->options['sub_menu_item_class'];
    $sub_menu_class = $this->options['sub_menu_class'];
    $sub_menu_toggle_class = $this->options['sub_menu_toggle_class'];
    $sub_menu_item_link_class = $this->options['sub_menu_item_link_class'];

    $caret = isset($this->options['caret']) ? $this->options['caret'] : '';
    $caret_class = isset($this->options['caret_class']) ? $this->options['caret_class'] : 'caret';

    /**
     * Dividers, Headers or Disabled
     * =============================
     * Determine whether the item is a Divider, Header, Disabled or regular
     * menu item. To prevent errors we use the strcasecmp() function to so a
     * comparison that is not case sensitive. The strcasecmp() function returns
     * a 0 if the strings are equal.
     */
    if ( strcasecmp( $item->attr_title, 'divider' ) == 0 && $depth === 1 ) {
      $output .= $indent . '<li role="presentation" class="' . $divider_class . '">';
    } else if ( strcasecmp( $item->title, 'divider') == 0 && $depth === 1 ) {
      $output .= $indent . '<li role="presentation" class="' . $divider_class . '">';
    } else if ( strcasecmp( $item->attr_title, 'dropdown-header') == 0 && $depth === 1 ) {
      $output .= $indent . '<li role="presentation" class="' . $sub_menu_header_class . '">' . esc_attr( $item->title );
    } else if ( strcasecmp($item->attr_title, 'disabled' ) == 0 ) {
      $output .= $indent . '<li role="presentation" class="disabled"><a href="#">' . esc_attr( $item->title ) . '</a>';
    } else {

      $class_names = $value = '';

      $classes = empty( $item->classes ) ? array() : (array) $item->classes;
      $classes[] = $menu_item_class;
      $classes[] = 'menu-item-' . $item->ID;
      $classes = apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args );

      $class_names = join( ' ', $classes );

      if ( $args->has_children ) {
        $class_names .= ' dropdown';
      }

      if ( in_array('current-menu-item', $classes)
        || in_array('current-post-ancestor', $classes)
        || in_array('current-category-parent', $classes)) {
        $class_names .= ' active';
      }

      $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

      $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
      $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

      if ($depth === 0) {
        $output .= $indent . '<li' . $id . $value . $class_names .'>';
      } else {
        $output .= $indent . "<$sub_menu_item_tag class=\"$sub_menu_item_class\" data-depth=\"$depth\">";
      }

      $atts = array();
      $atts['title']  = ! empty( $item->title ) ? $item->title  : '';
      $atts['target'] = ! empty( $item->target )  ? $item->target : '';
      $atts['rel']    = ! empty( $item->xfn )   ? $item->xfn  : '';
      $atts['class'] = '';

      if ($depth === 0) {
        $atts['class'].= ' ' . $menu_item_link_class;
      } else {
        $atts['class'].= ' ' . $sub_menu_item_link_class;
        // if ( in_array( 'current-menu-item', $classes ) )
          // $atts['class'] .= ' active';
      }

      if ( in_array( 'current-menu-item', $classes ) ) {
        $atts['class'].= ' ' . $menu_item_link_active_class;
      }

      $atts['href'] = ! empty( $item->url ) ? $item->url : '';

      // If item has_children add atts to a.
      if ( $args->has_children && $depth === 0 ) {
        $atts['data-toggle'] = 'dropdown'; // Bootstrap 4
        $atts['data-bs-toggle'] = 'dropdown'; // Bootstrap 5
        $atts['class'].= ' ' . $sub_menu_toggle_class;
      }

      $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

      $attributes = '';
      foreach ( $atts as $attr => $value ) {
        if ( ! empty( $value ) ) {
          $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
          $attributes .= ' ' . $attr . '="' . $value . '"';
        }
      }

      $title = apply_filters( 'the_title', $item->title, $item->ID );
      $title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

      $item_output = '';

      $item_output .= $args->before;
      $item_output .= '<a'. $attributes .'>';
      $item_output .= $args->link_before;
      $item_output .= $title;
      $item_output .= $args->link_after;
      $item_output .= ( $caret && $args->has_children && 0 === $depth ) ? sprintf($caret, $caret_class) . '</a>' : '</a>';
      $item_output .= $args->after;

      $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
  }

  /**
   * Traverse elements to create list from elements.
   *
   * Display one element if the element doesn't have any children otherwise,
   * display the element and its children. Will only traverse up to the max
   * depth and no ignore elements under that depth.
   *
   * This method shouldn't be called directly, use the walk() method instead.
   *
   * @see Walker::start_el()
   * @since 2.5.0
   *
   * @param object $element Data object
   * @param array $children_elements List of elements to continue traversing.
   * @param int $max_depth Max depth to traverse.
   * @param int $depth Depth of current element.
   * @param array $args
   * @param string $output Passed by reference. Used to append additional content.
   * @return null Null on failure with no changes to parameters.
   */
  public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
    if ( ! $element ) {
      return;
    }

    $id_field = $this->db_fields['id'];

    // Display this element.
    if ( is_object( $args[0] ) ) {
      $args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
    }

    parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
  }

  /**
   * Menu Fallback
   * =============
   * If this function is assigned to the wp_nav_menu's fallback_cb variable
   * and a manu has not been assigned to the theme location in the WordPress
   * menu manager the function with display nothing to a non-logged in user,
   * and will add a link to the WordPress menu manager if logged in as an admin.
   *
   * @param array $args passed from the wp_nav_menu function.
   *
   */
  public static function fallback( $args ) {
    $options = wp_bootstrap_options();

    if ( current_user_can( 'manage_options' ) ) {

      extract( $args );

      $menu_item_class = $options['menu_item_class'];
      $menu_item_link_class = $options['menu_item_link_class'];

      $fb_output = null;

      $container_tagname = $container ? (is_string($container) ? $container : 'div') : null;

      if ( $container ) {
        $fb_output = '<' . $container_tagname;

        if ( $container_id )
          $fb_output .= ' id="' . $container_id . '"';

        if ( $container_class )
          $fb_output .= ' class="' . $container_class . '"';

        $fb_output .= '>';
      }

      $fb_output .= '<ul';

      if ( $menu_id )
        $fb_output .= ' id="' . $menu_id . '"';

      if ( $menu_class )
        $fb_output .= ' class="' . $menu_class . '"';

      $fb_output .= '>';
      // TODO: Text Domain
      $fb_output .= '<li class="' . $menu_item_class . '"><a class="' . $menu_item_link_class . '" href="' . admin_url( 'nav-menus.php' ) . '">' . __('Add a menu', 'bootstrap-hooks') . '</a></li>';
      $fb_output .= '</ul>';

      if ( $container ) {
        $fb_output .= '</' . $container_tagname . '>';
      }

      echo $fb_output;
    }
  }
}