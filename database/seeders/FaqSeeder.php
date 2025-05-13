<?php

namespace Database\Seeders;

use App\Models\FAQ;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $faqs = [
            [
                'question' => 'What is Ayes?',
                'answer' => "Ayes is a service platform designed to...\n\n- Provide solution X\n- Offer feature Y\n- Deliver benefit Z"
            ],
            [
                'question' => 'How to Use Ayes',
                'answer' => "Using Ayes is simple:\n\n1. Create an account\n2. Log in\n3. Access the dashboard\n4. Start your booking process"
            ],
            [
                'question' => 'How do I cancel a booking?',
                'answer' => "To cancel:\n\n- Go to My Bookings\n- Select the booking\n- Click Cancel\n- Confirm action"
            ]
        ];

        foreach ($faqs as $faq) {
            FAQ::firstOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}
