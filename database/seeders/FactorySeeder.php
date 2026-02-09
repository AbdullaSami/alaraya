<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Factory;
use App\Models\Client;

class FactorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        
        $factories = [
            // Factories for Client 1 (Al-Rajhi Industrial Group)
            [
                'client_id' => 1,
                'factory_name' => 'Al-Rajhi Steel Plant',
                'location' => 'Riyadh Industrial City',
                'contact_person' => 'Abdullah Mohammed',
                'contact_number' => '+966501112223',
                'loading_person' => 'Saeed Khalid',
                'loading_contact' => '+966501112224',
                'notes' => 'Main steel manufacturing facility',
            ],
            [
                'client_id' => 1,
                'factory_name' => 'Al-Rajhi Cement Factory',
                'location' => 'Jeddah Industrial Zone',
                'contact_person' => 'Mohammed Ahmed',
                'contact_number' => '+966501112225',
                'loading_person' => 'Ali Hassan',
                'loading_contact' => '+966501112226',
                'notes' => 'Cement production facility',
            ],
            [
                'client_id' => 1,
                'factory_name' => 'Al-Rajhi Chemical Plant',
                'location' => 'Dammam Industrial Area',
                'contact_person' => 'Khalid Omar',
                'contact_number' => '+966501112227',
                'loading_person' => 'Nasser Ali',
                'loading_contact' => '+966501112228',
                'notes' => 'Chemical processing plant',
            ],
            [
                'client_id' => 1,
                'factory_name' => 'Al-Rajhi Packaging Unit',
                'location' => 'Yanbu Industrial City',
                'contact_person' => 'Fahad Mohammed',
                'contact_number' => '+966501112229',
                'loading_person' => 'Turki Abdullah',
                'loading_contact' => '+966501112230',
                'notes' => 'Packaging and distribution center',
            ],
            // Factories for Client 2 (Saudi Manufacturing Co.)
            [
                'client_id' => 2,
                'factory_name' => 'Saudia Manufacturing Main Plant',
                'location' => 'Riyadh Industrial Area',
                'contact_person' => 'Ibrahim Hassan',
                'contact_number' => '+966502233445',
                'loading_person' => 'Yousef Mohammed',
                'loading_contact' => '+966502233446',
                'notes' => 'Primary manufacturing facility',
            ],
            [
                'client_id' => 2,
                'factory_name' => 'Saudia Manufacturing Assembly Unit',
                'location' => 'Dammam Industrial City',
                'contact_person' => 'Mahmoud Ali',
                'contact_number' => '+966502233447',
                'loading_person' => 'Abdulaziz Ahmed',
                'loading_contact' => '+966502233448',
                'notes' => 'Product assembly facility',
            ],
            [
                'client_id' => 2,
                'factory_name' => 'Saudia Manufacturing Storage',
                'location' => 'Jeddah Free Zone',
                'contact_person' => 'Sultan Khalid',
                'contact_number' => '+966502233449',
                'loading_person' => 'Mansour Omar',
                'loading_contact' => '+966502233450',
                'notes' => 'Storage and distribution center',
            ],
            // Factories for Client 3 (Gulf Trading Corporation)
            [
                'client_id' => 3,
                'factory_name' => 'Gulf Trading Warehouse 1',
                'location' => 'Riyadh Logistics Park',
                'contact_person' => 'Hamed Abdullah',
                'contact_number' => '+966503344556',
                'loading_person' => 'Faisal Mohammed',
                'loading_contact' => '+966503344557',
                'notes' => 'Main trading warehouse',
            ],
            [
                'client_id' => 3,
                'factory_name' => 'Gulf Trading Distribution Center',
                'location' => 'Dammam Port Area',
                'contact_person' => 'Rashad Ali',
                'contact_number' => '+966503344558',
                'loading_person' => 'Waleed Hassan',
                'loading_contact' => '+966503344559',
                'notes' => 'Distribution hub for eastern region',
            ],
            [
                'client_id' => 3,
                'factory_name' => 'Gulf Trading Storage Facility',
                'location' => 'Jeddah Industrial Zone',
                'contact_person' => 'Nayef Ahmed',
                'contact_number' => '+966503344560',
                'loading_person' => 'Bader Khalid',
                'loading_contact' => '+966503344561',
                'notes' => 'Storage facility for western region',
            ],
            // Factories for Client 4 (National Logistics Ltd.)
            [
                'client_id' => 4,
                'factory_name' => 'National Logistics Hub',
                'location' => 'Riyadh Central Area',
                'contact_person' => 'Samir Mohammed',
                'contact_number' => '+966504455667',
                'loading_person' => 'Khaled Ali',
                'loading_contact' => '+966504455668',
                'notes' => 'Main logistics hub',
            ],
            [
                'client_id' => 4,
                'factory_name' => 'National Logistics Storage',
                'location' => 'Dammam Industrial City',
                'contact_person' => 'Tariq Hassan',
                'contact_number' => '+966504455669',
                'loading_person' => 'Majed Abdullah',
                'loading_contact' => '+966504455670',
                'notes' => 'Storage and distribution facility',
            ],
            [
                'client_id' => 4,
                'factory_name' => 'National Logistics Center',
                'location' => 'Jeddah Free Zone',
                'contact_person' => 'Adnan Ahmed',
                'contact_number' => '+966504455671',
                'loading_person' => 'Ziad Omar',
                'loading_contact' => '+966504455672',
                'notes' => 'Central logistics operations',
            ],
            // Factories for Client 5 (Middle East Exporters)
            [
                'client_id' => 5,
                'factory_name' => 'ME Exporters Main Facility',
                'location' => 'Riyadh Export Zone',
                'contact_person' => 'Faris Mohammed',
                'contact_number' => '+966505566778',
                'loading_person' => 'Hussam Ali',
                'loading_contact' => '+966505566779',
                'notes' => 'Primary export facility',
            ],
            [
                'client_id' => 5,
                'factory_name' => 'ME Exporters Storage',
                'location' => 'Dammam Port Area',
                'contact_person' => 'Marwan Hassan',
                'contact_number' => '+966505566780',
                'loading_person' => 'Basil Abdullah',
                'loading_contact' => '+966505566781',
                'notes' => 'Port storage facility',
            ],
            [
                'client_id' => 5,
                'factory_name' => 'ME Exporters Distribution',
                'location' => 'Jeddah Industrial Area',
                'contact_person' => 'Rami Ahmed',
                'contact_number' => '+966505566782',
                'loading_person' => 'Sami Khalid',
                'loading_contact' => '+966505566783',
                'notes' => 'Distribution center for exports',
            ],
            [
                'client_id' => 5,
                'factory_name' => 'ME Exporters Processing',
                'location' => 'Yanbu Industrial City',
                'contact_person' => 'Karim Omar',
                'contact_number' => '+966505566784',
                'loading_person' => 'Yasser Ali',
                'loading_contact' => '+966505566785',
                'notes' => 'Export processing facility',
            ],
        ];

        foreach ($factories as $factory) {
            Factory::create($factory);
        }
    }
}
