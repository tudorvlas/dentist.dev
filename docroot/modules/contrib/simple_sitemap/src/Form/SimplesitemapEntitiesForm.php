<?php

namespace Drupal\simple_sitemap\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * SimplesitemapSettingsFrom
 */
class SimplesitemapEntitiesForm extends SimplesitemapFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'simple_sitemap_entities_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $generator = \Drupal::service('simple_sitemap.generator');

    $form['simple_sitemap_entities']['#prefix'] = $this->getDonationLink();

    $form['simple_sitemap_entities']['entities'] = [
      '#title' => $this->t('Sitemap entities'),
      '#type' => 'fieldset',
      '#markup' => '<p>' . $this->t("Simple XML sitemap settings will be added only to entity forms of entity types enabled here. For all entity types featuring bundles (e.g. <em>node</em>) sitemap settings have to be set on their bundle pages (e.g. <em>page</em>).") . '</p>',
    ];

    $form['#attached']['library'][] = 'simple_sitemap/sitemapEntities';
    $form['#attached']['drupalSettings']['simple_sitemap'] = ['all_entities' => [], 'atomic_entities' => []];

    $entity_type_labels = [];
    foreach ($generator->getSitemapEntityTypes() as $entity_type_id => $entity_type) {
      $entity_type_labels[$entity_type_id] = $entity_type->getLabel() ? : $entity_type_id;
    }
    asort($entity_type_labels);

    $f = \Drupal::service('simple_sitemap.form')->processForm($form_state);

    foreach ($entity_type_labels as $entity_type_id => $entity_type_label) {
      $form['simple_sitemap_entities']['entities'][$entity_type_id] = [
      '#type' => 'details',
      '#title' => $entity_type_label,
      '#open' => $generator->entityTypeIsEnabled($entity_type_id),
    ];
      $form['simple_sitemap_entities']['entities'][$entity_type_id][$entity_type_id . '_enabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable @entity_type_label <em>(@entity_type_id)</em> support', ['@entity_type_label' => strtolower($entity_type_label), '@entity_type_id' => $entity_type_id]),
        '#description' => $this->t('Sitemap settings for this entity type can be set on its bundle pages and overridden on its entity pages.'),
        '#default_value' => $generator->entityTypeIsEnabled($entity_type_id),
      ];
      $form['#attached']['drupalSettings']['simple_sitemap']['all_entities'][] = str_replace('_', '-', $entity_type_id);
      if ($generator->entityTypeIsAtomic($entity_type_id)) {
        $form['simple_sitemap_entities']['entities'][$entity_type_id][$entity_type_id . '_enabled']['#description'] = $this->t('Sitemap settings for this entity type can be set below and overridden on its entity pages.');
        $f->setEntityCategory('bundle')
          ->setEntityTypeId($entity_type_id)
          ->setBundleName($entity_type_id)
          ->displayEntitySettings($form['simple_sitemap_entities']['entities'][$entity_type_id][$entity_type_id . '_settings'], TRUE);
        $form['#attached']['drupalSettings']['simple_sitemap']['atomic_entities'][] = str_replace('_', '-', $entity_type_id);
      }
    }
    $f->displayRegenerateNow($form['simple_sitemap_entities']['entities']);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $generator = \Drupal::service('simple_sitemap.generator');
    $values = $form_state->getValues();
    foreach($values as $field_name => $value) {
      if (substr($field_name, -strlen('_enabled')) == '_enabled') {
        $entity_type_id = substr($field_name, 0, -8);
        if ($value) {
          $generator->enableEntityType($entity_type_id);
          if ($generator->entityTypeIsAtomic($entity_type_id)) {
            $generator->setBundleSettings($entity_type_id, $entity_type_id, [
                'index' => TRUE,
                'priority' => $values[$entity_type_id . '_simple_sitemap_priority']
              ]);
          }
        }
        else
          $generator->disableEntityType($entity_type_id);
      }
    }
    parent::submitForm($form, $form_state);

    // Regenerate sitemaps according to user setting.
    if ($form_state->getValue('simple_sitemap_regenerate_now')) {
      $generator->generateSitemap();
    }
  }
}
