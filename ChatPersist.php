<?php

class ChatPersist
{

    /**
     * @var SQLite3
     */
    private $db;
    private $persistStatement;
    private $retrieveStatement;

    /**
     * ChatPersist constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->db = new SQLite3($name . ".sqlite", SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        $this->db->query('CREATE TABLE IF NOT EXISTS "messages" (
                                "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                                "message" VARCHAR(255),
                                "time" DATETIME)');

        $this->persistStatement = $this->db->prepare('INSERT INTO "messages" ("message", "time") VALUES (:message, :time)');
        $this->retrieveStatement = $this->db->prepare('SELECT * FROM "messages" ORDER BY "id" DESC LIMIT :last');
    }

    public function __destruct()
    {
        $this->db->close();
    }

    public function persist(string $message): void
    {
        $this->persistStatement->bindValue(':message', $message);
        $this->persistStatement->bindValue(':time', date('Y-m-d H:i:s'));
        $this->persistStatement->execute();
    }

    public function find(int $last): array
    {
        $history = [];
        $this->retrieveStatement->bindValue(':last', $last);
        $result = $this->retrieveStatement->execute();
        while (($row = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
            $history[] =  $row['message'];
        }
        return $history;
    }
}