/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",
    "./assets/js/**/*.js"
  ],
  theme: {
    extend: {
        colors: {
            brand: {
                50: '#f0fdf4',
                100: '#dcfce7',
                500: '#0ca14e',
                600: '#09813e',
                700: '#15803d',
            }
        },
        fontFamily: {
            sans: ['Inter', 'sans-serif'],
        }
    }
  },
  plugins: [],
}
