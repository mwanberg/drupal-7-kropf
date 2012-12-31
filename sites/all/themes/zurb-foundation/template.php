<?php

/**
 * Implements template_preprocess_html().
 * 
 * Adds additional classes
 */
function zurb_foundation_preprocess_html(&$variables) {
  global $language;

  // Clean up the lang attributes
  $variables['html_attributes'] = 'lang="' . $language->language . '" dir="' . $language->dir . '"';

  // Add language body class.
  if (function_exists('locale')) {
    $variables['classes_array'][] = 'lang-' . $variables['language']->language;
  }

  //  @TODO Custom fonts from Google web-fonts
  //  $font = str_replace(' ', '+', theme_get_setting('zurb_foundation_font'));
  //  if (theme_get_setting('zurb_foundation_font')) {
  //    drupal_add_css('http://fonts.googleapis.com/css?family=' . $font , array('type' => 'external', 'group' => CSS_THEME));
  //  }

  // Classes for body element. Allows advanced theming based on context
  if (!$variables['is_front']) {
    // Add unique class for each page.
    $path = drupal_get_path_alias($_GET['q']);
    // Add unique class for each website section.
    list($section, ) = explode('/', $path, 2);
    $arg = explode('/', $_GET['q']);
    if ($arg[0] == 'node' && isset($arg[1])) {
      if ($arg[1] == 'add') {
        $section = 'node-add';
      }
      elseif (isset($arg[2]) && is_numeric($arg[1]) && ($arg[2] == 'edit' || $arg[2] == 'delete')) {
        $section = 'node-' . $arg[2];
      }
    }
    $variables['classes_array'][] = drupal_html_class('section-' . $section);
  }

  // Store the menu item since it has some useful information.
  $variables['menu_item'] = menu_get_item();
  if ($variables['menu_item']) {
    switch ($variables['menu_item']['page_callback']) {
      case 'views_page':
        $variables['classes_array'][] = 'views-page';
        break;
      case 'page_manager_page_execute':
      case 'page_manager_node_view':
      case 'page_manager_contact_site':
        $variables['classes_array'][] = 'panels-page';
        break;
    }
  }
}

/**
 * Implements hook_html_head_alter().
 */
function zurb_foundation_html_head_alter(&$head_elements) {
  // HTML5 charset declaration.
  $head_elements['system_meta_content_type']['#attributes'] = array(
    'charset' => 'utf-8',
  );

  // Optimize mobile viewport.
  $head_elements['mobile_viewport'] = array(
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => array(
      'name' => 'viewport',
      'content' => 'width=device-width',
    ),
  );

  // Force IE to use Chrome Frame if installed.
  $head_elements['chrome_frame'] = array(
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => array(
      'content' => 'ie=edge, chrome=1',
      'http-equiv' => 'x-ua-compatible',
    ),
  );

  // Remove image toolbar in IE.
  $head_elements['ie_image_toolbar'] = array(
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => array(
      'http-equiv' => 'ImageToolbar',
      'content' => 'false',
    ),
  );
}

/**
 * Implements template_preprocess_page
 *
 * Add convenience variables and template suggestions
 */
