const { records } = require('../../../prices-export.json');

module.exports = {
  headings: ['Услуга', 'Стоимость'],
  rows: records
    .filter(record => record.service_titles.includes('Доставка топлива') && !record.acf.price_additional)
    .map(record => ({ label: record.post_title, prices: [record.acf.price_value] })),
};
