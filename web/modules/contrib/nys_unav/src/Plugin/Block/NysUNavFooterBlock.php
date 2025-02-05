<?php

namespace Drupal\nys_unav\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a NYS uNav footer block.
 *
 * @Block(
 *     id = "nys_unav_footer_block",
 *     admin_label = @Translation("NYS uNav Footer"),
 *     category = @Translation("NYS")
 * )
 */
class NysUNavFooterBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return $account->hasPermission('administer nys unav');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Return equivalent to theme function.
    $block = ['#theme' => 'nys_unav_footer'];
    return $block;
  }

}
