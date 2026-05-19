<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use App\Models\Domain;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    public function run(): void
    {
        $domains = [
            [
                'name' => 'Healthcare & Life Sciences',
                'slug' => 'healthcare',
                'icon' => 'heart-pulse',
                'units' => ['Patient Administration', 'Clinical Operations', 'Pharmacy', 'Laboratory', 'Billing & Insurance', 'Telemedicine', 'Medical Records', 'Compliance & Quality'],
            ],
            [
                'name' => 'Banking & Financial Services',
                'slug' => 'banking',
                'icon' => 'landmark',
                'units' => ['Retail Banking', 'Corporate Banking', 'Wealth Management', 'Treasury', 'Risk & Compliance', 'Payments & Settlements', 'Lending', 'Trade Finance'],
            ],
            [
                'name' => 'Insurance',
                'slug' => 'insurance',
                'icon' => 'shield-check',
                'units' => ['Underwriting', 'Claims Management', 'Policy Administration', 'Actuarial', 'Reinsurance', 'Agent Management', 'Customer Service'],
            ],
            [
                'name' => 'Education & EdTech',
                'slug' => 'education',
                'icon' => 'graduation-cap',
                'units' => ['Student Administration', 'Curriculum Management', 'Assessment & Grading', 'Learning Management', 'Admissions', 'Alumni Relations', 'Research Management', 'Faculty Management'],
            ],
            [
                'name' => 'Manufacturing',
                'slug' => 'manufacturing',
                'icon' => 'factory',
                'units' => ['Production Planning', 'Quality Control', 'Inventory Management', 'Supply Chain', 'Maintenance', 'Shop Floor', 'Procurement', 'Logistics'],
            ],
            [
                'name' => 'Retail & E-commerce',
                'slug' => 'retail',
                'icon' => 'shopping-cart',
                'units' => ['Point of Sale', 'Inventory', 'Order Management', 'Customer Loyalty', 'Merchandising', 'Warehouse', 'Returns & Refunds', 'Vendor Management'],
            ],
            [
                'name' => 'Logistics & Supply Chain',
                'slug' => 'logistics',
                'icon' => 'truck',
                'units' => ['Fleet Management', 'Warehouse Operations', 'Route Planning', 'Freight Management', 'Last Mile Delivery', 'Customs & Compliance', 'Inventory Tracking'],
            ],
            [
                'name' => 'Real Estate & Construction',
                'slug' => 'real-estate',
                'icon' => 'building',
                'units' => ['Property Management', 'Lease Administration', 'Project Management', 'Cost Estimation', 'Site Management', 'Facility Management', 'Sales & Marketing'],
            ],
            [
                'name' => 'Hospitality & Tourism',
                'slug' => 'hospitality',
                'icon' => 'concierge-bell',
                'units' => ['Reservations', 'Front Desk', 'Housekeeping', 'Food & Beverage', 'Event Management', 'Guest Services', 'Revenue Management'],
            ],
            [
                'name' => 'Media & Entertainment',
                'slug' => 'media',
                'icon' => 'film',
                'units' => ['Content Management', 'Production', 'Distribution', 'Advertising', 'Subscription Management', 'Rights Management', 'Analytics'],
            ],
            [
                'name' => 'Telecommunications',
                'slug' => 'telecom',
                'icon' => 'signal',
                'units' => ['Network Operations', 'Customer Management', 'Billing', 'Service Provisioning', 'Field Operations', 'Product Catalog', 'Partner Management'],
            ],
            [
                'name' => 'Energy & Utilities',
                'slug' => 'energy',
                'icon' => 'zap',
                'units' => ['Generation', 'Distribution', 'Metering', 'Customer Service', 'Asset Management', 'Regulatory Compliance', 'Trading'],
            ],
            [
                'name' => 'Agriculture & AgriTech',
                'slug' => 'agriculture',
                'icon' => 'sprout',
                'units' => ['Farm Management', 'Crop Planning', 'Supply Chain', 'Market Access', 'IoT & Sensors', 'Weather & Advisory', 'Finance & Insurance'],
            ],
            [
                'name' => 'Government & Public Sector',
                'slug' => 'government',
                'icon' => 'landmark-dome',
                'units' => ['Citizen Services', 'Tax Administration', 'Public Health', 'Law Enforcement', 'Permits & Licensing', 'Welfare & Benefits', 'Infrastructure'],
            ],
            [
                'name' => 'Legal & Compliance',
                'slug' => 'legal',
                'icon' => 'scale',
                'units' => ['Case Management', 'Document Management', 'Contract Management', 'Billing & Time Tracking', 'Compliance Monitoring', 'E-Discovery'],
            ],
            [
                'name' => 'Human Resources & Staffing',
                'slug' => 'hr',
                'icon' => 'users',
                'units' => ['Recruitment', 'Onboarding', 'Payroll', 'Performance Management', 'Learning & Development', 'Benefits Administration', 'Workforce Planning', 'Employee Self-Service'],
            ],
            [
                'name' => 'Marketing & Advertising',
                'slug' => 'marketing',
                'icon' => 'megaphone',
                'units' => ['Campaign Management', 'Digital Marketing', 'Content Creation', 'Analytics & Attribution', 'CRM', 'Social Media', 'Brand Management'],
            ],
            [
                'name' => 'Non-Profit & Social Enterprise',
                'slug' => 'non-profit',
                'icon' => 'hand-heart',
                'units' => ['Donor Management', 'Program Management', 'Volunteer Coordination', 'Grant Management', 'Impact Measurement', 'Fundraising'],
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'icon' => 'car',
                'units' => ['Vehicle Sales', 'After-Sales Service', 'Parts Management', 'Fleet Management', 'Dealer Management', 'Connected Vehicles'],
            ],
            [
                'name' => 'Aviation & Aerospace',
                'slug' => 'aviation',
                'icon' => 'plane',
                'units' => ['Flight Operations', 'Crew Management', 'Maintenance & Repair', 'Cargo', 'Passenger Services', 'Airport Operations', 'Safety & Compliance'],
            ],
            [
                'name' => 'Professional Services & Consulting',
                'slug' => 'professional-services',
                'icon' => 'briefcase',
                'units' => ['Project Management', 'Resource Planning', 'Time & Expense', 'Client Management', 'Knowledge Management', 'Billing & Invoicing'],
            ],
            [
                'name' => 'Information Technology',
                'slug' => 'it',
                'icon' => 'server',
                'units' => ['Service Desk', 'Infrastructure Management', 'Application Development', 'Security Operations', 'DevOps', 'Data Management', 'Cloud Services'],
            ],
        ];

        $sortOrder = 0;
        foreach ($domains as $domainData) {
            $units = $domainData['units'];
            unset($domainData['units']);
            $domainData['sort_order'] = $sortOrder++;

            $domain = Domain::firstOrCreate(
                ['slug' => $domainData['slug']],
                $domainData
            );

            $unitOrder = 0;
            foreach ($units as $unitName) {
                BusinessUnit::firstOrCreate(
                    ['domain_id' => $domain->id, 'slug' => \Str::slug($unitName)],
                    [
                        'domain_id' => $domain->id,
                        'name' => $unitName,
                        'slug' => \Str::slug($unitName),
                        'sort_order' => $unitOrder++,
                    ]
                );
            }
        }
    }
}
