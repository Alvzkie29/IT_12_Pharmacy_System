<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = DB::table('suppliers')->pluck('supplierID')->toArray();

        $products = [
            // ANTIBIOTICS
            ['productName' => 'Amoxil', 'genericName' => 'Amoxicillin', 'productWeight' => '500mg', 'dosageForm' => 'Capsule', 'category' => 'Antibiotic', 'description' => 'Broad-spectrum antibiotic for bacterial infections.'],
            ['productName' => 'Augmentin', 'genericName' => 'Amoxicillin + Clavulanic Acid', 'productWeight' => '625mg', 'dosageForm' => 'Tablet', 'category' => 'Antibiotic', 'description' => 'Used for resistant bacterial infections.'],
            ['productName' => 'Zithromax', 'genericName' => 'Azithromycin', 'productWeight' => '500mg', 'dosageForm' => 'Tablet', 'category' => 'Antibiotic', 'description' => 'Macrolide antibiotic used for respiratory infections.'],
            ['productName' => 'Flagyl', 'genericName' => 'Metronidazole', 'productWeight' => '500mg', 'dosageForm' => 'Tablet', 'category' => 'Antibiotic', 'description' => 'For anaerobic bacterial and protozoal infections.'],

            // VITAMINS
            ['productName' => 'Ceelin', 'genericName' => 'Ascorbic Acid', 'productWeight' => '100ml', 'dosageForm' => 'Syrup', 'category' => 'Vitamins', 'description' => 'Vitamin C supplement for children.'],
            ['productName' => 'Enervon', 'genericName' => 'Multivitamins + Vitamin C', 'productWeight' => '500mg', 'dosageForm' => 'Tablet', 'category' => 'Vitamins', 'description' => 'Daily multivitamin for adults.'],
            ['productName' => 'Neurobion', 'genericName' => 'Vitamin B1 + B6 + B12', 'productWeight' => '500mg', 'dosageForm' => 'Tablet', 'category' => 'Vitamins', 'description' => 'For nerve health and energy support.'],

            // ANALGESICS
            ['productName' => 'Biogesic', 'genericName' => 'Paracetamol', 'productWeight' => '500mg', 'dosageForm' => 'Tablet', 'category' => 'Analgesic', 'description' => 'For mild to moderate pain and fever.'],
            ['productName' => 'Tempra', 'genericName' => 'Paracetamol', 'productWeight' => '120mg/5ml', 'dosageForm' => 'Syrup', 'category' => 'Analgesic', 'description' => 'Children’s fever and pain reliever.'],
            ['productName' => 'Advil', 'genericName' => 'Ibuprofen', 'productWeight' => '200mg', 'dosageForm' => 'Capsule', 'category' => 'Analgesic', 'description' => 'NSAID for pain, fever, and inflammation.'],

            // PRESCRIPTION
            ['productName' => 'Norvasc', 'genericName' => 'Amlodipine', 'productWeight' => '5mg', 'dosageForm' => 'Tablet', 'category' => 'Prescription', 'description' => 'Calcium channel blocker for hypertension.'],
            ['productName' => 'Losartan', 'genericName' => 'Losartan Potassium', 'productWeight' => '50mg', 'dosageForm' => 'Tablet', 'category' => 'Prescription', 'description' => 'Angiotensin receptor blocker for high blood pressure.'],
            ['productName' => 'Metformin', 'genericName' => 'Metformin Hydrochloride', 'productWeight' => '500mg', 'dosageForm' => 'Tablet', 'category' => 'Prescription', 'description' => 'First-line treatment for type 2 diabetes.'],
            ['productName' => 'Lipitor', 'genericName' => 'Atorvastatin', 'productWeight' => '20mg', 'dosageForm' => 'Tablet', 'category' => 'Prescription', 'description' => 'Statin used to lower cholesterol levels.'],

            // TOPICAL
            ['productName' => 'Betnovate', 'genericName' => 'Betamethasone', 'productWeight' => '15g', 'dosageForm' => 'Cream', 'category' => 'Prescription', 'description' => 'Steroid cream for skin inflammation.'],
            ['productName' => 'Fucidin', 'genericName' => 'Fusidic Acid', 'productWeight' => '15g', 'dosageForm' => 'Cream', 'category' => 'Antibiotic', 'description' => 'Topical antibiotic for skin infections.'],
            ['productName' => 'Ketoconazole', 'genericName' => 'Ketoconazole', 'productWeight' => '15g', 'dosageForm' => 'Cream', 'category' => 'Prescription', 'description' => 'Antifungal treatment for skin infections.'],
        ];

        foreach ($products as &$product) {
            $product['supplierID'] = $suppliers[array_rand($suppliers)];
            $product['created_at'] = now();
            $product['updated_at'] = now();
        }

        DB::table('products')->insert($products);
    }
}