function zurb_foundation_preprocess_page(&$variables) {
  // Add page--node_type.tpl.php suggestions
  if (!empty($variables['node'])) {
    $variables['theme_hook_suggestions'][] = 'page__' . $variables['node']->type;
  }

  $variables['logo_img'] = '';
  if (!empty($variables['logo'])) {
    $variables['logo_img'] = theme('image', array(
      'path'  => $variables['logo'],
      'alt'   => strip_tags($variables['site_name']) . ' ' . t('logo'),
      'title' => strip_tags($variables['site_name']) . ' ' . t('Home'),
						'attributes' => array(
        'class' => array('logo'),
      ),
    ));
  }
  $variables['linked_logo']  = '';
  if (!empty($variables['logo_img'])) {
    $variables['linked_logo'] = l($variables['logo_img'], '<front>', array(
      'attributes' => array(
        'rel'   => 'home',
        'title' => strip_tags($variables['site_name']) . ' ' . t('Home'),
      ),
      'html' => TRUE,
    ));
  }
  $variables['linked_site_name'] = '';
  if (!empty($variables['site_name'])) {
    $variables['linked_site_name'] = l($variables['site_name'], '<front>', array(
      'attributes' => array(
        'rel'   => 'home',
        'title' => strip_tags($variables['site_name']) . ' ' . t('Home'),
      ),
    ));
  }

  // Site navigation links.
  $variables['main_menu_links'] = '';
  if (isset($variables['main_menu'])) {
    $variables['main_menu_links'] = theme('links__system_main_menu', array(
      'links' => $variables['main_menu'],
      'attributes' => array(
        'id' => 'main-menu',
        'class' => array('link-list'),
      ),
      'heading' => array(
        'text' => t('Main menu'),
        'level' => 'h2',
        'class' => array('element-invisible'),
      ),
    ));
  }
  $variables['secondary_menu_links'] = '';
  if (isset($variables['secondary_menu'])) {
    $variables['secondary_menu_links'] = theme('links__system_secondary_menu', array(
      'links' => $variables['secondary_menu'],
      'attributes' => array(
        'id'    => 'secondary-menu',
        'class' => array('secondary', 'nav-bar'),
      ),
      'heading' => array(
        'text' => t('Secondary menu'),
        'level' => 'h2',
        'class' => array('element-invisible'),
      ),
    ));
  }

  // Convenience variables
  $left = $variables['page']['sidebar_first'];
  $right = $variables['page']['sidebar_second'];

  // Dynamic sidebars
  if (!empty($left) && !empty($right)) {
    $variables['main_grid'] = 'six push-three';
    $variables['sidebar_first_grid'] = 'three pull-six';
    $variables['sidebar_sec_grid'] = 'three';
  } elseif (empty($left) && !empty($right)) {
    $variables['main_grid'] = 'nine';
    $variables['sidebar_first_grid'] = '';
    $variables['sidebar_sec_grid'] = 'three';
  } elseif (!empty($left) && empty($right)) {
    $variables['main_grid'] = 'nine push-three';
    $variables['sidebar_first_grid'] = 'three pull-nine';
    $variables['sidebar_sec_grid'] = '';
  } else {
    $variables['main_grid'] = 'twelve';
    $variables['sidebar_first_grid'] = '';
    $variables['sidebar_sec_grid'] = '';
  }

}

/**
 * Implements template_preprocess_node
 *
 * Add template suggestions and classes
 */
function zurb_foundation_preprocess_node(&$vars) {
  // Add node--node_type--view_mode.tpl.php suggestions
  $vars['theme_hook_suggestions'][] = 'node__' . $vars['type'] . '__' . $vars['view_mode'];
  
  // Add node--view_mode.tpl.php suggestions
  $vars['theme_hook_suggestions'][] = 'node__' . $vars['view_mode'];
  
  // Add a class for the view mode.
  if (!$vars['teaser']) {
    $vars['classes_array'][] = 'view-mode-' . $vars['view_mode'];
  }
  $vars['title_attributes_array']['class'][] = 'node-title';
//  // Add classes based on node type.
//  switch ($vars['type']) {
//    case 'news':
//    case 'pages':
//      $vars['attributes_array']['class'][] = 'content-wrapper';
//      $vars['attributes_array']['class'][] = 'text-content';
//      break;
//  }
//
//  // Add classes & theme hook suggestions based on view mode.
//  switch ($vars['view_mode']) {
//    case 'block_display':
//      $vars['theme_hook_suggestions'][] = 'node__aside';
//      $vars['title_attributes_array']['class'] = array('title-block');
//      $vars['attributes_array']['class'][] = 'block-content';
//      break;
//  }
}

function zurb_foundation_field($variables) {
  $output = '';

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    $output .= '<div ' . $variables['title_attributes'] . '>' . $variables['label'] . ':&nbsp;</div>';
  }

  foreach ($variables['items'] as $delta => $item) {
    $output .= drupal_render($item);
  }

  // Render the top-level DIV.
  $output = '<div class="' . $variables['classes'] . '"' . $variables['attributes'] . '>' . $output . '</div>';

  return $output;
}

/**
 * Implements template_preprocess_field().
 */
