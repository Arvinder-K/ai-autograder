<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AIAssignmentEvaluation;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        $evaluations = AIAssignmentEvaluation::with('savedPrompt')->get();
        $chartData = [];

        foreach ($evaluations as $evaluation) {
            $studentName = $evaluation->student_name ?? 'Unknown Learner';
            $promptTitle = $evaluation->savedPrompt ? $evaluation->savedPrompt->title : ($evaluation->assignment_name ?? 'Unknown Assignment');
            $moduleName = $evaluation->savedPrompt ? ($evaluation->savedPrompt->module_name ?: 'Uncategorized') : 'Uncategorized';
            
            $score = 0;
            $report = json_decode($evaluation->evaluation_report, true);
            
            if (is_array($report)) {
                if (isset($report['assignment_title'])) {
                    $assignmentName = $report['assignment_title'];
                }
                
                if (isset($report['summary']['earned_score'])) {
                    $score = (float) $report['summary']['earned_score'];
                } elseif (isset($report['overall_score'])) {
                    $score = (float) $report['overall_score'];
                } else {
                    // Try to calculate score manually from criteria feedback if missing
                    if (!empty($report['criteria_feedback'])) {
                        foreach ($report['criteria_feedback'] as $criteria) {
                            $scoreStr = $criteria['score'] ?? '';
                            if (preg_match('/(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)/', $scoreStr, $matches)) {
                                $score += (float) $matches[1];
                            }
                        }
                    }
                }
            }

            // Only show data if there is a matched saved_prompt record
            if ($score > 0 && strtolower($studentName) !== 'unknown learner' && $evaluation->savedPrompt) {
                $shortPrompt = strlen($promptTitle) > 25 ? substr($promptTitle, 0, 22) . '...' : $promptTitle;
                $label = $shortPrompt . ' (' . $studentName . ')';
                
                $chartData[] = [
                    'label' => $label,
                    'score' => $score,
                    'full_prompt' => $promptTitle,
                    'module_name' => $moduleName,
                    'student_name' => $studentName
                ];
            }
        }

        // Sort alphabetically by label so assignments appear in order
        usort($chartData, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        // Summary Statistics
        $totalEvaluations = count($chartData);
        $totalScore = array_sum(array_column($chartData, 'score'));
        $averageScore = $totalEvaluations > 0 ? round($totalScore / $totalEvaluations, 1) : 0;

        // Module Performance
        $moduleStats = [];
        foreach ($chartData as $data) {
            $mod = $data['module_name'];
            if (!isset($moduleStats[$mod])) {
                $moduleStats[$mod] = ['total' => 0, 'count' => 0];
            }
            $moduleStats[$mod]['total'] += $data['score'];
            $moduleStats[$mod]['count']++;
        }
        
        $moduleAverages = [];
        foreach ($moduleStats as $mod => $stats) {
            $moduleAverages[] = [
                'module' => $mod,
                'average' => round($stats['total'] / $stats['count'], 1)
            ];
        }

        return view('admin.analytics.index', compact('chartData', 'totalEvaluations', 'averageScore', 'moduleAverages'));
    }
}
