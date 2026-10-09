<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateSchemaUml extends Command
{
    protected $signature = 'db:uml';
    protected $description = 'Generate PlantUML diagram from information_schema';

    public function handle()
    {
        $database = DB::connection()->getDatabaseName();
        $tables = DB::select('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?', [$database]);

        $columnsByTable = [];
        $primaryKeys = [];
        $foreignKeys = [];

        foreach ($tables as $tbl) {
            $table = $tbl->TABLE_NAME;
            // Columns
            $cols = DB::select(
                'SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
                [$database, $table]
            );
            $columnsByTable[$table] = $cols;

            // Primary keys
            $pkCols = array_filter($cols, fn($c) => $c->COLUMN_KEY === 'PRI');
            if (!empty($pkCols)) {
                $primaryKeys[$table] = array_map(fn($c) => $c->COLUMN_NAME, $pkCols);
            }

            // Foreign keys
            $fks = DB::select(
                'SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
                [$database, $table]
            );
            if (!empty($fks)) {
                $foreignKeys[$table] = $fks;
            }
        }

        $this->line('@startuml');

        foreach ($columnsByTable as $table => $cols) {
            $this->line("entity $table {");
            foreach ($cols as $col) {
                $null = $col->IS_NULLABLE === 'YES' ? 'nullable' : '';
                $this->line("  * {$col->COLUMN_NAME} : {$col->DATA_TYPE} $null");
            }
            if (!empty($primaryKeys[$table])) {
                $this->line("  --");
                $this->line("  == primary ==");
                foreach ($primaryKeys[$table] as $pk) {
                    $this->line("  * $pk");
                }
            }
            $this->line("}");
        }

        foreach ($foreignKeys as $table => $fks) {
            foreach ($fks as $fk) {
                $local = $fk->COLUMN_NAME;
                $refTable = $fk->REFERENCED_TABLE_NAME;
                $refCol = $fk->REFERENCED_COLUMN_NAME;
                $this->line("$table ||--o{ $refTable : \"$local -> $refCol\"");
            }
        }

        $this->line('@enduml');
    }
}