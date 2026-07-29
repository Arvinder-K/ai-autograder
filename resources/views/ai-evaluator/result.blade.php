<x-layouts.app :title="'AI Evaluation Result'">
    <div class="page-header">
        <div>
            <h1 class="font-heading font-bold text-title-lg">AI Evaluation Result</h1>
            <p class="text-body-sm text-txt-muted mt-1">Review the evaluation and download the report as PDF or DOCX.</p>
        </div>

        @unless(isset($forPdf) && $forPdf)
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <a href="{{ route('ai.evaluator.download.pdf', $evaluation) }}" class="btn-secondary" download>Download Report (.txt)</a>
                <a href="{{ route('ai.evaluator.download.docx', $evaluation) }}" class="btn-secondary" download>Download Report (.docx)</a>
                <a href="{{ route('ai.evaluator') }}" class="btn-ghost">Back to Evaluator</a>
            </div>

        @endunless
    </div>

    <div class="page-body grid gap-6">
    <div class="page-body grid gap-6">
        @include('ai-evaluator._report')
    </div>
    </div>
</x-layouts.app>
