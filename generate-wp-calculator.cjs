const fs = require('node:fs');
const html = fs.readFileSync('dist/index.html', 'utf8');
const start = html.indexOf('<div class="app" id="app"');
if (start < 0) throw new Error('Calculator root not found in dist/index.html');
const tags = /<\/?div\b[^>]*>/g;
let depth = 0;
let end = -1;
for (const match of html.slice(start).matchAll(tags)) {
  const tag = match[0];
  depth += tag.startsWith('</') ? -1 : 1;
  if (depth === 0) {
    end = start + match.index + tag.length;
    break;
  }
}
if (end < 0) throw new Error('Calculator root is not closed');
const markup = html.slice(start, end).replace(/(src|href)="img\/([^\"]+)"/g, (_all, attr, file) =>
  `${attr}="<?php echo esc_url(get_template_directory_uri() . '/img/${file}'); ?>"`
).replace(/<form class="order-confirm"[^>]*>[\s\S]*?<\/form>/, '<?php edemchinim_landing_cf7(function_exists("get_field") ? get_field("calculator_form_shortcode", get_queried_object_id()) : ""); ?>');
process.stdout.write(`<?php /** Calculator markup exported from src/pages/index.pug. */ ?>\n${markup}\n`);
