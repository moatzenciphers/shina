const content = require('../content/seasonal-preparation.html').replace('__ARTICLE_IMAGE__', require('../../img/visit-puncture.jpg'));
const wordCount = content.replace(/<[^>]*>/g, ' ').trim().split(/\s+/).length;
module.exports = {
  title: 'Как подготовить колёса к сезонной замене',
  description: 'Комплект шин, крепёж, осмотр после хранения и запись на обслуживание: что подготовить перед сезонной заменой колёс.',
  category: 'Шины и диски', author: 'Едем-чиним', date: '2026-10-16', dateLabel: '16 октября 2026',
  readingLabel: `Около ${Math.max(1, Math.ceil(wordCount / 180))} мин на чтение`,
  image: require('../../img/visit-home.jpg'), content,
  contents: [
    { id: 'set-type', title: 'Комплект на дисках или без' },
    { id: 'inspection', title: 'Осмотр после хранения' },
    { id: 'equipment', title: 'Крепёж и секретки' },
    { id: 'appointment', title: 'Подготовка к выезду' },
    { id: 'after-service', title: 'После замены' },
  ],
  relatedService: {
    eyebrow: 'УСЛУГА ПО ТЕМЕ СТАТЬИ', title: 'Сезонная замена шин с выездом',
    text: 'Сменим комплект у дома, офиса или на парковке в Москве и МО. Посмотрите варианты обслуживания и прайс для вашего автомобиля.',
    href: './seasonal.html', button: 'Подробнее об услуге',
  },
  form: {
    title: 'Нужна помощь с сезонной заменой?',
    lead: 'Сообщите автомобиль, размер колёс и где находится комплект. Оператор уточнит состав работ и согласует выезд.',
    placeholder: 'Например: Kia Rio, R16. Зимние шины без дисков, нужна замена у дома.',
  },
};
