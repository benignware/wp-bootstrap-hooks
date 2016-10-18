# wp-bootstrap-hooks

> A collection of filters and actions for bootstrap-based themes

When integrating [Bootstrap](http://getbootstrap.com/) with Wordpress, it is not sufficient to just include assets and add some css-classes to templates. You will also need to inject bootstrap-compatible markup into programmatically generated sections, such as menus, widgets, comments etc. 
Bootstrap Hooks aims to cover most of these cases and make us start developing immediately after this point.
    
Bootstrap Hooks consists of six separate modules for Comments, Gallery, Navbar, Pagination, Forms and Widgets which can be used altogether or independently from each other. Every module is customizable by passing options to a filter method.

## Install

Either install as a must-use-plugin or copy the desired files directly to your theme and require them in your functions.php.

### Plugin
When utilizing the plugin, require specific hooks from your functions.php as follows:

```php
wp_bootstrap_hooks('menu', 'widgets', ...);
```

To include all hooks, just omit the parameters:

```php
wp_bootstrap_hooks();
```
Please note that it's recommended to install Bootstrap Hooks as a Must Use Plugin which should only be updated manually by theme developers.

### Template

When used from inside a theme, all hooks can be required by including only the main file:

```php
require_once 'inc/wp-bootstrap-hooks/bootstrap-hooks.php';
```

## Usage

### Comments

Comments are rendered as nested media-objects. 

You can customize the label 'Comment' by utilizing the `bootstrap_comments_options`-filter:

```php
// Customize Comment Label
function bootstrap_comments_options($args) {
  return array_merge($args, array(
    'comment_label' => ('Comment' , 'textdomain');
  ));
}
add_filter( 'bootstrap_comments_options', 'bootstrap_comments_options' );
```

### Content

The Content-Hook takes care of your post content primarily. It sets proper markup and classes to images, captions, blockquotes and tables. It also handles the post thumbnail to add the responsive image class.

In Bootstrap 4, the Tag-component may break Wordpress\` default taxonomy styles. See [here](https://github.com/twbs/bootstrap/issues/20542) for reference. 
To avoid undesired effects, the `tag` class is replaced with `post-tag` in `body_class`- or `post_class`-methods and also when it's found in the content.  


### Forms

This module handles search- and password-forms.

A search-form is rendered as input-group. 
You can customize the button's icon by passing arguments from a filter. This example shows how to integrate font-awesome: 

```php
// Show Font-Awesome search icon in Searchform
function bootstrap_forms_options($options) {
  return array_merge($options, array(
    'search_submit_label' => '<i class="fa fa-search"></i>'
  ));
}
add_filter( 'bootstrap_forms_options', 'bootstrap_forms_options' );
```


### Gallery

The gallery hook uses a grid of thumbnails in combination with a carousel inside a modal for zoom view.

In typical Bootstrap-driven layout, column sizes may differ from Wordpress default thumbnail size. 
You may update thumbnail size to your needs in order to fit thumbnail images into at least three columns:

```
// Adjust thumbnail size
update_option( 'thumbnail_size_w', 230 );
update_option( 'thumbnail_size_h', 230 );
update_option( 'thumbnail_crop', 1 );
``` 

The implementation does not handle different zoom-image heights. An easy way to fix this, is to register a custom image size with cropping enabled and apply to the Gallery Hook:

```php
// Register custom image sizes
add_image_size( 'gallery-zoom', 900, 500, true );
// Apply custom image size to gallery zoom
function bootstrap_gallery_options($options) {
  return array_merge($options, array(
    'gallery_large_size' => 'gallery-zoom'
  ));
}
add_filter( 'bootstrap_gallery_options', 'bootstrap_gallery_options' );
```


### Menu

Bootstrap Hooks provides a Nav Menu Walker based on the work by Edward McIntyre which is automatically injected into menus per default. For the primary menu the `navbar-nav`-class will be added.


### Pagination

Since there's no existing hook for posts pagination, we need to call a custom method from archive templates:


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

If you're already working with [Bootstrap 4](https://v4-alpha.getbootstrap.com/), you need to override at least some options. 

```php
// Bootstrap 4 Widget Options
function bootstrap4_widgets_options($options) {
  return array_merge($options, array(
    'widget_class' => 'card',
    'widget_modifier_class' => '',
    'widget_header_class' => 'card-header',
    'widget_content_class' => 'card-block'
  ));
}
add_filter( 'bootstrap_widgets_options', 'bootstrap4_widgets_options' );

// Bootstrap 4 Gallery Options
function bootstrap4_gallery_options($options) {
  return array_merge($options, array(
    'gallery_thumbnail_class' => '',
    'gallery_thumbnail_img_class' => 'img-thumbnail m-b-2',
    'close_button_class' => 'btn btn-secondary',
    'carousel_item_class' => 'carousel-item'
  ));
}
add_filter( 'bootstrap_gallery_options', 'bootstrap4_gallery_options' );

// Bootstrap 4 Content Options
function bootstrap4_content_options($options) {
  return array_merge($options, array(
    'image_class' => 'img-fluid',
    'align_center_class' => 'm-x-auto',
    'edit_post_link_class' => 'btn btn-secondary'
  ));
}
add_filter( 'bootstrap_content_options', 'bootstrap4_content_options' );
```  

Please note that as soon as Bootstrap 4 is finally released, the default configuration will change.

## API

### Comments

#### Filters

<table>
  <tr>
    <th>Signature</th>
    <th>Description</th>
  </tr>
  <tr>
    <td>
      bootstrap_comments_options ( $options )
    </td>
    <td>
      Inject custom options
    </td>
  </tr>
</table>

#### Options

<table>
  <tr>
    <th>Name</th>
    <th>Description</th>
    <th>Default</th>
  </tr>
  <tr>
    <td>text_input_class</td>
    <td>Sets the text input class used in comments</td>
    <td>form-control</td>
  </tr>
  <tr>
    <td>submit_class</td>
    <td>Sets submit button css class</td>
    <td>btn btn-primary</td>
  </tr>
  <tr>
    <td>reply_link_class</td>
    <td>Set reply link css class</td>
    <td>btn btn-primary btn-xs</td>
  </tr>
  <tr>
    <td>comment_label</td>
    <td>Sets the comment label</td>
    <td>Comment</td>
  </tr>
</table>

### Content

#### Filters

<table>
  <tr>
    <th>Signature</th>
    <th>Description</th>
  </tr>
  <tr>
    <td>
      bootstrap_contents_options ( $options )
    </td>
    <td>
      Inject custom options
    </td>
  </tr>
</table>

#### Options

<table>
  <tr>
    <th>Name</th>
    <th>Description</th>
    <th>Default</th>
  </tr>
  <tr>
    <td>image_class</td>
    <td>Sets the general image class used in content and thumbnails</td>
    <td>img-responsive</td>
  </tr>
  <tr>
    <td>align_left_class</td>
    <td>Aligns an image to left</td>
    <td>pull-left</td>
  </tr>
  <tr>
    <td>align_right_class</td>
    <td>Aligns an image to right</td>
    <td>pull-right</td>
  </tr>
  <tr>
    <td>img_caption_tag</td>
    <td>Sets the tag for the img caption element</td>
    <td>figure</td>
  </tr>
  <tr>
    <td>img_caption_class</td>
    <td>Sets the css class for the img caption element</td>
    <td>figure</td>
  </tr>
  <tr>
    <td>img_caption_text_tag</td>
    <td>Sets the tag for the img caption text element</td>
    <td>figcaption</td>
  </tr>
  <tr>
    <td>img_caption_text_class</td>
    <td>Sets the css class for the img caption text element</td>
    <td>figure-caption</td>
  </tr>
  <tr>
    <td>img_caption_img_class</td>
    <td>Sets the css class for the image of the img element of the caption</td>
    <td>figure-img</td>
  </tr>
  <tr>
    <td>table_class</td>
    <td>Sets the table css class</td>
    <td>table</td>
  </tr>
  <tr>
    <td>blockquote_class</td>
    <td>Sets the blockquote css class</td>
    <td>blockquote</td>
  </tr>
  <tr>
    <td>blockquote_footer_tag</td>
    <td>Sets the blockquote footer tag</td>
    <td>footer</td>
  </tr>
  <tr>
    <td>blockquote_footer_class</td>
    <td>Sets the blockquote footer css class</td>
    <td>blockquote-footer</td>
  </tr>
  <tr>
    <td>edit_post_link_class</td>
    <td>Sets the edit post link css class</td>
    <td>btn btn-secondary</td>
  </tr>
  <tr>
    <td>edit_post_link_container_class</td>
    <td>Sets the edit post link container css class</td>
    <td>form-group btn-group btn-group-sm</td>
  </tr>
</table>

### Forms


#### Filters

<table>
  <tr>
    <th>Signature</th>
    <th>Description</th>
  </tr>
  <tr>
    <td>
      bootstrap_forms_options ( $options )
    </td>
    <td>
      Inject custom options
    </td>
  </tr>
</table>

#### Options

<table>
  <tr>
    <th>Name</th>
    <th>Description</th>
    <th>Default</th>
  </tr>
  <tr>
    <td>search_submit_label</td>
    <td>Sets the searchfield's submit label</td>
    <td><i>🔎</i></td>
  </tr>
  <tr>
    <td>text_input_class</td>
    <td>Sets the class of textfields used in search- and password-forms</td>
    <td><i>🔎</i></td>
  </tr>
  <tr>
    <td>submit_button_class</td>
    <td>Sets the class of submit buttons used in search- and password-forms</td>
    <td><i>🔎</i></td>
  </tr>
</table>

### Gallery


#### Filters

<table>
  <tr>
    <th>Signature</th>
    <th>Description</th>
  </tr>
  <tr>
    <td>
      bootstrap_gallery_options ( $options )
    </td>
    <td>
      Inject custom options
    </td>
  </tr>
</table>

#### Options
    
<table>
  <tr>
    <th>Name</th>
    <th>Description</th>
    <th>Default</th>
  </tr>
  <tr>
    <td>gallery_thumbnail_size</td>
    <td>Sets the default thumbnail size</td>
    <td>'thumbnail'</td>
  </tr>
  <tr>
    <td>gallery_large_size</td>
    <td>Sets the image size for the carousel view</td>
    <td>large</td>
  </tr>
  <tr>
    <td>close_button_class</td>
    <td>Sets the modal's close button class</td>
    <td>btn btn-secondary</td>
  </tr>
  <tr>
    <td>close_button_label</td>
    <td>Sets the modal's close button label</td>
    <td>__('Close')</td>
  </tr>
</table>

### Menu

#### Filters

<table>
  <tr>
    <th>Signature</th>
    <th>Description</th>
  </tr>
  <tr>
    <td>
      bootstrap_menu_options ( $options )
    </td>
    <td>
      Inject custom options
    </td>
  </tr>
</table>

#### Options

<table>
  <tr>
    <th>Name</th>
    <th>Description</th>
    <th>Default</th>
  </tr>
  <tr>
    <td>menu_item_class</td>
    <td>Sets the menu item class</td>
    <td>nav-item</td>
  </tr>
  <tr>
    <td>menu_item_link_class</td>
    <td>Sets the menu item link class</td>
    <td>nav-link</td>
  </tr>
  <tr>
    <td>sub_menu_tag</td>
    <td>Sets the sub menu tag</td>
    <td>ul</td>
  </tr>
  <tr>
    <td>sub_menu_class</td>
    <td>Sets the sub menu class</td>
    <td>dropdown-menu</td>
  </tr>
  <tr>
    <td>sub_menu_item_link_class</td>
    <td>Sets the sub menu item link class</td>
    <td>dropdown-item</td>
  </tr>
  <tr>
    <td>caret</td>
    <td>Sets the menu item caret class</td>
    <td><span class="caret"></span></td>
  </tr>
</table>

### Pagination

#### Filters

<table>
  <tr>
    <th>Signature</th>
    <th>Description</th>
  </tr>
  <tr>
    <td>
      bootstrap_pagination_options ( $options )
    </td>
    <td>
      Inject custom options
    </td>
  </tr>
</table>

#### Options

<table>
  <tr>
    <th>Name</th>
    <th>Description</th>
    <th>Default</th>
  </tr>
  <tr>
    <td>pagination_class</td>
    <td>Sets the pagination class</td>
    <td>pagination</td>
  </tr>
  <tr>
    <td>page_item_class</td>
    <td>Sets the page item class</td>
    <td>page-item</td>
  </tr>
  <tr>
    <td>page_item_active_class</td>
    <td>Sets the page item active class</td>
    <td>active</td>
  </tr>
  <tr>
    <td>page_link_class</td>
    <td>Sets the page link css class</td>
    <td>page-link</td>
  </tr>
  <tr>
    <td>post_nav_class</td>
    <td>Sets the post navigation class</td>
    <td>nav</td>
  </tr>
  <tr>
    <td>post_nav_tag</td>
    <td>Sets the post navigation tag</td>
    <td>ul</td>
  </tr>
  <tr>
    <td>post_nav_item_class</td>
    <td>Sets the post navigation item class</td>
    <td>nav-item</td>
  </tr>
  <tr>
    <td>post_nav_item_tag</td>
    <td>Sets the post navigation item tag</td>
    <td>li</td>
  </tr>
  <tr>
    <td>post_nav_link_class</td>
    <td>Sets the post navigation link class</td>
    <td>nav-link</td>
  </tr>
</table>


### Widgets

#### Filters

<table>
  <tr>
    <th>Signature</th>
    <th>Description</th>
  </tr>
  <tr>
    <td>
      bootstrap_widgets_options ( $options )
    </td>
    <td>
      Inject custom options
    </td>
  </tr>
</table>

#### Options

<table>
  <tr>
    <th>Name</th>
    <th>Description</th>
    <th>Default</th>
  </tr>
  <tr>
    <td>widget_class</td>
    <td>Sets the widget class</td>
    <td>panel</td>
  </tr>
  <tr>
    <td>widget_modifier_class</td>
    <td>Sets the widget modifier class</td>
    <td>panel-default</td>
  </tr>
  <tr>
    <td>widget_header_class</td>
    <td>Sets the widget header class</td>
    <td>panel-heading</td>
  </tr>
  <tr>
    <td>widget_content_class</td>
    <td>Sets the widget content class</td>
    <td>panel-block</td>
  </tr>
</table>



## Recipes

#### Use as Plugin

When intended to use as plugin, you should take care of a situation where the plugin is unistalled: 

```php
  $edit_post_link_args = array(
    sprintf(
      /* translators: %s: Name of current post */
      __( 'Edit<span class="screen-reader-text"> "%s"</span>', 'twentysixteen' ),
      get_the_title()
    ),
    '<span class="edit-link">',
    '</span>'
  );
  $edit_post_link_method = function_exists('wp_bootstrap_edit_post_link') ? 'wp_bootstrap_edit_post_link' : 'edit_post_link';
  call_user_func_array($edit_post_link_method, $edit_post_link_args);
```


#### Less/Sass

To get the most out of Bootstrap, you should consider to build your setup on the native preprocessor language in which Bootstrap is build, meaning Less for Bootstrap 3 respectively Sass for Bootstrap 4. 
