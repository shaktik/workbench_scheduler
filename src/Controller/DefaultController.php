<?php /**
 * @file
 * Contains \Drupal\workbench_scheduler\Controller\DefaultController.
 */

namespace Drupal\workbench_scheduler\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Default controller for the workbench_scheduler module.
 */
class DefaultController extends ControllerBase {

  public function workbench_scheduler_admin_page() {
    // Build a table to show the different schedules.
    $headers = [
      [
        'data' => t('Name')
        ],
      ['data' => t('Machine Name')],
      ['data' => t('Start State')],
      [
        'data' => t('End State')
        ],
      ['data' => t('Content Types')],
      [
        'data' => t('Operations'),
        'colspan' => 2,
      ],
    ];

    $rows = [];
    // Retrieve any schedules that exist.
    if ($schedules = workbench_scheduler_load_schedules()) {
      // Get list of the different moderation states.
      $states = workbench_scheduler_state_labels();
      // Get list of the different content types.
      $node_types = node_type_get_types();
      // Loop through the schedules to add them to the table.
      foreach ($schedules as $name => $schedule) {
        // Format the content types the schedule is available for,
      // Based on number.
        $type_count = count($schedule->types);
        // More then one type?
        if ($type_count > 1) {
          $items = [];
          // Loop through each type.
          foreach ($schedule->types as $type) {
            // Display the human readable name.
            $items[] = $node_types[$type]->name;
          }

          // Format into an item list.
          // @FIXME
          // theme() has been renamed to _theme() and should NEVER be called directly.
          // Calling _theme() directly can alter the expected output and potentially
          // introduce security issues (see https://www.drupal.org/node/2195739). You
          // should use renderable arrays instead.
          // 
          // 
          // @see https://www.drupal.org/node/2195739
          // $types = theme('item_list', array('items' => $items, 'type' => 'ul'));

        }
          // Have only a single type?
        elseif (count($schedule->types) == 1) {
          // Display the human readable name.
          $types = $node_types[array_pop($schedule->types)]->name;
        }
          // No types found (either deleted or not imported?).
        else {
          // Display null.
          $types = 'NULL';
        }
        // Format the row.
        // @FIXME
        // l() expects a Url object, created from a route name or external URI.
        // $row = array(
        //         $schedule->label,
        //         $name,
        //         (!empty($states[$schedule->start_state]) ? $states[$schedule->start_state] : ''),
        //         (!empty($states[$schedule->end_state]) ? $states[$schedule->end_state] : ''),
        //         $types,
        //         // Link to edit the schedule.
        //         l(t('Edit'), 'admin/config/workbench/scheduler/schedules/' . $name . '/edit'),
        //         // Link to delete the scheduler.
        //         l(t('Delete'), 'admin/config/workbench/scheduler/schedules/' . $name . '/delete'),
        //       );

        // Add to the rows array.
        $rows[] = $row;
      }
    }
      // No schedules found.
    else {
      // Display message in first row.
      $rows[] = [
        [
          'data' => t('No Schedules Found'),
          'colspan' => 7,
        ]
        ];
    }
    // Add a row for a link to add a new schedule.
    // @FIXME
    // l() expects a Url object, created from a route name or external URI.
    // $rows[] = array(
    //     array(
    //       'data'    => l(t('Add Schedule'), 'admin/config/workbench/scheduler/schedules/add'),
    //       'colspan' => 7,
    //     ),
    //   );

    // Returned the themed table.
    // @FIXME
    // theme() has been renamed to _theme() and should NEVER be called directly.
    // Calling _theme() directly can alter the expected output and potentially
    // introduce security issues (see https://www.drupal.org/node/2195739). You
    // should use renderable arrays instead.
    // 
    // 
    // @see https://www.drupal.org/node/2195739
    // return theme('table', array('header' => $headers, 'rows' => $rows));


  }

}
