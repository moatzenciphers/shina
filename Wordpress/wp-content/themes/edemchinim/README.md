# Едем-чиним

WordPress-тема для сайта выездного шиномонтажа и автомобильной техпомощи.
Контент страниц и настройки сайта управляются через WordPress и ACF.

## Возможности

- Страницы из настраиваемых ACF-блоков.
- Каталог услуг с прайсами.
- Калькулятор вызова мастера с картой и расчётом стоимости.
- Статьи с оглавлением и связанными услугами.
- Отзывы, фотографии работ и контакты.
- Формы Contact Form 7 с отправкой заявок в Telegram.

## Структура

```text
functions.php          точка входа темы
inc/
  Bootstrap.php        регистрация WordPress-хуков
  autoload.php         автозагрузка классов Edemchinim
  Theme/               настройка темы и подключение ресурсов
  Support/             поля, форматирование и вспомогательный вывод
  Content/             услуги, прайсы, статьи, отзывы и блоки страниц
  Rest/                REST-контроллеры и маршруты
  Calculator/          конфигурация калькулятора и работа с картами
  telegram-forms.php   интеграция форм с Telegram
template-parts/
  blocks/              ACF-блоки страниц
  layout/              общая шапка и футер
  forms/               общие формы
  pages/               общие шаблоны служебных страниц
page-*.php             шаблоны страниц WordPress
single-article.php     отдельная статья
category-articles.php  список статей
search.php             результаты поиска
css/                   стили страниц
js/                    клиентские скрипты
img/                   изображения и иконки
languages/             файлы локализации
scripts/               инструменты WP-CLI
tests/                 локальные проверки
```

PHP-классы используют пространство имён `Edemchinim`. Путь класса соответствует
его пространству имён: `Edemchinim\Content\Prices` находится в
`inc/Content/Prices.php`. Автозагрузка работает без Composer.

## Зависимости

- WordPress.
- ACF с поддержкой используемых Flexible Content и Repeater-полей.
- Расширение ACF для поля `yandex-map`.
- Contact Form 7.
- WP-CLI для запуска инструментов из `scripts/`.

Тема использует типы записей `services`, `prices`, `reviews`, `faq` и таксономию
`area`, зарегистрированные в конфигурации сайта вне темы. Данные форм, меню и
ACF-полей хранятся в WordPress. Для отображения карт используется API Яндекс.Карт.

## REST API

| Метод | Маршрут | Данные |
| --- | --- | --- |
| GET | `/wp-json/shina/v1/reviews` | Отзывы |
| GET | `/wp-json/shina/v1/photos` | Фотографии работ |
| GET | `/wp-json/shina/v1/faq` | Вопросы и ответы |

## Проверки

Из каталога темы:

```bash
php tests/theme-contract.php
php tests/theme-contract.php --without-acf
php tests/template-render-check.php
```

Все проверки также доступны через `composer test:compat`. Они используют
заглушки WordPress и тестовые данные, без подключения к рабочей базе и отправки заявок.