function zurb_foundation_preprocess_field(&$vars) {
  $vars['title_attributes_array']['class'][] = 'field-label';

  // Edit classes for taxonomy term reference fields.
  if ($vars['field_type_css'] == 'taxonomy-term-reference') {
    $vars['content_attributes_array']['class'][] = 'comma-separated';
  }
  
  // Convinence variables
  $name = $vars['element']['#field_name'];
  $bundle = $vars['element']['#bundle'];
  $mode = $vars['element']['#view_mode'];
  $classes = &$vars['classes_array'];
  $title_classes = &$vars['title_attributes_array']['class'];
  $content_classes = &$vars['content_attributes_array']['class'];
  $item_classes = array();
 
  // Global field classes
  $classes[] = 'field-wrapper';
  $content_classes[] = 'field-items';
  $item_classes[] = 'field-item';
 
  // Uncomment the lines below to see variables you can use to target a field
  // print '<strong>Name:</strong> ' . $name . '<br/>';
  // print '<strong>Bundle:</strong> ' . $bundle  . '<br/>';
  // print '<strong>Mode:</strong> ' . $mode .'<br/>';
 
  // Add specific classes to targeted fields 
  if(isset($field)) {
    switch ($mode) {
      // All teasers 
      case 'teaser':
        switch ($field) {
          // Teaser read more links
          case 'node_link':
            $item_classes[] = 'more-link';
            break;
          // Teaser descriptions
          case 'body':
          case 'field_description':
            $item_classes[] = 'description';
            break;
        }
      break;
    }
  }
 // Check if exists
//  switch ($field) {
//    case 'field_authors':
//      $title_classes[] = 'inline';
//      $content_classes[] = 'authors';
//      $item_classes[] = 'author';
//      break;
//  }
 
  // Apply odd or even classes along with our custom classes to each item
  foreach ($vars['items'] as $delta => $item) {
    $item_classes[] = $delta % 2 ? 'odd' : 'even';
    $vars['item_attributes_array'][$delta]['class'] = $item_classes;
  }
  
  // Add class to a specific fields across content types.
  switch ($vars['element']['#field_name']) {
    case 'body':
      $vars['classes_array'] = array('body');
      break;

    case 'field_summary':
      $vars['classes_array'][] = 'text-teaser';
      break;

    case 'field_link':
    case 'field_date':
      // Replace classes entirely, instead of adding extra.
      $vars['classes_array'] = array('text-content');
      break;

    case 'field_image':
      // Replace classes entirely, instead of adding extra.
      $vars['classes_array'] = array('image');
      break;

    default:
      break;
  }
  // Add classes to body based on content type and view mode.
  if ($vars['element']['#field_name'] == 'body') {

    // Add classes to Foobar content type.
    if ($vars['element']['#bundle'] == 'foobar') {
      $vars['classes_array'][] = 'text-secondary';
    }

    // Add classes to other content types with view mode 'teaser';
    elseif ($vars['element']['#view_mode'] == 'teaser') {
      $vars['classes_array'][] = 'text-secondary';
    }

    // The rest is text-content.
    else {
      $vars['classes_array'][] = 'field';
    }
  }
}

/**
 * Implements template_breadcrumb().
 */
function zurb_foundation_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  $title = strip_tags(drupal_get_title());

  if (!empty($breadcrumb)) {
    // Provide a navigational heading to give context for breadcrumb links to
    // screen-reader users. Make the heading invisible with .element-invisible.
    $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';

    $output .= '<div class="breadcrumb">' . implode(' &raquo; ', $breadcrumb) . ' &raquo; ' . $title . '</div>';
    return $output;
  }
}

/**
 * Implements hook_preprocess_block()
 */
