<?php

/**
 * @file
 * Preprocessors and helper functions to make theming easier.
 */

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Template\Attribute;
use Drupal\views_bootstrap\ViewsBootstrap;

/**
 * Prepares variables for views accordion templates.
 *
 * Default template: views-bootstrap-accordion.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_accordion(array &$vars): void {
  $view = $vars['view'];
  $options = $view->style_plugin->options;
  $vars['id'] = ViewsBootstrap::getUniqueId($view);
  $group_title_field = isset($options['grouping'][0]) ? $options['grouping'][0]['field'] : "";
  $panel_title_field = $options['panel_title_field'];
  $vars['output'] = $options['panel_output'];
  $vars['behavior'] = $options['behavior'];
  $vars['collapse'] = $options['sections'];
  $vars['attributes'] = new Attribute(['class' => ['accordion']]);

  // Define allowed HTML markup for XSS filtering.
  $allowed_markup = ['img', 'br', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'strong', 'em', 'i', 'small'];

  if ($options['flush']) {
    $vars['attributes']->addClass('accordion-flush');
  }

  if ($vars['output'] == 'grouped') {
    // Initialize an array to hold grouped content.
    $grouped_content = [];

    // Iterate over each row.
    foreach ($vars['rows'] as $id => $row) {
      // Get the panel title for the current row.
      $panel_title = $view->style_plugin->getField($id, $panel_title_field);

      // Filter the panel title for XSS protection.
      $filtered_title = Xss::filter($panel_title, $allowed_markup);

      // Initialize title if it doesn't exist in the grouped content array.
      if (!isset($grouped_content[$filtered_title])) {
        $grouped_content[$filtered_title] = [
          'title' => ['#markup' => $filtered_title],
          'content' => [],
        ];
      }

      // Add the current row's content to the grouped content.
      $grouped_content[$filtered_title]['content'][] = $row;
    }

    // Replace the rows with the grouped content.
    $vars['rows'] = $grouped_content;
  }
  else {
    foreach ($vars['rows'] as $id => $row) {
      $vars['group_title'] = $group_title_field ? $view->style_plugin->getField($id, $group_title_field) : "";
      $vars['rows'][$id] = [];
      $vars['rows'][$id]['content'] = $row;
      $vars['rows'][$id]['title'] = [
        '#markup' => Xss::filter($view->style_plugin->getField($id, $panel_title_field), $allowed_markup),
      ];
    }
  }
}

/**
 * Prepares variables for views cards templates.
 *
 * Default template: views-bootstrap-cards.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_cards(array &$vars): void {
  $view = $vars['view'];
  $options = $view->style_plugin->options;

  // Get unique ID and initialize card group attributes.
  $vars['id'] = ViewsBootstrap::getUniqueId($view);

  if ($options['card_group']) {
    $vars['use_card_group'] = TRUE;
    $vars['attributes'] = new Attribute(['class' => ['card-group']]);
    // Add custom classes if defined.
    if (!empty($options['card_group_class_custom'])) {
      $custom_classes = array_filter(explode(' ', $options['card_group_class_custom']));
      $clean_classes = array_map([Html::class, 'cleanCssIdentifier'], $custom_classes);
      $vars['attributes']->addClass($clean_classes);
    }
  }
  else {
    $vars['row_attributes'] = new Attribute(['class' => ['row']]);
    if (!empty($options['row_class_custom'])) {
      $custom_classes = array_filter(explode(' ', $options['row_class_custom']));
      $clean_classes = array_map([Html::class, 'cleanCssIdentifier'], $custom_classes);
      $vars['row_attributes']->addClass($clean_classes);
    }
    $vars['col_attributes'] = new Attribute(['class' => ['col']]);
    if (!empty($options['col_class_custom'])) {
      $custom_classes = array_filter(explode(' ', $options['col_class_custom']));
      $clean_classes = array_map([Html::class, 'cleanCssIdentifier'], $custom_classes);
      $vars['col_attributes']->addClass($clean_classes);
    }
  }

  // Set columns and display settings.
  $vars['columns'] = $options['columns'] ?? 1;
  $display = $options['display'] ?? 'content';

  // Check if the view is using fields to display instead of content.
  $using_fields = ($display != 'content' && $view->style_plugin->usesFields());

  // Define field variables if using fields.
  $image_field = $using_fields ? ($options['card_image_field'] ?? NULL) : NULL;
  $title_field = $using_fields ? ($options['card_title_field'] ?? NULL) : NULL;
  $content_field = $using_fields ? ($options['card_content_field'] ?? NULL) : NULL;

  // Process each row in the view.
  foreach ($vars['rows'] as $id => $row) {
    // Initialize the row attributes.
    $row_attributes = new Attribute(['class' => ['card']]);
    if ($row_class = $view->style_plugin->getRowClass($id)) {
      $row_attributes->addClass($row_class);
    }

    // Process row based on display type (fields vs content).
    if ($using_fields) {
      $vars['rows'][$id] = [
        'attributes' => $row_attributes,
        'image' => $image_field ? $view->style_plugin->getField($id, $image_field) : NULL,
        'title' => $title_field ? $view->style_plugin->getField($id, $title_field) : NULL,
        'content' => $content_field ? $view->style_plugin->getField($id, $content_field) : NULL,
      ];
    }
    else {
      // Display row content directly.
      $vars['rows'][$id] = [
        'attributes' => $row_attributes,
        'content' => $row,
      ];
    }
  }
}

/**
 * Prepares variables for views carousel template.
 *
 * Default template: views-bootstrap-carousel.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_carousel(array &$vars): void {
  $view = $vars['view'];
  $options = $view->style_plugin->options;
  $vars['id'] = ViewsBootstrap::getUniqueId($view);
  $vars['attributes']['class'][] = 'views-bootstrap-media-object';
  $vars['attributes']['class'][] = 'media-list';

  // Carousel options.
  $vars['interval'] = $options['interval'];
  $vars['navigation'] = $options['navigation'];
  $vars['indicators'] = $options['indicators'];
  $vars['pause'] = $options['pause'] ? 'hover' : FALSE;
  $vars['wrap'] = $options['wrap'];
  $vars['effect'] = $options['effect'];
  $vars['columns'] = $options['columns'];
  $prefix = ViewsBootstrap::getColumnPrefix($options['breakpoints']);
  $vars['breakpoints'] = $prefix . '-' . intval(12 / $options['columns']);
  $vars['use_caption'] = $options['use_caption'];
  $vars['caption_breakpoints'] = $options['caption_breakpoints'];
  $vars['ride'] = $options['ride'];
  $vars['display'] = 'content';

  // Carousel rows.
  if ($options['display'] != 'content' && $view->style_plugin->usesFields()) {
    $image = $options['image'];
    $title = $options['title'];
    $description = $options['description'];
    $fieldLabels = $view->display_handler->getFieldLabels(TRUE);
    $vars['display'] = 'fields';
  }

  foreach ($vars['rows'] as $id => $row) {
    $vars['rows'][$id] = [];
    $row_attributes = ['class' => []];
    $class = $options['row_class'];
    if ($vars['display'] == 'fields') {
      $vars['rows'][$id]['image'] = $view->style_plugin->getField($id, $image);
      $vars['rows'][$id]['title'] = $view->style_plugin->getField($id, $title);
      $vars['rows'][$id]['description'] = $view->style_plugin->getField($id, $description);
      // Add any additional fields to result.
      foreach (array_keys($fieldLabels) as $label) {
        if (!in_array($label, [$image, $title, $description])) {
          $vars['rows'][$id][$label] = $view->style_plugin->getField($id, $label);
        }
      }
      $class = strip_tags($view->style_plugin->tokenizeValue($class, $id));
      $class = Html::cleanCssIdentifier($class);
    }
    else {
      $vars['rows'][$id]['content'] = $row;
    }
    $classes = explode(' ', $class);
    foreach ($classes as &$class) {
      $class = Html::cleanCssIdentifier($class);
    }
    $row_class = array_filter($classes);
    if (!empty($row_class)) {
      $row_attributes['class'] = array_merge($row_attributes['class'], $row_class);
    }
    $vars['rows'][$id]['attributes'] = new Attribute($row_attributes);

  }

}

/**
 * Prepares variables for views dropdown templates.
 *
 * Adds 'dropdown' classes and some aria roles to the list structure.
 *
 * Default template: views-bootstrap-dropdown.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 *
 * @see template_preprocess_views_view_list()
 */
