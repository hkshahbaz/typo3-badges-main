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

/**
 * BadgeProvider.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
export default class BadgeProvider {
  identifier;

  name;

  elements;

  constructor(identifier, name) {
    this.identifier = identifier;
    this.name = name;
    this.elements = this.locateElements();
  }

  enable() {
    [...this.elements].forEach((el) => {
      el.classList.remove('hidden');
    });
  }

  disable() {
    [...this.elements].forEach((el) => {
      el.classList.add('hidden');
    });
  }

  locateElements() {
    return document.querySelectorAll(`[data-badge-provider="${this.identifier}"]`);
  }
}
