<?php

namespace Drupal\entity_usage;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\entity_usage\Events\EntityUsageEvent;
use Drupal\entity_usage\Events\Events;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Defines the entity usage base class.
 */
class EntityUsage implements EntityUsageInterface {

  /**
   * Construct the EntityUsage service.
   */
  final public function __construct(
    private Connection $connection,
    private EventDispatcherInterface $eventDispatcher,
    private ConfigFactoryInterface $configFactory,
    private ModuleHandlerInterface $moduleHandler,
    private string $tableName,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function registerUsage($target_id, $target_type, $source_id, $source_type, $source_langcode, $source_vid, $method, $field_name, $count = 1) {
    // Check if target entity type is enabled, all entity types are enabled by
    // default.
    $enabled_target_entity_types = $this
      ->configFactory
      ->get('entity_usage.settings')
      ->get('track_enabled_target_entity_types');
    if (is_array($enabled_target_entity_types) && !in_array($target_type, $enabled_target_entity_types, TRUE)) {
      return;
    }

    // Allow modules to block this operation.
    $context = [
      'target_id' => $target_id,
      'target_type' => $target_type,
      'source_id' => $source_id,
      'source_type' => $source_type,
      'source_langcode' => $source_langcode,
      'source_vid' => $source_vid,
      'method' => $method,
      'field_name' => $field_name,
      'count' => $count,
    ];
    $abort = $this->moduleHandler->invokeAll('entity_usage_block_tracking', $context);
    // If at least one module wants to block the tracking, bail out.
    if (in_array(TRUE, $abort, TRUE)) {
      return;
    }

    // Entities can have string IDs. We support that by using different columns
    // on each case.
    $target_id_column = $this->isInt($target_id) ? 'target_id' : 'target_id_string';
    $source_id_column = $this->isInt($source_id) ? 'source_id' : 'source_id_string';

    // If $count is 0, we want to delete the record.
    if ($count <= 0) {
      $this->connection->delete($this->tableName)
        ->condition($target_id_column, $target_id)
        ->condition('target_type', $target_type)
        ->condition($source_id_column, $source_id)
        ->condition('source_type', $source_type)
        ->condition('source_langcode', $source_langcode)
        ->condition('source_vid', $source_vid)
        ->condition('method', $method)
        ->condition('field_name', $field_name)
        ->execute();
    }
    else {
      $this->connection->merge($this->tableName)
        ->keys([
          $target_id_column => $target_id,
          'target_type' => $target_type,
          $source_id_column => $source_id,
          'source_type' => $source_type,
          'source_langcode' => $source_langcode,
          'source_vid' => $source_vid ?: 0,
          'method' => $method,
          'field_name' => $field_name,
        ])
        ->fields(['count' => $count])
        ->execute();
    }

    $event = new EntityUsageEvent($target_id, $target_type, $source_id, $source_type, $source_langcode, $source_vid, $method, $field_name, $count);
    $this->eventDispatcher->dispatch($event, Events::USAGE_REGISTER);
  }

  /**
   * {@inheritdoc}
   */
  public function bulkDeleteTargets($target_type) {
    $query = $this->connection->delete($this->tableName)
      ->condition('target_type', $target_type);
    $query->execute();

    $event = new EntityUsageEvent(NULL, $target_type, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    $this->eventDispatcher->dispatch($event, Events::BULK_DELETE_DESTINATIONS);
  }

  /**
   * {@inheritdoc}
   */
  public function bulkDeleteSources($source_type) {
    $query = $this->connection->delete($this->tableName)
      ->condition('source_type', $source_type);
    $query->execute();

    $event = new EntityUsageEvent(NULL, NULL, NULL, $source_type, NULL, NULL, NULL, NULL, NULL);
    $this->eventDispatcher->dispatch($event, Events::BULK_DELETE_SOURCES);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteByField($source_type, $field_name) {
    $query = $this->connection->delete($this->tableName)
      ->condition('source_type', $source_type)
      ->condition('field_name', $field_name);
    $query->execute();

    $event = new EntityUsageEvent(NULL, NULL, NULL, $source_type, NULL, NULL, NULL, $field_name, NULL);
    $this->eventDispatcher->dispatch($event, Events::DELETE_BY_FIELD);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteBySourceEntity($source_id, $source_type, $source_langcode = NULL, $source_vid = NULL) {
    // Entities can have string IDs. We support that by using different columns
    // on each case.
    $source_id_column = $this->isInt($source_id) ? 'source_id' : 'source_id_string';

    $query = $this->connection->delete($this->tableName)
      ->condition($source_id_column, $source_id)
      ->condition('source_type', $source_type);
    if ($source_langcode) {
      $query->condition('source_langcode', $source_langcode);
    }
    if ($source_vid) {
      $query->condition('source_vid', $source_vid);
    }
    $query->execute();

    $event = new EntityUsageEvent(NULL, NULL, $source_id, $source_type, $source_langcode, $source_vid, NULL, NULL, NULL);
    $this->eventDispatcher->dispatch($event, Events::DELETE_BY_SOURCE_ENTITY);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteByTargetEntity($target_id, $target_type) {
    // Entities can have string IDs. We support that by using different columns
    // on each case.
    $target_id_column = $this->isInt($target_id) ? 'target_id' : 'target_id_string';

    $query = $this->connection->delete($this->tableName)
      ->condition($target_id_column, $target_id)
      ->condition('target_type', $target_type);
    $query->execute();

    $event = new EntityUsageEvent($target_id, $target_type, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    $this->eventDispatcher->dispatch($event, Events::DELETE_BY_TARGET_ENTITY);
  }

  /**
   * {@inheritdoc}
   */
  public function listSources(EntityInterface $target_entity, $nest_results = TRUE, int $limit = 0) {
    // Entities can have string IDs. We support that by using different columns
    // on each case.
    $target_id_column = $this->isInt($target_entity->id()) ? 'target_id' : 'target_id_string';
    $query = $this->connection->select($this->tableName, 'e')
      ->fields('e', [
        'source_id',
        'source_id_string',
        'source_type',
        'source_langcode',
        'source_vid',
        'method',
        'field_name',
        'count',
      ])
      ->condition($target_id_column, $target_entity->id())
      ->condition('target_type', $target_entity->getEntityTypeId())
      ->condition('count', 0, '>')
      ->orderBy('source_type')
      ->orderBy('source_id', 'DESC')
      ->orderBy('source_vid', 'DESC')
      ->orderBy('source_langcode');

    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $result = $query->execute();

    $references = [];
    foreach ($result as $usage) {
      $source_id_value = !empty($usage->source_id) ? (string) $usage->source_id : (string) $usage->source_id_string;
      if ($nest_results) {
        $references[$usage->source_type][$source_id_value][] = [
          'source_langcode' => $usage->source_langcode,
          'source_vid' => $usage->source_vid,
          'method' => $usage->method,
          'field_name' => $usage->field_name,
          'count' => $usage->count,
        ];
      }
      else {
        $references[] = [
          'source_type' => $usage->source_type,
          'source_id' => $source_id_value,
          'source_langcode' => $usage->source_langcode,
          'source_vid' => $usage->source_vid,
          'method' => $usage->method,
          'field_name' => $usage->field_name,
          'count' => $usage->count,
        ];
      }
    }

    return $references;
  }

  /**
   * {@inheritdoc}
   */
  public function listTargets(EntityInterface $source_entity, $vid = NULL) {
    // Entities can have string IDs. We support that by using different columns
    // on each case.
    $source_id_column = $this->isInt($source_entity->id()) ? 'source_id' : 'source_id_string';
    $query = $this->connection->select($this->tableName, 'e')
      ->fields('e', [
        'target_id',
        'target_id_string',
        'target_type',
        'method',
        'field_name',
        'count',
      ])
      ->condition($source_id_column, $source_entity->id())
      ->condition('source_type', $source_entity->getEntityTypeId())
      ->condition('count', 0, '>')
      ->orderBy('target_id', 'DESC');

    if ($vid) {
      $query->condition('source_vid', $vid);
    }

    $result = $query->execute();

    $references = [];
    foreach ($result as $usage) {
      $target_id_value = !empty($usage->target_id) ? $usage->target_id : $usage->target_id_string;
      $references[$usage->target_type][(string) $target_id_value][] = [
        'method' => $usage->method,
        'field_name' => $usage->field_name,
        'count' => $usage->count,
      ];
    }

    return $references;
  }

  /**
   * Check if a value is an integer, or an integer string.
   *
   * Core doesn't support big integers (bigint) for entity reference fields.
   * Therefore we consider integers with more than 10 digits (big integer) to be
   * strings.
   *
   * @param int|string $value
   *   The value to check.
   *
   * @return bool
   *   TRUE if the value is a numeric integer or a string containing an integer,
   *   FALSE otherwise.
   *
   * @todo Fix bigint support once fixed in core. More info on #2680571 and
   *   #2989033.
   */
  protected function isInt($value) {
    return ((string) (int) $value === (string) $value) && strlen($value) < 11;
  }

  /**
   * {@inheritdoc}
   */
  public function listUsage(EntityInterface $entity, $include_method = FALSE) {
    $result = $this->listSources($entity);
    $references = [];
    foreach ($result as $source_entity_type => $entity_record) {
      foreach ($entity_record as $entity_id => $records) {
        foreach ($records as $record) {
          if ($include_method) {
            if (empty($references[$record['method']][$source_entity_type][$entity_id])) {
              // This is the first of this entity type/id, just store the count.
              $references[$record['method']][$source_entity_type][$entity_id] = $record['count'];
            }
            else {
              // Sum all counts for different revisions or translations.
              $references[$record['method']][$source_entity_type][$entity_id] += $record['count'];
            }
          }
          else {
            if (empty($references[$source_entity_type][$entity_id])) {
              // This is the first of this entity type/id, just store the count.
              $references[$source_entity_type][$entity_id] = $record['count'];
            }
            else {
              // Sum all counts for different revisions or translations.
              $references[$source_entity_type][$entity_id] += $record['count'];
            }
          }
        }
      }
    }
    return $references;
  }

  /**
   * {@inheritdoc}
   */
  public function listReferencedEntities(EntityInterface $entity) {
    $result = $this->listTargets($entity);
    $references = [];
    foreach ($result as $target_entity_type => $entity_record) {
      foreach ($entity_record as $entity_id => $records) {
        foreach ($records as $record) {
          if (empty($references[$target_entity_type][$entity_id])) {
            // This is the first of this entity type/id, just store the count.
            $references[$target_entity_type][$entity_id] = $record['count'];
          }
          else {
            // Sum all counts for different revisions or translations.
            $references[$target_entity_type][$entity_id] += $record['count'];
          }
        }
      }
    }
    return $references;
  }

}
