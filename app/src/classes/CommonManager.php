<?php

namespace App;

use App\AuthorsManager;
use App\BooksManager;

use PDO;
use Exception;

class CommonManager
{
    private PDO $connection;
    public AuthorsManager $authorsManager;
    public BooksManager $booksManager;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->authorsManager = new AuthorsManager($this->connection);
        $this->booksManager = new BooksManager($this->connection);
    }

    public function toJson(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function addBook(string $name, string $description, string $createdYear, string $author, array $genres, ?string $coAuthor): void
    {
        if (!empty($this->booksManager->getBookByName($name))) {
            throw new Exception('Книга с таким названием уже существует');
        }
        try {
            $authorId = $this->authorsManager->getIdAuthorByName($author);
            $newBook = $this
                ->connection
                ->prepare('INSERT INTO books(name, description, created_year, author_id) VALUES (:name, :description, :createdYear, :authorId)');

            $newBook->execute([
                'name' => $name,
                'description' => $description,
                'createdYear' => $createdYear,
                'authorId' => $authorId
            ]);

            $newBook->fetch();

            $book = $this->booksManager->getBookByName($name);

            $bookId = $book['id'];

            $coAuthorId = $coAuthor ? $this->authorsManager->getIdAuthorByName($coAuthor) : null;

            $authors = [$authorId, $coAuthorId];

            $authorIds = array_filter($authors, fn($a) => !is_null($a));

            foreach ($authorIds as $a) {
                $stmt = $this
                    ->connection
                    ->prepare('INSERT INTO authors_of_book VALUES (:book_id, :author_id)');
                $stmt->execute([
                    'book_id' => $bookId,
                    'author_id' => $a
                ]);
                $stmt->fetch();
            }

            foreach ($genres as $genre) {
                $id = $this->booksManager->getGenreIdByName($genre);
                $this->booksManager->addRowToGenresOfBook($id, $bookId);
            }

        } catch (Exception $exception) {
            throw new Exception('Ошибка добавления новой книги - ' . $exception->getMessage());
        }

    }

    public function updateBook(int $id, string $name, string $description, string $createdYear, string $author, array $genres, ?string $coAuthor): void
    {
        $newAuthorId = $this->authorsManager->getIdAuthorByName($author);
        $stmt = $this
            ->connection
            ->prepare('UPDATE books SET name = :name, description = :description, created_year = :createdYear, author_id = :newAuthorId WHERE id = :id');

        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'createdYear' => $createdYear,
            'newAuthorId' => $newAuthorId,
            'id' => $id
        ]);
        $stmt->fetch();
    }

}