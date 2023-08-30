/*
 * This file is part of the Symfony project "eliashaeussler/typo3-badges".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * CodeTabs.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
export default class CodeTabs {
  static observer;

  static currentLanguage;

  /**
   * Connect all current code tabs to a new MutationObserver.
   */
  static connect() {
    CodeTabs.disconnect();

    // Fetch all tab sync triggers
    const syncTriggers = document.querySelectorAll('[data-sync-language]');

    // Create observer
    CodeTabs.observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        CodeTabs.syncTabs(mutation, syncTriggers);
      });
    });

    // Observe all tab sync triggers
    [...syncTriggers].forEach((trigger) => {
      CodeTabs.observer.observe(trigger, { attributeFilter: ['aria-selected'] });
    });
  }

  /**
   * Disconnect the current MutationObserver.
   */
  static disconnect() {
    this.observer?.disconnect();
  }

  /**
   * Apply mutation to all connected code tabs.
   *
   * @param mutation {MutationRecord} Current mutation
   * @param syncTriggers {NodeListOf<HTMLElement>} Registered trigger elements
   */
  static syncTabs(mutation, syncTriggers) {
    const { target } = mutation;

    // Early return if mutation is unsupported
    if (mutation.type !== 'attributes'
      || mutation.attributeName !== 'aria-selected'
      || target.getAttribute('aria-selected') !== 'true'
    ) {
      return;
    }

    // Avoid endless mutations when modifying tabs
    CodeTabs.disconnect();

    // Store current language
    CodeTabs.currentLanguage = target.dataset.syncLanguage;

    // Apply current language to all tabs
    [...syncTriggers].forEach((t) => {
      if (t.dataset.syncLanguage === CodeTabs.currentLanguage) {
        t.click();
      }
    });

    // Reconnect all code tabs with a new MutationObserver
    CodeTabs.connect();
  }

  /**
   * Get current active language across all code tabs.
   *
   * @returns {string|undefined} Current active language across all code tabs
   */
  static getCurrentLanguage() {
    return CodeTabs.currentLanguage;
  }
}
