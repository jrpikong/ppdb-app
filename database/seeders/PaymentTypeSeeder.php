<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{School, PaymentType};
use Illuminate\Support\Facades\DB;

class PaymentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💰 Creating Payment Types...');
        
        $schools = School::all();
        $created = 0;
        
        $paymentTypes = [
            [
                'code' => 'SAVING_SEAT',
                'name' => 'Saving Seat Payment',
                'description' => 'Non-refundable payment to secure your child\'s place in the admission process. Required before application submission.',
                'amount' => 2500000,
                'payment_stage' => 'pre_submission',
                'is_mandatory' => true,
                'is_refundable' => false,
                'is_active' => true,
            ],
            [
                'code' => 'REGISTRATION',
                'name' => 'Registration Fee',
                'description' => 'One-time registration fee for new students. Covers administrative processing and student records setup.',
                'amount' => 5000000,
                'payment_stage' => 'post_acceptance',
                'is_mandatory' => true,
                'is_refundable' => false,
                'is_active' => true,
            ],
            [
                'code' => 'DEVELOPMENT',
                'name' => 'Development Fee',
                'description' => 'Annual development fee supporting school facilities, infrastructure improvements, and educational programs.',
                'amount' => 10000000,
                'payment_stage' => 'post_acceptance',
                'is_mandatory' => true,
                'is_refundable' => false,
                'is_active' => true,
            ],
            [
                'code' => 'UNIFORM',
                'name' => 'Uniform Package',
                'description' => 'Complete uniform set including PE attire, formal wear, and school accessories.',
                'amount' => 3500000,
                'payment_stage' => 'enrollment',
                'is_mandatory' => true,
                'is_refundable' => false,
                'is_active' => true,
            ],
            [
                'code' => 'BOOKS',
                'name' => 'Book Package',
                'description' => 'Complete set of textbooks, workbooks, and learning materials for the academic year.',
                'amount' => 4000000,
                'payment_stage' => 'enrollment',
                'is_mandatory' => true,
                'is_refundable' => false,
                'is_active' => true,
            ],
            [
                'code' => 'TECHNOLOGY',
                'name' => 'Technology Fee',
                'description' => 'Annual fee for technology infrastructure, digital learning platforms, and IT support.',
                'amount' => 2000000,
                'payment_stage' => 'enrollment',
                'is_mandatory' => false,
                'is_refundable' => false,
                'is_active' => true,
            ],
        ];
        
        DB::beginTransaction();
        
        try {
            foreach ($schools as $school) {
                foreach ($paymentTypes as $type) {
                    PaymentType::create(array_merge($type, [
                        'school_id' => $school->id,
                        'bank_info' => [
                            'bank_name' => 'Bank Mandiri',
                            'account_number' => '137-0012345678-9',
                            'account_holder' => 'PT Veritas Intercultural School',
                            'swift_code' => 'BMRIIDJA',
                            'branch' => $school->city,
                        ],
                        'payment_instructions' => $this->getInstructions($type['name']),
                    ]));
                    $created++;
                }
                
                $this->command->info("  ✓ {$school->code}: 6 payment types created");
            }
            
            DB::commit();
            
            // Summary
            $this->command->newLine();
            $this->command->info('════════════════════════════════════════');
            $this->command->info('✅ PAYMENT TYPES SEEDING COMPLETE');
            $this->command->info('════════════════════════════════════════');
            $this->command->table(
                ['Stage', 'Type', 'Amount (IDR)', 'Mandatory'],
                [
                    ['Pre-Submission', 'Saving Seat', '2,500,000', 'Yes'],
                    ['Post-Acceptance', 'Registration', '5,000,000', 'Yes'],
                    ['Post-Acceptance', 'Development', '10,000,000', 'Yes'],
                    ['Enrollment', 'Uniform', '3,500,000', 'Yes'],
                    ['Enrollment', 'Books', '4,000,000', 'Yes'],
                    ['Enrollment', 'Technology', '2,000,000', 'No'],
                ]
            );
            $this->command->info("Total Payment Types: {$created}");
            $this->command->info("Mandatory Total: IDR 25,000,000 per student");
            $this->command->newLine();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("✗ Error: {$e->getMessage()}");
            throw $e;
        }
    }
    
    private function getInstructions(string $name): string
    {
        return <<<HTML
<h3>Payment Instructions for {$name}</h3>
<ol>
    <li>Make a bank transfer to the account details provided</li>
    <li>Use your application number as the transfer reference</li>
    <li>Upload clear photo/scan of the payment receipt</li>
    <li>Wait for verification (typically within 1-2 business days)</li>
</ol>
<p><strong>Note:</strong> Payment verification is required before proceeding to the next stage.</p>
HTML;
    }
}
