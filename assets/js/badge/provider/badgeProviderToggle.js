/*
 * This file is part of the Symfony project "eliashaeussler/typo3-badges".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

import BadgeProvider from './badgeProvider';

/**
 * BadgeProviderToggle.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
export default class BadgeProviderToggle {
  buttons;

  inputs;

  providers;

  constructor(element) {
    this.buttons = document.querySelectorAll(element);
    this.inputs = this.locateInputs();
    this.providers = this.buildProviders();

    const preselectedProvider = BadgeProviderToggle.getProviderFromUrl();
    this.setActiveProvider(preselectedProvider);
    if (preselectedProvider !== null) {
      this.hideButtonPings();
    }

    this.addEventListeners();
  }

  addEventListeners() {
    [...this.buttons].forEach((el) => {
      ['focus', 'click'].forEach((e) => el.addEventListener(e, () => this.hideButtonPings()));
    });
    [...this.inputs].forEach((el) => {
      el.addEventListener('change', () => this.setActiveProvider(BadgeProviderToggle.getIdentifierByInput(el)));
    });
  }

  /**
   * Locate input elements used to control the current badge provider.
   *
   * @returns {Element[]} Array of located input elements
   */
  locateInputs() {
    const inputs = [];

    [...this.buttons].forEach((button) => {
      const buttonTarget = button.getAttribute('data-dropdown-toggle');
      const dropdown = document.querySelector(`#${buttonTarget}`);

      inputs.push(...dropdown.querySelectorAll('[data-badge-provider]'));
    });

    return inputs;
  }

  /**
   * Initialize badge providers from input elements.
   *
   * @returns {Object.<string, BadgeProvider>} Initialized badge providers with
   *                                           their identifiers as keys
   */
  buildProviders() {
    const providers = {};

    [...this.inputs].forEach((el) => {
      const identifier = BadgeProviderToggle.getIdentifierByInput(el);

      if (!(identifier in providers)) {
        const name = BadgeProviderToggle.getNameByInput(el);
        providers[identifier] = new BadgeProvider(identifier, name);
      }
    });

    return providers;
  }

  /**
   * Determine currently active badge provider.
   *
   * @returns {string} Identifier of the currently active badge provider
   */
  getActiveProvider() {
    const activeProvider = [...this.inputs].filter((input) => input.checked)[0];

    return BadgeProviderToggle.getIdentifierByInput(activeProvider);
  }

  /**
   * Update currently active provider, either by given provider or by detection.
   *
   * @param activeProvider {string|null} Provider to be set as active
   *                                     or `NULL` to determine active provider
   */
  setActiveProvider(activeProvider = null) {
    if (activeProvider === null || !(activeProvider in this.providers)) {
      // eslint-disable-next-line no-param-reassign
      activeProvider = this.getActiveProvider();
    }

    // Apply provider as URL fragment
    window.location.hash = activeProvider;

    // Apply "checked" state of all input elements
    [...this.inputs].forEach((el) => {
      // eslint-disable-next-line
      el.checked = BadgeProviderToggle.getIdentifierByInput(el) === activeProvider;
    });

    // Apply states to dependent elements
    Object.entries(this.providers).forEach(([identifier, provider]) => {
      if (identifier === activeProvider) {
        provider.enable();

        [...this.buttons].forEach((button) => {
          BadgeProviderToggle.getButtonLabel(button).textContent = provider.name;
        });
      } else {
        provider.disable();
      }
    });
  }

  /**
   * Get all available providers.
   *
   * @returns {string[]} Identifiers of all available providers
   */
  getAllProviders() {
    return [...this.inputs].map((input) => BadgeProviderToggle.getIdentifierByInput(input));
  }

  /**
   * Hide animated pings on toggle buttons.
   */
  hideButtonPings() {
    [...this.buttons].forEach((el) => {
      BadgeProviderToggle.getButtonPing(el).classList.add('hidden');
    });
  }

  /**
   * Get provider identifier from URL fragment.
   *
   * @returns {string|null} Provider identifier or `NULL` if no URL fragment is set or if it's empty
   */
  static getProviderFromUrl() {
    let { hash } = window.location;

    if (hash === null || hash.length === 0) {
      return null;
    }

    if (hash.startsWith('#')) {
      hash = hash.substr(1);
    }

    return hash;
  }

  static getButtonLabel(button) {
    return button.querySelector('.badge-providers-button-label');
  }

  static getButtonPing(button) {
    return button.querySelector('.badge-providers-button-ping');
  }

  static getIdentifierByInput(input) {
    return input.getAttribute('data-badge-provider');
  }

  static getNameByInput(input) {
    return input.getAttribute('data-badge-provider-name');
  }
}
