<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ContactUsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = [
            [
                'name' => 'Facebook',
                'icon' => 'facebook.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Twitter',
                'icon' => 'twitter.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Customer Support',
                'icon' => 'customer-service.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'WhatsApp',
                'icon' => 'whatsapp.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Ensure storage directory exists
        if (!file_exists(public_path('storage'))) {
            mkdir(public_path('storage'), 0755, true);
        }

        foreach ($contacts as $key => $contact) {
            $image = $contact['icon'];
            $contacts[$key]['icon'] = 'storage/' . $image;
            $sourcePath = public_path('img/' . $image);
            $destinationPath = public_path('storage/' . $image);

            // Only try to copy if source file exists
            if (file_exists($sourcePath)) {
                copy($sourcePath, $destinationPath);
            } else {

                $contacts[$key]['icon'] = '';
            }
        }

        DB::table('contact_details')->insert($contacts);
    }
}
