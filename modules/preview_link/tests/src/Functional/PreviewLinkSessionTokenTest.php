<?php

declare(strict_types = 1);

namespace Drupal\Tests\preview_link\Functional;

use Drupal\Core\Logger\LogMessageParser;
use Drupal\Core\Url;
use Drupal\entity_test\Entity\EntityTestMulRevPub;
use Drupal\entity_test\Entity\EntityTestRevPub;
use Drupal\preview_link\Entity\PreviewLink;
use Drupal\preview_link_test_time\TimeMachine;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\system\Functional\Cache\AssertPageCacheContextsAndTagsTrait;
use Drupal\user\RoleInterface;

/**
 * Tests tokens claimed against sessions.
 *
 * @group preview_link
 */
class PreviewLinkSessionTokenTest extends BrowserTestBase {

  use AssertPageCacheContextsAndTagsTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'dynamic_entity_reference',
    'preview_link',
    'entity_test',
    'preview_link_test',
    'preview_link_test_time',
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $timeMachine = \Drupal::service('datetime.time');
    assert($timeMachine instanceof TimeMachine);
    $currentTime = new \DateTime('14 May 2014 14:00:00');
    $timeMachine->setTime($currentTime);

    /** @var \Drupal\preview_link_test\StateLogger $logger */
    $logger = \Drupal::service('logger.preview_link_test');
    $logger->cleanLogs();
  }

  /**
   * Tests session token unlocks multiple entities.
   */
  public function testSessionToken(): void {
    $entity1 = EntityTestRevPub::create(['name' => 'test entity 1']);
    $entity1->save();
    $entity2 = EntityTestRevPub::create(['name' => 'test entity 2']);
    $entity2->save();

    // Navigating to these entities proves no access and primes caches.
    $this->drupalGet($entity1->toUrl());
    $this->assertNoCacheContext('session');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet($entity2->toUrl());
    $this->assertNoCacheContext('session');
    $this->assertSession()->statusCodeEquals(403);

    $previewLink = PreviewLink::create()
      ->setEntities([$entity1, $entity2]);
    $previewLink->save();

    $previewLinkUrl1 = Url::fromRoute('entity.entity_test_revpub.preview_link', [
      $entity1->getEntityTypeId() => $entity1->id(),
      'preview_token' => $previewLink->getToken(),
    ]);
    $this->drupalGet($previewLinkUrl1);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertCacheContext('session');

    // Navigating to canonical should redirect to preview link.
    $this->drupalGet($entity2->toUrl());
    $previewLinkUrl2 = Url::fromRoute('entity.entity_test_revpub.preview_link', [
      $entity2->getEntityTypeId() => $entity2->id(),
      'preview_token' => $previewLink->getToken(),
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->addressEquals($previewLinkUrl2);
    $this->assertCacheContext('session');
    $this->assertSession()->pageTextContains('You are viewing this page because a preview link granted you access. Click here to remove token.');

    // Now back to the canonical route for the original entity.
    $this->drupalGet($entity1->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertCacheContext('session');
    $this->assertSession()->addressEquals($previewLinkUrl1);
    $this->assertSession()->pageTextContains('You are viewing this page because a preview link granted you access. Click here to remove token.');

    // Each canonical page now inaccessible after removing session tokens.
    $this->drupalGet(Url::fromRoute('preview_link.session_tokens.remove'));
    $this->assertSession()->pageTextContains('Removed preview link tokens.');
    $this->drupalGet($entity1->toUrl());
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet($entity2->toUrl());
    $this->assertSession()->statusCodeEquals(403);

    /** @var \Drupal\preview_link_test\StateLogger $logger */
    $logger = \Drupal::service('logger.preview_link_test');
    $messages = array_map(function ($log): string {
      [1 => $message, 2 => $messagePlaceholders, 3 => $context] = $log;
      return empty($messagePlaceholders) ? $message : strtr($message, $messagePlaceholders);
    }, $logger->getLogs());
    $channels = array_map(function ($log): ?string {
      return $log[3]['channel'] ?? NULL;
    }, $logger->getLogs());

    $this->assertContains('preview_link', $channels);
    $this->assertContains('Redirecting to preview link of test entity 2', $messages);

    // The log sent to 'php' channel in ExceptionLoggingSubscriber::onError
    // must not be triggered.
    $this->assertNotContains('php', $channels);
  }

  /**
   * Tests trying to claim a token multiple times.
   *
   * Tests that trying to re-claim a preview token doesnt return a cached
   * response which doesnt end up claiming a token to the session.
   */
  public function testSessionTokenReclaimAttempt(): void {
    $entity = EntityTestRevPub::create();
    $entity->save();

    $previewLink = PreviewLink::create()->addEntity($entity);
    $previewLink->save();

    $previewLinkUrl = Url::fromRoute('entity.entity_test_revpub.preview_link', [
      $entity->getEntityTypeId() => $entity->id(),
      'preview_token' => $previewLink->getToken(),
    ]);
    $this->drupalGet($previewLinkUrl);
    $this->assertSession()->statusCodeEquals(200);

    // Should redirect to preview link.
    $this->drupalGet($entity->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->addressEquals($previewLinkUrl);

    // Remove session tokens.
    $this->drupalGet(Url::fromRoute('preview_link.session_tokens.remove'));
    $this->assertSession()->pageTextContains('Removed preview link tokens.');

    // Try to re-claim.
    // If this fails [with a 403] then something isnt allowing us to claim the
    // token to the session.
    $this->drupalGet($previewLinkUrl);
    $this->assertSession()->statusCodeEquals(200);

    // Should redirect to preview link again.
    $this->drupalGet($entity->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->addressEquals($previewLinkUrl);
  }

  /**
   * Tests destination/redirect for unclaiming.
   *
   * For when user has access to canonical route, without the token.
   */
  public function testSessionTokenUnclaimDestination(): void {
    user_role_change_permissions(RoleInterface::ANONYMOUS_ID, [
      'view test entity' => TRUE,
    ]);

    $entity = EntityTestRevPub::create();
    // Must be published so session always has access.
    $entity->setPublished();
    $entity->save();

    // Make sure anon session can access canonical.
    $this->drupalGet($entity->toUrl());

    $previewLink = PreviewLink::create()->addEntity($entity);
    $previewLink->save();

    // Claim the token to the session.
    $previewLinkUrl = Url::fromRoute('entity.entity_test_revpub.preview_link', [
      $entity->getEntityTypeId() => $entity->id(),
      'preview_token' => $previewLink->getToken(),
    ]);
    $this->drupalGet($previewLinkUrl);
    $this->assertSession()->statusCodeEquals(200);

    // Make the unclaim message appear by visiting the canonical page.
    $this->drupalGet($entity->toUrl());
    $this->assertSession()->pageTextContains('You are viewing this page because a preview link granted you access. Click here to remove token.');

    // Link should have the canonical URL as the destination.
    $this->assertSession()->linkByHrefExists(Url::fromRoute('preview_link.session_tokens.remove', [], [
      'query' => [
        'destination' => $entity->toUrl()->toString(),
      ],
    ])->toString());
  }

  /**
   * Tests accessibility of entities where session doesnt have a relevant token.
   *
   * Tests an accessible entity with a claimed key can still access entities
   * not matching claimed token.
   */
  public function testCanonicalAccessNoClaimedToken(): void {
    user_role_change_permissions(RoleInterface::ANONYMOUS_ID, [
      'view test entity' => TRUE,
    ]);

    // Must be accessible.
    $claimedEntity = EntityTestRevPub::create();
    $claimedEntity->save();

    $previewLink = PreviewLink::create()->addEntity($claimedEntity);
    $previewLink->save();

    // Claim the token to the session.
    $previewLinkUrl = Url::fromRoute('entity.entity_test_revpub.preview_link', [
      $claimedEntity->getEntityTypeId() => $claimedEntity->id(),
      'preview_token' => $previewLink->getToken(),
    ]);
    $this->drupalGet($previewLinkUrl);
    $this->assertSession()->statusCodeEquals(200);

    $otherEntity = EntityTestRevPub::create();
    // Must be accessible.
    $otherEntity->setPublished();
    $otherEntity->save();

    $this->drupalGet($otherEntity->toUrl());
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test simulating route access doesnt result in a Preview Link redirection.
   *
   * Ensures a user rendering a page which also simulates an access check to the
   * canonical route doesnt get redirected to the Preview Link route.
   * For example on the entity edit form, the breadcrumb will simulate
   * the request on the canonical route because it renders a link to canonical.
   */
  public function testRouteSimulateNoRedirect(): void {
    $this->drupalPlaceBlock('system_breadcrumb_block');

    $this->drupalLogin($this->createUser([
      'view test entity',
      'administer entity_test content',
    ]));

    // Must be accessible.
    $claimedEntity = EntityTestMulRevPub::create();
    $claimedEntity->save();

    $previewLink = PreviewLink::create()->addEntity($claimedEntity);
    $previewLink->save();

    // Claim the token to the session.
    $previewLinkUrl = Url::fromRoute('entity.entity_test_mulrevpub.preview_link', [
      $claimedEntity->getEntityTypeId() => $claimedEntity->id(),
      'preview_token' => $previewLink->getToken(),
    ]);
    $this->drupalGet($previewLinkUrl);
    $this->assertSession()->statusCodeEquals(200);

    $editUrl = $claimedEntity->toUrl('edit-form');
    $this->drupalGet($editUrl);
    $this->assertSession()->addressEquals($editUrl->toString());
  }

  /**
   * Test accessing a page without preview links.
   */
  public function testEntityNoPreviewLink(): void {
    $this->drupalPlaceBlock('system_breadcrumb_block');

    $this->drupalLogin($this->createUser([
      'view test entity',
      'administer entity_test content',
    ]));

    $otherEntity = EntityTestMulRevPub::create();
    $otherEntity->save();

    $claimedEntity = EntityTestMulRevPub::create();
    $claimedEntity->save();

    $previewLink = PreviewLink::create()->addEntity($claimedEntity);
    $previewLink->save();

    // Claim the token to the session.
    $previewLinkUrl = Url::fromRoute('entity.entity_test_mulrevpub.preview_link', [
      $claimedEntity->getEntityTypeId() => $claimedEntity->id(),
      'preview_token' => $previewLink->getToken(),
    ]);
    $this->drupalGet($previewLinkUrl);
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet($otherEntity->toUrl());
    $this->assertNoCacheContext('session');
  }

}
