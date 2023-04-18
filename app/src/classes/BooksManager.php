<?php

namespace App;

use PDO;
use Exception;

class BooksManager
{
    private PDO $connection;

    /**
     * __construct
     *
     * @param  PDO $connection
     * @return void
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * getAllBooks
     * Возвращает список авторов
     * @return array
     */
    public function getAllBooks(): array
    {
        try {
            $result = $this
                ->connection
                ->query('SELECT * FROM books')
                ->fetchAll();

            return $result;
        } catch (Exception $exception) {
            throw new Exception('Ошибка получения списка всех книг');
        }
    }

    /**
     * getBooksByAuthorId
     * Возвращает список книг конкретного автора
     * @param  int $id
     * @return array
     */
    public function getBooksByAuthorId(int $id): array
    {
        try {
            $stmt = $this
                ->connection
                ->prepare('SELECT * FROM books WHERE author_id = :id');

            $stmt->execute([
                'id' => $id
            ]);

            return $stmt->fetchAll();
        } catch (Exception $exception) {
            throw new Exception("Ошибка вывода списка книг автора с идентификатором {$id}");
        }
    }

    /**
     * genreIsExists
     * Проверяет наличие жанра в БД. Только для внутреннего использования
     * @param  mixed $genre
     * @return bool
     */
    private function genreIsExists(string $genre): bool
    {
        $normalizeGenre = ucfirst(strtolower($genre));

        try {
            $stmt = $this
                ->connection
                ->prepare('SELECT * FROM genres WHERE name = :name');

            $stmt->execute([
                'name' => $normalizeGenre
            ]);

            return !empty($stmt->fetch());
        } catch (Exception $exception) {
            throw new Exception('Ошибка проверки наличия жанра в БД');
        }
    }

    /**
     * getGenreIdByName
     * Возвращает идентификатор жанра. 
     * @param  string $genre
     * @return int
     */
    public function getGenreIdByName(string $genre): int
    {
        if (!$this->genreIsExists($genre)) {
            throw new Exception('Жанр в БД не найден');
        }

        $normalizeGenre = ucfirst(strtolower($genre));

        try {
            $stmt = $this
                ->connection
                ->prepare('SELECT id FROM genres WHERE name = :name');

            $stmt->execute([
                'name' => $normalizeGenre
            ]);

            return $stmt->fetch()['id'];
        } catch (Exception $exception) {
            throw new Exception('Ошибка выдачи идентификатора жанра');
        }
    }

    /**
     * addRowToGenresOfBook
     * Добавляет запись в таблицу genres_of_book
     * @param  int $genreId
     * @param  int $bookId
     * @return void
     */
    public function addRowToGenresOfBook(int $genreId, int $bookId): void
    {
        try {
            $stmt = $this
                ->connection
                ->prepare('INSERT INTO genres_of_book (book_id, genre_id) VALUES (:book_id, :genre_id)');
            $stmt->execute([
                'book_id' => $bookId,
                'genre_id' => $genreId
            ]);
            $stmt->fetch();
        } catch (Exception $exception) {
            throw new Exception('Ошибка добавления строки в genres_of_book');
        }
    }


    /**
     * getBookByOneGenreId
     * Возвращает список книг одного жанра. Только для внутреннего использования
     * @param  mixed $genreId
     * @return array
     */
    private function getBooksByOneGenreId(int $genreId): array
    {
        try {
            $stmt = $this
                ->connection
                ->prepare('SELECT * FROM genres_of_book WHERE genre_id = :id');

            $stmt->execute([
                'id' => $genreId
            ]);

            return $stmt->fetchAll();
        } catch (Exception $exception) {
            throw new Exception('Ошибка при выдаче списка книг одного жанра');
        }
    }

    /**
     * getListFromGenresOfBooks
     * Возвращает список из таблицы genres_of_book. Только для внутреннего использования
     * @return array
     */
    private function getListFromGenresOfBooks(): array
    {
        try {
            $result = $this
                ->connection
                ->query('SELECT * FROM genres_of_book')
                ->fetchAll();

            return $result;
        } catch (Exception $exception) {
            throw new Exception('Ошибка получения данных из таблицы genres_of_book');
        }
    }

    /**
     * getBookById
     * Возвращает книгк по идентификатору. Только для внутреннего использования
     * @param  mixed $id
     * @return array
     */
    private function getBookById(int $id): array
    {
        try {
            $stmt = $this
                ->connection
                ->prepare('SELECT * FROM books WHERE id = :id');

            $stmt->execute([
                'id' => $id
            ]);
            return $stmt->fetch();
        } catch (Exception $exception) {
            throw new Exception('Ошибка при поиске книги с идентификатором ' . $id);
        }
    }

    /**
     * getBooksByGenres
     * Возвращает список книг по указанным жанрам
     * @param  array $genres
     * @return array
     */
    public function getBooksByGenres($genres): array
    {
        $genreIds = array_map(fn($genre) => $this->getGenreIdByName($genre), $genres);
        $allBooksAndGenres = $this->getListFromGenresOfBooks();
        $bookIds = array_map(fn($book) => $book['book_id'], $allBooksAndGenres);

        $filteredBooks = array_reduce($genreIds, function ($acc, $genreId) {
            $current = $this->getBooksByOneGenreId($genreId);
            $currentIds = array_map(fn($book) => $book['book_id'], $current);
            return array_intersect($acc, $currentIds);
        }, $bookIds);

        $books = array_map(fn($bookId) => $this->getBookById($bookId), array_unique($filteredBooks));

        return $books;
    }

