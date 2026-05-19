<?php

namespace Database\Seeders;

use App\Models\QuizQuestion;
use App\Models\QuizSection;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'name' => 'Process Overview',
                'slug' => 'process-overview',
                'description' => 'Define the scope and type of business process',
                'icon' => 'clipboard-list',
                'sort_order' => 1,
                'questions' => [
                    ['question_text' => 'Is this a single process or multi-process system?', 'question_type' => 'single_choice', 'options' => ['Single Process', 'Multi-Process'], 'sort_order' => 1],
                    ['question_text' => 'Provide a brief title for this project or initiative.', 'question_type' => 'text', 'sort_order' => 2],
                    ['question_text' => 'Describe the core business problem or opportunity this addresses.', 'question_type' => 'textarea', 'sort_order' => 3],
                    ['question_text' => 'What is the current state of this process?', 'question_type' => 'single_choice', 'options' => ['Fully Manual', 'Partially Automated', 'Automated but Needs Improvement', 'Greenfield / New Process'], 'sort_order' => 4],
                    ['question_text' => 'What is the expected timeline for delivery?', 'question_type' => 'single_choice', 'options' => ['1–3 Months (MVP)', '3–6 Months', '6–12 Months', '12+ Months'], 'sort_order' => 5],
                    ['question_text' => 'How many end users will use this system?', 'question_type' => 'single_choice', 'options' => ['1–10', '10–50', '50–200', '200–1000', '1000+'], 'sort_order' => 6],
                ],
            ],
            [
                'name' => 'Business Domain & Units',
                'slug' => 'business-domain',
                'description' => 'Select the industry domain and business units involved',
                'icon' => 'building-2',
                'sort_order' => 2,
                'questions' => [
                    ['question_text' => 'Select the primary industry domain.', 'question_type' => 'dropdown', 'options' => ['__dynamic_domains__'], 'sort_order' => 1],
                    ['question_text' => 'Select additional domains if this is a cross-domain initiative.', 'question_type' => 'multi_dropdown', 'options' => ['__dynamic_domains__'], 'is_required' => false, 'sort_order' => 2],
                    ['question_text' => 'Select the business units involved.', 'question_type' => 'multi_choice', 'options' => ['__dynamic_business_units__'], 'sort_order' => 3, 'help_text' => 'Business units will update based on your selected domain(s).'],
                    ['question_text' => 'Does this involve multiple geographic locations or regions?', 'question_type' => 'yes_no', 'sort_order' => 4],
                    ['question_text' => 'If multi-region, specify the regions or countries.', 'question_type' => 'textarea', 'is_required' => false, 'sort_order' => 5, 'conditional_on' => ['section' => 'business-domain', 'question_order' => 4, 'answer' => 'Yes']],
                ],
            ],
            [
                'name' => 'Stakeholders & Tasks',
                'slug' => 'stakeholders',
                'description' => 'Identify users, roles, and their key responsibilities',
                'icon' => 'users',
                'sort_order' => 3,
                'questions' => [
                    ['question_text' => 'List the primary user roles/actors in this system.', 'question_type' => 'textarea', 'sort_order' => 1, 'help_text' => 'e.g., Admin, Manager, Operator, Customer, Auditor'],
                    ['question_text' => 'Describe the key tasks each stakeholder performs.', 'question_type' => 'textarea', 'sort_order' => 2, 'help_text' => 'Map tasks to roles. e.g., Manager: approve requests, generate reports'],
                    ['question_text' => 'Is there an approval or review chain?', 'question_type' => 'yes_no', 'sort_order' => 3],
                    ['question_text' => 'Describe the approval workflow.', 'question_type' => 'textarea', 'is_required' => false, 'sort_order' => 4, 'conditional_on' => ['section' => 'stakeholders', 'question_order' => 3, 'answer' => 'Yes']],
                    ['question_text' => 'Are there external stakeholders (vendors, partners, customers)?', 'question_type' => 'yes_no', 'sort_order' => 5],
                    ['question_text' => 'Describe the external stakeholder interactions.', 'question_type' => 'textarea', 'is_required' => false, 'sort_order' => 6, 'conditional_on' => ['section' => 'stakeholders', 'question_order' => 5, 'answer' => 'Yes']],
                ],
            ],
            [
                'name' => 'Data & Compliance',
                'slug' => 'data-compliance',
                'description' => 'Define data handling, regulatory, and compliance needs',
                'icon' => 'shield',
                'sort_order' => 4,
                'questions' => [
                    ['question_text' => 'What types of data will this system handle?', 'question_type' => 'multi_choice', 'options' => ['Personal / PII Data', 'Financial / Transaction Data', 'Health / Medical Records', 'Intellectual Property', 'Operational / IoT Data', 'Public / Open Data', 'Confidential / Internal Only'], 'sort_order' => 1],
                    ['question_text' => 'Which regulatory frameworks apply?', 'question_type' => 'multi_choice', 'options' => ['GDPR', 'HIPAA', 'PCI-DSS', 'SOX', 'PDPA', 'ISO 27001', 'Industry-Specific', 'None / Unsure'], 'sort_order' => 2],
                    ['question_text' => 'Does data require encryption at rest and in transit?', 'question_type' => 'yes_no', 'sort_order' => 3],
                    ['question_text' => 'Are there data retention or archival policies?', 'question_type' => 'yes_no', 'sort_order' => 4],
                    ['question_text' => 'Describe specific data retention requirements.', 'question_type' => 'textarea', 'is_required' => false, 'sort_order' => 5, 'conditional_on' => ['section' => 'data-compliance', 'question_order' => 4, 'answer' => 'Yes']],
                    ['question_text' => 'Is multi-currency support required?', 'question_type' => 'yes_no', 'sort_order' => 6],
                    ['question_text' => 'Is GST/VAT/Tax calculation required?', 'question_type' => 'yes_no', 'sort_order' => 7],
                ],
            ],
            [
                'name' => 'AI Features',
                'slug' => 'ai-features',
                'description' => 'Define AI and intelligent automation capabilities',
                'icon' => 'sparkles',
                'sort_order' => 5,
                'questions' => [
                    ['question_text' => 'What AI capabilities are needed?', 'question_type' => 'multi_choice', 'options' => ['Natural Language Processing', 'Document Analysis & OCR', 'Predictive Analytics', 'Recommendation Engine', 'Chatbot / Conversational AI', 'Content Generation', 'Image / Video Analysis', 'Anomaly Detection', 'Process Automation (RPA)', 'Decision Support', 'None Required'], 'sort_order' => 1],
                    ['question_text' => 'Should AI agents operate autonomously or require human approval?', 'question_type' => 'single_choice', 'options' => ['Fully Autonomous', 'Human-in-the-Loop', 'Approval Required for All Actions', 'Mixed (depends on task)'], 'sort_order' => 2],
                    ['question_text' => 'What is the preferred AI provider?', 'question_type' => 'multi_choice', 'options' => ['OpenAI (GPT)', 'Azure OpenAI', 'Google Gemini', 'Anthropic Claude', 'AWS Bedrock', 'Open Source (LLaMA, Mistral)', 'No Preference'], 'sort_order' => 3],
                    ['question_text' => 'Is AI training on organization-specific data required?', 'question_type' => 'yes_no', 'sort_order' => 4],
                    ['question_text' => 'Describe the AI training data sources.', 'question_type' => 'textarea', 'is_required' => false, 'sort_order' => 5, 'conditional_on' => ['section' => 'ai-features', 'question_order' => 4, 'answer' => 'Yes']],
                ],
            ],
            [
                'name' => 'Integration & SSO',
                'slug' => 'integration-sso',
                'description' => 'Define integrations, APIs, and authentication methods',
                'icon' => 'plug',
                'sort_order' => 6,
                'questions' => [
                    ['question_text' => 'What authentication methods are required?', 'question_type' => 'multi_choice', 'options' => ['Microsoft SSO (Azure AD)', 'Google OAuth', 'Email/Password', 'SAML', 'LDAP / Active Directory', 'Multi-Factor Authentication (MFA)', 'API Key Authentication'], 'sort_order' => 1],
                    ['question_text' => 'What external systems need integration?', 'question_type' => 'multi_choice', 'options' => ['ERP (SAP, Oracle, etc.)', 'CRM (Salesforce, HubSpot, etc.)', 'Payment Gateway', 'Email / Messaging', 'Document Management', 'Accounting Software', 'HR / Payroll System', 'Custom APIs', 'None'], 'sort_order' => 2],
                    ['question_text' => 'What integration patterns are preferred?', 'question_type' => 'multi_choice', 'options' => ['REST APIs', 'GraphQL', 'Webhooks', 'Message Queues (RabbitMQ, Kafka)', 'File-based (CSV, SFTP)', 'Real-time (WebSocket)', 'Batch Processing'], 'sort_order' => 3],
                    ['question_text' => 'Is there an existing API gateway or middleware?', 'question_type' => 'yes_no', 'sort_order' => 4],
                    ['question_text' => 'Describe the existing middleware or gateway.', 'question_type' => 'textarea', 'is_required' => false, 'sort_order' => 5, 'conditional_on' => ['section' => 'integration-sso', 'question_order' => 4, 'answer' => 'Yes']],
                ],
            ],
            [
                'name' => 'Reporting & Analytics',
                'slug' => 'reporting-analytics',
                'description' => 'Define reporting, dashboards, and analytics requirements',
                'icon' => 'chart-bar',
                'sort_order' => 7,
                'questions' => [
                    ['question_text' => 'What types of reports are needed?', 'question_type' => 'multi_choice', 'options' => ['Operational Dashboards', 'Executive / KPI Summaries', 'Financial Reports', 'Compliance / Audit Reports', 'User Activity Reports', 'Custom / Ad-hoc Reports', 'Automated Scheduled Reports', 'None Required'], 'sort_order' => 1],
                    ['question_text' => 'What export formats are required?', 'question_type' => 'multi_choice', 'options' => ['PDF', 'Excel / CSV', 'PowerPoint', 'JSON / API', 'Email Digest', 'Print-ready'], 'sort_order' => 2],
                    ['question_text' => 'Is real-time data visualization required?', 'question_type' => 'yes_no', 'sort_order' => 3],
                    ['question_text' => 'Should reports support drill-down or filtering?', 'question_type' => 'yes_no', 'sort_order' => 4],
                    ['question_text' => 'Are there BI tools in use (Power BI, Tableau, etc.)?', 'question_type' => 'yes_no', 'sort_order' => 5],
                    ['question_text' => 'Specify the BI tools used.', 'question_type' => 'text', 'is_required' => false, 'sort_order' => 6, 'conditional_on' => ['section' => 'reporting-analytics', 'question_order' => 5, 'answer' => 'Yes']],
                ],
            ],
            [
                'name' => 'Architecture Readiness',
                'slug' => 'architecture-readiness',
                'description' => 'Define infrastructure, deployment, and scalability needs',
                'icon' => 'server',
                'sort_order' => 8,
                'questions' => [
                    ['question_text' => 'What is the preferred deployment model?', 'question_type' => 'single_choice', 'options' => ['Cloud (AWS, Azure, GCP)', 'On-Premises', 'Hybrid Cloud', 'Shared Hosting / cPanel', 'Containerized (Docker/K8s)'], 'sort_order' => 1],
                    ['question_text' => 'What is the preferred technology stack?', 'question_type' => 'multi_choice', 'options' => ['Laravel / PHP', 'React / Next.js', 'Vue.js / Nuxt.js', 'Angular', 'Node.js / Express', 'Python / Django / FastAPI', '.NET / C#', 'Java / Spring Boot', 'Mobile (React Native / Flutter)', 'No Preference'], 'sort_order' => 2],
                    ['question_text' => 'What database technology is preferred?', 'question_type' => 'multi_choice', 'options' => ['MySQL', 'PostgreSQL', 'Microsoft SQL Server', 'MongoDB', 'Redis (Cache)', 'Elasticsearch', 'No Preference'], 'sort_order' => 3],
                    ['question_text' => 'What scalability level is expected?', 'question_type' => 'single_choice', 'options' => ['Small (< 100 concurrent users)', 'Medium (100–1000 concurrent users)', 'Large (1000–10000 concurrent users)', 'Enterprise (10000+ concurrent users)'], 'sort_order' => 4],
                    ['question_text' => 'Is high availability (HA) or disaster recovery (DR) required?', 'question_type' => 'yes_no', 'sort_order' => 5],
                    ['question_text' => 'Is CI/CD pipeline required?', 'question_type' => 'yes_no', 'sort_order' => 6],
                    ['question_text' => 'Are there existing DevOps practices in place?', 'question_type' => 'yes_no', 'sort_order' => 7],
                ],
            ],
            [
                'name' => 'Workflow & Notifications',
                'slug' => 'workflow-notifications',
                'description' => 'Define workflow automation and notification needs',
                'icon' => 'bell',
                'sort_order' => 9,
                'questions' => [
                    ['question_text' => 'Are automated workflows required?', 'question_type' => 'yes_no', 'sort_order' => 1],
                    ['question_text' => 'Describe the key workflows.', 'question_type' => 'textarea', 'is_required' => false, 'sort_order' => 2, 'conditional_on' => ['section' => 'workflow-notifications', 'question_order' => 1, 'answer' => 'Yes']],
                    ['question_text' => 'What notification channels are needed?', 'question_type' => 'multi_choice', 'options' => ['Email', 'SMS', 'In-App Notifications', 'Push Notifications', 'Slack / Teams', 'Webhooks', 'None'], 'sort_order' => 3],
                    ['question_text' => 'Are SLA or deadline tracking features required?', 'question_type' => 'yes_no', 'sort_order' => 4],
                    ['question_text' => 'Is multi-language / localization support needed?', 'question_type' => 'yes_no', 'sort_order' => 5],
                    ['question_text' => 'Specify the languages required.', 'question_type' => 'textarea', 'is_required' => false, 'sort_order' => 6, 'conditional_on' => ['section' => 'workflow-notifications', 'question_order' => 5, 'answer' => 'Yes']],
                ],
            ],
            [
                'name' => 'Additional Requirements',
                'slug' => 'additional-requirements',
                'description' => 'Capture any additional context, constraints, or requirements',
                'icon' => 'list-plus',
                'sort_order' => 10,
                'questions' => [
                    ['question_text' => 'Are there existing documents describing the process? (You can upload them after this quiz)', 'question_type' => 'yes_no', 'sort_order' => 1],
                    ['question_text' => 'Are there any specific constraints or limitations?', 'question_type' => 'textarea', 'is_required' => false, 'sort_order' => 2],
                    ['question_text' => 'Are there any specific non-functional requirements (performance, security, etc.)?', 'question_type' => 'textarea', 'is_required' => false, 'sort_order' => 3],
                    ['question_text' => 'Any additional context or notes for user story generation?', 'question_type' => 'textarea', 'is_required' => false, 'sort_order' => 4],
                ],
            ],
        ];

        foreach ($sections as $sectionData) {
            $questions = $sectionData['questions'];
            unset($sectionData['questions']);

            $section = QuizSection::firstOrCreate(
                ['slug' => $sectionData['slug']],
                $sectionData
            );

            foreach ($questions as $qData) {
                $qData['quiz_section_id'] = $section->id;
                if (isset($qData['options'])) {
                    $qData['options'] = json_encode($qData['options']);
                }
                if (isset($qData['conditional_on'])) {
                    $qData['conditional_on'] = json_encode($qData['conditional_on']);
                }
                if (!isset($qData['is_required'])) {
                    $qData['is_required'] = true;
                }

                QuizQuestion::firstOrCreate(
                    [
                        'quiz_section_id' => $section->id,
                        'sort_order' => $qData['sort_order'],
                    ],
                    $qData
                );
            }
        }
    }
}
