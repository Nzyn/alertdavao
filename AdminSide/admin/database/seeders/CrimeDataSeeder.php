<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CrimeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Theft', 'Robbery', 'Assault', 'Burglary', 'Vandalism', 'Drug Related', 'Homicide'];
        $statuses = ['Pending', 'Under Investigation', 'Resolved', 'Closed'];
        $locations = ['Poblacion District 1', 'Poblacion District 2', 'Agdao', 'Buhangin', 'Paquibato', 'Toril', 'Calinan', 'Marilog', 'Baguio District'];
        
        // Generate 24 months of dummy crime data
        $startDate = Carbon::now()->subMonths(24);
        
        for ($month = 0; $month < 24; $month++) {
            $monthDate = $startDate->copy()->addMonths($month);
            
            // Generate 20-60 reports per month with some variation
            $reportsCount = rand(20, 60);
            
            for ($i = 0; $i < $reportsCount; $i++) {
                $randomDay = rand(1, $monthDate->daysInMonth);
                $createdAt = $monthDate->copy()->day($randomDay)->setTime(rand(0, 23), rand(0, 59));
                
                DB::table('reports')->insert([
                    'user_id' => 1,
                    'location_id' => rand(1, 9), // Assuming location IDs 1-9 exist
                    'report_type' => $types[array_rand($types)],
                    'title' => 'Sample Crime Report ' . uniqid(),
                    'description' => 'This is a sample crime report for statistical purposes.',
                    'date_reported' => $createdAt,
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt
                ]);
            }
        }
        
        $this->command->info('Created crime reports for the last 24 months.');
    }
}
