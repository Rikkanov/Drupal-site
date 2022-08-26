<?php

declare(strict_types = 1);

namespace Drupal\Tests\preview_link\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Base class for preview link testing.
 */
abstract class PreviewLinkBase extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'preview_link',
    'dynamic_entity_reference',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('preview_link');
  }

}