function template_preprocess_views_bootstrap_dropdown(array &$vars): void {
  /** @var \Drupal\views\ViewExecutable $view */
  $view = $vars['view'];
  $options = $view->style_plugin->options;
  $vars['id'] = ViewsBootstrap::getUniqueId($view);

  // Fetch classes from handler options. Sanitize user input.
  $wrapper_class = explode(' ', $options['wrapper_class']);
  $wrapper_class[] = 'dropdown';
  $wrapper_class = array_map([Html::class, 'cleanCssIdentifier'], $wrapper_class);
  $vars['attributes'] = new Attribute(['class' => $wrapper_class]);

  $class = explode(' ', $options['class']);
  $class[] = "dropdown-menu";
  $class = array_map([Html::class, 'cleanCssIdentifier'], $class);
  $vars['list']['attributes'] = new Attribute(['class' => $class]);

  $vars['button']['text'] = $options['button_text'];
  $button_class = explode(' ', $options['button_class']);
  $button_class[] = 'dropdown-toggle';
  $button_class = array_map([Html::class, 'cleanCssIdentifier'], $button_class);
  $vars['button']['attributes'] = new Attribute(['class' => $button_class]);

  // Inject additional dropdown aria attributes into the individual rows to
  // make them behave as menu items.
  // The most common case should be one linked field, but there seems
  // no reason why the whole rendered row can't be here if that's what you want.
  foreach ($vars['rows'] as $id => $row) {
    $vars['rows'][$id] = [];
    $vars['rows'][$id]['content'] = $row;
    // Using role=presentation here is supposed to diminish the screen readers
    // treatment of list items as "List Items". Being a menu item is sufficient.
    // tabindex -1 means that all these links will not waylay keyboard
    // navigation (until the user deliberately opens that list).
    $vars['rows'][$id]['attributes'] = new Attribute(['role' => 'menuitem presentation', 'tabindex' => -1]);
    if ($row_class = $view->style_plugin->getRowClass($id)) {
      $vars['rows'][$id]['attributes']->addClass($row_class);
    }
  }
}