function zurb_foundation_preprocess_block(&$vars) {
  // Add a striping class.
  $vars['classes_array'][] = 'block-' . $vars['zebra'];
  $vars['title_attributes_array']['class'][] = 'block-title';

  // In the header region visually hide block titles.
  if ($vars['block']->region == 'header') {
    $vars['title_attributes_array']['class'][] = 'element-invisible';
  }
//  $block_id = $vars['block']->module . '-' . $vars['block']->delta;
//  $classes = &$vars['classes_array'];
//  $title_classes = &$vars['title_attributes_array']['class'];
//  $content_classes = &$vars['content_attributes_array']['class'];
 
  // Add global classes to all blocks 
//  $title_classes[] = 'block-title';
//  $content_classes[] = 'block-content';

  // Add classes based on the block delta. 
//  switch ($block_id) {
//    // System Navigation block
//    case 'system-navigation':
//      $classes[] = 'block-rounded';
//      $title_classes[] = 'block-fancy-title';
//      $content_classes[] = 'block-fancy-content';
//      break;
//    /* Main Menu block */
//    case 'system-main-menu':
//    /* User Login block */
//    case 'user-login':
//      $title_classes[] = 'element-invisible';
//      break;
//  }
  // Add template suggestions for block.tpl.php.
//    // Add theme suggestion based module.
//    switch($vars['elements']['#block']->module) {
//      case 'menu':
//      case 'menu_block':
//        $vars['theme_hook_suggestions'][] = 'block__navigation';
//        break;
//
//      // Render some blocks without wrapper and leave it to the module.
//      case 'views':
//      case 'mini_panels':
//        $vars['theme_hook_suggestions'][] = 'block__nowrapper';
//    }
// /**
//   * Add classes to blocks created by Views based on views name.
//   */
//  // Check if block was created by views.
//  if ($vars['elements']['#block']->module == 'views') {
//
//    // Get views name from $vars['elements']['#block']->delta.
//    $block_delta = explode('-', $vars['elements']['#block']->delta);
//    $views_name = $block_delta[0];
//
//    // Add classes based on views name.
//    switch ($views_name) {
//      case 'view_foo':
//      case 'view_bar':
//        $vars['title_attributes_array']['class'][] = 'title-list';
//        break;
//
//      case 'view_baz':
//        $vars['title_attributes_array']['class'] = 'title-block';
//        $vars['classes_array'][] = 'block-secondary';
//        break;
//
//      default:
//        $vars['title_attributes_array']['class'][] = 'title-block';
//    }
//  }
//   /**
//   * Add classes based on region.
//   */
//  switch ($vars['elements']['#block']->region) {
//    case 'region_foo':
//    case 'region_bar':
//    case 'region_baz':
//      $vars['title_attributes_array']['class'][] = 'title-list';
//      break;
//
//    case 'region_foobar':
//      $vars['classes_array'][] = 'block-list';
//      break;
//
//    default;
//  }
//  /*
//   * Add classes based on module excluding certain regions.
//   */
//  switch ($vars['elements']['#block']->region) {
//
//    // Exclude certain blocks in certain regions.
//    case 'footer_sitemap':
//    case 'user_first':
//    case 'user_second':
//    case 'menu':
//    case 'footer_first':
//    case 'footer_second':
//      // Do nothing.
//      break;
//
//    default:
//      switch($vars['elements']['#block']->module) {
//        // For the rest of the regions add classes to navigation blocks.
//        case 'menu':
//        case 'menu_block':
//          $vars['attributes_array']['class'][] = 'block-style-menu';
//          break;
//
//        // And style standard blocks.
//        case 'block':
//          $vars['attributes_array']['class'][] = 'block-secondary';
//          break;
//      }
//  }
}

/**
 * Implements theme_form_element_label()
 */
function zurb_foundation_form_element_label($vars) {
  if (!empty($vars['element']['#title'])) {
    $vars['element']['#title'] = '<label>' . $vars['element']['#title'] . '</label>';
  }
  if (!empty($vars['element']['#description'])) {
    $vars['element']['#description'] = ' <span class="has-tip tip-top radius" data-width="250" title="' . $vars['element']['#description'] . '">' . t('More information?') . '</span>';
  }
  return theme_form_element_label($vars);
}

function zurb_foundation_form_alter(&$form, &$form_state, $form_id) {
  // Sexy submit buttons
  if (!empty($form['actions']) && $form['actions']['submit']) {
    $form['actions']['submit']['#attributes'] = array('class' => array('secondary', 'button', 'radius'));
  }
}

/**
 * Implements theme_field__field_type().
 */
