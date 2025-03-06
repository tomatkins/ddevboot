<?php

namespace Drupal\nys_unav\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements class for nys unav module.
 *
 * NysUNavForm class file used to define configuration form for Nys Unav module.
 */
class NysUNavForm extends ConfigFormBase {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs NysUNavForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nys_unav_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    $form['nys_unav_unav_fieldset'] = $this->nysUnavUnavFieldset();
    $form['nys_unav_unav_fieldset']['nys_unav_search_settings'] = $this->nysUnavSearchSettings();
    $form['nys_unav_language_access_fieldset'] = $this->nysUnavLanguageAccessFieldset();
    $form['nys_unav_language_access_fieldset']['nys_unav_language_access_header'] = $this->nysUnavLanguageAccessHeader();
    $form['nys_unav_language_access_fieldset']['nys_unav_language_access_footer'] = $this->nysUnavLanguageAccessFooter();
    $form['nys_unav_language_access_fieldset']['nys_unav_language_access_stripwww'] = $this->nysUnavLanguageAccessStripwww();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('nys_unav.settings');
    $config->set('nys_unav.nys_unav_search_settings', $form_state->getValue('nys_unav_search_settings'));
    $config->set('nys_unav.nys_unav_language_access_header', $form_state->getValue('nys_unav_language_access_header'));
    $config->set('nys_unav.nys_unav_language_access_footer', $form_state->getValue('nys_unav_language_access_footer'));
    $config->set('nys_unav.nys_unav_language_access_stripwww', $form_state->getValue('nys_unav_language_access_stripwww'));
    $config->save();

    // Invalidate render cache for search settings hide/show
    \Drupal::service('cache.render')->invalidateAll();
    // Clear Drupal caches.
    \Drupal::cache()->invalidateAll();
    // Regenerate router cache and other router changes because the invalidateAll command doesn't do that.
    \Drupal::service("router.builder")->rebuild();
    
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'nys_unav.settings',
    ];
  }

  /**
   * NYS Universal Navigation language unav fieldset field.
   *
   * @return array
   *   Form API element for field.
   */
  public function nysUnavUnavFieldset() {
    return [
      '#type' => 'fieldset',
      '#title' => $this->t('NYS Universal Navigation Options'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
  }

  /**
   * NYS Universal Navigation Search Settings.
   *
   * @return array
   *   Form API element for field.
   */
  public function nysUnavSearchSettings() {
    $config = $this->configFactory->getEditable('nys_unav.settings');
    return [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Search'),
      '#default_value' => $config->get('nys_unav.nys_unav_search_settings'),
      '#multiple' => FALSE,
      '#description' => $this->t('Select if the website should contain the universal navigation searchbar.'),
    ];
  }

  /**
   * NYS Universal Navigation language access fieldset field.
   *
   * @return array
   *   Form API element for field.
   */
  public function nysUnavLanguageAccessFieldset() {
    return [
      '#type' => 'fieldset',
      '#title' => $this->t('NYS Language Access Options'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
  }

  /**
   * NYS Universal Navigation language access field.
   *
   * @return array
   *   Form API element for field.
   */
  public function nysUnavLanguageAccessHeader() {
    $config = $this->configFactory->getEditable('nys_unav.settings');
    return [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable the Language Access Header'),
      '#default_value' => $config->get('nys_unav.nys_unav_language_access_header'),
      '#multiple' => FALSE,
      '#description' => $this->t('Select if the language access header is to be automatically inserted into the page.'),
    ];
  }

  /**
   * NYS Universal Navigation language access field.
   *
   * @return array
   *   Form API element for field.
   */
  public function nysUnavLanguageAccessFooter() {
    $config = $this->configFactory->getEditable('nys_unav.settings');
    return [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable the Language Access Footer'),
      '#default_value' => $config->get('nys_unav.nys_unav_language_access_footer'),
      '#multiple' => FALSE,
      '#description' => $this->t('Select if the language access footer is to be automatically inserted into the page.'),
    ];
  }

  /**
   * NYS Universal Navigation language access strip www toggle.
   *
   * @return array
   *   Form API element for field.
   */
  public function nysUnavLanguageAccessStripwww() {
    $config = $this->configFactory->getEditable('nys_unav.settings');
    return [
      '#type' => 'checkbox',
      '#title' => $this->t('Strip www from the language access links'),
      '#default_value' => $config->get('nys_unav.nys_unav_language_access_stripwww'),
      '#multiple' => FALSE,
      '#description' => $this->t('Turn on to strip the www from the language access links.'),
    ];
  }

}
