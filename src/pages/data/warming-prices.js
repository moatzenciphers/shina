const { records } = require('../../../prices-export.json');

module.exports = {
  headings: ['Услуга', 'Стоимость'],
  rows: records
    .filter(record => record.service_titles.includes('Отогрев автомобиля') && !record.acf.price_additional)
    .map(record => ({
      label: record.post_title,
      prices: [`от ${new Intl.NumberFormat('ru-RU').format(record.acf.price_value)} ₽`],
    })),
};