function zurb_foundation_field__taxonomy_term_reference($variables) {
  $output = '';

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    $output .= '<h2 class="field-label">' . $variables['label'] . ': </h2>';
  }

  // Render the items.
  $output .= ($variables['element']['#label_display'] == 'inline') ? '<ul class="links inline">' : '<ul class="links">';
  foreach ($variables['items'] as $delta => $item) {
    $output .= '<li class="taxonomy-term-reference-' . $delta . '"' . $variables['item_attributes'][$delta] . '>' . drupal_render($item) . '</li>';
  }
  $output .= '</ul>';

  // Render the top-level DIV.
  $output = '<div class="' . $variables['classes'] . (!in_array('clearfix', $variables['classes_array']) ? ' clearfix' : '') . '">' . $output . '</div>';

  return $output;
}

/**
 * Implements template_preprocess_views_view().
 *
 */
function zurb_foundation_preprocess_views_view(&$vars) {
}

/**
 * Implements template_preprocess_panels_pane().
 *
 */
function zurb_foundation_preprocess_panels_pane(&$vars) {
}

///**
// * Implements template_preprocess_views_views_fields().
// *
// */
//function THEMENAME_preprocess_views_view_fields(&$vars) {
//  if ($vars['view']->name == 'nodequeue_1') {
//
//    // Check if we have both an image and a summary
//    if (isset($vars['fields']['field_image'])) {
//
//      // If a combined field has been created, unset it and just show image
//      if (isset($vars['fields']['nothing'])) {
//        unset($vars['fields']['nothing']);
//      }
//
//    } elseif (isset($vars['fields']['title'])) {
//      unset ($vars['fields']['title']);
//    }
//
//    // Always unset the separate summary if set
//    if (isset($vars['fields']['field_summary'])) {
//      unset($vars['fields']['field_summary']);
//    }
//  }
//}

/**
 * Implements theme_menu_local_tasks().
 */
function zurb_foundation_menu_local_tasks(&$variables) {
  $output = '';

  if (!empty($variables['primary'])) {
    $variables['primary']['#prefix'] = '<h2 class="element-invisible">' . t('Primary tabs') . '</h2>';
    $variables['primary']['#prefix'] .= '<dl class="tabs">';
    $variables['primary']['#suffix'] = '</dl>';
    $output .= drupal_render($variables['primary']);
  }
  if (!empty($variables['secondary'])) {
    $variables['secondary']['#prefix'] = '<h2 class="element-invisible">' . t('Secondary tabs') . '</h2>';
    $variables['secondary']['#prefix'] .= '<dl class="tabs pill">';
    $variables['secondary']['#suffix'] = '</dl>';
    $output .= drupal_render($variables['secondary']);
  }

  return $output;
}

/**
 * Implements theme_menu_local_task().
 */
function zurb_foundation_menu_local_task(&$variables) {
  $link = $variables['element']['#link'];
  $link_text = $link['title'];

  if (!empty($variables['element']['#active'])) {
    // Add text to indicate active tab for non-visual users.
    $active = '<span class="element-invisible">' . t('(active tab)') . '</span>';

    // If the link does not contain HTML already, check_plain() it now.
    // After we set 'html'=TRUE the link will not be sanitized by l().
    if (empty($link['localized_options']['html'])) {
      $link['title'] = check_plain($link['title']);
    }
    $link['localized_options']['html'] = TRUE;
    $link_text = t('!local-task-title!active', array('!local-task-title' => $link['title'], '!active' => $active));
  }

  return '<dd' . (!empty($variables['element']['#active']) ? ' class="active"' : '') . '>' . l($link_text, $link['href'], $link['localized_options']) . "</dd>\n";
}

// @TODO maybe use hook_library_alter or hook_library
function zurb_foundation_js_alter(&$js) {
  if (!module_exists('jquery_update')) {
    // Swap out jQuery to use an updated version of the library.
    $js['misc/jquery.js']['data'] = drupal_get_path('theme', 'zurb_foundation') . '/js/jquery.js';
    $js['misc/jquery.js']['version'] = '1.8.2';
  } 
  // @TODO moving scripts to footer possibly remove?
  foreach ($js as $key => $js_script) {
    $js[$key]['scope'] = 'footer';
  }
}
