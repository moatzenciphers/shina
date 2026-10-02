const field = (name, label, type = 'text', extra = {}) => ({
  key: `field_landing_${name}`,
  label,
  name,
  type,
  required: 0,
  conditional_logic: 0,
  wrapper: { width: '', class: '', id: '' },
  ...extra,
});
const text = (name, label, value = '') => field(name, label, 'text', { default_value: value });
const area = (name, label, value = '') => field(name, label, 'textarea', { default_value: value, rows: 3, new_lines: '' });
const image = (name, label) => field(name, label, 'image', { return_format: 'id', preview_size: 'medium', library: 'all' });
const scopeFields = (fields, prefix) => fields.map((item) => ({
  ...item,
  key: `field_landing_${prefix}_${item.name}`,
  ...(item.sub_fields ? { sub_fields: scopeFields(item.sub_fields, `${prefix}_${item.name}`) } : {}),
}));
const repeater = (name, label, sub_fields, max = 0) => field(name, label, 'repeater', {
  layout: 'block', button_label: 'Добавить', min: 0, max, sub_fields: scopeFields(sub_fields, name),
});
const relation = (name, label, postType) => field(name, label, 'relationship', {
  instructions: 'Если оставить пустым, выводятся последние опубликованные записи.',
  post_type: [postType], taxonomy: [], filters: ['search'], return_format: 'id', min: 0, max: 0,
});
const layout = (name, label, sub_fields) => ({
  key: `layout_landing_${name}`, name, label, display: 'block', sub_fields, min: '', max: '',
});
const heading = (prefix, eyebrow, title) => [
  text(`${prefix}_eyebrow`, 'Надпись над заголовком', eyebrow),
  text(`${prefix}_title`, 'Заголовок', title),
];
const layouts = [
  layout('hero', 'Первый экран', [
    ...heading('hero', '', 'ВЫЕЗДНОЙ ШИНОМОНТАЖ 24/7'),
    text('hero_offer', 'Короткий оффер', 'Приедем и починим на месте.'),
    repeater('hero_benefits', 'Преимущества первого экрана', [text('title', 'Основной текст'), text('text', 'Подпись'), field('icon', 'SVG-код иконки', 'text', { instructions: 'Полный SVG-код с цветом currentColor. Если оставить пустым, используется стандартная иконка.' })], 3),
    image('hero_background', 'Фоновое изображение'),
    image('hero_background_mobile', 'Фоновое изображение для мобильных устройств'),
    repeater('hero_prices', 'Стоимость выезда', [text('label', 'Подпись'), text('price', 'Цена')], 2),
    field('hero_rating', 'Рейтинг из 5', 'number', { default_value: 4.9, min: 0, max: 5, step: 0.1, instructions: 'Подпись отзывов задаётся в общих настройках главной.' }),
  ]),
  layout('features_grid', 'Четыре преимущества', [
    ...heading('features', 'ПОМОЩЬ РЯДОМ', 'Поможем на месте — без эвакуатора и поездки в сервис'),
    repeater('features_items', 'Преимущества', [text('title', 'Название'), area('text', 'Описание')], 4),
    area('features_note', 'Текст под списком', 'Не хотите заполнять калькулятор? Оставьте номер — оператор перезвонит в течение 2 минут.'),
    text('features_button', 'Текст кнопки', 'Быстрый вызов'),
  ]),
  layout('steps_grid', 'Шаги заказа', [
    ...heading('steps', 'КАК ПРОХОДИТ ВЫЕЗД', 'От заявки до готового автомобиля — четыре шага'),
    repeater('steps_items', 'Шаги', [text('title', 'Название'), area('text', 'Описание')], 4),
    text('steps_button', 'Текст кнопки', 'Рассчитать стоимость'),
  ]),
  layout('cards_grid', 'Услуги и цены', [
    ...heading('services', 'УСЛУГИ И ЦЕНЫ', 'Выездной шиномонтаж и помощь на дороге'),
    area('services_intro', 'Вводный текст', 'Ориентировочные цены указаны «от». Точная стоимость зависит от автомобиля, объёма работ, адреса и времени выезда.'),
    relation('services_posts', 'Выбранные услуги', 'services'),
    field('services_count', 'Число услуг при автоматическом выводе', 'number', { default_value: 8, min: 1, max: 24 }),
    area('services_footer', 'Текст внизу'),
  ]),
  layout('media_rows', 'Ряды с фото и списком', [
    ...heading('media', 'ПОЧЕМУ НАМ МОЖНО ДОВЕРЯТЬ', 'Отвечаем за работу и заранее объясняем стоимость'),
    repeater('media_items', 'Ряды', [
      image('image', 'Изображение'),
      repeater('points', 'Пункты', [text('title', 'Заголовок'), area('text', 'Описание')], 3),
    ], 2),
  ]),
  layout('slider_grid', 'Отзывы', [
    ...heading('reviews', 'ОТЗЫВЫ КЛИЕНТОВ', 'Что говорят клиенты'),
    area('reviews_lead', 'Описание'),
    { ...relation('reviews_posts', 'Выбранные отзывы', 'reviews'), max: 9 },
    field('reviews_count', 'Число отзывов при автоматическом выводе', 'number', { default_value: 9, min: 1, max: 9 }),
  ]),
  layout('map_panel', 'Карта зоны выезда', [
    ...heading('map', 'ЗОНА ВЫЕЗДА', 'Выезжаем по Москве и Московской области'),
    area('map_lead', 'Описание', 'Москва и Подмосковье. За МКАД стоимость выезда зависит от расстояния.'),
    text('map_form_shortcode', 'Шорткод CF7 для проверки адреса'),
  ]),
  layout('gallery_grid', 'Фото выездов', [
    ...heading('gallery', 'ФОТО ВЫЕЗДОВ', 'Работаем там, где помощь нужна сейчас'),
    field('gallery_images', 'Фотографии из медиабиблиотеки', 'gallery', {
      instructions: 'Подписи берутся из поля shina_photo_label на вложении или из его названия.',
      return_format: 'id', preview_size: 'medium', insert: 'append', library: 'all', min: 0, max: 12,
    }),
  ]),
  layout('accordion_grid', 'Частые вопросы', [
    ...heading('faq', 'ЧАСТЫЕ ВОПРОСЫ', 'Ответы на вопросы о выездном шиномонтаже'),
    field('faq_mode', 'Источник вопросов', 'select', {
      choices: { latest: 'Последние 10', selected: 'Выбрать вручную' }, default_value: 'latest',
      return_format: 'value', ui: 1,
    }),
    field('faq_posts', 'Выбранные вопросы', 'relationship', {
      post_type: ['faq'], taxonomy: [], filters: ['search'], return_format: 'id', min: 0, max: 10,
      conditional_logic: [[{ field: 'field_landing_faq_mode', operator: '==', value: 'selected' }]],
    }),
  ]),
  layout('split_form', 'Форма помощи', [
    ...heading('help', 'НУЖНА ПОМОЩЬ?', 'Не знаете, какая услуга нужна?'),
    area('help_lead', 'Описание', 'Опишите, что произошло. Оператор разберётся в ситуации, уточнит детали и перезвонит в течение 2 минут.'),
    text('help_form_shortcode', 'Шорткод CF7 формы помощи'),
    area('help_note', 'Пояснение под формой', 'Калькулятор покажет предварительную стоимость и время приезда.'),
  ]),
];

