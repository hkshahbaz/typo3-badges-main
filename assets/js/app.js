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

import BadgeProviderToggle from './badge/provider/badgeProviderToggle';
import Clipboard from './clipboard';
import CodeTabs from './badge/codeTabs';
import LazyLoad from './badge/lazyLoad';
import TryOut from './badge/tryOut';

/**
 * App.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
export default class App {
  badgeProviderToggle;

  isTouchDevice;

  tryOut;

  constructor() {
    this.isTouchDevice = App.detectTouchDevice();
  }

  initializeEnvironment() {
    this.applyTouchDeviceState();
    App.enableJsOnlyElements();
  }

  initializeComponents() {
    Clipboard.connect('.clipboard-btn');
    CodeTabs.connect();
    LazyLoad.connect('body');

    this.badgeProviderToggle = new BadgeProviderToggle('.badge-providers-button');
    this.tryOut = new TryOut('[data-modal-trigger="try-out-modal"]');
  }

  /**
   * Report device state (touch / no touch) to document.
   */
  applyTouchDeviceState() {
    const { classList } = document.documentElement;

    if (this.isTouchDevice) {
      classList.add('touch');
      classList.remove('no-touch');
    } else {
      classList.add('no-touch');
      classList.remove('touch');
    }
  }

  /**
   * Enable all elements controlled by JavaScript.
   */
  static enableJsOnlyElements() {
    [...document.querySelectorAll('.js-hidden')].forEach((el) => el.classList.remove('js-hidden'));
  }

  /**
   * Test current device state (touch / no touch).
   *
   * @returns {boolean} `true` if the current device has touch, `false` otherwise
   */
  static detectTouchDevice() {
    return 'ontouchstart' in document.documentElement;
  }
}
