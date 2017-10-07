<?php
namespace Drupal\workbench_scheduler;

/**
 * Create Schedule.
 */
class WorkbenchSchedulerScheduleTestCase extends WorkbenchSchedulerTestCase {

  public static function getInfo() {
    return array(
      'name' => 'Create Workbench Schedule',
      'description' => 'Create a new workbench schedule',
      'group' => 'Workbench Scheduler',
    );
  }

  function setUp($modules = array()) {
    parent::setUp($modules);
    $this->drupalLogin($this->admin_user);
  }

  function testScheduleCreate() {

    $states = workbench_scheduler_state_labels();
    $indexes = array_rand($states, 2);

    // Create schedule.
    $edit = array();
    $edit['label'] = $this->randomName(8);
    $edit['name'] = strtolower($this->randomName(8));
    $edit['start_state'] = $indexes[0];
    $edit['end_state'] = $indexes[1];
    // $edit['#schedule'] = new stdClass;
    $edit["types[{$this->content_type}]"] = $this->content_type;

    $this->drupalPost('admin/config/workbench/scheduler/schedules/add', $edit, t('Save'));
    // Checking database integrity to see if it was created successfully.
    $query = db_select('workbench_scheduler_schedules', 'wss')
      ->fields('wss')
      ->condition('wss.name', $edit['name'], '=')
      ->condition('wss.label', $edit['label'], '=')
      ->condition('wss.start_state', $edit['start_state'], '=')
      ->condition('wss.end_state', $edit['end_state'], '=')
      ->range(0, 1);

    // Checking table relationships.
    $query->join('workbench_scheduler_types', 'wst', "wss.name = wst.name AND wst.type ='{$this->content_type}'");
    $query->execute();

    $this->assertTrue($query, 'Workbench Schedule saved');

  }

}
