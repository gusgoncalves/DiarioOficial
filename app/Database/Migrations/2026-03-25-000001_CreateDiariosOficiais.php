<?php
// app/Database/Migrations/2026-03-25-000001_CreateDiariosOficiais.php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateDiariosOficiais extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
            'edition'      => ['type' => 'INT', 'unsigned' => true],
            'edition_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'published_at' => ['type' => 'DATE'],
            'file_size'    => ['type' => 'BIGINT', 'unsigned' => true],
            'pdf_url'      => ['type' => 'TEXT', 'null' => true],
            'indexado'     => ['type' => 'TINYINT', 'default' => 0],
            'created_at'   => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'   => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('published_at');
        $this->forge->addKey('indexado');
        $this->forge->createTable('diarios_oficiais');

        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'diario_id'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'termo_buscado'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'pagina'         => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'trecho'         => ['type' => 'TEXT', 'null' => true],
            'encontrado_em'  => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('diario_id');
        $this->forge->addKey('termo_buscado');
        $this->forge->createTable('diarios_busca_resultado');

        $this->forge->addField([
            'id'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'diario_id' => ['type' => 'VARCHAR', 'constraint' => 50],
            'pagina'    => ['type' => 'INT', 'unsigned' => true],
            'texto'     => ['type' => 'LONGTEXT'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('diario_id');
        //this->forge->addFullTextKey('texto'); // permite busca FULLTEXT futuramente
        $this->forge->createTable('diarios_oficiais_paginas');
    }

    public function down()
    {
        $this->forge->dropTable('diarios_busca_resultado');
        $this->forge->dropTable('diarios_oficiais');
    }
}