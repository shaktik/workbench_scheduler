<?php
namespace Drupal\workbench_scheduler;

/**
 * @file
 * Tests for workbench_scheduler.module.
 */

class WorkbenchSchedulerTestCase extends DrupalWebTestCase {

  protected $admin_user;
  protected $content_type;

  function setUp($modules = array()) {
    $modules = array_merge($modules, array('workbench_scheduler', 'workbench_moderation'));
    parent::setUp($modules);

    // Create a new content type and enable moderation on it.
    $type = $this->drupalCreateContentType();
    $this->content_type = $type->name;
    // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// variable_set('node_options_' . $this->content_type, array('revision', 'moderation'));


    $this->admin_user = $this->drupalCreateUser(array(
      'bypass node access',
      'administer nodes',
      'view revisions',
      'view all unpublished content',
      'view moderation history',
      'view moderation messages',
      'bypass workbench moderation',
      "create {$this->content_type} content",
      'administer workbench schedules',
      'administer content types',
      'set workbench schedule',
      'set any workbench schedule',
    ));

  }

}
