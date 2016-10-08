# wp-bootstrap-hooks

> A collection of filters and actions for bootstrap based themes


Lets wordpress developers easily create bootstrap-integrated themes.

* Comments (Forms)
* CommentWalker (MediaObject)
* Gallery (Grid/Modal/Carousel)
* NavWalker (Navbar/DropDown)
* Widgets (Panel/Card/Listgroups)
* Pagination

### Install

Either install single files directly to your theme and require in your functions.php or install as a mu-plugin.

Init hooks and specify desired bootstrap version:

```php
wp_bootstrap_hooks(3);
```

Bootstrap 4 alpha is also supported:


```php
wp_bootstrap_hooks(4);
```

### Navbar

An extended version of [Bootstrap Navwalker]() by Edward McIntyre is included and integrated automatically for the primary menu

### Gallery

The gallery hook uses a grid of thumbnails in combination with a carousel inside a modal for zoom view

### Comments

Comments are rendered using as media-objects.

### Pagination

Since there's no existing hook for posts pagination, we need to call a custom method from the view:

```php
wp_bootstrap_get_the_posts_pagination();
```

### Search Form

A search-form is rendered as input-group. 
You can customize the output by passing arguments from a filter. This example shows how to integrate font-awesome: 

```php
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