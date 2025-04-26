<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OptimizeLeadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:optimize 
                            {--analyze : Analyze tables and update statistics}
                            {--indexes : Verify and add missing indexes}
                            {--vacuum : Run vacuum full on tables (PostgreSQL only)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the leads database for performance with millions of records';

    /**
     * Tables that should be optimized.
     *
     * @var array
     */
    protected $tables = [
        'leads',
        'lead_tag',
        'lead_lead_source',
        'notes',
        'activities',
    ];

    /**
     * Expected indexes for high performance.
     *
     * @var array
     */
    protected $expectedIndexes = [
        'leads' => [
            ['email'],
            ['personal_phone'],
            ['first_name', 'last_name'],
            ['status_id', 'created_at'],
            ['nationality_id', 'residence_country_id'],
            ['created_by'],
            ['updated_by'],
            ['created_at'],
            ['gender'],
            ['dob'],
        ],
        'lead_tag' => [
            ['lead_id'],
            ['tag_id'],
        ],
        'lead_lead_source' => [
            ['lead_id'],
            ['lead_source_id'],
        ],
        'notes' => [
            ['lead_id'],
            ['created_by'],
            ['created_at'],
        ],
        'activities' => [
            ['lead_id'],
            ['lead_id', 'activity_date'],
            ['activity_type_id', 'activity_date'],
            ['created_by'],
            ['activity_date'],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('analyze') && !$this->option('indexes') && !$this->option('vacuum')) {
            $this->info('Please specify at least one optimization option:');
            $this->line('--analyze : Analyze tables and update statistics');
            $this->line('--indexes : Verify and add missing indexes');
            $this->line('--vacuum : Run vacuum full on tables (PostgreSQL only)');
            return;
        }

        // Check database connection to determine database type
        $connection = DB::connection();
        $driverName = $connection->getDriverName();

        if ($this->option('indexes')) {
            $this->verifyIndexes();
        }

        if ($this->option('analyze')) {
            $this->analyzeDatabase($driverName);
        }

        if ($this->option('vacuum') && $driverName === 'pgsql') {
            $this->vacuumDatabase();
        } elseif ($this->option('vacuum') && $driverName !== 'pgsql') {
            $this->warn('Vacuum operation is only available for PostgreSQL databases.');
        }

        $this->info('Optimization complete!');
    }

    /**
     * Verify and create missing indexes.
     */
    protected function verifyIndexes()
    {
        $this->info('Verifying indexes...');
        
        foreach ($this->expectedIndexes as $table => $indexes) {
            $this->line("Checking indexes for table: $table");
            
            if (!Schema::hasTable($table)) {
                $this->warn("Table $table does not exist, skipping.");
                continue;
            }
            
            // Get existing indexes
            $existingIndexes = $this->getTableIndexes($table);
            
            foreach ($indexes as $columns) {
                $indexName = $this->generateIndexName($table, $columns);
                
                if (!$this->indexExists($existingIndexes, $columns)) {
                    $this->createIndex($table, $columns);
                } else {
                    $this->line("  - Index for " . implode(', ', $columns) . " already exists.");
                }
            }
        }
    }

    /**
     * Analyze database to update statistics.
     */
    protected function analyzeDatabase($driverName)
    {
        $this->info('Analyzing database tables...');
        
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                $this->warn("Table $table does not exist, skipping analysis.");
                continue;
            }
            
            $this->line("Analyzing table: $table");
            
            try {
                // Different syntax for different database engines
                if ($driverName === 'mysql') {
                    DB::statement("ANALYZE TABLE " . $table);
                } elseif ($driverName === 'pgsql') {
                    DB::statement("ANALYZE " . $table);
                } elseif ($driverName === 'sqlite') {
                    DB::statement("ANALYZE");
                } else {
                    $this->warn("Table analysis not supported for driver: $driverName");
                    break;
                }
            } catch (\Exception $e) {
                $this->error("Error analyzing table $table: " . $e->getMessage());
            }
        }
    }

    /**
     * Vacuum database (PostgreSQL only).
     */
    protected function vacuumDatabase()
    {
        $this->info('Vacuuming database tables...');
        
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                $this->warn("Table $table does not exist, skipping vacuum.");
                continue;
            }
            
            $this->line("Vacuuming table: $table");
            
            try {
                // Temporarily disable query log to avoid issues with vacuum
                DB::connection()->disableQueryLog();
                
                // Must be run outside of a transaction
                DB::statement("VACUUM (ANALYZE) " . $table);
                
                // Re-enable query log
                DB::connection()->enableQueryLog();
            } catch (\Exception $e) {
                $this->error("Error vacuuming table $table: " . $e->getMessage());
            }
        }
    }

    /**
     * Get all indexes for a table.
     */
    protected function getTableIndexes($table)
    {
        $connection = DB::connection();
        $driverName = $connection->getDriverName();
        
        $indexes = [];
        
        try {
            if ($driverName === 'mysql') {
                $result = DB::select("SHOW INDEX FROM $table");
                
                foreach ($result as $row) {
                    $indexName = $row->Key_name;
                    $column = $row->Column_name;
                    
                    if (!isset($indexes[$indexName])) {
                        $indexes[$indexName] = [];
                    }
                    
                    $indexes[$indexName][] = $column;
                }
            } elseif ($driverName === 'pgsql') {
                $result = DB::select("
                    SELECT
                        i.relname as index_name,
                        a.attname as column_name
                    FROM
                        pg_class t,
                        pg_class i,
                        pg_index ix,
                        pg_attribute a
                    WHERE
                        t.oid = ix.indrelid
                        AND i.oid = ix.indexrelid
                        AND a.attrelid = t.oid
                        AND a.attnum = ANY(ix.indkey)
                        AND t.relkind = 'r'
                        AND t.relname = '$table'
                    ORDER BY
                        i.relname,
                        a.attnum
                ");
                
                foreach ($result as $row) {
                    $indexName = $row->index_name;
                    $column = $row->column_name;
                    
                    if (!isset($indexes[$indexName])) {
                        $indexes[$indexName] = [];
                    }
                    
                    $indexes[$indexName][] = $column;
                }
            } elseif ($driverName === 'sqlite') {
                $result = DB::select("PRAGMA index_list($table)");
                
                foreach ($result as $row) {
                    $indexName = $row->name;
                    $indexColumns = DB::select("PRAGMA index_info($indexName)");
                    
                    $indexes[$indexName] = [];
                    foreach ($indexColumns as $column) {
                        $indexes[$indexName][] = $column->name;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("Error getting indexes for table $table: " . $e->getMessage());
        }
        
        return $indexes;
    }

    /**
     * Check if an index already exists.
     */
    protected function indexExists($existingIndexes, $columns)
    {
        foreach ($existingIndexes as $indexColumns) {
            // Check if this index covers exactly the same columns
            if (count($indexColumns) === count($columns)) {
                $matches = true;
                foreach ($columns as $column) {
                    if (!in_array($column, $indexColumns)) {
                        $matches = false;
                        break;
                    }
                }
                
                if ($matches) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Create an index on a table.
     */
    protected function createIndex($table, $columns)
    {
        $columnList = implode(', ', $columns);
        $indexName = $this->generateIndexName($table, $columns);
        
        $this->line("  - Creating index on $columnList");
        
        try {
            Schema::table($table, function ($table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
            
            $this->info("  - Index created successfully");
        } catch (\Exception $e) {
            $this->error("  - Error creating index: " . $e->getMessage());
        }
    }

    /**
     * Generate a consistent index name.
     */
    protected function generateIndexName($table, $columns)
    {
        return $table . '_' . implode('_', $columns) . '_idx';
    }
}