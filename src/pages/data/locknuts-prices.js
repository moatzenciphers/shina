const { records } = require('../../../prices-export.json');
const formatter = new Intl.NumberFormat('ru-RU');

module.exports = {
  headings: ['Услуга', 'Стоимость'],
  rows: records
    .filter(record => record.service_titles.includes('Снятие секреток') && !record.acf.price_additional)
    .map(record => ({
      label: record.post_title,
      prices: [`от ${formatter.format(record.acf.price_value)}\u00a0₽`],
    })),
};
