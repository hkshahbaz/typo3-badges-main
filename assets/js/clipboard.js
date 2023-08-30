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

import ClipboardJS from 'clipboard';

const ClipboardOptions = {
  copyIcon: '.clipboard-copy',
  checkIcon: '.clipboard-check',
  timeout: 2000,
  copyText: 'Copy to clipboard',
  successText: 'Copied to clipboard!',
};

/**
 * Clipboard.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
export default class Clipboard {
  static buttons = [];

  static timeouts = [];

  static clipboard;

  /**
   * Connect a new Clipboard instance to all nodes of given element.
   *
   * @param element {string} Clipboard button element selector
   */
  static connect(element) {
    // Destroy previous bindings
    Clipboard.clipboard?.destroy();

    // Add new button element
    if (!Clipboard.buttons.includes(element)) {
      Clipboard.buttons.push(element);
    }

    // Create new clipboard
    Clipboard.clipboard = new ClipboardJS(Clipboard.getAllButtons(), {
      target: (trigger) => trigger.nextElementSibling,
    });

    Clipboard.resetAllButtons();

    // Register events
    Clipboard.clipboard.on('success', (e) => Clipboard.onSuccessfulCopy(e.trigger));
    Clipboard.clipboard.on('error', () => Clipboard.resetAllButtons());
  }

  /**
   * Perform additional actions on successful copy to clipboard.
   *
   * @param button {Element} The copy button element
   */
  static onSuccessfulCopy(button) {
    Clipboard.resetAllButtons(false);
    Clipboard.hideCopyIcon(button);
    Clipboard.timeouts.push(
      window.setTimeout(() => Clipboard.resetButton(button), ClipboardOptions.timeout),
    );
  }

  /**
   * Hide clipboard copy icon after successful copy.
   *
   * @param button {Element} The copy button element
   */
  static hideCopyIcon(button) {
    Clipboard.getCopyIcon(button).classList.add('hidden');
    Clipboard.getCheckIcon(button).classList.remove('hidden');

    button.setAttribute('title', ClipboardOptions.successText);
  }

  /**
   * Reset copy to clipboard button after successful copy.
   *
   * @param button {Element} The copy button element
   * @param resetFocus {boolean} `true` if focus of button should be lost, `false` otherwise
   */
  static resetButton(button, resetFocus = true) {
    Clipboard.getCopyIcon(button).classList.remove('hidden');
    Clipboard.getCheckIcon(button).classList.add('hidden');

    button.setAttribute('title', ClipboardOptions.copyText);

    if (resetFocus) {
      button.blur();
    }
  }

  /**
   * Reset all copy to clipboard buttons.
   */
  static resetAllButtons(resetFocus = true) {
    Clipboard.timeouts.forEach((id) => window.clearTimeout(id));
    Clipboard.timeouts = [];

    [...Clipboard.getAllButtons()].forEach((button) => Clipboard.resetButton(button, resetFocus));
  }

  /**
   * Get "copy" icon within given button.
   *
   * @param button {HTMLElement} Button to be queried
   * @returns {HTMLElement} "Copy" icon within given button
   */
  static getCopyIcon(button) {
    return button.querySelector(ClipboardOptions.copyIcon);
  }

  /**
   * Get "check" icon within given button.
   *
   * @param button {HTMLElement} Button to be queried
   * @returns {HTMLElement} "Check" icon within given button
   */
  static getCheckIcon(button) {
    return button.querySelector(ClipboardOptions.checkIcon);
  }

  /**
   * Get all currently connected clipboard button elements.
   *
   * @returns {NodeListOf<Element>} List of connected clipboard button elements
   */
  static getAllButtons() {
    return document.querySelectorAll(Clipboard.buttons.join(','));
  }
}
