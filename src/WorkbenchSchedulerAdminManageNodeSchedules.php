<?php
namespace Drupal\workbench_scheduler;

class WorkbenchSchedulerAdminManageNodeSchedules extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'workbench_scheduler_admin_manage_node_schedules';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state, $node = NULL) {
    $revisions = node_revision_list($node);

    $schedules = [
      'schedule_active' => [],
      'schedule_inactive' => [],
    ];

    // Check to see if this node type supports more than one.
    // @FIXME
    // // @FIXME
    // // The correct configuration object could not be determined. You'll need to
    // // rewrite this call manually.
    // $type_settings = variable_get('workbench_scheduler_' . $node->type, array());

    $limit_current_revision = FALSE;
    if (in_array('workbench_scheduler_limit_current_revision', $type_settings)) {
      $limit_current_revision = TRUE;
    }
    $node_schedules = [];
    foreach ($revisions as $vid => $revision) {
      // Load up all the schedules for all revisions of this node.
      $node_schedules[$vid] = workbench_scheduler_load_node_schedule($node->nid, $vid);
    }
    $node_schedules = array_filter($node_schedules);
    // Load labels of moderation states.
    $moderation_states = workbench_scheduler_state_labels();
    // If there are schedules applied to this node.
    if ($node_schedules) {

      foreach ($node_schedules as $vid => $node_schedule) {
        $start_date = t('Not set');
        $end_date = t('Not set');
        if (isset($node_schedule->start_date) && $node_schedule->start_date != 0) {
          $start_date = format_date($node_schedule->start_date, 'custom', 'Y-m-d H:i:s');
        }
        if (isset($node_schedule->end_date) && $node_schedule->end_date != 0) {
          $end_date = format_date($node_schedule->end_date, 'custom', 'Y-m-d H:i:s');
        }

        // Build each row of the tableselect.
        // @FIXME
        // l() expects a Url object, created from a route name or external URI.
        // $manage_schedules = array(
        //         'title' => array('data' => array('#title' => $vid)),
        //         'vid' => l($vid, 'node/' . $node->nid . '/revisions/' . $vid . '/view'),
        //         'label' => $node_schedule->label,
        //         'start_state' => isset($moderation_states[$node_schedule->start_state]) ? $moderation_states[$node_schedule->start_state] : $node_schedule->start_state,
        //         'start_date' => $start_date,
        //         'end_state' => isset($moderation_states[$node_schedule->end_state]) ? $moderation_states[$node_schedule->end_state] : $node_schedule->end_state,
        //         'end_date' => $end_date,
        //         'completed' => ($node_schedule->completed) ? t('Yes') : t('No'),
        //         'vid_edit' => l(t('Edit'), 'node/' . $node->nid . '/manage_schedules/' . $vid . '/edit'),
        //       );


        // Limit Current revision - only run schedule on highest vid.
        if ($limit_current_revision) {
          // Active schedule?
          if ($node->workbench_moderation['current']->vid == $vid) {
            $schedules['schedule_active'][$vid] = $manage_schedules;
          }
          else {
            $schedules['schedule_inactive'][$vid] = $manage_schedules;

          }
        }
          // Run on all schedules that haven't completed yet (default behavior).
        else {
          // Active schedule?
          if (!$node_schedule->completed) {
            $schedules['schedule_active'][$vid] = $manage_schedules;
          }
          else {
            $schedules['schedule_inactive'][$vid] = $manage_schedules;

          }

        }
      }

      // Render Schedules.
      foreach ($schedules as $status => $schedule) {
        if (!empty($schedule)) {
          // Build the the tableselect form, with a hidden field for the nid.
          $form['nid'] = [
            '#type' => 'hidden',
            '#value' => $node->nid,
          ];

          // Fieldsets.
          if ($status == 'schedule_active') {
            $fieldset = [
              '#type' => 'fieldset',
              '#title' => t('Active Schedule'),
              '#description' => t('Only active schedules are run.'),
            ];

          }
          else {
            $fieldset = [
              '#type' => 'fieldset',
              '#title' => t('Inactive Schedules'),
            ];
          }

          // Output the form.
          $form[$status . '_fieldset'] = $fieldset;
          $form[$status . '_fieldset'][$status] = [
            '#type' => 'tableselect',
            '#title' => t('Schedules applied to this node'),
            '#options' => $schedule,
            '#multiple' => TRUE,
            '#header' => [
              'vid' => t('Revision ID'),
              'label' => t('Schedule Name'),
              'start_state' => t('Start State'),
              'start_date' => t('Start Date'),
              'end_state' => t('End State'),
              'end_date' => t('End Date'),
              'completed' => t('Completed'),
              'vid_edit' => t('Edit'),
            ],
          ];
        }
      }
      // The form submit actions.
      $form['actions'] = [
        '#type' => 'actions'
        ];
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => t('Delete selected'),
      ];
      return $form;
    }
    else {
      // When no schedules applied to this node, display a status message.
      drupal_set_message(t('No schedules applied to this node'), 'status', FALSE);
    }
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $nid = $form_state->getValue(['nid']);
    $fieldsets = ['schedule_active', 'schedule_inactive'];
    foreach ($fieldsets as $key) {
      foreach ($form_state->getValue([$key]) as $vid => $checked) {
        if ($vid == $checked) {
          workbench_scheduler_delete_node_schedule($nid, $vid);
        }
      }
    }
  }

}
