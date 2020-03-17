<?php

/**
 * Copyright 2020 Google Inc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

namespace Drupal\Tests\apigee_actions\Kernel\Plugin\RulesEvent;

use Drupal\apigee_edge\Entity\ApiProduct;
use Drupal\rules\Context\ContextConfig;

/**
 * Tests Edge entity add_product event.
 *
 * @package Drupal\Tests\apigee_actions\Kernel
 */
class EdgeEntityAddProductEventTest extends EdgeEntityEventTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'apigee_actions',
    'apigee_actions_debug',
    'apigee_edge',
    'apigee_edge_teams',
    'apigee_mock_api_client',
    'dblog',
    'key',
    'options',
  ];

  /**
   * Tests add_member events for Edge entities.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\rules\Exception\LogicException
   */
  public function testEvent() {
    $api_product_name = $this->randomMachineName();
    // Create an insert rule.
    $rule = $this->expressionManager->createRule();
    $rule->addAction('apigee_actions_log_message',
      ContextConfig::create()
        ->setValue('message', "Product {{ api_product_name }} was added to app {{ developer_app.name }}.")
        ->process('message', 'rules_tokens')
    );

    // Test condition.
    $rule->addCondition('rules_data_comparison', ContextConfig::create()
      ->map('data', 'api_product_name')
      ->setValue('value', $api_product_name)
    );

    $config_entity = $this->storage->create([
      'id' => 'app_insert_rule',
      'events' => [['event_name' => 'apigee_actions_entity_add_product:developer_app']],
      'expression' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    /** @var \Drupal\apigee_edge\Entity\AppInterface $developer_app */
    $developer_app = $this->createDeveloperApp();

    $api_product = ApiProduct::create([
      'name' => $api_product_name,
      'displayName' => $this->getRandomGenerator()->word(16),
      'approvalType' => ApiProduct::APPROVAL_TYPE_AUTO,
    ]);

    /** @var \Drupal\apigee_edge\Entity\ApiProduct $api_product */
    $this->stack->queueMockResponse([
      'api_product' => [
        'product' => $api_product,
      ],
    ]);

    $api_product->save();

    $this->stack->queueMockResponse([
      'api_product' => [
        'product' => $api_product,
      ],
    ]);
    $this->queueDeveloperResponse($this->account);
    $this->stack->queueMockResponse([
      'get_developer_apps' => [
        'apps' => [$developer_app]
      ],
    ]);

    /** @var \Drupal\apigee_edge\Entity\Controller\DeveloperAppCredentialControllerFactoryInterface $credential_factory */
    $credential_factory = \Drupal::service('apigee_edge.controller.developer_app_credential_factory');
    $credential_factory->developerAppCredentialController($this->account->uuid(), $developer_app->getName())->addProducts($this->randomString(), [$api_product->id()]);

    $this->assertLogsContains("Event apigee_actions_entity_add_product:developer_app was dispatched.");
    $this->assertLogsContains("Product {$api_product->getName()} was added to app {$developer_app->getName()}.");
  }

}