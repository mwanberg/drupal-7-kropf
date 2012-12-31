<?php
/**
 * @file
 * template.php file for zoundation
 */

/**
 * Implements hook_preprocess_html().
 */
function zoundation_preprocess_html(&$vars) {
  // Move JS files "$scripts" to page bottom for perfs/logic.
  // Add JS files that *needs* to be loaded in the head in a new "$head_scripts" scope.
  // For instance the Modernizr lib.
  $path = drupal_get_path('theme', 'zoundation');
  drupal_add_js($path . '/javascripts/foundation/modernizr.foundation.js', array('scope' => 'head_scripts', 'weight' => -1, 'preprocess' => FALSE));
}

/**
 * Implements hook_preprocess_page().
 */
function zoundation_preprocess_page(&$vars) {
  zoundation_check_dependencies();

  $first = $vars['page']['sidebar_first'];
  $second = $vars['page']['sidebar_second'];

  // Defining Main and Sidebar Classes
  if (!empty($first) && !empty($second)) {
    $vars['main_classes'] = 'six columns push-three';
    $vars['sidebar_first_classes'] = 'three columns pull-six';
    $vars['sidebar_second_classes'] = 'three columns';
  }
  elseif (empty($first) && !empty($second)) {
    $vars['main_classes'] = 'nine columns';
    $vars['sidebar_first_classes'] = '';
    $vars['sidebar_second_classes'] = 'three columns';
  }
  elseif (!empty($first) && empty($second)) {
    $vars['main_classes'] = 'nine columns push-three';
    $vars['sidebar_first_classes'] = 'three columns pull-nine';
    $vars['sidebar_second_classes'] = '';
  }
  else {
    $vars['main_classes'] = 'twelve columns';
    $vars['sidebar_first_classes'] = '';
    $vars['sidebar_second_classes'] = '';
  }

  // Determine column width of triptych regions.
  $triptychs = array(
    'first' => $vars['page']['triptych_first'],
    'middle' => $vars['page']['triptych_middle'],
    'last' => $vars['page']['triptych_last'],
  );
  $columns = zoundation_calculate_columns($triptychs);
  foreach ($triptychs as $column => $content) {
    if (!empty($columns[$column])) {
      $vars['triptych_' . $column . '_classes'] = zoundation_number_to_text($columns[$column]) . ' columns';
    }
    else {
      $vars['triptych_' . $column . '_classes'] = '';
    }
  }
}

/**
 * Implements hook_preprocess_node().
 */
function zoundation_preprocess_node(&$vars) {
  if ($vars['view_mode'] == 'teaser') {
    $vars['content']['links']['node']['#links']['node-readmore']['title'] = t('Read More');
    $vars['content']['links']['node']['#links']['node-readmore']['attributes']['class'] = array('button');
    $vars['content']['links']['comment']['#links']['comment-add']['attributes']['class'] = array('button', 'secondary', 'small');
  }
}

/**
 * Implements hook_process_html().
 */
function zoundation_process_html(&$vars) {
  $vars['head_scripts'] = drupal_get_js('head_scripts');
}



/**
 * Returns HTML for primary and secondary local tasks. This primarily changes
 * the ul to a dl for foundation
 *
 * @param $variables
 *   An associative array containing:
 *     - primary: (optional) An array of local tasks (tabs).
 *     - secondary: (optional) An array of local tasks (tabs).
 *
 * @see theme_menu_local_tasks()
 */
 function zoundation_menu_local_tasks(&$variables) {
  $output = '';

  //Change the ul to dl to create Foundation Tabs
  if (!empty($variables['primary'])) {
    $variables['primary']['#prefix'] = '<h2 class="element-invisible">' . t('Primary tabs') . '</h2>';
    $variables['primary']['#prefix'] .= '<dl class="tabs primary">';
    $variables['primary']['#suffix'] = '</dl>';
    $output .= drupal_render($variables['primary']);
  }
  if (!empty($variables['secondary'])) {
    $variables['secondary']['#prefix'] = '<h2 class="element-invisible">' . t('Secondary tabs') . '</h2>';
    $variables['secondary']['#prefix'] .= '<dl class="tabs secondary pill">';
    $variables['secondary']['#suffix'] = '</dl>';
    $output .= drupal_render($variables['secondary']);
  }

  return $output;
}

