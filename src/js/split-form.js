import Inputmask from 'inputmask';

export const initSplitForm = () => {
  const section = document.querySelector('.split-form');
  const form = section?.querySelector('[data-split-form], .wpcf7 form');
  const phone = form?.querySelector('[data-split-form-phone]');
  const status = section?.querySelector('[data-split-form-status]');
  if (!section || !status) return;

  const showStatus = (message, state) => {
    status.textContent = message;
    status.dataset.state = state;
    status.hidden = false;
  };

  // CF7 sends its own Ajax request; its DOM events are the source of submission status.
  section.addEventListener('wpcf7mailsent', () => showStatus('Заявка отправлена. Скоро свяжемся с вами.', 'success'));
  section.addEventListener('wpcf7invalid', () => showStatus('Проверьте заполнение полей.', 'error'));
  section.addEventListener('wpcf7mailfailed', () => showStatus('Не удалось отправить заявку. Попробуйте позже.', 'error'));
  section.addEventListener('wpcf7spam', () => showStatus('Не удалось отправить заявку. Попробуйте позже.', 'error'));

  if (!form || !phone) return;

  Inputmask({ mask: '+7 (999) 999-99-99', showMaskOnHover: false }).mask(phone);
  phone.addEventListener('input', () => {
    phone.setCustomValidity('');
    status.hidden = true;
  });

  form.addEventListener('submit', (event) => {
    if (form.matches('.wpcf7-form') || form.closest('.wpcf7')) return;
    event.preventDefault();
    if (!phone.inputmask?.isComplete()) {
      phone.setCustomValidity('Введите номер телефона полностью.');
      phone.reportValidity();
      phone.focus();
      return;
    }

    showStatus('Отправка заявок пока не подключена.', 'error');
  });
};
