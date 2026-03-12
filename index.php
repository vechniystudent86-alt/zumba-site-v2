<?php
/**
 * Zumba Site - Главная страница
 */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title>Zumba Красносельский | Александра Мельникова — Танцевальный фитнес в Санкт-Петербурге</title>
    <meta name="title" content="Zumba Красносельский | Александра Мельникова — Танцевальный фитнес в Санкт-Петербурге">
    <meta name="description" content="Zumba фитнес в Санкт-Петербурге с Александрой Мельниковой. Групповые тренировки в Красносельском районе. Клуб Радуга на Маршала Захарова 20Д. Пробная тренировка 500₽!">
    <meta name="keywords" content="Zumba, зумба, фитнес, танцы, Санкт-Петербург, Красносельский район, Маршала Захарова, тренировки, Александра Мельникова, Радуга, фитнес клуб, групповые тренировки, танцевальный фитнес">
    <meta name="author" content="Александра Мельникова">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://zumba-spb.ru/">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://zumba-spb.ru/">
    <meta property="og:title" content="Zumba Красносельский | Александра Мельникова">
    <meta property="og:description" content="Zumba фитнес в Санкт-Петербурге. Групповые тренировки в Красносельском районе. Пробная тренировка 500₽!">
    <meta property="og:image" content="https://zumba-spb.ru/hero-photo.png">
    <meta property="og:site_name" content="Zumba Красносельский">
    <meta property="og:locale" content="ru_RU">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://zumba-spb.ru/">
    <meta name="twitter:title" content="Zumba Красносельский | Александра Мельникова">
    <meta name="twitter:description" content="Zumba фитнес в Санкт-Петербурге. Групповые тренировки в Красносельском районе. Пробная тренировка 500₽!">
    <meta name="twitter:image" content="https://zumba-spb.ru/hero-photo.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="manifest" href="site.webmanifest">
    <meta name="theme-color" content="#FF2D75">
    
    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://api-maps.yandex.ru">
    <link rel="preconnect" href="https://mc.yandex.ru">
    
    <!-- Preload critical assets -->
    <link rel="preload" href="icon-192x192.png" as="image">
    <link rel="preload" href="icon-512x512.png" as="image">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="preload" href="styles.min.css?v=2.1" as="style">
    <link rel="preload" href="responsive.min.css?v=2.1" as="style">
    <link rel="stylesheet" href="styles.min.css?v=2.1">
    <link rel="stylesheet" href="responsive.min.css?v=2.1">
    
    <!-- Yandex.Metrika counter -->
    <?php
    // Отключаем Метрику при локальной разработке
    $isLocalhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost'], true) 
        || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false;
    ?>
    <?php if (!$isLocalhost): ?>
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){
            m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
        })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=106970869', 'ym');

        ym(106970869, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/106970869" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <?php endif; ?>
    <!-- /Yandex.Metrika counter -->
    
    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SportsActivityLocation",
        "name": "Zumba Красносельский | Александра Мельникова",
        "description": "Zumba фитнес в Санкт-Петербурге с Александрой Мельниковой. Групповые тренировки в Красносельском районе.",
        "url": "https://zumba-spb.ru",
        "telephone": "+79218925157",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "ул. Маршала Захарова, 20Д",
            "addressLocality": "Санкт-Петербург",
            "addressRegion": "Санкт-Петербург",
            "postalCode": "198332",
            "addressCountry": "RU"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": 59.837058,
            "longitude": 30.242849
        },
        "hasMap": "https://yandex.ru/maps/org/zumba_u_zaliva/99077668985?si=0pxbzfcp104m4ggn3bjbnyrv3m",
        "openingHoursSpecification": [
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": "Monday",
                "opens": "19:45",
                "closes": "21:00"
            },
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": "Wednesday",
                "opens": "20:00",
                "closes": "21:15"
            },
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": "Friday",
                "opens": "10:30",
                "closes": "11:45"
            }
        ],
        "priceRange": "500₽",
        "image": "https://zumba-spb.ru/hero-photo.png",
        "sameAs": [
            "https://t.me/ZumbaYugozapadSPB",
            "https://vk.ru/radugaclub20"
        ]
    }
    </script>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "Александра Мельникова",
        "jobTitle": "Инструктор Zumba",
        "description": "Сертифицированный инструктор Zumba и Zumba Gold (LDFA) в Красносельском районе Санкт-Петербурга. Педагог-хореограф с высшим образованием.",
        "url": "https://zumba-spb.ru",
        "image": "https://zumba-spb.ru/hero-photo.png",
        "sameAs": [
            "https://t.me/ZumbaYugozapadSPB",
            "https://vk.ru/radugaclub20"
        ],
        "knowsAbout": ["Zumba", "Zumba Gold", "Фитнес", "Танцы", "Хореография"],
        "hasOccupation": {
            "@type": "Occupation",
            "name": "Инструктор Zumba",
            "occupationLocation": {
                "@type": "City",
                "name": "Санкт-Петербург"
            }
        },
        "alumniOf": {
            "@type": "EducationalOrganization",
            "description": "Высшее педагогическое образование — педагог-хореограф, руководитель хореографического коллектива"
        },
        "hasCredential": {
            "@type": "EducationalOccupationalCredential",
            "description": "Сертифицированный инструктор компании LDFA программы Zumba и Zumba Gold"
        }
    }
    </script>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "ExerciseGym",
        "name": "Фитнес-клуб Радуга",
        "description": "Фитнес-клуб в Красносельском районе Санкт-Петербурга. Групповые тренировки Zumba.",
        "url": "https://zumba-spb.ru",
        "telephone": "+79218925157",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "ул. Маршала Захарова, 20Д",
            "addressLocality": "Санкт-Петербург",
            "addressRegion": "Санкт-Петербург",
            "postalCode": "198332",
            "addressCountry": "RU"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": 59.837058,
            "longitude": 30.242849
        },
        "amenityFeature": [
            {
                "@type": "LocationFeatureSpecification",
                "name": "Бесплатная парковка",
                "value": true
            },
            {
                "@type": "LocationFeatureSpecification",
                "name": "Групповые тренировки",
                "value": true
            }
        ],
        "image": "https://zumba-spb.ru/hero-photo.png"
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Главная",
                "item": "https://zumba-spb.ru/"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "О тренере",
                "item": "https://zumba-spb.ru/#about"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": "Программы",
                "item": "https://zumba-spb.ru/#programs"
            },
            {
                "@type": "ListItem",
                "position": 4,
                "name": "Отзывы",
                "item": "https://zumba-spb.ru/#reviews"
            },
            {
                "@type": "ListItem",
                "position": 5,
                "name": "Контакты",
                "item": "https://zumba-spb.ru/#contact"
            }
        ]
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Что взять с собой на тренировку?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Возьмите с собой удобную форму, сменные кроссовки, бутылку негазированной воды. Приходите за 7-10 мин до начала тренировки, чтобы успеть переодеться."
                }
            },
            {
                "@type": "Question",
                "name": "Нужна ли подготовка для занятий зумба?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Подготовка не нужна. На занятия приходят люди с разным уровнем подготовки, иногда и вовсе без нее. Главное — желание, а я вам помогу."
                }
            },
            {
                "@type": "Question",
                "name": "Сколько калорий сжигается за тренировку?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "За одну тренировку зумба сжигается до 500 калорий."
                }
            },
            {
                "@type": "Question",
                "name": "Можно ли беременным заниматься?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Да, но только после консультации с врачом! Для беременных рекомендую Zumba Gold — щадящая программа без прыжков и высокой нагрузки."
                }
            },
            {
                "@type": "Question",
                "name": "Как записаться на пробное занятие?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Заполните форму на странице, позвоните по телефону +7 (921) 892-51-57 или напишите в Telegram. Пробная тренировка стоит 500₽ и длится 55 минут."
                }
            },
            {
                "@type": "Question",
                "name": "Есть ли абонементы?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Да, действуют абонементы: 8 занятий — 4800₽, 6 занятий — 3900₽, 4 занятия — 2800₽. Разовое посещение — 750₽. Пробная тренировка — 500₽."
                }
            }
        ]
    }
    </script>
