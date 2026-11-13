const content = require('../../../Wordpress/wp-content/themes/edemchinim/inc/Content/about-content.json');

module.exports = {
  ...content,
  hero: { ...content.hero, image: require('../../img/visit-home.jpg') },
  mediaRows: {
    ...content.mediaRows,
    rows: content.mediaRows.rows.map((row, index) => ({
      ...row,
      image: index === 0 ? require('../../img/visit-evening.jpg') : require('../../img/visit-puncture.jpg'),
    })),
  },
};
