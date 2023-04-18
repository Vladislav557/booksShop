# Описание
Сервис по работе с книгами.

# Технологии
- PHP
- MYSQL
- NGINX
- Docker
- Slim (для роутинга)

# Установка
У вас должен быть установлен docker, docker-compose.
1. Перейти в /app, набрать composer install для установки зависимостей 
1. Создание образа и контейнера - docker-compose up -d
2. Сервер открывает на локальном хосте http://localhost:80

После создания контейнера необходимо немного подожать (5-10 сек), пока загрузиться дамп БД с тестовыми данными\

# Описание БД
- books - информация о книгах
- authors - информация об авторах
- genres - список жанров
- genres_of_book - содержит идентификатор книги и идентификатор жанра (много жанров у одной книги)
- authors_of_book - содержит идентификатор книги и автора (много авторов у одной книги)

# Описание API

## Вывод всех авторов GET /authors 
Предоставляет список авторов из таблицы authors

## Добавление нового автора POST /authors/new-add
В теле post-запроса должно содержаться имя, которое будет добавлено в таблицы authors. Редиректит на /authors

## Удаление автора POST /authors/{id}/remove
В теле post-запроса должен быть идентификатор автора, которого нужно удалить. Редиректит на /authors

## Обновление автора POST /authors/{id}/update
В теле post-запроса должно быть новое имя. Редиректит на /authors

## Вывод списка книг GET /books
Предоставляет список книг из таблицы books

## Вывод списка книг одного автора GET /authors/{id}/books
Выводит список всех книг одного автора

## Вывод списка книг по жанрам GET /books/genres[/{params:.*}]
Выводи список книг по жанру (нескольким жанрам). Жанры разделяются знаком +. Пример /books/genres/Фентези+Хоррор

## Вывод книг за определенный период GET /books/period[/start={start:.*}&end={end:.*}]
Выводит список книг за определенный период. Пример /books/period/start=2000&end=2010

## Вывод информации о книге GET /books/{id}/info
Выводит подробную информацию о книге

## Добавление новой книги POST /books/new
В теле запроса должно быть имя (name), описание (description), дата издания (created_year), имя автора (author), список жанров через запятую (genres), имя соавтора если есть (coAuthor). Книга будет добалвена в том случае, если автор и жанр есть в списках, а так же отсуствует книга с таким же названием.

## Обновление информации о книге POST /books/{id}/edit
В теле запроса должно быть: идентификатор книги (id), имя (name), описание (description), дата издания (created_year), имя автора (author), список жанров через запятую (genres), имя соавтора если есть (coAuthor). Обновляет запись только в таблице books.