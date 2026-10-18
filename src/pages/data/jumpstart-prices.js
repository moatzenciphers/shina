const { records } = require('../../../prices-export.json');
const money = value => `${new Intl.NumberFormat('ru-RU').format(value)} ₽`;

module.exports = {
  headings: ['Услуга', 'Стоимость'],
  rows: records
    .filter(record => record.service_titles.includes('Запуск двигателя') && !record.acf.price_additional)
    .map(record => ({
      label: record.post_title,
      prices: [`${record.acf.price_display_type === 'rub_from' ? 'от ' : ''}${money(record.acf.price_value)}`],
    })),
};
