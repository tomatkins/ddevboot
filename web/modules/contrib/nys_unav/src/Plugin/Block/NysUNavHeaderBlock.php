<?php

namespace Drupal\nys_unav\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a NYS uNav header block.
 *
 * @Block(
 *     id = "nys_unav_header_block",
 *     admin_label = @Translation("NYS uNav Header"),
 *     category = @Translation("NYS")
 * )
 */
class NysUNavHeaderBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Return equivalent to theme function.
    $block = ['#theme' => 'nys_unav_header'];
    return $block;
  }

}
