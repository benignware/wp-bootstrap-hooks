# wp-bootstrap-hooks

> A collection of filters and actions for bootstrap-based themes

Lets wordpress developers easily create bootstrap-integrated themes. When integrating bootstrap with wordpress, it is not sufficient to just include assets and add some css-classes to templates. You will also need to inject bootstrap-compatible markup into programmatically generated sections, such as menus, widgets, comments etc. 
Bootstrap Hooks aims to cover most of these and make us start developing immediately after this point without polluting your theme layer.
    

## Install

Either install as a mu-plugin or copy the desired files directly to your theme and require them in your functions.php.

Currently wp-bootstrap-hooks is split into different versions for  Bootstrap 3 and 4-alpha.

When utilizing the plugin, init hooks by specifying desired bootstrap version as follows:

```php
wp_bootstrap_hooks('3');
```

Otherwise you may include only those files that match your desired version and then require them explicitly like this:

```php
require_once 'inc/bootstrap-comments.php'
```

## Hooks

Included are solutions for Comments, Gallery, Navbar, Pagination, Search Form and Widgets.

### Comments

Comments are rendered as nested media-objects.

### Gallery

The gallery hook uses a grid of thumbnails in combination with a carousel inside a modal for zoom view

### Navbar

An extended version of [Bootstrap Navwalker]() by Edward McIntyre is included and automatically applied to the primary menu.

### Pagination

Since there's no existing hook for posts pagination, we need to call a custom method from the view:

```php
// Render posts pagination
wp_bootstrap_get_the_posts_pagination();
```

### Search Form

A search-form is rendered as input-group. 
You can customize the submit button by passing arguments from a filter. This example shows how to integrate font-awesome: 

```php
// Show Font-Awesome search icon in search-form
function bootstrap_get_search_form_args($args) {
  return array_merge($args, array(
    'submit_label' => '<i class="fa fa-search"></i>'
  ));
}
add_filter( 'wp_bootstrap_get_search_form_args', 'bootstrap_get_search_form_args' );
```


### Widgets

Widgets are rendered as panels respectively cards for bs4. Some manipulations take care of third-party widgets, such as applying list-groups to unordered lists. 
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