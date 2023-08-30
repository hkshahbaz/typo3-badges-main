module.exports = {
  content: [
    './assets/**/*.js',
    './templates/**/*.html.twig',
  ],
  theme: {
    fontFamily: {
      'sans': ['Lato', 'sans-serif'],
      'mono': ['JetBrains Mono', 'monospace'],
      'cursive': ['Indie Flower', 'cursive'],
    },
    triangles: {
      'right-up': {
        direction: 'right-up',
        size: '8rem',
      },
    },
    extend: {
      colors: {
        current: 'currentColor',
        'typo3-orange': '#f49700',
      },
    },
  },
  variants: {
    opacity: ['no-touch'],
  },
  plugins: [
    require('tailwindcss-triangles')({
      componentPrefix: 'c-',
      defaultColor: 'currentColor',
    }),
    require('tailwindcss-selection-variant'),
    require('tailwindcss-crossbrowser-touch')(),
  ],
}