    /**
     * getBooksByPeriod
     * Возвращает список книг за указанный период
     * @param  string $start
     * @param  string $end
     * @return array
     */
    public function getBooksByPeriod(string $start, string $end): array
    {
        try {
            $stmt = $this
                ->connection
                ->prepare('SELECT * FROM books WHERE created_year BETWEEN :start AND :end');
            $stmt->execute([
                'start' => (string) $start,
                'end' => (string) $end
            ]);
            return $stmt->fetchAll();
        } catch (Exception $exception) {
            throw new Exception('Ошибка при посике книг за период');
        }
    }

    /**
     * getDescription
     * Возвращает описание книги. Для внутреннего использования.
     * @param  string $id
     * @return string
     */
    private function getDescription($id): string
    {
        return $this->getBookById($id)['description'];
    }

    /**
     * getYear
     * Возвращает год написания книги. Для внутреннего использования.
     * @param  int $id
     * @return string
     */
    private function getYear($id): string
    {
        return $this->getBookById($id)['created_year'];
    }

    /**
     * getName
     * Возвращает название книги. Для внутреннего использования.
     * @param  int $id
     * @return string
     */
    private function getName($id): string
    {
        return $this->getBookById($id)['name'];
    }

    /**
     * getAuthor
     * Возвращает имя автора. Для внутреннего использования.
     * @param  int $id
     * @return string
     */
    private function getAuthor(int $id): array
    {
        $authorId = $this->getBookById($id)['author_id'];

        try {
            $stmt = $this
                ->connection
                ->prepare('SELECT * FROM authors WHERE id = :id');

            $stmt->execute([
                'id' => $authorId
            ]);

            return $stmt->fetch();
        } catch (Exception $exception) {
            throw new Exception('Ошибка получения имени автора');
        }
    }

    /**
     * getCoAuthor
     * Возвращает имя соавтора, если он есть. Для внутреннего использования.
     * @param  int $id
     * @return string
     */
    private function getCoAuthor(int $id): string
    {
        $authorId = $this->getAuthor($id)['id'];

        try {
            $stmt = $this
                ->connection
                ->prepare('SELECT * FROM authors_of_book WHERE author_id <> :author AND book_id = :id');

            $stmt->execute([
                'author' => $authorId,
                'id' => $id
            ]);

            $coAuthor = $stmt->fetch();

            if ($coAuthor) {
                $stmt = $this
                    ->connection
                    ->prepare('SELECT * FROM authors WHERE id = :id');

                $stmt->execute([
                    'id' => $coAuthor['author_id']
                ]);

                return $stmt->fetch()['fullname'];
            } else {
                return '';
            }
        } catch (Exception $exception) {
            throw new Exception('Ошибка получения имени соавтора');
        }
    }

    /**
     * getGenreById
     * Возвращает название жанра по идентификатору. Для внутреннего использования
     * @param  int $id
     * @return string
     */
    private function getGenreById(int $id): string
    {
        try {
            return $this
                ->connection
                ->query("SELECT name FROM genres WHERE id = {$id}")->fetch()['name'];
        } catch (Exception $exception) {
            throw new Exception('Ошибка получения имени жанра');
        }
    }

    /**
     * getBookGenres
     * Возвращает список жаноров конкретной книги. Для внутреннего использования
     * @param  int $id
     * @return array
     */
    private function getBookGenres(int $id): array
    {
        try {
            $genresId = $this
                ->connection
                ->query("SELECT genre_id FROM genres_of_book WHERE book_id = {$id}")
                ->fetchAll();
            $result = [];
            foreach ($genresId as $genreId) {
                $result[] = $this->getGenreById($genreId['genre_id']);
            }
            return $result;
        } catch (Exception $exception) {
            throw new Exception('Ошибка получения жанров книги');
        }
    }

    /**
     * getInfo
     * Возвращает информацию о книге
     * @param  int $id
     * @return array
     */
    public function getInfo(int $id): array
    {
        return [
            'id' => $id,
            'title' => $this->getName($id),
            'description' => $this->getDescription($id),
            'created_year' => $this->getYear($id),
            'main_author' => $this->getAuthor($id),
            'co_author' => $this->getCoAuthor($id),
            'genres' => $this->getBookGenres($id)
        ];
    }

    /**
     * getBookByName
     * Возвращает книгу по названию. 
     * @param  string $name
     * @return array
     */
    public function getBookByName(string $name): array|bool
    {
        try {
            $stmt = $this
                ->connection
                ->prepare('SELECT * FROM books WHERE name = :name');
            $stmt->execute([
                'name' => $name
            ]);
            return $stmt->fetch();
        } catch (Exception $exception) {
            throw new Exception('Ошибка поиска книги по имени');
        }
    }

    /**
     * addRowToAuthorsOfBook
     * Добавляет запись в таблицу authors_of_book
     * @param  int $authorId
     * @param  int $bookId
     * @return void
     */
    public function addRowToAuthorsOfBook(int $authorId, int $bookId): void
    {
        try {

            $stmt = $this
                ->connection
                ->prepare("INSERT INTO authors_of_book VALUES (:book_id, :author_id)");
            $stmt->execute([
                'book_id' => $bookId,
                'author_id' => $authorId
            ]);
            $stmt->fetch();
        } catch (Exception $exception) {
            throw new Exception('Ошибка добавления записи в authors_of_book');
        }
    }
}