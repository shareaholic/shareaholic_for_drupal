<?php
/**
 * @file
 * Field handler to present Shareaholic.
 */

class views_handler_field_node_shareaholic extends views_handler_field {
  function construct() {
    parent::construct();
    $this->additional_fields['nid'] = 'nid';
    $this->additional_fields['title'] = 'title';
  }

  function option_definition() {
    $options = parent::option_definition();

    $options['profile'] = array(
      'default' => 'default',
      'translatable' => FALSE
    );

    return $options;
  }

  function options_form(&$form, &$form_state) {
    $options = array();
    ctools_include('export');
    foreach (ctools_export_crud_load_all('shareaholic_profiles') as $profile) {
      if (empty($profile->disabled)) {
        $options[$profile->name] = $profile->name;
      }
    }
    $form['profile'] = array(
      '#type' => 'select',
      '#title' => t('Shareaholic profile'),
      '#empty_value' => '',
      '#options' => $options,
      '#default_value' => $this->options['profile'],
    );
    parent::options_form($form, $form_state);
  }

  function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  function render($values) {
    return theme('shareaholic', array(
      'url' => url('node/' . $this->get_value($values, 'nid'), array('absolute' => TRUE)),
      'title' => $this->get_value($values, 'title'),
      'profile' => $this->options['profile'],
    ));
  }
}
