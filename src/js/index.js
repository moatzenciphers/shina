import '../scss/main.scss';

import { initButtonOutlines } from './button-outlines';
import { initCookieConsent } from './cookie-consent';
import { initLanding } from './landing';
import { initSiteHeader } from './site-header';
import { initDataHrefLinks } from './data-href';
import { initMobileActions } from './mobile-actions';
import { initQuickCall } from './quick-call';
import { initReviewSlider } from './review-slider';
import './review-dialog';
import { initCoverageMap } from './coverage-map';
import { initMapPanel } from './map-panel';
import { initGalleryGrid } from './gallery-grid';
import { initPricesPage } from './prices-page';
import { initSplitForm } from './split-form';
import { initSiteFooter } from './site-footer';
import { initServiceFilters } from './service-filters';

const initSite = () => {
  initDataHrefLinks();
  initSiteHeader();
  initLanding();
  initMobileActions();
  initQuickCall();
  initReviewSlider();
  initMapPanel();
  initCoverageMap();
  initGalleryGrid();
  initSplitForm();
  initPricesPage();
  initSiteFooter();
  initServiceFilters();
  initCookieConsent();
  initButtonOutlines();
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initSite, { once: true });
} else {
  initSite();
}
