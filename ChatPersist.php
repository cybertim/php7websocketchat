<?php

class ChatPersist
{

    /**
     * @var SQLite3
     */
    private $db;
    private $statement;

    /**
     * ChatPersist constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->db = new SQLite3($name . "sqlite", SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        $this->db->query('CREATE TABLE IF NOT EXISTS "messages" (
                                "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                                "message" INTEGER,
                                "time" DATETIME)');

        $this->statement = $this->db->prepare('INSERT INTO "messages" ("message", "time") VALUES (:message, :time)');
    }

    public function __destruct()
    {
     $this->db->close();
    }

    public function persist(string $message) {
        $this->statement->bindValue(':message', $message);
        $this->statement->bindValue(':time', date('Y-m-d H:i:s'));
        $this->statement->execute();
    }
}