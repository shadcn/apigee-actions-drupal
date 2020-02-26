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

namespace Drupal\apigee_actions\Plugin\RulesEvent;

use Drupal\apigee_edge\Entity\EdgeEntityTypeInterface;

/**
 * Deriver for Edge entity delete events.
 */
class EdgeEntityDeleteEventDeriver extends EdgeEntityEventDeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getEventActionName(EdgeEntityTypeInterface $entity_type): string {
    return 'delete';
  }

  /**
   * {@inheritdoc}
   */
  public function getEventActionLabel(EdgeEntityTypeInterface $entity_type): string {
    return $this->t('After deleting a @entity_type', ['@entity_type' => $entity_type->getSingularLabel()]);
  }

}
