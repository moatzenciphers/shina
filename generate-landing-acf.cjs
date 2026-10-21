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
    field('hero_variant', 'Вариант первого экрана', 'select', {
      choices: { home: 'Главная: заголовок с 24/7 и рейтинг', service: 'Услуга: обычный заголовок и пояснение' },
      default_value: 'home', return_format: 'value', ui: 1,
    }),
    ...heading('hero', '', 'ВЫЕЗДНОЙ ШИНОМОНТАЖ 24/7'),
    text('hero_offer', 'Короткий оффер', 'Приедем и починим на месте.'),
    text('hero_button', 'Текст кнопки', 'Рассчитать стоимость'),
    area('hero_note', 'Пояснение под кнопкой (вариант услуги)'),
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
  layout('columns_list', 'Колонки со списками', [
    ...heading('columns', 'ПЕРЕД ОБСЛУЖИВАНИЕМ', ''),
    area('columns_description', 'Описание'),
    repeater('columns_items', 'Колонки', [
      text('title', 'Заголовок колонки'),
      repeater('items', 'Пункты списка', [area('text', 'Текст пункта')]),
    ], 2),
    area('columns_note', 'Пояснение перед кнопкой'),
    text('columns_button', 'Текст кнопки', 'Вызвать мастера'),
  ]),
  layout('price_table', 'Таблица цен', [
    ...heading('pricing', 'УСЛУГА И СТОИМОСТЬ', ''),
    area('pricing_description', 'Описание'),
    area('pricing_group_note', 'Пояснение под доплатами'),
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
      text('placeholder', 'Подпись заглушки, если фото пока нет'),
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
    area('help_note', 'Пояснение под формой', 'Оператор уточнит детали и согласует стоимость работ и время приезда.'),
  ]),
];

const groups = [
  {
    key: 'group_articles_archive', title: 'Статьи: настройки списка',
    fields: [
      text('articles_title', 'Заголовок H1', 'Статьи'),
      area('articles_intro', 'Вступление при наличии статей', 'Как подготовиться к обслуживанию и разобраться в частых вопросах о шинах, эксплуатации автомобиля и помощи на дороге.'),
      area('articles_empty_intro', 'Вступление пустого списка', 'Готовим материалы о шинах, обслуживании автомобиля и помощи на дороге.'),
      text('articles_empty_title', 'Заголовок пустого состояния', 'Здесь пока нет статей'),
      area('articles_empty_text', 'Текст пустого состояния', 'Скоро здесь появятся первые публикации. А пока можно посмотреть доступные услуги.'),
      field('articles_per_page', 'Статей на странице', 'number', { default_value: 6, min: 1, max: 48, step: 1 }),
      text('articles_form_shortcode', 'Шорткод CF7 для детальных статей по умолчанию'),
    ],
    instructions: 'Заполняйте у рубрики со слагом articles. Блоки после списка задаются общим гибким содержимым blocks. Форма архива добавляется макетом split_form, это поле задаёт резервную форму отдельных статей.',
    location: [[{ param: 'taxonomy', operator: '==', value: 'category' }]], active: true,
  },
  {
    key: 'group_article_details', title: 'Статья: первый экран, услуга и форма',
    fields: [
      area('article_lead', 'Лид первого экрана', ''),
      image('article_hero_image', 'Фоновое фото (если отличается от изображения записи)'),
      text('article_author_label', 'Подпись автора (если отличается от автора записи)'),
      field('article_show_contents', 'Показывать оглавление', 'true_false', { default_value: 1, ui: 1, instructions: 'Собирается автоматически из H2 основного контента. Существующие уникальные id заголовков сохраняются.' }),
      field('article_service', 'Связанная услуга', 'post_object', { post_type: ['services'], return_format: 'id', allow_null: 1, multiple: 0, ui: 1 }),
      text('article_form_title', 'Заголовок формы', 'Нужна помощь с автомобилем?'),
      area('article_form_lead', 'Пояснение формы', 'Опишите ситуацию. Оператор уточнит детали и согласует необходимую помощь.'),
      text('article_form_shortcode', 'Шорткод CF7 (заменяет форму из настроек рубрики)'),
    ],
    description: 'Используется для обычных записей рубрики articles и её дочерних рубрик. Название, текст, дата и миниатюра задаются стандартными полями WordPress.',
    location: [[{ param: 'post_type', operator: '==', value: 'post' }]], active: true,
  },
  {
    key: 'group_services_catalog', title: 'Каталог услуг: заголовок',
    fields: [
      text('catalog_title', 'Заголовок H1', 'Все услуги'),
      area('catalog_intro', 'Вводный текст', 'Выездной шиномонтаж и техпомощь в Москве и Московской области. Выберите услугу, чтобы узнать условия обслуживания и посмотреть подробный прайс.'),
    ],
    location: [
      [{ param: 'options_page', operator: '==', value: 'services-archive' }],
      [{ param: 'page_template', operator: '==', value: 'page-services.php' }],
    ],
    active: true,
  },
  { key: 'group_landing_review_caption', title: 'Лендинг: подпись отзывов', fields: [
    field('reviews_count_text', 'Подпись количества отзывов', 'text', { default_value: 'Отзывы клиентов', instructions: 'Готовый текст для первого экрана и блока отзывов. Например: Более 2000 отзывов. Количество автоматически не подсчитывается.' }),
  ], location: [[{ param: 'options_page', operator: '==', value: 'main-option' }]], active: true },
  {
    key: 'group_landing_front_page_flexible', title: 'Блоки страниц и услуг',
    fields: [
      field('blocks', 'Блоки страницы', 'flexible_content', {
        button_label: 'Добавить блок', layouts: Object.fromEntries(layouts.map((item) => [item.key, item])), min: 0, max: 0,
      }),
      text('quick_call_shortcode', 'Шорткод CF7 быстрого вызова'),
      text('calculator_form_shortcode', 'Шорткод CF7 подтверждения калькулятора'),
      field('calculator_service', 'Слаг услуги для калькулятора', 'text', {
        default_value: '', instructions: 'Необязательное поле обычных страниц. На записи services автоматически используется её слаг.',
      }),
    ],
    location: [
      [{ param: 'post_type', operator: '==', value: 'page' }],
      [{ param: 'post_type', operator: '==', value: 'services' }],
      [{ param: 'options_page', operator: '==', value: 'services-archive' }],
      [{ param: 'taxonomy', operator: '==', value: 'category' }],
    ],
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

const writeIfChanged = (filename, content) => {
  const fs = require('fs');
  const target = require('path').join(__dirname, filename);
  if (!fs.existsSync(target) || fs.readFileSync(target, 'utf8').replace(/\r\n/g, '\n') !== content) fs.writeFileSync(target, content);
};
writeIfChanged('Wordpress/wp-content/themes/edemchinim/inc/acf-review-fields.json', JSON.stringify(groups.find(group => group.key === 'group_landing_review_service'), null, 2) + '\n');
writeIfChanged('Wordpress/wp-content/themes/edemchinim/inc/acf-review-caption.json', JSON.stringify(groups.find(group => group.key === 'group_landing_review_caption'), null, 2) + '\n');
const output = `${JSON.stringify(groups, null, 2)}\n`;
if (process.argv.includes('--write')) {
  require('fs').writeFileSync(require('path').join(__dirname, 'acf-landing-import.json'), output);
  writeIfChanged('acf-articles-import.json', JSON.stringify(groups.filter(group => ['group_articles_archive', 'group_article_details', 'group_landing_front_page_flexible'].includes(group.key)), null, 2) + '\n');
} else {
  process.stdout.write(output);
}
