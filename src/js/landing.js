export const initLanding = () => {
  if (document.querySelector('[data-landing]')) document.body.classList.add('landing-page');
  const calculatorUrl = window.__CALCULATOR_URL__ || './calculator.html';
  document.querySelectorAll('[data-open-calculator]').forEach((trigger) => {
    if (trigger.tagName === 'A') trigger.href = calculatorUrl;
    if (trigger.hasAttribute('data-href')) trigger.dataset.href = calculatorUrl;
    trigger.addEventListener('click', (event) => {
      if (event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      event.preventDefault();
      try { sessionStorage.setItem('calculatorPreviousPage', window.location.href); } catch {}
      window.location.assign(calculatorUrl);
    });
  });
  if (window.location.hash === '#calculator') window.location.replace(calculatorUrl);
};
