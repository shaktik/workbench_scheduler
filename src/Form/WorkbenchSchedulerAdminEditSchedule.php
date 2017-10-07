<?php

/**
 * @file
 * Contains \Drupal\workbench_scheduler\Form\WorkbenchSchedulerAdminEditSchedule.
 */

namespace Drupal\workbench_scheduler\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class WorkbenchSchedulerAdminEditSchedule extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'workbench_scheduler_admin_edit_schedule';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state, $name = NULL) {
    $schedule = FALSE;
    // Passed a machine name? editing a schedule.
    if ($name) {
      // Attempt to load the schedule.
      if ($schedule = workbench_scheduler_load_schedules($name)) {
        // Store in the form.
        $form['#schedule'] = $schedule;
      }
    }
    // Label field.
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => t('Schedule Name'),
      '#required' => TRUE,
      '#description' => t('A user friendly name. Only used for admin purposes'),
      '#size' => 40,
      '#maxlength' => 127,
    ];

    // Field for machine name.
    $form['name'] = [
      '#title' => t('Machine Name'),
      '#description' => t('A machine friendly name.'),
      '#type' => 'machine_name',
      '#required' => TRUE,
      '#size' => 25,
      '#maxlength' => 25,
      '#default_value' => ($schedule ? $schedule->name : ''),
      '#machine_name' => [
        'exists' => '_workbench_schedule_check_machine_name_exists',
        'source' => [
          'label'
          ],
      ],
    ];
    // Fetch a list of the available moderation states.
    $states = workbench_scheduler_state_labels();
    // Save to the form.
    $form['#states'] = $states;

    // Add none option.
    $states = array_merge(['' => t('None')], $states);

    // Select list for start state.
    $form['start_state'] = [
      '#type' => 'select',
      '#title' => t('Start State'),
      '#description' => t('Select the state to be set when a node reaches its "start date"'),
      '#options' => $states,
      '#required' => FALSE,
    ];

    // Select list for end state.
    $form['end_state'] = [
      '#type' => 'select',
      '#title' => t('End State'),
      '#description' => t('Select the state to be set when a node reaches its "end date"'),
      '#options' => $states,
      '#required' => FALSE,
    ];

    // Fetch a list of content types that have moderation enabled.
    // From the workbench moderation module.
    module_load_include('module', 'workbench_moderation');
    $types = workbench_moderation_moderate_node_types();
    $info = \Drupal::entityManager()->getDefinition('node');

    $tmp_types = [];
    // Make into an associative array.
    foreach ($types as $type) {
      $states = workbench_moderation_states();
      // @FIXME
      // // @FIXME
      // // The correct configuration object could not be determined. You'll need to
      // // rewrite this call manually.
      // $default_state = variable_get('workbench_moderation_default_state_' . $type, workbench_moderation_state_none());

      $label = $info['bundles'][$type]['label'];
      $tmp_types[$type] = [
        'label' => $label,
        'default_state' => $states[$default_state]->label,
      ];
    }
    // Store types to the form.
    $form['#types'] = $types;
    $types = $tmp_types;
    unset($tmp_types);

    $header = [
      'label' => t('Content Type'),
      'default_state' => t('Default Moderation State'),
    ];

    // Label  and description.
    $form['table_label'] = [
      '#type' => 'markup',
      '#markup' => '<label>' . t('Content Types') . '</label>',
    ];
    $form['table_description'] = [
      '#type' => 'markup',
      '#markup' => t('Select the content types that can use this schedule'),
    ];
    // Checkboxes for content types.
    $form['types'] = [
      '#type' => 'tableselect',
      '#title' => t('Content Types'),
      '#description' => t('Select the content types that can use this schedule'),
      '#header' => $header,
      '#options' => $types,
      '#required' => TRUE,
      '#empty' => t('No Content Types are configured with workbench moderation'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => ($schedule ? t('Update') : t('Save')),
    ];

    // Editing a schedule?
    if ($schedule) {
      // Add a delete button.
      $form['delete'] = [
        '#type' => 'submit',
        '#value' => t('Delete'),
      ];

      // Remove the machine name field.
      unset($form['name']);
      // Set default values for the other fields.
      $form['label']['#default_value'] = $schedule->label;
      $form['start_state']['#default_value'] = $schedule->start_state;
      $form['end_state']['#default_value'] = $schedule->end_state;
      $form['types']['#default_value'] = array_combine($schedule->types, $schedule->types);
    }
    // Return the form.
    return $form;
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // Validate label.
    if (strlen(trim(strip_tags($form_state->getValue([
      'label'
      ])))) < 1) {
      $form_state->setErrorByName('label', t('Invalid Schedule name provided'));
    }
    // Check that the two states are not the same.
    if ($form_state->getValue([
      'start_state'
      ]) == $form_state->getValue(['end_state'])) {
      $form_state->setErrorByName('end_state', t('End state must be different from start state'));
    }
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // Check to see if the delete button was clicked.
    if ($form_state->get(['clicked_button', '#id']) == 'edit-delete') {
      // Redirect to the delete form.
      drupal_goto('admin/config/workbench/scheduler/schedules/' . $form['#schedule']->name . '/delete');
    }
      // Only other button is the submit button.
    else {
      // Build the array of schedule data.
      $schedule_data = [
        'label' => trim(strip_tags($form_state->getValue(['label']))),
        'start_state' => $form_state->getValue(['start_state']),
        'end_state' => $form_state->getValue(['end_state']),
        // Add the checked types to the schedule data.
      'types' => array_intersect($form['#types'], $form_state->getValue(['types'])),
      ];

      // Updating an existing schedule?
      if (isset($form['#schedule'])) {
        // Fetch the machine name form the object.
        $machine_name = $form['#schedule']->name;
      }
      else {
        // Fetch the new machine name from passed values.
        $machine_name = $form_state->getValue(['name']);
      }

      // Attempt a save/update
      if ($result = workbench_scheduler_save_schedule($machine_name, $schedule_data)) {
        drupal_set_message(t('Schedule @status', [
          '@status' => ($result == 1 ? t('Saved') : t('Updated'))
          ]), 'status', FALSE);
        // Redirect back to the schedules table.
        $form_state->set(['redirect'], 'admin/config/workbench/scheduler/schedules');
      }
      else {
        drupal_set_message(t('Error saving schedule'), 'error', FALSE);
      }
    }
  }

}
