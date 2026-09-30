import '../scss/main.scss';

import { initCalculator } from './calculator';
import { initCookieConsent } from './cookie-consent';
import { initYandexAddress } from './map';
import { initLanding } from './landing';
import { initSiteHeader } from './site-header';
import { initMobileActions } from './mobile-actions';
import { initQuickCall } from './quick-call';
import { initReviewSlider } from './review-slider';
import './review-dialog';
import { initMapPanel } from './map-panel';
import { initGalleryGrid } from './gallery-grid';
import { initSplitForm } from './split-form';
import { initSiteFooter } from './site-footer';

document.addEventListener('DOMContentLoaded', () => {
  const wpOrderForm = document.querySelector('.app .wpcf7 form');
  if (wpOrderForm) {
    wpOrderForm.classList.add('order-confirm');
    wpOrderForm.dataset.orderForm = '';
    wpOrderForm.hidden = true;
  }
  initCalculator();
  initYandexAddress();
  initSiteHeader();
  initLanding();
  initMobileActions();
  initQuickCall();
  initReviewSlider();
  initMapPanel();
  initGalleryGrid();
  initSplitForm();
  initSiteFooter();
  initCookieConsent();
});