</head>
<body>
    <!-- Skip to Content Link -->
    <a href="#home" class="skip-link">Перейти к основному контенту</a>

    <!-- Custom Cursor -->
    <div class="cursor" aria-hidden="true"></div>
    <div class="cursor-follower" aria-hidden="true"></div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">
                <img src="logo.png" alt="AM ZUMBA FIT" class="logo-image" width="180" height="90">
            </a>
            <ul class="nav-menu">
                <li><a href="#home">Главная</a></li>
                <li><a href="#about">О тренере</a></li>
                <li><a href="#programs">Программы</a></li>
                <li><a href="#reviews">Отзывы</a></li>
                <li><a href="#faq">Вопросы</a></li>
                <li><a href="#contact">Контакты</a></li>
                <li><a href="#location">Как найти</a></li>
            </ul>
            <button class="nav-toggle" aria-label="Меню">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-bg-animation">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>
            <div class="floating-shape shape-4"></div>
        </div>
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    <span class="title-line">Александра</span>
                    <span class="title-line highlight">Мельникова</span>
                </h1>
                <p class="hero-subtitle">Профессиональный Zumba тренер • Сертифицированный инструктор • Танцуй. Сияй. Вдохновляй.</p>
                <div class="hero-cta">
                    <a href="#contact" class="btn btn-primary">Записаться на тренировку</a>
                    <a href="#programs" class="btn btn-secondary">Узнать больше</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="image-container">
                    <div class="image-frame">
                        <picture>
                            <source srcset="hero-photo-320.webp 320w, hero-photo-480.webp 480w, hero-photo-640.webp 640w, hero-photo-800.webp 800w" sizes="(max-width: 480px) 280px, (max-width: 768px) 350px, 480px" type="image/webp">
                            <source srcset="hero-photo-320.png 320w, hero-photo-480.png 480w, hero-photo-640.png 640w, hero-photo-800.png 800w" sizes="(max-width: 480px) 280px, (max-width: 768px) 350px, 480px" type="image/png">
                            <img src="hero-photo.png?v=3" alt="Александра Мельникова — профессиональный инструктор Zumba в Санкт-Петербурге" width="480" height="480" loading="eager" fetchpriority="high">
                        </picture>
                    </div>
                    <div class="image-ring ring-1"></div>
                    <div class="image-ring ring-2"></div>
                    <div class="image-ring ring-3"></div>
                </div>
            </div>
        </div>
        <div class="scroll-indicator">
            <span>Листайте вниз</span>
            <div class="scroll-arrow"></div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">О тренере</h2>
                <div class="title-decoration"></div>
            </div>
            <div class="about-content">
                <div class="about-image">
                    <div class="image-card">
                        <div class="image-overlay"></div>
                    </div>
                </div>
                <div class="about-text">
                    <h3>Всем привет! ✋👋</h3>
                    <p>Рада, что заглянули ко мне! Меня зовут Александра Мельникова, и я сертифицированный инструктор танцевальных тренировок Zumba в Красносельском районе Санкт-Петербурга!</p>
                    <p><strong>Образование:</strong> высшее педагогическое — педагог-хореограф, руководитель хореографического коллектива.</p>
                    <p>Сертифицированный инструктор компании LDFA программы Zumba и Zumba Gold.</p>
                    <p>Регулярно прохожу обучения, повышаю свои профессиональные навыки.</p>
                    <p>Каждая моя тренировка — это праздник энергии, где вы не просто сжигаете калории, а заряжаетесь позитивом, встречаете новых друзей и влюбляетесь в танец заново.</p>
                    <div class="achievements">
                        <div class="achievement-item">
                            <span class="achievement-icon">🎓</span>
                            <span>Педагог-хореограф, руководитель хореографического коллектива</span>
                        </div>
                        <div class="achievement-item">
                            <span class="achievement-icon">⭐</span>
                            <span>Сертифицированный инструктор LDFA (Zumba и Zumba Gold)</span>
                        </div>
                        <div class="achievement-item">
                            <span class="achievement-icon">💃</span>
                            <span>Регулярно повышаю профессиональные навыки</span>
                        </div>
                    </div>
                    <div class="stats">
                        <div class="stat-item">
                            <span class="stat-number" data-target="5000">5000+</span>
                            <span class="stat-label">Довольных клиентов</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number" data-target="15">15+</span>
                            <span class="stat-label">Сертификатов</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number" data-target="7">7+</span>
                            <span class="stat-label">Лет опыта</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Programs Section -->
    <section id="programs" class="programs">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Программы тренировок</h2>
                <div class="title-decoration"></div>
            </div>
            <div class="programs-grid">
                <div class="program-card" data-tilt>
                    <div class="card-icon">
                        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="2"/>
                            <path d="M32 16 L32 32 L44 40" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3>Zumba Classic</h3>
                    <p>Классическая программа Zumba с латиноамериканскими ритмами: сальса, меренге, кумбия и реггетон.</p>
                    <ul class="program-features">
                        <li>60 минут</li>
                        <li>Средняя интенсивность</li>
                        <li>До 600 ккал</li>
                    </ul>
                    <a href="#contact" class="card-link">Записаться →</a>
                </div>
                <div class="program-card featured" data-tilt>
                    <div class="featured-badge">Популярное</div>
                    <div class="card-icon">
                        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M32 8 L38 24 L56 24 L42 36 L48 54 L32 44 L16 54 L22 36 L8 24 L26 24 Z" stroke="currentColor" stroke-width="2" fill="none"/>
                        </svg>
                    </div>
                    <h3>Zumba Gold</h3>
                    <p>Щадящая программа для начинающих и людей старшего возраста. Мягкие движения без нагрузки на суставы.</p>
                    <ul class="program-features">
                        <li>45 минут</li>
                        <li>Низкая интенсивность</li>
                        <li>До 350 ккал</li>
                    </ul>
                    <a href="#contact" class="card-link">Записаться →</a>
                </div>
            </div>
            
            <!-- CTA After Programs -->
            <div class="section-cta">
                <h3>Готовы начать?</h3>
                <p>Запишитесь на пробную тренировку уже сегодня!</p>
                <a href="#contact" class="btn btn-primary">Записаться сейчас</a>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section id="reviews" class="reviews">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Отзывы клиентов</h2>
                <div class="title-decoration"></div>
            </div>
            <div class="reviews-slider-container">
                <div class="reviews-slider">
                    <div class="reviews-track">
                        <div class="review-card" data-index="0">
                            <div class="review-stars">★★★★★</div>
                            <p class="review-text">«Приходя к Александре на тренировку, я всегда отдыхаю душой и телом. Я люблю танцевальный фитнес, это всегда заряд хорошего настроения и Александра от души делится своей положительной энергией со своими студентами. От души рекомендую всем посетить тренировку!»</p>
                            <div class="review-author">
                                <div class="author-avatar">СА</div>
                                <div class="author-info">
                                    <span class="author-name">Светлана Анатольевна</span>
                                    <span class="author-role">VK Отзывы</span>
                                </div>
                            </div>
                        </div>
                        <div class="review-card" data-index="1">
                            <div class="review-stars">★★★★★</div>
                            <p class="review-text">«Классные тренировки, очень хорошо переключают от ежедневной рутины! Супер пупер! Это обязательно нужно попробовать!»</p>
                            <div class="review-author">
                                <div class="author-avatar">МФ</div>
                                <div class="author-info">
                                    <span class="author-name">Мария Фролович</span>
                                    <span class="author-role">VK Отзывы</span>
                                </div>
                            </div>
                        </div>
                        <div class="review-card" data-index="2">
                            <div class="review-stars">★★★★★</div>
                            <p class="review-text">«Зумба и Саша возвращают меня к жизни даже в самые сложные моменты! Огонёк этой прекрасной души может и согреть, и взбодрить! Выхожу всегда мокрой и с песней в голове! Рекомендую от всего сердца!»</p>
                            <div class="review-author">
                                <div class="author-avatar">НЗ</div>
                                <div class="author-info">
                                    <span class="author-name">Надежда Земская</span>
                                    <span class="author-role">VK Отзывы</span>
                                </div>
                            </div>
                        </div>
                        <div class="review-card" data-index="3">
                            <div class="review-stars">★★★★★</div>
                            <p class="review-text">«Супер профессионал своего дела!»</p>
                            <div class="review-author">
                                <div class="author-avatar">ВМ</div>
                                <div class="author-info">
                                    <span class="author-name">Виктория Мороз</span>
                                    <span class="author-role">VK Отзывы</span>
                                </div>
                            </div>
                        </div>
                        <div class="review-card" data-index="4">
                            <div class="review-stars">★★★★★</div>
                            <p class="review-text">«Спасибо, Саша, за тот заряд энергии, который я получаю после каждой тренировки!»</p>
                            <div class="review-author">
                                <div class="author-avatar">ОК</div>
                                <div class="author-info">
                                    <span class="author-name">Ольга Косолапова</span>
                                    <span class="author-role">VK Отзывы</span>
                                </div>
                            </div>
                        </div>
                        <div class="review-card" data-index="5">
                            <div class="review-stars">★★★★★</div>
                            <p class="review-text">«Тренировки очень нравятся, заряжают супер энергией. Весело и непринужденно можно привести себя в форму. Рекомендую!»</p>
                            <div class="review-author">
                                <div class="author-avatar">АЧ</div>
                                <div class="author-info">
                                    <span class="author-name">Анна Чернышева</span>
                                    <span class="author-role">VK Отзывы</span>
                                </div>
                            </div>
                        </div>
                        <div class="review-card" data-index="6">
                            <div class="review-stars">★★★★★</div>
                            <p class="review-text">«Замечательные тренировки, замечательная Саша! С удовольствием бегу на занятия. Время пролетает незаметно в кругу нашей уже такой большой зумбовской семьи! Приходите и не пожалеете!»</p>
                            <div class="review-author">
                                <div class="author-avatar">ЕП</div>
                                <div class="author-info">
                                    <span class="author-name">Екатерина Петрова</span>
                                    <span class="author-role">VK Отзывы</span>
                                </div>
                            </div>
                        </div>
                        <div class="review-card" data-index="7">
                            <div class="review-stars">★★★★★</div>
                            <p class="review-text">«Рекомендую всем! Зажигательные тренировки поднимают настроение, разминают каждую мышцу, ну а тренер просто умничка! Весело и классно!»</p>
                            <div class="review-author">
                                <div class="author-avatar">ОЖ</div>
                                <div class="author-info">
                                    <span class="author-name">Оксана Жарикова</span>
                                    <span class="author-role">VK Отзывы</span>
                                </div>
                            </div>
                        </div>
                        <div class="review-card" data-index="8">
                            <div class="review-stars">★★★★★</div>
                            <p class="review-text">«На тренировку, как на праздник! Спасибо Сашуле за эмоции и зажигательные танцы!»</p>
                            <div class="review-author">
                                <div class="author-avatar">ИБ</div>
                                <div class="author-info">
                                    <span class="author-name">Ирина Борисова</span>
                                    <span class="author-role">VK Отзывы</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slider-nav">
                    <button class="slider-btn slider-prev" aria-label="Предыдущий отзыв">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </button>
                    <div class="slider-dots"></div>
                    <button class="slider-btn slider-next" aria-label="Следующий отзыв">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Записаться на тренировку</h2>
                <div class="title-decoration"></div>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <h3>Свяжитесь со мной</h3>
                    <p>Готовы начать свой фитнес-путь? Запишитесь на первую тренировку уже сегодня!</p>
                    <div class="contact-details">
                        <div class="contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <span>+7 (921) 892-51-57</span>
                        </div>
                        <div class="contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Санкт-Петербург, ул. Маршала Захарова, 20Д (Зумба у залива)</span>
                        </div>
                    </div>
                    <div class="schedule-info">
                        <?php
                            $contentFile = __DIR__ . '/data/content.json';
                            $contentData = file_exists($contentFile) ? json_decode(file_get_contents($contentFile), true) : [];
                            $pricesData = $contentData['prices'] ?? [];
                            $scheduleData = $contentData['schedule'] ?? [];
                        ?>
                        <h4>Абонементы:</h4>
                        <ul class="schedule-list">
                            <li><span><?= htmlspecialchars($pricesData['8_lessons']['name'] ?? '8 занятий') ?></span> <span><?= htmlspecialchars($pricesData['8_lessons']['price'] ?? '4800₽') ?></span></li>
                            <li><span><?= htmlspecialchars($pricesData['6_lessons']['name'] ?? '6 занятий') ?></span> <span><?= htmlspecialchars($pricesData['6_lessons']['price'] ?? '3900₽') ?></span></li>
                            <li><span><?= htmlspecialchars($pricesData['4_lessons']['name'] ?? '4 занятия') ?></span> <span><?= htmlspecialchars($pricesData['4_lessons']['price'] ?? '2800₽') ?></span></li>
                            <li><span><?= htmlspecialchars($pricesData['single']['name'] ?? 'Разовое посещение') ?></span> <span><?= htmlspecialchars($pricesData['single']['price'] ?? '750₽') ?></span></li>
                            <li><span><?= htmlspecialchars($pricesData['trial']['name'] ?? 'Пробная тренировка') ?></span> <span><?= htmlspecialchars($pricesData['trial']['price'] ?? '500₽') ?></span></li>
                        </ul>
                        <h4>Расписание:</h4>
                        <ul class="schedule-list">
                            <?php if (!empty($scheduleData)): ?>
                                <?php foreach ($scheduleData as $item): ?>
                                    <li><span><?= htmlspecialchars($item['day']) ?></span> <span><?= htmlspecialchars($item['time_and_program']) ?></span></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><span>Пн</span> <span>19:45 - Zumba fitness</span></li>
                                <li><span>Ср</span> <span>20:00 - Zumba fitness</span></li>
                                <li><span>Пт</span> <span>10:30 - Zumba fitness</span></li>
                                <li><span>Вс</span> <span>12:00 - Zumba Gold</span></li>
                            <?php endif; ?>
                        </ul>
                        <p class="cta-text">Не откладывай жизнь на завтра! Запишись на пробную тренировку!</p>
                    </div>
                    <div class="social-links">
                        <a href="https://t.me/ZumbaYugozapadSPB" class="social-link" aria-label="Telegram" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 2L11 13"/>
                                <path d="M22 2L15 22L11 13L2 9L22 2Z"/>
                            </svg>
                        </a>
                        <a href="https://vk.ru/radugaclub20" class="social-link" aria-label="ВКонтакте" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M15.073 3H8.937C5.009 3 3 4.988 3 8.927v6.146C3 18.991 4.998 21 8.927 21h6.146c3.929 0 5.927-1.998 5.927-5.927V8.927C21 4.998 19.002 3 15.073 3zM17.9 13.54c.575.54.986.995.986 1.68v.058c0 .695-.492 1.04-1.04 1.04h-1.544c-1.17 0-1.705-.623-2.024-1.26-.453-.912-.99-1.307-1.432-1.307-.316 0-.585.165-.585.554v1.32c0 .402-.13.644-.932.644-1.37 0-2.95-.83-4.034-2.373-1.64-2.295-2.89-5.01-2.89-5.453 0-.375.145-.54.685-.54h1.75c.505 0 .74.224.943.655 1.03 2.254 2.765 4.22 3.46 4.22.263 0 .385-.12.385-.534v-1.92c-.08-1.19-.635-1.29-.635-1.724 0-.315.274-.644.71-.644h1.583c.424 0 .554.214.554.695v2.785c0 .395.175.534.295.534.234 0 .435-.14.878-.584.544-.544.93-1.16.93-1.16.048-.08.224-.144.447-.144h1.75c.524 0 .635.264.524.624-.18.475-1.544 2.443-1.71 2.645z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <form class="contact-form" id="contactForm" aria-label="Форма записи на тренировку" novalidate>
                    <div class="form-group">
                        <input type="text" id="name" name="name" required placeholder=" " 
                               aria-label="Ваше имя" 
                               aria-required="true"
                               aria-describedby="name-hint"
                               autocomplete="name"
                               minlength="2" maxlength="100">
                        <label for="name">Ваше имя</label>
                        <span id="name-hint" class="visually-hidden">Введите ваше имя от 2 до 100 символов</span>
                    </div>
                    <div class="form-group">
                        <input type="tel" id="phone" name="phone" required placeholder=" " 
                               aria-label="Номер телефона" 
                               aria-required="true"
                               aria-describedby="phone-hint"
                               autocomplete="tel"
                               pattern="^(\+7|8)\d{10}$">
                        <label for="phone">Телефон</label>
                        <span id="phone-hint" class="visually-hidden">Введите номер телефона в формате +7 999 123-45-67</span>
                    </div>
                    <div class="form-group">
                        <select id="program" name="program" required 
                                aria-label="Выберите программу тренировок"
                                aria-required="true">
                            <option value="" disabled selected>Выберите вариант</option>
                            <option value="classic">Zumba Classic</option>
                            <option value="gold">Zumba Gold</option>
                            <option value="trial">🌟 Пробная тренировка</option>
                            <option value="single">🎟 Разовая тренировка</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea id="message" name="message" rows="4" placeholder=" " 
                                  aria-label="Сообщение (необязательно)"
                                  aria-describedby="message-hint"
                                  maxlength="500"></textarea>
                        <label for="message">Сообщение</label>
                        <span id="message-hint" class="visually-hidden">Необязательное поле, максимум 500 символов</span>
                    </div>
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="privacy" name="privacy" required 
                                   aria-required="true"
                                   aria-describedby="privacy-hint">
                            <span>Я согласен на <a href="privacy-policy.html" target="_blank" rel="noopener noreferrer">обработку персональных данных</a></span>
                        </label>
                        <span id="privacy-hint" class="visually-hidden">Согласие на обработку персональных данных обязательно для отправки формы</span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full" 
                            aria-label="Отправить заявку на тренировку">
                        Отправить заявку
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- CTA After Reviews -->
    <section class="reviews-cta">
        <div class="container">
            <div class="cta-content">
                <h2>Присоединяйтесь к нашей команде!</h2>
                <p>Более 5000 довольных клиентов уже с нами. Станьте частью сообщества!</p>
                <div class="cta-buttons">
                    <a href="#contact" class="btn btn-primary">Записаться на тренировку</a>
                    <a href="tel:+79218925157" class="btn btn-secondary">Позвонить</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Location Section -->
    <section id="location" class="location">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Как нас найти</h2>
                <div class="title-decoration"></div>
            </div>
            <div class="location-content">
                <div class="map-container">
                    <div id="yandex-map"></div>
                </div>
                <div class="location-info">
                    <div class="location-card">
                        <h3>📍 Зумба у залива</h3>
                        <p class="address">Санкт-Петербург, ул. Маршала Захарова, 20Д</p>
                        <div class="location-details">
                            <div class="detail-item">
                                <span class="detail-icon">🚇</span>
                                <span>Метро Проспект Ветеранов (10 мин)</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-icon">🅿️</span>
                                <span>Бесплатная парковка</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-icon">🏢</span>
                                <span>Вход со двора, 2 этаж</span>
                            </div>
                        </div>
                        <a href="https://yandex.ru/maps/org/zumba_u_zaliva/99077668985?si=0pxbzfcp104m4ggn3bjbnyrv3m" target="_blank" rel="noopener" class="btn btn-primary">
                            Построить маршрут
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="faq">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Частые вопросы</h2>
                <div class="title-decoration"></div>
            </div>
            <div class="faq-container">
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span>Что взять с собой на тренировку?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Возьмите с собой удобную форму, сменные кроссовки, бутылку негазированной воды. Приходите за 7-10 мин до начала тренировки, чтобы успеть переодеться.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span>Нужна ли подготовка для занятий зумба?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Подготовка не нужна. На занятия приходят люди с разным уровнем подготовки, иногда и вовсе без нее. Главное — желание, а я вам помогу.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span>Сколько калорий сжигается за тренировку?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>За одну тренировку зумба сжигается до 500 калорий.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span>Можно ли беременным заниматься?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Да, но только после консультации с врачом! Для беременных рекомендую Zumba Gold — щадящая программа без прыжков и высокой нагрузки. Обязательно предупредите меня о положении перед тренировкой.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span>Как записаться на пробное занятие?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Заполните форму на этой странице, позвоните по телефону +7 (921) 892-51-57 или напишите в Telegram. Пробная тренировка стоит 500₽ и длится 55 минут.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        <span>Есть ли абонементы?</span>
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Да, действуют абонементы: 8 занятий — 4800₽, 6 занятий — 3900₽, 4 занятия — 2800₽. Разовое посещение — 750₽. Пробная тренировка — 500₽.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="logo.png" alt="AM ZUMBA FIT" class="logo-image" width="150" height="75">
                </div>
                <p>© 2024 Александра Мельникова | Zumba Красносельский, Санкт-Петербург</p>
                <p class="footer-links">
                    <a href="https://t.me/ZumbaYugozapadSPB" target="_blank" rel="noopener">Telegram</a> •
                    <a href="https://vk.ru/radugaclub20" target="_blank" rel="noopener">ВКонтакте</a>
                </p>
                <p class="footer-legal">
                    <a href="privacy-policy.html" target="_blank">Политика конфиденциальности</a> •
                    <a href="offer.html" target="_blank">Публичная оферта</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Heart Particles Container -->
    <div id="hearts-container"></div>

    <!-- Quick Contact Button -->
    <div class="quick-contact">
        <a href="tel:+79218925157" class="quick-btn phone" aria-label="Позвонить">
            <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
            </svg>
        </a>
    </div>

    <!-- Scripts -->
    <script src="script.min.js"></script>
</body>
</html>
