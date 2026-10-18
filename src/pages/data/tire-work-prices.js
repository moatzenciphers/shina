const { records } = require('../../../prices-export.json');
const positions = records.filter(record => record.service_titles.includes('Шиномонтажные работы'));
const formatter = new Intl.NumberFormat('ru-RU');
const additions = positions.filter(record => record.acf.price_additional);

module.exports = {
  headings: ['Диаметр', 'За одно колесо'],
  rows: positions.filter(record => !record.acf.price_additional).map(record => ({
    label: record.post_title,
    prices: [`${formatter.format(record.acf.price_value)}\u00a0₽/шт.`],
  })),
  groups: additions.length ? [{
    title: 'Доплаты',
    rows: additions.map(record => ({ label: record.post_title, value: `+${record.acf.price_value}%` })),
    note: 'Применение доплат и итоговый расчёт согласуем с учётом автомобиля и особенностей шин.',
  }] : [],
};
