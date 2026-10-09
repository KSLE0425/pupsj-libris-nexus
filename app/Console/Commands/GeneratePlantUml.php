<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GeneratePlantUml extends Command
{
    protected $signature = 'db:uml';
    protected $description = 'Generate PlantUML database diagram';

    public function handle()
    {
        $schema = DB::getDoctrineSchemaManager();
        $tables = $schema->listTables();

        $this->line('@startuml');

        // Define entities
        foreach ($tables as $table) {
            $name = $table->getName();
            $this->line("entity $name {");
            foreach ($table->getColumns() as $column) {
                $type = $column->getType()->getName();
                $null = $column->getNotnull() ? '' : 'nullable';
                $this->line("  * {$column->getName()} : $type $null");
            }
            $pk = $table->getPrimaryKey();
            if ($pk) {
                $this->line("  --");
                $this->line("  == primary ==");
                foreach ($pk->getColumns() as $col) {
                    $this->line("  * $col");
                }
            }
            $this->line("}");
        }

        // Relationships
        foreach ($tables as $table) {
            $name = $table->getName();
            foreach ($table->getForeignKeys() as $fk) {
                $localCols = implode(', ', $fk->getLocalColumns());
                $refTable = $fk->getForeignTableName();
                $refCols = implode(', ', $fk->getForeignColumns());
                $this->line("$name ||--o{ $refTable : \"$localCols -> $refCols\"");
            }
        }

        $this->line('@enduml');
    }
}