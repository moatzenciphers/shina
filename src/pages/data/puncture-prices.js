// Source: the owner's Excel export. Keep both repair options and their exact prices.
const { records } = require('../../../prices-export.json');
const positions = records.filter(record => record.service_titles.includes('Ремонт проколов') && !record.acf.price_additional);
const headings = [...new Set(positions.flatMap(record => record.acf.price_rows.map(row => row.name)))];

module.exports = {
  headings: ['Диаметр', ...headings],
  rows: positions.map(record => ({
    label: record.post_title,
    prices: headings.map(name => record.acf.price_rows.find(row => row.name === name)?.value ?? '—'),
  })),
};