/**
 * Prepares variables for views grid templates.
 *
 * Default template: views-bootstrap-grid.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_grid(array &$vars): void {
  $view = $vars['view'];
  $rows = $vars['rows'];
  $options = $view->style_plugin->options;
  $vars['id'] = ViewsBootstrap::getUniqueId($view);
  $attributes = new Attribute(['class' => ['grid']]);

  if ($options['grid_class']) {
    $grid_class = explode(' ', $options['grid_class']);
    $grid_classes = array_map([Html::class, 'cleanCssIdentifier'], array_filter($grid_class));
    $attributes->addClass($grid_classes);
  }

  $col_classes = [];
  foreach (ViewsBootstrap::getBreakpoints() as $breakpoint) {
    if ($options["col_$breakpoint"] == 'none') {
      continue;
    }
    $col_classes[] = $options["col_$breakpoint"];
  }
  foreach ($rows as $id => $row) {
    $row_attributes = new Attribute();
    if ($row_class = $view->style_plugin->getRowClass($id)) {
      $row_attributes->addClass($row_class);
    }
    $row_attributes->addClass($col_classes);
    $vars['rows'][$id] = [
      'content' => $row,
      'attributes' => $row_attributes,
    ];
  }

  $vars['attributes'] = $attributes;
}

/**
 * Prepares variables for views list group templates.
 *
 * Default template: views-bootstrap-list-group.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_list_group(array &$vars): void {
  $view = $vars['view'];
  $options = $view->style_plugin->options;
  $vars['id'] = ViewsBootstrap::getUniqueId($view);
  $group_title_field = isset($options['grouping'][0]) ? $options['grouping'][0]['field'] : "";
  $vars['attributes'] = new Attribute(['class' => ['views-bootstrap-list-group']]);

  if ($options['list_group_class_custom']) {
    $classes = explode(' ', $options['list_group_class_custom']);
    $classes = array_map([Html::class, 'cleanCssIdentifier'], array_filter($classes));
    $vars['attributes']->addClass($classes);
  }

  foreach ($vars['rows'] as $id => $row) {
    $vars['group_title'] = $group_title_field ? $view->style_plugin->getField($id, $group_title_field) : "";
    $vars['rows'][$id] = [];
    $vars['rows'][$id]['content'] = $row;
    $vars['rows'][$id]['title'] = $vars['view']->style_plugin->getField($id, $options['title_field']);
    $vars['rows'][$id]['attributes'] = new Attribute(['class' => ['list-group-item']]);
    if ($row_class = $view->style_plugin->getRowClass($id)) {
      $vars['rows'][$id]['attributes']->addClass($row_class);
    }
  }

}

/**
 * Prepares variables for views media object templates.
 *
 * Default template: views-bootstrap-media-object.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_media_object(array &$vars): void {
  $view = $vars['view'];
  $options = $view->style_plugin->options;
  $vars['id'] = ViewsBootstrap::getUniqueId($vars['view']);
  $image_field = $options['image_field'];
  $heading_field = $options['heading_field'];
  $body_field = $options['body_field'];
  $alignment = $options['image_class'];
  $image_placement = $options['image_placement'];
  $body_placement = $image_placement == 'first' ? 'last' : 'first';
  $vars['alignment'] = "align-items-{$alignment}";
  $vars['order_image'] = "order-{$image_placement}";
  $vars['order_body'] = "order-{$body_placement}";

  foreach ($vars['rows'] as $id => $row) {
    $vars['rows'][$id] = [
      'image' => $view->style_plugin->getField($id, $image_field),
      'heading' => $view->style_plugin->getField($id, $heading_field),
      'body' => $view->style_plugin->getField($id, $body_field),
    ];
  }
}

/**
 * Prepares variables for views tab templates.
 *
 * Default template: views-bootstrap-tab.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_tab(array &$vars): void {
  $view = $vars['view'];
  $options = $view->style_plugin->options;

  $tab_field = $options['tab_field'] ?? '';
  $group_title_field = isset($options['grouping'][0]) ? $options['grouping'][0]['field'] : "";
  $vars['id'] = ViewsBootstrap::getUniqueId($view);
  $vars['output'] = $options['tab_output'];
  $vars['tab_type'] = $options['tab_type'];
  $vars['tab_position'] = $options['tab_position'];
  $vars['tab_fade'] = $options['tab_fade'] ? 'fade' : '';

  // Define allowed HTML markup for XSS filtering.
  $allowed_markup = ['img', 'br', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'strong', 'em', 'i', 'small'];

  // Keeps which id is used for group by field value.
  $tab_ids = [];
  foreach (array_keys($vars['rows']) as $key) {
    $tab_field_value = Xss::filter($view->style_plugin->getField($key, $tab_field), $allowed_markup);

    // If tabs are grouped, get previous key for the value.
    if ($vars['output'] == 'grouped' && isset($tab_ids[$tab_field_value])) {
      $key = $tab_ids[$tab_field_value];
    }
    else {
      $tab_ids[$tab_field_value] = $key;
    }
    $vars['tabs'][$key] = $tab_field_value;
  }
  foreach ($vars['rows'] as $id => $row) {
    $vars['group_title'] = $view->style_plugin->getField($id, $group_title_field) ?? "";
    $vars['rows'][$id] = [];

    $tab_field_value = Xss::filter($view->style_plugin->getField($id, $tab_field), $allowed_markup);

    // If grouped, the tabs create new array with all the values for the tab
    // content.
    if ($vars['output'] == 'grouped' && isset($tab_ids[$tab_field_value])) {
      $vars['rows'][$id]['content_tabs'] ??= [];

      // Group by the key of first met value of the field grouped by.
      $id = $tab_ids[$tab_field_value];
      $vars['rows'][$id]['content_tabs'][] = $row;
    }
    else {
      $vars['rows'][$id]['content'] = $row;
    }

    $vars['rows'][$id]['attributes'] ??= new Attribute();

    if ($row_class = $view->style_plugin->getRowClass($id)) {
      $vars['rows'][$id]['attributes']->addClass($row_class);
    }
  }
}

/**
 * Prepares variables for views table templates.
 *
 * Default template: views-bootstrap-table.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function template_preprocess_views_bootstrap_table(array &$vars): void {
  $options = $vars['view']->style_plugin->options;
  $vars['attributes'] = new Attribute(['class' => ['table']]);
  $vars['responsive'] = $options['responsive'];
  foreach (array_filter($options['bootstrap_styles']) as $style) {
    $vars['attributes']->addClass('table-' . $style);
  }
  if ($options['table_class_custom']) {
    $option_classes = array_filter(explode(' ', $options['table_class_custom']));
    $classes = array_map([Html::class, 'cleanCssIdentifier'], $option_classes);
    $vars['attributes']->addClass($classes);
  }

}
