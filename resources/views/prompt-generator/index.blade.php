<x-layouts.app :title="'Prompt Generator'" :apiStatus="$apiStatus ?? null">
    <div class="page-body max-w-5xl mx-auto flex flex-col gap-8">
        <div class="bg-white/95 backdrop-blur-xl border border-white/20 shadow-[0_8px_30px_rgb(0,0,0,0.04)] w-full p-8 rounded-[32px]">
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-heading font-black text-sm transition-all" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10B981;">
                        1
                    </div>
                    <h2 class="text-[22px] font-bold text-[#111827]">Agent 1: Prompt Builder</h2>
                </div>
                <p class="text-[15px] text-[#4B5563] ml-13">Upload a Project Brief, Assignment Brief, or Assessment Guide to automatically generate a structured grading prompt for the Auto Grader.</p>
            </div>
            
            <form method="POST" action="{{ route('prompt.generator.generate') }}" enctype="multipart/form-data" 
                  x-data="{ 
                      loading: false, 
                      progress: 0, 
                      statusText: 'Analyzing document...',
                      startProgress() {
                          this.loading = true;
                          this.progress = 0;
                          
                          setTimeout(() => { this.statusText = 'Extracting assignment details...'; }, 2000);
                          setTimeout(() => { this.statusText = 'Generating grading criteria...'; }, 5000);
                          setTimeout(() => { this.statusText = 'Finalizing Auto-Grader prompt (this may take a minute)...'; }, 10000);
                          
                          let interval = setInterval(() => {
                              if (this.progress < 50) {
                                  this.progress += 2;
                              } else if (this.progress < 80) {
                                  this.progress += 0.5;
                              } else if (this.progress < 98) {
                                  this.progress += 0.1;
                              }
                          }, 500);
                      }
                  }" 
                  @submit.prevent="startProgress(); $event.target.submit();">
                @csrf

                <div class="flex flex-wrap gap-4 mb-6">
                    <!-- Brief File Box -->
                    <div class="bg-[#FDFDFD] border border-[#E5E7EB] rounded-2xl p-6 shadow-sm hover:border-emerald-500/40 transition-colors flex-1 w-full">
                        <label class="block font-bold text-[14px] text-[#111827] mb-1" for="brief_file">Upload Assignment Brief</label>
                        <p class="text-[12px] text-[#6B7280] mb-4">Select the PDF, DOCX, or TXT file containing the assignment instructions. (Max 50MB)</p>
                        <input id="brief_file" type="file" name="brief_file" accept=".pdf,.doc,.docx,.txt" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-[12px] file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" required @change="if($event.target.files[0] && $event.target.files[0].size > 50*1024*1024) { alert('File must not exceed 50MB.'); $event.target.value = ''; }">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-6 rounded-xl shadow-sm transition-colors" :disabled="loading">
                        <span x-show="!loading">Generate Prompt</span>
                        <span x-show="loading" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>

                <!-- Progress Bar -->
                <div x-show="loading" class="mt-8 bg-surface-panel p-6 rounded-2xl border border-border shadow-sm" style="display: none;" x-transition>
                    <div class="flex justify-between text-sm font-semibold text-txt-primary mb-2">
                        <span x-text="statusText"></span>
                        <span x-text="Math.round(progress) + '%'"></span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="bg-emerald-500 h-3 rounded-full transition-all duration-500 ease-out" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
            </form>

            @if (session('generated_prompt'))
                <div class="mt-10 border-t border-gray-100 pt-8" x-data="{ 
                    saveTitle: '', 
                    saving: false, 
                    saveSuccess: false,
                    saveError: '',
                    async savePrompt() {
                        if (!this.saveTitle.trim()) {
                            alert('Please enter a title for the prompt.');
                            return;
                        }
                        
                        this.saving = true;
                        this.saveError = '';
                        this.saveSuccess = false;
                        
                        // We need to create a text file from the textarea content to upload it
                        const content = document.getElementById('generated_prompt_text').value;
                        const blob = new Blob([content], { type: 'text/plain' });
                        const file = new File([blob], this.saveTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.txt', { type: 'text/plain' });
                        
                        const formData = new FormData();
                        formData.append('title', this.saveTitle);
                        formData.append('prompt_file', file);
                        
                        try {
                            const response = await fetch('/prompts', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                                }
                            });
                            
                            if (response.ok) {
                                this.saveSuccess = true;
                                this.saveTitle = '';
                            } else {
                                const data = await response.json();
                                this.saveError = data.message || 'Failed to save prompt';
                            }
                        } catch (e) {
                            this.saveError = e.message;
                        } finally {
                            this.saving = false;
                        }
                    }
                }">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-[20px] font-bold text-[#111827]">Generated Auto-Grader Prompt</h2>
                        <div class="flex gap-2">
                            <button class="bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 font-medium py-1.5 px-4 rounded-lg text-sm transition-colors" onclick="
                                const text = document.getElementById('generated_prompt_text').value;
                                const blob = new Blob([text], { type: 'application/msword' });
                                const url = window.URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.href = url;
                                a.download = 'Generated_Prompt.doc';
                                document.body.appendChild(a);
                                a.click();
                                document.body.removeChild(a);
                                window.URL.revokeObjectURL(url);
                            ">Download (.doc)</button>
                            <button class="bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 font-medium py-1.5 px-4 rounded-lg text-sm transition-colors" onclick="navigator.clipboard.writeText(document.getElementById('generated_prompt_text').value); alert('Copied to clipboard!')">Copy</button>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <textarea id="generated_prompt_text" class="w-full h-96 p-4 border border-gray-200 rounded-xl bg-gray-50 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" spellcheck="false" readonly>{{ session('generated_prompt') }}</textarea>
                        <p class="text-sm font-medium text-emerald-600 mt-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Your prompt was successfully generated! You can copy it or download it to save it.
                        </p>
                    </div>
                    
                    <div x-show="saveSuccess" class="mt-3 text-sm text-emerald-600 bg-emerald-50 p-3 rounded-lg border border-emerald-100" style="display: none;" x-transition>
                        Successfully saved! You can now use this prompt in the <a href="{{ route('ai.evaluator') }}" class="font-bold underline">AI Evaluator</a>.
                    </div>
                    <div x-show="saveError" class="mt-3 text-sm text-red-600 bg-red-50 p-3 rounded-lg border border-red-100" style="display: none;" x-text="saveError" x-transition></div>
                    
                </div>
            @endif

        </div>
    </div>
</x-layouts.app>
