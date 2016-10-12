# wp-bootstrap-hooks

> A collection of filters and actions for bootstrap-based themes

When integrating [Bootstrap](http://getbootstrap.com/) with Wordpress, it is not sufficient to just include assets and add some css-classes to templates. You will also need to inject bootstrap-compatible markup into programmatically generated sections, such as menus, widgets, comments etc. 
Bootstrap Hooks aims to cover most of these cases and make us start developing immediately after this point.
    

## Install

Either install as a mu-plugin or copy the desired files directly to your theme and require them in your functions.php.

When utilizing the plugin, we need to require hooks by specifying desired bootstrap version as follows:

```php
wp_bootstrap_hooks();
```

Otherwise you may require single hooks as desired

```php
require_once 'inc/bootstrap-comments.php'
```

## Usage

Included are solutions for Comments, Gallery, Navbar, Pagination, Search Form and Widgets. With exception of the Pagination-Hook, you're done with requiring source files.

### Comments

Comments are rendered as nested media-objects. You can customize the label 'Comment' by utilizing the `bootstrap_comments_options`-filter:

```php
// Customize Comment Label
function bootstrap_comments_options($args) {
  return array_merge($args, array(
    'comment_label' => ('Comment' , 'textdomain');
  ));
}
add_filter( 'bootstrap_comments_options', 'bootstrap_comments_options' );
```

### Gallery

The gallery hook uses a grid of thumbnails in combination with a carousel inside a modal for zoom view

### Navbar

An extended version of [Bootstrap Navwalker]() by Edward McIntyre is included and automatically applied to the primary menu.

### Pagination

Since there's no existing hook for posts pagination, we need to call a custom method from archive templates:

```php
// Previous/next page navigation.
echo wp_bootstrap_get_the_posts_pagination( array(
  'prev_text'          => __( 'Previous page', 'textdomain' ),
  'next_text'          => __( 'Next page', 'textdomain' ),
  'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'textdomain' ) . ' </span>',
));
```

### Search Form

A search-form is rendered as input-group. 
You can customize the submit button by passing arguments from a filter. This example shows how to integrate font-awesome: 

```php
// Show Font-Awesome search icon in Searchform
function bootstrap_searchform_options($args) {
  return array_merge($args, array(
    'submit_label' => '<i class="fa fa-search"></i>'
  ));
}
add_filter( 'bootstrap_searchform_options', 'bootstrap_searchform_options' );
```


### Widgets

Widgets are rendered as panels. Some manipulations take care of third-party widgets, such as applying list-groups to unordered lists. 
Make sure that you registered any widget areas in your `functions.php`:

```php
// Register widget area.
register_sidebar( array(
  'name'          => __( 'Widget Area', 'kicks-app' ),
  'id'            => 'sidebar-1',
  'description'   => __( 'Add widgets here to appear in your sidebar.', 'kicks-app' ),
  'before_widget' => '<aside id="%1$s" class="widget %2$s">',
  'after_widget'  => '</aside>'
) );
```


## Bootstrap 4

If you're already working with [Bootstrap 4](https://v4-alpha.getbootstrap.com/), you need to override at least widget options. 
Most of options can be shared between both versions without any harm.

```php
// Bootstrap 4 Widget Options
function bootstrap_widgets_options($args) {
  return array_merge($args, array(
    'widget_class' => 'card',
    'widget_modifier_class' => '',
    'widget_header_class' => 'card-header',
    'widget_content_class' => 'card-block'
  ));
}
add_filter( 'bootstrap_widgets_options', 'bootstrap_widgets_options' );
```  


Please note that as soon as Bootstrap 4 is finally released, the default configuration will change.