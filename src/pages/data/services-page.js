const { records } = require('../../../prices-export.json');
const formatter = new Intl.NumberFormat('ru-RU');

const priceFrom = (title, { item = false, position } = {}) => {
  const entries = records.filter(record => record.service_titles.includes(title) && !record.acf.price_additional && (position ? record.post_title === position : record.post_title !== 'Доставка'));
  const amounts = entries.flatMap(record => record.acf.price_type === 'table' ? record.acf.price_rows.map(row => row.value) : [record.acf.price_value]).filter(value => typeof value === 'number');
  return amounts.length ? `от ${formatter.format(Math.min(...amounts))}\u00a0₽${item ? '/шт.' : ''}` : '';
};

const servicesPage = {
  meta: {
    title: 'Все услуги: шиномонтаж и техпомощь в Москве | Едем-чиним',
    description: 'Выездной шиномонтаж, помощь на дороге и заправка автокондиционера в Москве и МО. Все услуги, начальные цены и подробности обслуживания на отдельных страницах.',
  },
  groups: [
    {
      id: 'tire-services', title: 'Шиномонтаж',
      description: 'Сезонное обслуживание, работа с шинами и дисками, хранение комплекта.',
      items: [
        { title: 'Сезонная замена шин', href: './seasonal.html', description: 'Плановая смена летнего или зимнего комплекта у автомобиля.', price: priceFrom('Сезонная замена шин') },
        { title: 'Шиномонтажные работы', href: './tire-work.html', description: 'Обслуживание одного колеса или нужного количества колёс.', price: priceFrom('Шиномонтажные работы', { item: true }) },
        { title: 'Ремонт проколов', href: './puncture.html', description: 'Осмотр повреждения и выбор подходящего варианта ремонта.', price: priceFrom('Ремонт проколов') },
        { title: 'Ремонт боковых порезов', href: './sidewall.html', description: 'Оценка боковины шины и возможности восстановления.', price: priceFrom('Ремонт боковых порезов') },
        { title: 'Правка дисков', href: './wheels.html', description: 'Осмотр деформации и согласование допустимых работ с диском.', price: priceFrom('Правка дисков') },
        { title: 'Снятие секреток', href: './locknuts.html', description: 'Помощь, если ключ потерян или секретный крепёж не откручивается.', price: priceFrom('Снятие секреток', { item: true }) },
        { title: 'Сезонное хранение шин', href: './storage.html', description: 'Хранение шин без дисков и колёс в сборе до следующего сезона.', price: priceFrom('Сезонное хранение шин') },
      ],
    },
    {
      id: 'roadside-services', title: 'Техпомощь на дороге',
      description: 'Запуск двигателя, обслуживание аккумулятора и автокондиционера, отогрев и подвоз топлива.',
      items: [
        { title: 'Запуск двигателя', href: './jumpstart.html', description: 'Помощь с запуском при разряженном аккумуляторе.', price: priceFrom('Запуск двигателя') },
        { title: 'Замена аккумулятора', href: './battery.html', description: 'Подбор подходящего аккумулятора, доставка и замена.', price: priceFrom('Замена аккумулятора') },
        { title: 'Отогрев автомобиля', href: './warming.html', description: 'Выезд к замёрзшей машине и оценка необходимой помощи.', price: priceFrom('Отогрев автомобиля') },
        { title: 'Доставка топлива', href: './fuel.html', description: 'Подвоз бензина или дизеля к месту остановки.', price: `${formatter.format(records.find(record => record.service_titles.includes('Доставка топлива') && !record.acf.price_additional).acf.price_value)}\u00a0₽`, note: 'Топливо оплачивается отдельно' },
        { title: 'Заправка автокондиционера', href: './conditioner.html', description: 'Проверка состояния системы и согласование необходимого обслуживания.', price: priceFrom('Заправка автокондиционера', { position: 'Заправка фреоном 134А до 400 г' }) },
      ],
    },
  ],
  columnsList: {
    eyebrow: 'ПОДБЕРИТЕ НУЖНОЕ ОБСЛУЖИВАНИЕ',
    title: 'Как выбрать услугу',
    columns: [
      {
        title: 'Плановые работы',
        items: [
          'Для смены всего летнего или зимнего комплекта выбирайте сезонную замену. Если нужно обслужить отдельное колесо или переставить шину на диск — шиномонтажные работы.',
          'Снятый комплект можно передать на хранение. Заправку кондиционера стоит обсудить, если охлаждение стало слабее: сначала нужно оценить состояние системы.',
        ],
      },
      {
        title: 'Помощь при неисправности',
        items: [
          'При повреждении колеса важен характер дефекта: прокол, боковой порез или деформация диска. Если причина неизвестна, опишите симптомы — мастер определит нужные действия после осмотра.',
          'Если двигатель не запускается, сообщите, что происходило перед остановкой. Разряженный аккумулятор, мороз и отсутствие топлива требуют разной помощи; выбирать замену деталей заранее не нужно.',
        ],
      },
    ],
  },
  form: {
    title: 'Не знаете, какая услуга нужна?',
    lead: 'Опишите проблему с автомобилем. Оператор поможет определить нужную помощь и согласует выезд.',
    placeholder: 'Что произошло с автомобилем и какая помощь требуется?',
  },
};

servicesPage.cards = servicesPage.groups.flatMap(group => group.items.map(card => ({
  ...card,
  id: `catalog-${card.href.replace('./', '').replace('.html', '')}`,
  terms: `${group.id === 'tire-services' ? 'tire' : 'roadside'}${['./seasonal.html', './puncture.html', './jumpstart.html', './conditioner.html'].includes(card.href) ? ' popular' : ''}`,
})));

module.exports = servicesPage;
