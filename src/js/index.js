import '../scss/main.scss';
import 'glightbox/dist/css/glightbox.css';

import { initCalculator } from './calculator';
import { initFaqScreen } from './faq';
import { initYandexAddress } from './map';
import { initPhotosScreen } from './photos';
import { initReviewsScreen } from './reviews';
import { initScreens } from './screens';

document.addEventListener('DOMContentLoaded', () => {
  initCalculator();
  initYandexAddress();
  initReviewsScreen();
  initPhotosScreen();
  initFaqScreen();
  initScreens();
});
