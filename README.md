# ⚡ YouTubeZero

**Библиотека для получения статистики видео с YouTube на PHP.**

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-777bb4.svg?style=flat-square)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)
[![Zero Dependencies](https://img.shields.io/badge/dependencies-zero-success?style=flat-square)]()

## 🚀 Особенности

* Никаких `vendor`, Guzzle или Google Client. Работает на нативном cURL.
* Понимает любые ссылки: `youtube.com/watch`, `youtu.be` (короткие), `shorts`, и даже `embed`.
* Работает через официальный API v3. Никаких банов за парсинг HTML.
* Выбрасывает понятные исключения, возвращает чистый массив данных без мусора.
* Подключил и получил данные за 2 строчки кода.

---

## 📦 Установка

Скачайте папку `YouTubeDataZero` и подключите к проекту главный файл:  
`require_once 'path/to/YouTubeDataZero/YouTubeDataZero.php';`

---

## 🛠 Использование

1. Инициализация
```php
use Wakanda\YouTubeDataZero\YouTubeDataZero;

$apiKey = 'YOUR_GOOGLE_API_KEY'; // Ключ от YouTube Data API v3
$yt = new YouTubeDataZero($apiKey);
```

2. Получение данных
```php
try {
    $url = 'https://www.youtube.com/watch?v=syPZhuPRSJg&t=281s'; // Пример ссылки
    
    // Получаем массив с информацией
    $info = $yt->getVideoInfo($url);

    if ($info) {
        echo "<h1>{$info['title']}</h1>";
        echo "<img src='{$info['thumbnail']}' style='border-radius: 8px'>";
        echo "<p>Просмотров: " . number_format($info['views']) . "</p>";
        echo "<p>Канал: {$info['channel']}</p>";
    } else {
        echo "Видео не найдено (или удалено).";
    }

} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}
```

---

## 🧩 Структура данных

Метод `getVideoInfo()` возвращает удобный ассоциативный массив. Библиотека сама выбирает картинку наилучшего качества (`maxres` или `high`).

```php
[
    'id'           => 'dQw4w9WgXcQ',
    'title'        => 'Rick Astley - Never Gonna Give You Up',
    'description'  => 'The official video for...',
    'channel'      => 'Rick Astley',
    'views'        => 1450300200, // int
    'likes'        => 17000000,   // int
    'thumbnail'    => 'https://i.ytimg.com/vi/dQw4w9WgXcQ/maxresdefault.jpg',
    'published_at' => '2009-10-25T06:57:33Z',
    'duration_iso' => 'PT3M33S',  // string
    'duration'     => '00:03:33'  // string
]
```

---

## 🔑 Как получить API Key

Для работы требуется бесплатный ключ **YouTube Data API v3**:

1. Зайдите в [Google Cloud Console](https://console.cloud.google.com/).
2. Создайте новый проект.
3. В меню "API и сервисы" -> "Библиотека" найдите и включите **YouTube Data API v3**.
4. В меню "Учетные данные" (Credentials) создайте **API Key**.

---

## ❓ Частые проблемы (FAQ)

**В: Ошибка `cURL Error: SSL certificate problem`.**  
О: Это частая проблема локальных серверов (OpenServer=). Библиотека пытается отключить строгую проверку SSL, но если ошибка сохраняется, убедитесь, что у вас обновлен `cacert.pem` в настройках PHP, или используйте библиотеку на реальном хостинге.

**В: Есть ли лимиты?**  
О: Да, у Google есть бесплатная квота (10 000 единиц в день). Один запрос на получение информации о видео стоит **1 единицу**. Этого хватит на 10 000 запросов в сутки бесплатно.

---

## 📄 Лицензия

MIT License. Делайте с кодом что хотите, это Open Source.
