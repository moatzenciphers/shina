const cookieConsentStorageKey = 'shina_cookie_consent_accepted';

const hasAcceptedCookieConsent = () => {
  try {
    return window.localStorage.getItem(cookieConsentStorageKey) === 'yes';
  } catch {
    return false;
  }
};

const acceptCookieConsent = () => {
  try {
    window.localStorage.setItem(cookieConsentStorageKey, 'yes');
  } catch {
    // The banner can still close when localStorage is unavailable.
  }
};

export const initCookieConsent = () => {
  const popup = document.querySelector('[data-cookie-consent]');
  const acceptButton = document.querySelector('[data-cookie-consent-accept]');

  if (!popup || !acceptButton || hasAcceptedCookieConsent()) {
    return;
  }

  popup.hidden = false;

  acceptButton.addEventListener('click', () => {
    acceptCookieConsent();
    popup.hidden = true;
  });
};
