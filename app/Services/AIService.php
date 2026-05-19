<?php

namespace App\Services;

use App\Models\Configuration;
use App\Models\Story;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $provider;
    private string $model;
    private int $maxTokens;
    private float $temperature;

    public function __construct()
    {
        $this->provider = Configuration::getValue('ai', 'ai_provider', config('services.ai.provider', 'openai'));
        $this->model = Configuration::getValue('ai', 'ai_model', config('services.ai.model', 'gpt-4o'));
        $this->maxTokens = (int) Configuration::getValue('ai', 'ai_max_tokens', config('services.ai.max_tokens', 4096));
        $this->temperature = (float) Configuration::getValue('ai', 'ai_temperature', config('services.ai.temperature', 0.7));
    }

    public function generateUserStory(Story $story, array $quizData): string
    {
        $prompt = $this->buildUserStoryPrompt($story, $quizData);
        return $this->callAI($prompt);
    }

    public function generateFeatureList(Story $story, string $formatType, array $existingTables = []): string
    {
        $prompt = $this->buildFeatureListPrompt($story, $formatType, $existingTables);
        return $this->callAI($prompt);
    }

    public function generateTechnicalDesign(Story $story, array $featureData): string
    {
        $prompt = $this->buildTechnicalDesignPrompt($story, $featureData);
        return $this->callAI($prompt);
    }

    public function generateCodingPrompts(array $featureData, string $targetTool, int $maxTokensPerPrompt): array
    {
        $prompt = $this->buildCodingPromptRequest($featureData, $targetTool, $maxTokensPerPrompt);
        $result = $this->callAI($prompt);

        return $this->splitIntoMicroPrompts($result, $maxTokensPerPrompt);
    }

    public function analyzeDocument(string $documentContent): string
    {
        $prompt = "Analyze the following business process document and extract:\n"
            . "1. Key business processes described\n"
            . "2. Stakeholders and their roles\n"
            . "3. Data entities and relationships\n"
            . "4. Integration points\n"
            . "5. Business rules and constraints\n\n"
            . "Document content:\n" . mb_substr($documentContent, 0, 10000);

        return $this->callAI($prompt);
    }

    private function buildUserStoryPrompt(Story $story, array $quizData): string
    {
        $prompt = "You are an expert Business Analyst. Generate a comprehensive, detailed user story based on the following requirements gathered through a structured questionnaire.\n\n";
        $prompt .= "Project Title: {$story->title}\n";
        $prompt .= "Description: {$story->description}\n";
        $prompt .= "Process Type: {$story->process_type}\n\n";

        foreach ($quizData as $section => $questions) {
            $prompt .= "## " . ucfirst(str_replace('-', ' ', $section)) . "\n";
            foreach ($questions as $qa) {
                $answer = is_array($qa['answer']) ? implode(', ', $qa['answer']) : $qa['answer'];
                $prompt .= "- {$qa['question']}: {$answer}\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "\nGenerate a detailed user story that includes:\n";
        $prompt .= "1. Executive Summary\n";
        $prompt .= "2. Business Context & Problem Statement\n";
        $prompt .= "3. User Personas / Actors\n";
        $prompt .= "4. Detailed User Stories (As a [role], I want [goal], So that [benefit])\n";
        $prompt .= "5. Acceptance Criteria for each story\n";
        $prompt .= "6. Non-Functional Requirements\n";
        $prompt .= "7. Data Requirements\n";
        $prompt .= "8. Integration Requirements\n";
        $prompt .= "9. Assumptions and Constraints\n";

        return $prompt;
    }

    private function buildFeatureListPrompt(Story $story, string $formatType, array $existingTables): string
    {
        $prompt = "You are an expert Software Architect. Generate a detailed feature list from the following user story.\n\n";
        $prompt .= "User Story:\n{$story->generated_story}\n\n";

        if (!empty($existingTables)) {
            $prompt .= "Existing Database Tables (reuse where appropriate):\n";
            foreach ($existingTables as $table) {
                $prompt .= "- {$table['table_name']} ({$table['table_type']}): " . implode(', ', $table['columns']) . "\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "Include Admin & Config feature clusters:\n";
        $prompt .= "- User Management (roles, activate/deactivate)\n";
        $prompt .= "- Configuration (GST, Currency, Business Units, etc.)\n";
        $prompt .= "- Audit Logs\n\n";

        $format = match ($formatType) {
            'business' => "Format: Sr. No | Feature Cluster | Feature | Detailed Workflow | Feature Description | Table Name | Table Column Names",
            'procode' => "Format: Sr. No | Feature Cluster | Feature | Detailed Workflow | Feature Description | Table Name | Table Column Names | Technology Stack | Actor User",
            'agentic' => "Format: Sr. No | Feature Cluster | Feature | Detailed Workflow | Feature Description | Table Name | Table Column Names | Agent Type | Actor User",
        };

        $prompt .= "Output the feature list in this tabular format:\n{$format}\n\n";
        $prompt .= "Return the data as a JSON array of objects with the column names as keys.\n";
        $prompt .= "Ensure every feature maps to a database table. Include step numbers for workflow sequencing.";

        return $prompt;
    }

    private function buildTechnicalDesignPrompt(Story $story, array $featureData): string
    {
        $prompt = "You are a Senior Technical Architect. Generate a comprehensive technical design document.\n\n";
        $prompt .= "User Story: {$story->title}\n{$story->generated_story}\n\n";
        $prompt .= "Feature Data:\n" . json_encode($featureData, JSON_PRETTY_PRINT) . "\n\n";
        $prompt .= "Generate:\n";
        $prompt .= "1. System Architecture Overview\n";
        $prompt .= "2. Component Design\n";
        $prompt .= "3. Database Schema (Master vs Transactional tables, PKs, FKs)\n";
        $prompt .= "4. API Design\n";
        $prompt .= "5. Security Architecture\n";
        $prompt .= "6. Integration Architecture\n";
        $prompt .= "7. Deployment Architecture\n";
        $prompt .= "8. Technical Presentation Summary (suitable for slides)\n";

        return $prompt;
    }

    private function buildCodingPromptRequest(array $featureData, string $targetTool, int $maxTokens): string
    {
        $prompt = "You are an expert prompt engineer for AI coding tools. Generate coding prompts for {$targetTool}.\n\n";
        $prompt .= "Features to implement:\n" . json_encode($featureData, JSON_PRETTY_PRINT) . "\n\n";
        $prompt .= "Rules:\n";
        $prompt .= "- Split into micro-prompts of approximately {$maxTokens} tokens each\n";
        $prompt .= "- Each prompt should build on the previous one\n";
        $prompt .= "- Include continuation context for chaining\n";
        $prompt .= "- Optimize for AI credit efficiency\n";
        $prompt .= "- Each prompt should produce testable output\n";

        return $prompt;
    }

    private function splitIntoMicroPrompts(string $content, int $maxTokensPerPrompt): array
    {
        $estimatedCharsPerToken = 4;
        $maxChars = $maxTokensPerPrompt * $estimatedCharsPerToken;
        $prompts = [];
        $segments = str_split($content, $maxChars);

        foreach ($segments as $i => $segment) {
            $prompts[] = [
                'sequence' => $i + 1,
                'content' => trim($segment),
                'is_continuation' => $i > 0,
            ];
        }

        return $prompts;
    }

    private function callAI(string $prompt): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.ai.api_key', env('OPENAI_API_KEY')),
                'Content-Type' => 'application/json',
            ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert software analyst and architect.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content', '');
            }

            Log::error('AI API Error', ['status' => $response->status(), 'body' => $response->body()]);
            return 'AI generation failed. Please check your API configuration and try again.';
        } catch (\Exception $e) {
            Log::error('AI Service Exception', ['message' => $e->getMessage()]);
            return 'AI service unavailable. Please try again later.';
        }
    }
}