/**
 * Returns HTML for a single local task link. This changes the li to a dd for
 * foundation.
 *
 * @param $variables
 *   An associative array containing:
 *   - element: A render element containing:
 *     - #link: A menu link array with 'title', 'href', and 'localized_options'
 *       keys.
 *     - #active: A boolean indicating whether the local task is active.
 *
 * @see theme_menu_local_task()
 */
function zoundation_menu_local_task($variables) {
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

/**
 * Helper function to make sure zoundation_support is installed and enabled.
 */
function zoundation_check_dependencies() {
  if (!module_exists('zoundation_support')) {
    drupal_set_message(t('The zoundation_support module is required for zoundation to run properly.'), 'error');
  }
}

/**
 * Returns HTML for a button form element.
 *
 * @param $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #attributes, #button_type, #name, #value.
 *
 * @see theme_button()
 */
function zoundation_button($variables) {

  $element = $variables['element'];
  $element['#attributes']['type'] = 'submit';
  $element['#attributes']['class'][] = 'button';
  element_set_attributes($element, array('id', 'name', 'value'));

  $element['#attributes']['class'][] = 'form-' . $element['#button_type'];
  if (!empty($element['#attributes']['disabled'])) {
    $element['#attributes']['class'][] = 'form-button-disabled';
  }
  //Adding alert class for Cancel Account button
  if ($variables['element']['#id'] == 'edit-cancel') {
    $element['#attributes']['class'][] = 'alert';
  }

  return '<input' . drupal_attributes($element['#attributes']) . ' />';
}

/**
 * Returns HTML for status and/or error messages, grouped by type.
 *
 * An invisible heading identifies the messages for assistive technology.
 * Sighted users see a colored box. See http://www.w3.org/TR/WCAG-TECHS/H69.html
 * for info.
 *
 * @param $variables
 *   An associative array containing:
 *   - display: (optional) Set to 'status' or 'error' to display only messages
 *     of that type.
 *
 * @see  theme_status_messages()
 */
function zoundation_status_messages($variables) {
  $display = $variables['display'];
  $output = '';

  $status_heading = array(
    'status' => t('Status message'),
    'error' => t('Error message'),
    'warning' => t('Warning message'),
  );

  foreach (drupal_get_messages($display) as $type => $messages) {

    // Creating new variable to add Foundation messages class into code
    if ($type == 'error') {
      $zoundation_type = 'alert';
      $zoundation_icon = 'error';
    }
    elseif ($type == 'status') {
      $zoundation_type = 'success';
      $zoundation_icon = 'checkmark';
    }
    elseif ($type == 'warning') {
      $zoundation_type = '';
      $zoundation_icon = 'flag';
    }

    $output .= "<div class=\"messages alert-box $type $zoundation_type\">\n";
    if (!empty($status_heading[$type])) {
      $output .= '<h2 class="element-invisible">' . $status_heading[$type] . "</h2>\n";
    }
    if (count($messages) > 1) {
      $output .= " <ul>\n";
      foreach ($messages as $message) {
        $output .= "  <li><i class=\"foundicon-" . $zoundation_icon. "\"><p>" . $message . "</p></i></li>\n";
      }
      $output .= " </ul>\n";
    }
    else {
      //$output .= $messages[0];
      $output .= "<i class=\"foundicon-" . $zoundation_icon. "\"><p>" . $messages[0] . '</p></i>';
    }
    $output .= "<a href=\"\" class=\"close\">&times;</a></div>\n";
  }
  return $output;
}

/**
 * Returns HTML for a single local action link.
 *
 * @param $variables
 *   An associative array containing:
 *   - element: A render element containing:
 *     - #link: A menu link array with 'title', 'href', and 'localized_options'
 *       keys.
 *
 * @see theme_menu_local_action()
 */
function zoundation_menu_local_action($variables) {
  $link = $variables['element']['#link'];

  $output = '<li>';
  if (isset($link['href'])) {
    $output .= l($link['title'], $link['href'], array('attributes' => array('class' => array('button'))));
  }
  elseif (!empty($link['localized_options']['html'])) {
    $output .= $link['title'];
  }
  else {
    $output .= check_plain($link['title']);
  }
  $output .= "</li>\n";

  return $output;
}


/**
 * Returns HTML for an administrative block for display.
 *
 * @param $variables
 *   An associative array containing:
 *   - block: An array containing information about the block:
 *     - show: A Boolean whether to output the block. Defaults to FALSE.
 *     - title: The block's title.
 *     - content: (optional) Formatted content for the block.
 *     - description: (optional) Description of the block. Only output if
 *       'content' is not set.
 *
 * @see theme_admin_block()
 */
function zoundation_admin_block($variables) {
  $block = $variables['block'];
  $output = '';

  // Don't display the block if it has no content to display.
  if (empty($block['show'])) {
    return $output;
  }

  $output .= '<div class="admin-panel panel">';
  if (!empty($block['title'])) {
    $output .= '<h3>' . $block['title'] . '</h3>';
  }
  if (!empty($block['content'])) {
    $output .= '<div class="body">' . $block['content'] . '</div>';
  }
  else {
    $output .= '<div class="description">' . $block['description'] . '</div>';
  }
  $output .= '</div>';

  return $output;
}

/**
 * Returns HTML for a link to show or hide inline help descriptions.
 *
 * @see theme_system_compact_link()
 */
function zoundation_system_compact_link() {
  $output = '<div class="compact-link">';
  if (system_admin_compact_mode()) {
    $output .= l(t('Show descriptions'), 'admin/compact/off',
      array(
        'attributes' => array(
          'title' => t('Expand layout to include descriptions.'),
          'class' => array('button')
          ),
        'query' => drupal_get_destination()
        )
      );
  }
  else {
    $output .= l(t('Hide descriptions'), 'admin/compact/on',
      array(
        'attributes' => array(
          'title' => t('Compress layout by hiding descriptions.'),
          'class' => array('button')
          ),
        'class' => array('button'),
        'query' => drupal_get_destination()
        )
      );
  }
  $output .= '</div>';

  return $output;
}

// Create layout for admin

/**
 * Returns HTML for the output of the dashboard page.
 *
 * @param $variables
 *   An associative array containing:
 *   - menu_items: An array of modules to be displayed.
 *
 * @see  theme_system_admin_index()
 */
function zoundation_system_admin_index($variables) {
  $menu_items = $variables['menu_items'];

  $stripe = 0;
  $container = array(
    'left' => '',
    'right' => '',
  );
  $flip = array(
    'left' => 'right',
    'right' => 'left',
  );
  $position = 'left';

  // Iterate over all modules.
  foreach ($menu_items as $module => $block) {
    list($description, $items) = $block;

    // Output links.
    if (count($items)) {
      $block = array();
      $block['title'] = $module;
      $block['content'] = theme('admin_block_content', array('content' => $items));
      $block['description'] = t($description);
      $block['show'] = TRUE;

      if ($block_output = theme('admin_block', array('block' => $block))) {
        if (!isset($block['position'])) {
          // Perform automatic striping.
          $block['position'] = $position;
          $position = $flip[$position];
        }
        $container[$block['position']] .= $block_output;
      }
    }
  }

  $output = '<div class="admin clearfix">';
  $output .= theme('system_compact_link');
  foreach ($container as $id => $data) {
    $output .= '<div class="columns six ' . $id . ' clearfix">';
    $output .= $data;
    $output .= '</div>';
  }
  $output .= '</div>';

  return $output;
}

/**
 * Returns HTML for the Appearance page.
 *
 * @param $variables
 *   An associative array containing:
 *   - theme_groups: An associative array containing groups of themes.
 *
 * @see theme_system_themes_page
 */
function zoundation_system_themes_page($variables) {
  $theme_groups = $variables['theme_groups'];

  $output = '<div id="system-themes-page">';

  foreach ($variables['theme_group_titles'] as $state => $title) {
    if (!count($theme_groups[$state])) {
      // Skip this group of themes if no theme is there.
      continue;
    }
    // Start new theme group.
    $output .= '<div class="system-themes-list system-themes-list-' . $state . ' clearfix"><h2>' . $title . '</h2>';

    foreach ($theme_groups[$state] as $theme) {

      // Theme the screenshot.
      $screenshot = $theme->screenshot ? theme('image', $theme->screenshot) : '<div class="no-screenshot">' . t('no screenshot') . '</div>';

      // Localize the theme description.
      $description = t($theme->info['description']);

      // Style theme info
      $notes = count($theme->notes) ? ' (' . join(', ', $theme->notes) . ')' : '';
      $theme->classes[] = 'theme-selector';
      $theme->classes[] = 'clearfix';
      $output .= '<div class="' . join(' ', $theme->classes) . '">' . $screenshot . '<div class="theme-info"><h3>' . $theme->info['name'] . ' ' . (isset($theme->info['version']) ? $theme->info['version'] : '') . $notes . '</h3><div class="theme-description"><p>' . $description . '</p></div>';

      // Make sure to provide feedback on compatibility.
      if (!empty($theme->incompatible_core)) {
        $output .= '<div class="incompatible">' . t('This version is not compatible with Drupal !core_version and should be replaced.', array('!core_version' => DRUPAL_CORE_COMPATIBILITY)) . '</div>';
      }
      elseif (!empty($theme->incompatible_php)) {
        if (substr_count($theme->info['php'], '.') < 2) {
          $theme->info['php'] .= '.*';
        }
        $output .= '<div class="incompatible">' . t('This theme requires PHP version @php_required and is incompatible with PHP version !php_version.', array('@php_required' => $theme->info['php'], '!php_version' => phpversion())) . '</div>';
      }
      else {
        $output .= theme('links', array('links' => $theme->operations, 'attributes' => array('class' => array('operations', 'clearfix'))));
      }
      $output .= '</div></div>';
    }
    $output .= '</div>';
  }
  $output .= '</div>';

  return $output;
}

/**
 * Returns HTML for a query pager.
 *
 *  Menu callbacks that display paged query results should call theme('pager') to retrieve a pager control so that users  
 *  can view other results. Format a list of nearby pages with additional query results.
 *
 * @param $variables
 *   An associative array containing: 
 *    - tags: An array of labels for the controls in the pager.
 *    - element: An optional integer to distinguish between multiple pagers on one page.
 *    - parameters: An associative array of query string parameters to append to the pager links.
 *    - quantity: The number of pages in the list.
 * @see theme_pager()
 */
function zoundation_pager($variables) {

  $tags = $variables['tags'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $quantity = $variables['quantity'];
  global $pager_page_array, $pager_total;

  // Calculate various markers within this pager piece:
  // Middle is used to "center" pages around the current page.
  $pager_middle = ceil($quantity / 2);
  // current is the page we are currently paged to
  $pager_current = $pager_page_array[$element] + 1;
  // first is the first page listed by this pager piece (re quantity)
  $pager_first = $pager_current - $pager_middle + 1;
  // last is the last page listed by this pager piece (re quantity)
  $pager_last = $pager_current + $quantity - $pager_middle;
  // max is the maximum page number
  $pager_max = $pager_total[$element];
  // End of marker calculations.

  // Prepare for generation loop.
  $i = $pager_first;
  if ($pager_last > $pager_max) {
    // Adjust "center" if at end of query.
    $i = $i + ($pager_max - $pager_last);
    $pager_last = $pager_max;
  }
  if ($i <= 0) {
    // Adjust "center" if at start of query.
    $pager_last = $pager_last + (1 - $i);
    $i = 1;
  }
  // End of generation loop preparation.

  $li_first = theme('pager_first', array('text' => (isset($tags[0]) ? $tags[0] : t('«')), 'element' => $element, 'parameters' => $parameters));
  // $li_previous = theme('pager_previous', array('text' => (isset($tags[1]) ? $tags[1] : t('‹ previous')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  // $li_next = theme('pager_next', array('text' => (isset($tags[3]) ? $tags[3] : t('next ›')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_last = theme('pager_last', array('text' => (isset($tags[4]) ? $tags[4] : t('»')), 'element' => $element, 'parameters' => $parameters));

  if ($pager_total[$element] > 1) {
    if ($li_first) {
      $items[] = array(
        'class' => array(''), 
        'data' => '<a href="">&laquo;</a>' . $li_first,
      );
    }

    // When there is more than one page, create the pager list.
    if ($i != $pager_max) {
      if ($i > 1) {
        $items[] = array(
          'class' => array('pager-ellipsis'), 
          'data' => '…',
        );
      }
      // Now generate the actual pager piece.
      for (; $i <= $pager_last && $i <= $pager_max; $i++) {
        if ($i < $pager_current) {
          $items[] = array(
            'class' => array('pager-item'), 
            'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($pager_current - $i), 'parameters' => $parameters)),
          );
        }
        if ($i == $pager_current) {
          $items[] = array(
            'class' => array('current'), 
            'data' => '<a href="#">' . $i . '</a>',
          );
        }
        if ($i > $pager_current) {
          $items[] = array(
            'class' => array('pager-item'), 
            'data' => theme('pager_next', array('text' => $i, 'element' => $element, 'interval' => ($i - $pager_current), 'parameters' => $parameters)),
          );
        }
      }
      if ($i < $pager_max) {
        $items[] = array(
          'class' => array('pager-ellipsis'), 
          'data' => '…',
        );
      }
    }
    if ($li_last) {
      $items[] = array(
        'class' => array('pager-last'), 
        'data' => $li_last,
      );
    }
    return '<h2 class="element-invisible">' . t('Pages') . '</h2>' . theme('item_list', array(
      'items' => $items, 
      'attributes' => array('class' => array('pagination')),
    ));
  }
}

/**
 * Returns HTML for a breadcrumb trail.
 *
 * @param $variables
 *   An associative array containing:
 *   - breadcrumb: An array containing the breadcrumb links.
 *
 * @see theme_breadcrumb()
 */
function zoundation_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  
  if (!empty($breadcrumb)) {
    // Provide a navigational heading to give context for breadcrumb links to
    // screen-reader users. Make the heading invisible with .element-invisible.
    $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';

    $output .= '<ul class="breadcrumbs"><li>' . implode('</li><li>', $breadcrumb) . '</li></ul>';
    return $output;
  }
}

/**
 * Given an array of columnar content determine the number of columns they
 * should take up.
 *
 * @param array $vars
 *   The columns with key and content. The keys are arbitrary.
 * @param integer $max
 *   How many columsn is 100% (foundation default is 12)
 *
 * @return array
 *   Keyed corresponding to the keys provided in $vars containing the number of
 *   columns the respective column should consume.
 *
 * @todo Can we abstract this further to cover the sidebars and main content?
 */
function zoundation_calculate_columns($vars, $max = 12) {
  // run through each var and find the total set variables
  $count = 0;
  $keys = array();
  foreach ($vars as $key => $val) {
    if (!empty($val)) {
      $count++;
      // track this key
      $keys[] = $key;
    }
  }

  if ($count !== 0) {
    $width = $max / $count;
    return array_fill_keys($keys, $width);
  }

  return array();
}

/**
 * Return a string representation of a given number.
 *
 * @param int $number
 *   The integer to return the string value for.
 *
 * @return string
 *   A string representing the numeric value or an empty string if the value is
 *   outside the provided range 1-12 or invalid.
 *
 * @todo Consider refactoring all calls to this function to use the
 * NumberFormatter() class that is shipped standard with PHP >= 5.3.0 see:
 * http://www.php.net/manual/en/class.numberformatter.php
 *
 */
function zoundation_number_to_text($number) {
  $lookup = array(
    1 => 'one',
    2 => 'two',
    3 => 'three',
    4 => 'four',
    5 => 'five',
    6 => 'six',
    7 => 'seven',
    8 => 'eight',
    9 => 'nine',
    10 => 'ten',
    11 => 'eleven',
    12 => 'twelve',
  );

  // Let other modules adjust our response.
  drupal_alter('zoundation_number_to_text', $lookup);

  if (isset($lookup[$number])) {
    return $lookup[$number];
  }
  return '';
}