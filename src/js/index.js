import '../scss/main.scss';

import { initButtonOutlines } from './button-outlines';
import { initCookieConsent } from './cookie-consent';
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
import { initServiceFilters } from './service-filters';

document.addEventListener('DOMContentLoaded', () => {
  initSiteHeader();
  initLanding();
  initMobileActions();
  initQuickCall();
  initReviewSlider();
  initMapPanel();
  initGalleryGrid();
  initSplitForm();
  initSiteFooter();
  initServiceFilters();
  initCookieConsent();
  initButtonOutlines();
});
