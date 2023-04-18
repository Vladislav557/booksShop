<?php

namespace App;

use PDO;
use Exception;

class AuthorsManager
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
     * getAllAuthors
     * Возвращает список всех авторов
     * @return array
     */
    public function getAllAuthors(): array
    {
        try {
            $result = $this
                ->connection
                ->query('SELECT * FROM authors')
                ->fetchAll();

            return $result;
        } catch (Exception $exception) {
            throw new Exception('Ошибка при получении списка авторов');
        }
    }

    /**
     * isExists
     * Проверяет, создан ли автор с таким именем
     * @param  string $fullname
     * @return bool
     */
    public function isExists(string $fullname): bool
    {
        try {
            $stmt = $this
                ->connection
                ->prepare('SELECT * FROM authors WHERE fullname = :fullname');
            $stmt->execute([
                'fullname' => $fullname
            ]);
            $author = $stmt->fetch();
            return !empty($author);
        } catch (Exception $exception) {
            throw new Exception('Ошибка проверки существующего автора');
        }
    }

    /**
     * addNewAuthor
     * Добавляет нового автора
     * @param  string $fullname
     * @return bool
     */
    public function addNewAuthor(string $fullname): bool
    {
        if ($this->isExists($fullname)) {
            throw new Exception("Автор с именем {$fullname} уже есть в БД");
        } else {
            try {
                $stmt = $this
                    ->connection
                    ->prepare('INSERT INTO authors (fullname) VALUES (:fullname)');

                $stmt->execute([
                    'fullname' => $fullname
                ]);

                $stmt->fetch();

                return true;
            } catch (Exception $exception) {
                throw new Exception('Ошибка добавления нового автора');
            }
        }
    }

    /**
     * removeAuthor
     * Удаляет автора по идентификатору
     * @param  int $id
     * @return bool
     */
    public function removeAuthor(int $id): bool
    {
        try {
            $stmt = $this
                ->connection
                ->prepare('DELETE FROM authors WHERE id = :id');
            $stmt->execute([
                'id' => $id
            ]);
            return $stmt->fetch();
        } catch (Exception $exception) {
            throw new Exception('Ошибка удаления автора');
        }
    }

    /**
     * updateAuthor
     * Обновляет имя автора по идентификатору
     * @param  int $id
     * @param  string $fullname
     * @return bool
     */
    public function updateAuthor(int $id, string $fullname): bool
    {
        if ($this->isExists($fullname)) {
            throw new Exception("Автор с именем {$fullname} уже есть в БД");
        } else {
            try {
                $stmt = $this
                    ->connection
                    ->prepare('UPDATE authors SET fullname = :fullname WHERE id = :id');
                $stmt->execute([
                    'id' => $id,
                    'fullname' => $fullname
                ]);
                return $stmt->fetch();
            } catch (Exception $exception) {
                throw new Exception('Ошибка удаления автора');
            }
        }
    }

    /**
     * getIdAuthorByName
     * Возвращает идентификатор пользователя по имени
     * @param  string $fullname
     * @return int
     */
    public function getIdAuthorByName(string $fullname): int
    {
        try {
            $stmt = $this
                ->connection
                ->prepare('SELECT id FROM authors WHERE fullname = :fullname');
            $stmt->execute([
                'fullname' => $fullname
            ]);
            $author = $stmt->fetch();
            return $author['id'];
        } catch (Exception $exception) {
            throw new Exception('Ошибка поиска автора');
        }
    }
}