<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the "Misc" category if it doesn't already exist
        if (!Category::where('category_title', 'Misc')->exists()) {
            Category::create([
                'category_title' => 'Misc',
            ]);
        }
    }
} 