const groups = [
  { key: 'group_landing_review_caption', title: 'Лендинг: подпись отзывов', fields: [
    field('reviews_count_text', 'Подпись количества отзывов', 'text', { default_value: 'Отзывы клиентов', instructions: 'Готовый текст для первого экрана и блока отзывов. Например: Более 2000 отзывов. Количество автоматически не подсчитывается.' }),
  ], location: [[{ param: 'options_page', operator: '==', value: 'main-option' }]], active: true },
  {
    key: 'group_landing_front_page_flexible', title: 'Лендинг: блоки главной',
    fields: [
      field('blocks', 'Блоки страницы', 'flexible_content', {
        button_label: 'Добавить блок', layouts: Object.fromEntries(layouts.map((item) => [item.key, item])), min: 0, max: 0,
      }),
      text('quick_call_shortcode', 'Шорткод CF7 быстрого вызова'),
      text('calculator_form_shortcode', 'Шорткод CF7 подтверждения калькулятора'),
    ],
    location: [[{ param: 'page_type', operator: '==', value: 'front_page' }]],
    menu_order: 0, position: 'normal', style: 'default', label_placement: 'top',
    instruction_placement: 'label', hide_on_screen: '', active: true, show_in_rest: 0,
    description: 'Порядок блоков задаётся гибким содержимым. Хедер и футер управляются отдельно.',
    modified: Math.floor(Date.now() / 1000),
  },
  { key: 'group_landing_review_service', title: 'Отзыв: услуга и заголовок', fields: [
    field('review_service', 'Услуга', 'post_object', { post_type: ['services'], return_format: 'id', allow_null: 1, multiple: 0, ui: 1, instructions: 'Выберите услугу, к которой относится этот существующий отзыв. Из неё берутся название и иконка для карточки и фильтра.' }),
    text('review_heading', 'Короткий заголовок отзыва'),
  ], location: [[{ param: 'post_type', operator: '==', value: 'reviews' }]], active: true },
  { key: 'group_calculator_page', title: 'Калькулятор: форма заказа', fields: [{ ...text('calculator_form_shortcode', 'Шорткод CF7 подтверждения калькулятора'), key: 'field_calculator_page_shortcode' }], location: [[{ param: 'page_template', operator: '==', value: 'page-calculator.php' }]], active: true },
  {
    key: 'group_landing_service_price', title: 'Услуга: карточка на главной',
    fields: [
      field('service_icon', 'SVG-код иконки услуги', 'text', { default_value: '', instructions: 'Вставьте полный SVG-код. Используйте currentColor для цвета. Одна иконка используется в меню, карточке услуги и отзывах; активный код и внешние ссылки удаляются.' }),
      area('service_card_description', 'Текст карточки на главной'),
      field('service_price_from', 'Цена от, ₽', 'number', { instructions: 'Число без знака рубля.', min: 0, step: 1 }),
      text('service_price_note', 'Пояснение под ценой'),
    ],
    location: [[{ param: 'post_type', operator: '==', value: 'services' }]],
    menu_order: 1, position: 'normal', style: 'default', label_placement: 'top',
    instruction_placement: 'label', hide_on_screen: '', active: true, show_in_rest: 1,
    modified: Math.floor(Date.now() / 1000),
  },
];

require('fs').writeFileSync(require('path').join(__dirname, 'Wordpress/wp-content/themes/edemchinim/inc/acf-review-fields.json'), JSON.stringify(groups.find(group => group.key === 'group_landing_review_service'), null, 2) + '\n');
require('fs').writeFileSync(require('path').join(__dirname, 'Wordpress/wp-content/themes/edemchinim/inc/acf-review-caption.json'), JSON.stringify(groups.find(group => group.key === 'group_landing_review_caption'), null, 2) + '\n');
process.stdout.write(`${JSON.stringify(groups, null, 2)}\n`);
