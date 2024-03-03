<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuizTableMigration extends Migration
{
    public function up()
    {

        $this->forge->addField([
            "id" => [
                "type" => "INT",
                "null" => false,
                "auto_increment" => true
            ],
            "question" => [
                "type" => "TEXT",
                "null" => true
            ],
            "correct" => [
                "type" => "VARCHAR",
                "null" => false,
                "constraint" => 255
            ],
            "answer_1" => [
                "type" => "VARCHAR",
                "null" => false,
                "constraint" => 255
            ],
            "answer_2" => [
                "type" => "VARCHAR",
                "null" => false,
                "constraint" => 255
            ],
            "answer_3" => [
                "type" => "VARCHAR",
                "null" => false,
                "constraint" => 255
            ],
            "answer_4" => [
                "type" => "VARCHAR",
                "null" => false,
                "constraint" => 255
            ],
        ]);

        $this->forge->addPrimaryKey("id");

        $this->forge->createTable("quiz_questions");
    }

    public function down()
    {
        $this->forge->dropTable("quiz_questions");
    }
}
