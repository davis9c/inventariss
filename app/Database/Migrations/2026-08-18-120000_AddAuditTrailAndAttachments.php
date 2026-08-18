<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuditTrailAndAttachments extends Migration
{
    public function up()
    {
        $this->forge->addColumn('assets', [
            'acquisition_source' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'acquisition_price'],
            'acquisition_date' => ['type' => 'DATE', 'null' => true, 'after' => 'acquisition_source'],
            'acquisition_document_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'acquisition_date'],
            'supplier_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'acquisition_document_number'],
            'funding_source' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'supplier_name'],
            'acquisition_notes' => ['type' => 'TEXT', 'null' => true, 'after' => 'funding_source'],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addColumn('stock_items', ['deleted_at' => ['type' => 'DATETIME', 'null' => true]]);
        $this->forge->addColumn('locations', ['deleted_at' => ['type' => 'DATETIME', 'null' => true]]);
        $this->forge->addColumn('categories', ['deleted_at' => ['type' => 'DATETIME', 'null' => true]]);
        $this->forge->addColumn('units', ['deleted_at' => ['type' => 'DATETIME', 'null' => true]]);
        $this->forge->addColumn('asset_mutations', ['deleted_at' => ['type' => 'DATETIME', 'null' => true]]);
        $this->forge->addColumn('stock_opnames', ['deleted_at' => ['type' => 'DATETIME', 'null' => true]]);
        $this->forge->addColumn('inventory_transactions', [
            'outbound_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'transaction_type'],
            'recipient_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'outbound_type'],
            'destination_unit' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'recipient_name'],
            'document_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'destination_unit'],
            'handed_over_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'document_number'],
            'received_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'handed_over_by'],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'owner_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'owner_id' => ['type' => 'INT', 'unsigned' => true],
            'document_type' => ['type' => 'VARCHAR', 'constraint' => 60],
            'document_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'document_date' => ['type' => 'DATE', 'null' => true],
            'valid_from' => ['type' => 'DATE', 'null' => true],
            'valid_until' => ['type' => 'DATE', 'null' => true],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true], 'updated_at' => ['type' => 'DATETIME', 'null' => true], 'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true); $this->forge->addKey(['owner_type', 'owner_id']); $this->forge->createTable('inventory_documents');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'owner_type' => ['type' => 'VARCHAR', 'constraint' => 30], 'owner_id' => ['type' => 'INT', 'unsigned' => true],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 255], 'original_name' => ['type' => 'VARCHAR', 'constraint' => 255], 'mime_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'caption' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true], 'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true], 'updated_at' => ['type' => 'DATETIME', 'null' => true], 'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true); $this->forge->addKey(['owner_type', 'owner_id']); $this->forge->createTable('inventory_photos');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true], 'transaction_id' => ['type' => 'INT', 'unsigned' => true],
            'evidence_type' => ['type' => 'VARCHAR', 'constraint' => 50], 'file_path' => ['type' => 'VARCHAR', 'constraint' => 255], 'original_name' => ['type' => 'VARCHAR', 'constraint' => 255], 'mime_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'notes' => ['type' => 'TEXT', 'null' => true], 'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true], 'created_at' => ['type' => 'DATETIME', 'null' => true], 'updated_at' => ['type' => 'DATETIME', 'null' => true], 'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true); $this->forge->addKey('transaction_id'); $this->forge->createTable('transaction_evidence');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true], 'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'role_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true], 'action' => ['type' => 'VARCHAR', 'constraint' => 40], 'module' => ['type' => 'VARCHAR', 'constraint' => 50],
            'record_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true], 'before_data' => ['type' => 'TEXT', 'null' => true], 'after_data' => ['type' => 'TEXT', 'null' => true], 'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true); $this->forge->addKey(['module', 'record_id']); $this->forge->addKey('created_at'); $this->forge->createTable('audit_logs');
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs'); $this->forge->dropTable('transaction_evidence'); $this->forge->dropTable('inventory_photos'); $this->forge->dropTable('inventory_documents');
    }
}
