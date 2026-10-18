const { records } = require('../../../prices-export.json');
const money = value => `от ${new Intl.NumberFormat('ru-RU').format(value)} ₽`;

module.exports = {
  headings: ['Диаметр', 'Стоимость'],
  rows: records
    .filter(record => record.service_titles.includes('Ремонт боковых порезов') && !record.acf.price_additional)
    .map(record => ({ label: record.post_title, prices: [money(record.acf.price_value)] })),
};
