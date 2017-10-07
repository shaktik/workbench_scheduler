<?php
namespace Drupal\workbench_scheduler;

class WorkbenchSchedulerAdminDeleteSchedule extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'workbench_scheduler_admin_delete_schedule';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state, $name = NULL) {
    // Attempt to load the schedule.
    if ($schedule = workbench_scheduler_load_schedules($name)) {
      // Store to form.
      $form['#schedule'] = $schedule;
      // Build confirmation form.
      return confirm_form($form, t('Are you sure you want to delete the schedule "@label"?', [
        '@label' => $schedule->label
        ]), 'admin/config/workbench/scheduler/schedules', t('This action cannot be undone'));
    }
      // Unable to load form, not sure what trying to delete.
    else {
      drupal_set_message(t('Invalid Schedule machine name'), 'error', FALSE);
      // Send back to schedules table.
      drupal_goto('admin/config/workbench/scheduler/schedules');
    }
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // Fetch the schedule to delete.
    $schedule = $form['#schedule'];
    // Attempt to delete.
    if (workbench_scheduler_delete_schedules($schedule->name)) {
      drupal_set_message(t('Schedule and associated data deleted'), 'status', FALSE);
      // Go back to schedules page.
      $form_state->set(['redirect'], 'admin/config/workbench/scheduler/schedules');
    }
      // Unable to delete, show error message.
    else {
      drupal_set_message(t('Error deleting schedule'), 'error', FALSE);
    }
  }

}
