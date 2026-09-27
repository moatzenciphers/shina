import Inputmask from 'inputmask';

export const initQuickCall = () => {
  const backdrop = document.querySelector('[data-quick-call-dialog]');
  const dialog = backdrop?.querySelector('[role="dialog"]');
  const form = backdrop?.querySelector('.wpcf7 form, [data-quick-call-form]');
  const phone = backdrop?.querySelector('[data-quick-call-phone], #quick-call-phone');
  const status = backdrop?.querySelector('[data-quick-call-status]');
  let opener = null;

  if (!backdrop || !dialog || !form || !phone || !status) return;

  form.classList.add('quick-call-dialog__form');
  backdrop.addEventListener('wpcf7mailsent', () => {
    status.textContent = 'Заявка отправлена. Скоро свяжемся с вами.';
    status.hidden = false;
  });
  backdrop.addEventListener('wpcf7invalid', () => {
    status.textContent = 'Проверьте номер телефона и согласие.';
    status.hidden = false;
  });
  backdrop.addEventListener('wpcf7unaccepted', () => {
    status.textContent = 'Подтвердите согласие с политикой конфиденциальности.';
    status.hidden = false;
  });
  backdrop.addEventListener('wpcf7mailfailed', () => {
    status.textContent = 'Не удалось отправить заявку. Попробуйте позже.';
    status.hidden = false;
  });

  Inputmask({ mask: '+7 (999) 999-99-99', showMaskOnHover: false }).mask(phone);

  const close = () => {
    backdrop.hidden = true;
    document.body.classList.remove('quick-call-open');
    opener?.focus();
  };

  document.querySelectorAll('[data-quick-call-open]').forEach((button) => {
    button.addEventListener('click', () => {
      opener = button;
      status.hidden = true;
      status.textContent = '';
      backdrop.hidden = false;
      document.body.classList.add('quick-call-open');
      phone.focus();
    });
  });

  backdrop.querySelector('[data-quick-call-close]')?.addEventListener('click', close);
  backdrop.addEventListener('click', (event) => {
    if (event.target === backdrop) close();
  });

  document.addEventListener('keydown', (event) => {
    if (backdrop.hidden) return;

    if (event.key === 'Escape') {
      event.preventDefault();
      close();
      return;
    }

    if (event.key !== 'Tab') return;

    const focusable = Array.from(dialog.querySelectorAll('button, input, a[href]'))
      .filter((element) => !element.disabled && element.getClientRects().length > 0);
    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last?.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first?.focus();
    }
  });

  phone.addEventListener('input', () => {
    phone.setCustomValidity('');
    status.hidden = true;
  });

  form.addEventListener('submit', (event) => {
    if (!phone.inputmask?.isComplete()) {
      event.preventDefault();
      event.stopImmediatePropagation();
      phone.setCustomValidity('Введите номер телефона полностью.');
      phone.reportValidity();
      phone.focus();
      return;
    }

    if (form.closest('.wpcf7')) return;
    event.preventDefault();

    // The frontend has no callback endpoint yet. This event lets the future integration handle the request.
    const request = new CustomEvent('quick-call:submit', {
      bubbles: true,
      cancelable: true,
      detail: { phone: phone.value, consent: true },
    });
    form.dispatchEvent(request);

    if (!request.defaultPrevented) {
      status.textContent = 'Отправка заявок пока не подключена.';
      status.hidden = false;
    }
  }, true);
};
