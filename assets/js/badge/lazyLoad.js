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
 * LazyLoad.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
export default class LazyLoad {
  /**
   * Connect all lazy-loaded badges within given element to a new IntersectionObserver.
   *
   * @param element {string|HTMLElement} Root element for all images to be lazy-loaded
   */
  static connect(element) {
    const observer = new IntersectionObserver(
      (elements) => {
        elements.forEach((entry) => {
          LazyLoad.loadImage(entry.target);
        });
      },
      {
        root: typeof element === 'string' ? document.querySelector(element) : element,
        rootMargin: '10px',
        threshold: 0.5,
      },
    );

    // Query all badges
    const badges = document.querySelectorAll('img[data-src]');

    // Observe all badges
    [...badges].forEach((badge) => {
      observer.observe(badge);
    });
  }

  /**
   * Load image within given element.
   *
   * @param element {HTMLElement} Image to be loaded
   */
  static loadImage(element) {
    const { lazyClasses, src } = element.dataset;

    // Early return if "data-src" attribute is missing or is part of a template section
    if (typeof src === 'undefined' || src.includes('EXTENSION_KEY')) {
      return;
    }

    // Exchange image source attribute
    element.setAttribute('src', src);
    element.removeAttribute('data-src');

    // Remove lazy-load classes
    element.addEventListener('load', () => {
      element.classList.remove(...lazyClasses.split(' '));
    });
  }
}
