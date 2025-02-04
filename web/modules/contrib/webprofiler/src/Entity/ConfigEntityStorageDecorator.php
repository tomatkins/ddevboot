<?php

declare(strict_types=1);

namespace Drupal\webprofiler\Entity;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Config\Entity\ImportableEntityStorageInterface;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for a decorator of a config entity storage class.
 */
class ConfigEntityStorageDecorator extends EntityDecorator implements ConfigEntityStorageInterface, ImportableEntityStorageInterface, EntityHandlerInterface {

  /**
   * ConfigEntityStorageDecorator constructor.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $config_entity_storage
   *   The config entity storage to decorate.
   */
  final public function __construct(
    ConfigEntityStorageInterface $config_entity_storage,
  ) {
    parent::__construct($config_entity_storage);

    $this->entities = [];
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache(?array $ids = NULL) {
    $this->getOriginalObject()->resetCache($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(?array $ids = NULL) {
    $entities = $this->getOriginalObject()->loadMultiple($ids);

    $this->entities = \array_merge($this->entities, $entities);

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    $entity = $this->getOriginalObject()->load($id);

    $this->entities[$id] = $entity;

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function loadUnchanged($id) {
    return $this->getOriginalObject()->loadUnchanged($id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadRevision($revision_id) {
    return $this->getOriginalObject()->loadRevision($revision_id);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRevision($revision_id) {
    $this->getOriginalObject()->deleteRevision($revision_id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadByProperties(array $values = []) {
    $entities = $this->getOriginalObject()->loadByProperties($values);

    $this->entities = \array_merge($this->entities, $entities);

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $values = []) {
    return $this->getOriginalObject()->create($values);
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $entities) {
    $this->getOriginalObject()->delete($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $entity) {
    return $this->getOriginalObject()->save($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function hasData() {
    return $this->getOriginalObject()->hasData();
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery($conjunction = 'AND') {
    return $this->getOriginalObject()->getQuery($conjunction);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeId() {
    return $this->getOriginalObject()->getEntityTypeId();
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityType() {
    return $this->getOriginalObject()->getEntityType();
  }

  /**
   * {@inheritdoc}
   *
   * phpcs:disable Drupal.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
   */
  public static function getIDFromConfigName($config_name, $config_prefix) {
    return \substr($config_name, \strlen($config_prefix . '.'));
  }

  /**
   * {@inheritdoc}
   */
  public function createFromStorageRecord(array $values) {
    return $this->getOriginalObject()->createFromStorageRecord($values);
  }

  /**
   * {@inheritdoc}
   */
  public function updateFromStorageRecord(ConfigEntityInterface $entity, array $values) {
    return $this->getOriginalObject()
      ->updateFromStorageRecord($entity, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function getAggregateQuery($conjunction = 'AND') {
    return $this->getOriginalObject()->getAggregateQuery($conjunction);
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrideFree($id) {
    return $this->getOriginalObject()->loadOverrideFree($id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultipleOverrideFree(?array $ids = NULL) {
    return $this->getOriginalObject()->loadMultipleOverrideFree($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function importCreate($name, Config $new_config, Config $old_config) {
    $this->getOriginalObject()->importCreate($name, $new_config, $old_config);
  }

  /**
   * {@inheritdoc}
   */
  public function importUpdate($name, Config $new_config, Config $old_config) {
    $this->getOriginalObject()->importUpdate($name, $new_config, $old_config);
  }

  /**
   * {@inheritdoc}
   */
  public function importDelete($name, Config $new_config, Config $old_config) {
    $this->getOriginalObject()->importDelete($name, $new_config, $old_config);
  }

  /**
   * {@inheritdoc}
   */
  public function importRename($old_name, Config $new_config, Config $old_config) {
    $this->getOriginalObject()->importRename($old_name, $new_config, $old_config);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    \assert($entity_type instanceof ConfigEntityStorageInterface);

    return new static(
      $entity_type,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function restore(EntityInterface $entity) {
    $this->getOriginalObject()->restore($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityClass(?string $bundle = NULL): string {
    return $this->getOriginalObject()->getEntityClass($bundle);
  }

}
