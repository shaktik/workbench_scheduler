<?php

/**
 * @file
 * Contains \Drupal\workbench_scheduler\Form\WorkbenchSchedulerAdminEditRevisionSchedule.
 */

namespace Drupal\workbench_scheduler\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class WorkbenchSchedulerAdminEditRevisionSchedule extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'workbench_scheduler_admin_edit_revision_schedule';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state, $node = NULL, $vid = NULL) {
    $form['nid'] = ['#type' => 'hidden', '#value' => $node->nid];
    $form['vid'] = ['#type' => 'hidden', '#value' => $vid];
    $type_schedules = workbench_scheduler_load_type_schedules($node->type);
    $schedule_options = [];
    $schedule_options[] = [
      'label' => t('No Schedule'),
      'start_state' => '',
      'end_state' => '',
    ];
    $moderation_states = workbench_scheduler_state_labels();
    // Add each type schedule to the options array.
    foreach ($type_schedules as $schedule) {
      if (\Drupal::currentUser()->hasPermission('set any workbench schedule') || \Drupal::currentUser()->hasPermission('set workbench schedule for ' . $schedule->name)) {
        $schedule_options[$schedule->sid] = [
          'label' => $schedule->label,
          'start_state' => isset($moderation_states[$schedule->start_state]) ? $moderation_states[$schedule->start_state] : '',
          'end_state' => isset($moderation_states[$schedule->end_state]) ? $moderation_states[$schedule->end_state] : '',
        ];
      }
    }

    $form['workbench_scheduler_sid'] = [
      '#type' => 'tableselect',
      '#title' => t('Select Schedule'),
      '#description' => t('Select the schedule to use for this node.'),
      '#options' => $schedule_options,
      '#multiple' => FALSE,
      '#header' => [
        'label' => t('Name'),
        'start_state' => t('Start State'),
        'end_state' => t('End State'),
      ],
    ];
    // Dates.
    $form['workbench_scheduler_start_date'] = [
      '#type' => 'date_popup',
      '#date_format' => 'Y-m-d H:i',
      '#title' => t('Start date'),
      '#description' => t('Select the date to switch this node to the scheduled "start state".'),
    ];
    $form['workbench_scheduler_end_date'] = [
      '#type' => 'date_popup',
      '#date_format' => 'Y-m-d H:i',
      '#title' => t('End date'),
      '#description' => t('Select the date to switch this node to the scheduled "end state".'),
    ];

    // Node only allow most recent revision schedule?
    // @FIXME
    // // @FIXME
    // // The correct configuration object could not be determined. You'll need to
    // // rewrite this call manually.
    // $type_settings = variable_get('workbench_scheduler_' . $node->type, array());

    // Check if only process latest revision.
    if (in_array('workbench_scheduler_limit_current_revision', $type_settings)) {
      // Getting the latest revision.
      $rev_list = node_revision_list($node);
      $latest_vid = max(array_keys($rev_list));

      // Is this vid not the most recent?
      if ($latest_vid != $vid) {
        // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $form['note'] = array(
//         '#type' => 'item',
//         '#title' => t('Note'),
//         '#markup' => t('THIS SCHEDULE WILL NOT BE RUN. This node type is set to run the schedule for only the most !link', array(
//           '!link' => l(t('recent revision'), 'node/' . $node->nid . '/manage_schedules/' . $latest_vid . '/edit'),
//         )),
//       );

      }
    }

    // Add existing node schedule as defaults.
    $node_schedule = workbench_scheduler_load_node_schedule($node->nid, $vid);
    if ($node_schedule) {
      $form['workbench_scheduler_sid']['#default_value'] = $node_schedule->sid;
      if ($node_schedule->start_date) {
        $start_date = format_date($node_schedule->start_date, 'custom', 'Y-m-d H:i:s');
        $form['workbench_scheduler_start_date']['#default_value'] = $start_date;
      }
      if ($node_schedule->end_date) {
        $end_date = format_date($node_schedule->end_date, 'custom', 'Y-m-d H:i:s');
        $form['workbench_scheduler_end_date']['#default_value'] = $end_date;
      }
    }
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Update'),
      '#validate' => [
        'workbench_scheduler_node_form_validate'
        ],
    ];
    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $schedule_data = [
      'sid' => $form_state->getValue(['workbench_scheduler_sid']),
      'start_date' => strtotime($form_state->getValue(['workbench_scheduler_start_date'])),
      'end_date' => strtotime($form_state->getValue(['workbench_scheduler_end_date'])),
    ];
    $nid = $form_state->getValue(['nid']);
    $vid = $form_state->getValue(['vid']);
    if (workbench_scheduler_save_node_schedule($nid, $vid, $schedule_data)) {
      drupal_set_message(t('Revision schedule updated'), 'status', FALSE);
      drupal_goto('node/' . $nid . '/manage_schedules/');
    }
    else {
      drupal_set_message(t('Error saving workbench schedule for node'), 'error', FALSE);
    }
  }

}
